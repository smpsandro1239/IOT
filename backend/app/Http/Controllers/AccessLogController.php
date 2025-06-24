<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AccessLog;

class AccessLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Placeholder for admin dashboard to view logs
        // $logs = AccessLog::orderBy('timestamp_event', 'desc')->paginate(50);
        // return view('admin.access-logs.index', compact('logs'));
        return response()->json(['message' => 'AccessLogController@index placeholder']);
    }

    /**
     * Store a newly created resource in storage.
     * API endpoint for ESP32.
     */
    public function store(Request $request)
    {
        // TODO: Add more robust error handling for validation failures, e.g., logging
        $validatedData = $request->validate([
            'vehicle_lora_id' => 'required|string|max:16', // MAC address length typically
            'timestamp_event' => 'required|date_format:Y-m-d H:i:s', // Expecting this format from ESP32
            'direction_detected' => 'required|string|in:north_south,south_north,undefined,conflito',
            'base_station_id' => 'required|string|max:16', // MAC address length
            'sensor_reports' => 'nullable|json', // Validates if it's a JSON string
            'authorization_status' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        // The 'sensor_reports' is already validated as a JSON string.
        // If it needs to be stored as a JSON type in DB, no further casting needed here if model handles it.
        // If it's an array in the request, ensure it's converted to JSON string before create or model handles array casting.
        // Assuming model's $casts handles 'sensor_reports' => 'array', so direct assignment is fine.

        try {
            $log = \App\Models\AccessLog::create($validatedData);
            return response()->json(['message' => 'Access log created successfully', 'log_id' => $log->id], 201);
        } catch (\Exception $e) {
            // Log::error('Failed to create access log: ' . $e->getMessage()); // Requires Log facade
            return response()->json(['message' => 'Failed to create access log', 'error' => $e->getMessage()], 500);
        }
    }
}
