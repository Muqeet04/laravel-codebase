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

class PushApiController extends Controller
{
    /**
     * POST /api/push/event
     */
    public function pushEvent(Request $request)
    {
        $validated = $request->validate([
            'person_name' => 'required|string',
            'event_type' => 'required|string',
            'timestamp' => 'required',
            'confidence' => 'nullable|numeric',
            'snapshot_path' => 'nullable|string',
        ]);

        $record = AttendanceRecord::create([
            'person_name' => $validated['person_name'],
            'event_type' => $validated['event_type'],
            'timestamp' => date('Y-m-d H:i:s', strtotime($validated['timestamp'])),
            'date' => date('Y-m-d', strtotime($validated['timestamp'])),
            'confidence' => $validated['confidence'] ?? 0.0,
            'snapshot_path' => $validated['snapshot_path'] ?? null,
        ]);

        return response()->json(['success' => true, 'record' => $record]);
    }

    /**
     * POST /api/push/sighting
     */
    public function pushSighting(Request $request)
    {
        $validated = $request->validate([
            'person_name' => 'required|string',
            'timestamp' => 'required',
            'snapshot_path' => 'nullable|string',
        ]);

        $sighting = Sighting::create([
            'person_name' => $validated['person_name'],
            'timestamp' => date('Y-m-d H:i:s', strtotime($validated['timestamp'])),
            'date' => date('Y-m-d', strtotime($validated['timestamp'])),
            'snapshot_path' => $validated['snapshot_path'] ?? null,
        ]);

        return response()->json(['success' => true, 'sighting' => $sighting]);
    }

    /**
     * POST /api/push/snapshot
     */
    public function pushSnapshot(Request $request)
    {
        $file = $request->file('file');
        $relativePath = $request->input('relative_path');

        if (!$file || !$relativePath) {
            return response()->json(['error' => 'Missing file or relative_path'], 400);
        }

        // Save to public/snapshots/<relative_path>
        $targetPath = public_path('snapshots/' . $relativePath);
        File::ensureDirectoryExists(dirname($targetPath));
        $file->move(dirname($targetPath), basename($targetPath));

        return response()->json(['success' => true, 'saved_path' => $relativePath]);
    }

    /**
     * POST /api/push/register-face
     */
    public function pushRegisterFace(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'embedding' => 'required|string', // Base64 encoded
            'image' => 'required|file|image',
        ]);

        $name = trim($validated['name']);
        try {
            $embeddingBytes = base64_decode($validated['embedding']);
        } catch (\Exception $e) {
            return response()->json(['detail' => 'Invalid base64 embedding'], 400);
        }

        $imageFile = $request->file('image');
        $count = Face::where('name', $name)->count();
        $safeName = str_replace([' ', '/'], '_', $name);
        $filename = "{$safeName}_" . ($count + 1) . ".jpg";

        // Save to public/data/registered_faces/
        $regDir = public_path('data/registered_faces');
        File::ensureDirectoryExists($regDir);
        $imageFile->move($regDir, $filename);

        $imgRelPath = "registered_faces/{$filename}";

        $face = Face::create([
            'name' => $name,
            'image_path' => $imgRelPath,
            'embedding' => $embeddingBytes,
            'status' => 'synced',
        ]);

        return response()->json(['success' => true, 'face' => $face]);
    }

    /**
     * DELETE /api/push/people/{name}
     */
    public function pushDeletePerson($name)
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

        // Delete any shift assigned to them
        Shift::where('person_name', $name)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/push/cameras-config
     */
    public function pushCamerasConfig(Request $request)
    {
        $validated = $request->validate([
            'cameras' => 'required|array',
        ]);

        // Wipe old cameras configurations and sync with local ones
        Camera::truncate();

        foreach ($validated['cameras'] as $cam) {
            Camera::create([
                'id' => $cam['id'],
                'name' => $cam['name'],
                'url' => $cam['url'],
                'enabled' => $cam['enabled'] ?? true,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/push/shifts
     */
    public function pushShift(Request $request)
    {
        $validated = $request->validate([
            'person_name' => 'required|string',
            'shift_start' => 'required|string',
            'shift_end' => 'required|string',
            'days' => 'required|string',
        ]);

        $shift = Shift::updateOrCreate(
            ['person_name' => $validated['person_name']],
            [
                'shift_start' => $validated['shift_start'],
                'shift_end' => $validated['shift_end'],
                'days' => $validated['days'],
            ]
        );

        return response()->json(['success' => true, 'shift' => $shift]);
    }

    /**
     * DELETE /api/push/shifts/{name}
     */
    public function pushDeleteShift($name)
    {
        $deleted = Shift::where('person_name', $name)->delete();
        if (!$deleted) {
            return response()->json(['error' => 'Shift not found'], 404);
        }
        return response()->json(['success' => true]);
    }

    /**
     * GET /api/health
     */
    public function health()
    {
        return response()->json([
            'status' => 'running',
            'service' => 'cPanel Web Dashboard',
        ]);
    }

    /**
     * GET /api/push/pending-registrations
     */
    public function getPendingRegistrations()
    {
        $rows = Face::where('status', 'pending')->get(['id', 'name', 'image_path']);
        return response()->json(['pending' => $rows]);
    }

    /**
     * POST /api/push/complete-registration
     */
    public function completeRegistration(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'embedding' => 'required|string',
        ]);

        $face = Face::find($validated['id']);
        if (!$face) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        $face->update([
            'embedding' => base64_decode($validated['embedding']),
            'status' => 'synced',
            'error_reason' => null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/push/fail-registration
     */
    public function failRegistration(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
            'reason' => 'required|string',
        ]);

        $face = Face::find($validated['id']);
        if (!$face) {
            return response()->json(['error' => 'Registration not found'], 404);
        }

        $face->update([
            'status' => 'failed',
            'error_reason' => $validated['reason'],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * GET /api/push/active-people
     */
    public function getActivePeople()
    {
        $names = Face::where('status', 'synced')->distinct()->pluck('name');
        return response()->json(['people' => $names]);
    }

    /**
     * GET /api/push/cameras-config
     */
    public function getPushCameraConfigs()
    {
        $cameras = Camera::all();
        return response()->json(['cameras' => $cameras]);
    }
}
