<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\Sighting;
use App\Models\Face;
use App\Models\Shift;
use App\Models\Camera;
use Illuminate\Support\Facades\File;

class DashboardApiController extends Controller
{
    // =========================================================================
    // Attendance Logs
    // =========================================================================

    public function todayRecords(Request $request)
    {
        $limit = $request->query('limit', 20);
        $today = date('Y-m-d');

        $query = AttendanceRecord::where('date', $today)->orderBy('id', 'desc');
        $totalCount = $query->count();

        if ($limit > 0) {
            $query->limit($limit);
        }

        $records = $query->get()->map(function ($r) {
            return [
                'person_name' => $r->person_name,
                'event_type' => $r->event_type,
                'timestamp' => $r->timestamp->format('Y-m-d H:i:s'),
                'confidence' => round($r->confidence, 1),
                'snapshot_path' => $r->snapshot_path ?? '',
            ];
        });

        return response()->json([
            'records' => $records,
            'total_count' => $totalCount,
        ]);
    }

    public function currentlyInside()
    {
        $today = date('Y-m-d');
        $records = AttendanceRecord::where('date', $today)->orderBy('id', 'asc')->get();

        $state = [];
        foreach ($records as $r) {
            $state[$r->person_name] = $r->event_type;
        }

        $inside = [];
        foreach ($state as $name => $evt) {
            if ($evt === 'IN') {
                $inside[] = $name;
            }
        }

        return response()->json(['inside' => $inside]);
    }

    public function history(Request $request)
    {
        $dateFrom = $request->query('date_from', date('Y-m-d'));
        $dateTo = $request->query('date_to', date('Y-m-d'));

        $records = AttendanceRecord::where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'person_name' => $r->person_name,
                    'event_type' => $r->event_type,
                    'timestamp' => $r->timestamp->format('Y-m-d H:i:s'),
                    'confidence' => round($r->confidence, 1),
                    'snapshot_path' => $r->snapshot_path ?? '',
                ];
            });

        return response()->json(['records' => $records]);
    }

    public function personHistory(Request $request, $name)
    {
        $dateFrom = $request->query('date_from', date('Y-m-d'));
        $dateTo = $request->query('date_to', date('Y-m-d'));

        $records = AttendanceRecord::where('person_name', $name)
            ->where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'event_type' => $r->event_type,
                    'timestamp' => $r->timestamp->format('Y-m-d H:i:s'),
                    'confidence' => round($r->confidence, 1),
                    'snapshot_path' => $r->snapshot_path ?? '',
                ];
            });

        $sightings = Sighting::where('person_name', $name)
            ->where('date', '>=', $dateFrom)
            ->where('date', '<=', $dateTo)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($s) {
                return [
                    'timestamp' => $s->timestamp->format('Y-m-d H:i:s'),
                    'snapshot_path' => $s->snapshot_path ?? '',
                ];
            });

        return response()->json([
            'records' => $records,
            'sightings' => $sightings,
        ]);
    }

    // =========================================================================
    // Camera CRUD
    // =========================================================================

    public function getCameras()
    {
        $cameras = Camera::orderBy('id', 'asc')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'url' => $c->url,
                'enabled' => (bool)$c->enabled,
            ];
        });
        return response()->json(['cameras' => $cameras]);
    }

    public function addCamera(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'url' => 'required|string',
            'enabled' => 'nullable|boolean',
        ]);

        $camera = Camera::create([
            'name' => trim($validated['name']),
            'url' => trim($validated['url']),
            'enabled' => $validated['enabled'] ?? true,
        ]);

        return response()->json(['success' => true, 'camera' => $camera]);
    }

    public function deleteCamera($id)
    {
        $deleted = Camera::where('id', $id)->delete();
        if (!$deleted) {
            return response()->json(['error' => 'Camera not found'], 404);
        }
        return response()->json(['success' => true, 'deleted_id' => $id]);
    }

    // =========================================================================
    // Guard Shifts CRUD
    // =========================================================================

    public function getShifts()
    {
        $shifts = Shift::all()->map(function ($s) {
            return [
                'person_name' => $s->person_name,
                'shift_start' => $s->shift_start,
                'shift_end' => $s->shift_end,
                'days' => $s->days,
            ];
        });
        return response()->json(['shifts' => $shifts]);
    }

    public function saveShift(Request $request)
    {
        $validated = $request->validate([
            'person_name' => 'required|string',
            'shift_start' => 'required|string',
            'shift_end' => 'required|string',
            'days' => 'nullable|string',
        ]);

        $shift = Shift::updateOrCreate(
            ['person_name' => $validated['person_name']],
            [
                'shift_start' => $validated['shift_start'],
                'shift_end' => $validated['shift_end'],
                'days' => $validated['days'] ?? 'mon,tue,wed,thu,fri',
            ]
        );

        return response()->json(['success' => true, 'shift' => $shift]);
    }

    public function deleteShift($name)
    {
        $deleted = Shift::where('person_name', $name)->delete();
        if (!$deleted) {
            return response()->json(['error' => 'Shift not found'], 404);
        }
        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Face Registration & People Query
    // =========================================================================

    public function getPeople()
    {
        // Return a distinct list of registered people and their sync statuses
        // Matching cPanel server response fields: name, image_path, face_count, status, error_reason
        $people = Face::all()->groupBy('name')->map(function ($group, $name) {
            $first = $group->first();
            $syncedCount = $group->where('status', 'synced')->count();
            return [
                'name' => $name,
                'image_path' => $first->image_path ?? '',
                'face_count' => $group->count(),
                'status' => $first->status,
                'error_reason' => $first->error_reason ?? '',
            ];
        })->values();

        return response()->json(['people' => $people]);
    }

    public function registerFace(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'image' => 'required|file|image',
        ]);

        $name = trim($validated['name']);
        $imageFile = $request->file('image');

        $count = Face::where('name', $name)->count();
        $safeName = str_replace([' ', '/'], '_', $name);
        $filename = "{$safeName}_" . ($count + 1) . ".jpg";

        // Save into public/data/registered_faces/
        $regDir = public_path('data/registered_faces');
        File::ensureDirectoryExists($regDir);
        $imageFile->move($regDir, $filename);

        $imgRelPath = "registered_faces/{$filename}";

        $face = Face::create([
            'name' => $name,
            'image_path' => $imgRelPath,
            'embedding' => null,
            'status' => 'pending',
        ]);

        $newCount = $count + 1;
        return response()->json([
            'success' => true,
            'name' => $name,
            'current_images_count' => $newCount,
            'required_images' => 3,
            'is_complete' => $newCount >= 3,
        ]);
    }

    public function deletePerson($name)
    {
        $faces = Face::where('name', $name)->get();
        foreach ($faces as $face) {
            if ($face->image_path) {
                $fullPath = public_path('data/' . $face->image_path);
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
            $face->delete();
        }

        // Delete shift also
        Shift::where('person_name', $name)->delete();

        return response()->json(['success' => true]);
    }
}
