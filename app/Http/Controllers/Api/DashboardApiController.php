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
                'late_minutes' => $r->late_minutes ?? 0,
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
                    'late_minutes' => $r->late_minutes ?? 0,
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
                    'late_minutes' => $r->late_minutes ?? 0,
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
    // Guard Shifts CRUD (recurring + temporary)
    // =========================================================================

    public function getShifts()
    {
        $shifts = Shift::all()->map(function ($s) {
            return [
                'person_name' => $s->person_name,
                'shift_start' => $s->shift_start,
                'shift_end' => $s->shift_end,
                'days' => $s->days,
                'date' => $s->date,
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
            'date' => 'nullable|string',  // "YYYY-MM-DD" for temp shifts
        ]);

        $dateVal = $validated['date'] ?? null;
        if ($dateVal === '') $dateVal = null;

        $shift = Shift::updateOrCreate(
            [
                'person_name' => $validated['person_name'],
                'date' => $dateVal,
            ],
            [
                'shift_start' => $validated['shift_start'],
                'shift_end' => $validated['shift_end'],
                'days' => $validated['days'] ?? 'mon,tue,wed,thu,fri',
            ]
        );

        return response()->json(['success' => true, 'shift' => $shift]);
    }

    public function deleteShift(Request $request, $name)
    {
        $dateVal = $request->query('date');
        if ($dateVal) {
            $deleted = Shift::where('person_name', $name)->where('date', $dateVal)->delete();
        } else {
            $deleted = Shift::where('person_name', $name)->delete();
        }
        if (!$deleted) {
            return response()->json(['error' => 'Shift not found'], 404);
        }
        return response()->json(['success' => true]);
    }

    // =========================================================================
    // 30-Day Attendance Report
    // =========================================================================

    public function getReport(Request $request, $name)
    {
        $days = (int) $request->query('days', 30);
        $endDate = now()->toDateString();
        $startDate = now()->subDays($days - 1)->toDateString();

        // Get all shifts (recurring + temp) for this person
        $recurringShift = Shift::where('person_name', $name)->whereNull('date')->first();
        $tempShifts = Shift::where('person_name', $name)->whereNotNull('date')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get()->keyBy('date');

        // Get all attendance records for this person in the date range
        $records = AttendanceRecord::where('person_name', $name)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->orderBy('timestamp', 'asc')
            ->get();

        // Group records by date
        $recordsByDate = $records->groupBy(function ($r) {
            return $r->date->format('Y-m-d');
        });

        $timeline = [];
        $presentDays = 0;
        $absentDays = 0;
        $offDays = 0;
        $totalLateMinutes = 0;
        $lateDays = 0;

        $current = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $dayAbbr = strtolower(substr($current->format('D'), 0, 3));

            // Determine active shift for this date
            $activeShift = null;
            if (isset($tempShifts[$dateStr])) {
                $activeShift = $tempShifts[$dateStr];
            } elseif ($recurringShift) {
                $scheduledDays = array_map('trim', explode(',', strtolower($recurringShift->days)));
                if (in_array($dayAbbr, $scheduledDays)) {
                    $activeShift = $recurringShift;
                }
            }

            $dayRecords = $recordsByDate->get($dateStr, collect());
            $inRecord = $dayRecords->where('event_type', 'IN')->first();
            $outRecord = $dayRecords->where('event_type', 'OUT')->last();

            $status = 'off_day';
            $lateMin = 0;
            $checkIn = null;
            $checkOut = null;

            if ($activeShift) {
                // This person had a shift on this day
                if ($inRecord) {
                    $status = 'present';
                    $presentDays++;
                    $lateMin = $inRecord->late_minutes ?? 0;
                    $checkIn = $inRecord->timestamp->format('H:i:s');

                    if ($lateMin > 0) {
                        $totalLateMinutes += $lateMin;
                        $lateDays++;
                    }

                    if ($outRecord && $outRecord->id !== $inRecord->id) {
                        $checkOut = $outRecord->timestamp->format('H:i:s');
                    }
                } else {
                    // Had shift but no check-in → absent (only for past days)
                    if ($dateStr < date('Y-m-d')) {
                        $status = 'absent';
                        $absentDays++;
                    } else {
                        $status = 'pending'; // Today, not yet checked in
                    }
                }
            } else {
                $offDays++;
            }

            $timeline[] = [
                'date' => $dateStr,
                'day' => $current->format('D'),
                'status' => $status,
                'shift' => $activeShift ? [
                    'start' => $activeShift->shift_start,
                    'end' => $activeShift->shift_end,
                ] : null,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'late_minutes' => $lateMin,
            ];

            $current->addDay();
        }

        return response()->json([
            'person_name' => $name,
            'period' => ['from' => $startDate, 'to' => $endDate, 'days' => $days],
            'summary' => [
                'present' => $presentDays,
                'absent' => $absentDays,
                'off_days' => $offDays,
                'late_days' => $lateDays,
                'avg_late_minutes' => $lateDays > 0 ? round($totalLateMinutes / $lateDays, 1) : 0,
            ],
            'timeline' => $timeline,
        ]);
    }

    // =========================================================================
    // Face Registration & People Query
    // =========================================================================

    public function getPeople()
    {
        // Return a distinct list of registered people and their sync statuses
        $people = Face::all()->groupBy('name')->map(function ($group, $name) {
            $faceCount = $group->count();
            $syncedCount = $group->where('status', 'synced')->count();
            $failedCount = $group->where('status', 'failed')->count();
            $pendingCount = $group->where('status', 'pending')->count();

            // Determine sync status expected by frontend (ready/pending/failed/incomplete)
            if ($failedCount > 0) {
                $status = 'failed';
            } elseif ($pendingCount > 0) {
                $status = 'pending';
            } elseif ($syncedCount === $faceCount && $faceCount >= 3) {
                $status = 'ready';
            } else {
                $status = 'incomplete';
            }

            $firstFailed = $group->where('status', 'failed')->first();
            $errorReason = $firstFailed ? ($firstFailed->error_reason ?? '') : '';

            $firstWithImage = $group->whereNotNull('image_path')->first();
            $imagePath = $firstWithImage ? $firstWithImage->image_path : '';

            return [
                'name' => $name,
                'image_path' => $imagePath,
                'face_count' => $faceCount,
                'status' => $status,
                'error_reason' => $errorReason,
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

