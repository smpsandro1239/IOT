<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Placeholder: return Vehicle::all();
        return response()->json(['message' => 'VehicleController@index placeholder']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Placeholder: Validate and create vehicle
        return response()->json(['message' => 'VehicleController@store placeholder']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Placeholder: return Vehicle::findOrFail($id);
        return response()->json(['message' => "VehicleController@show placeholder for ID: $id"]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Placeholder: Validate and update vehicle
        return response()->json(['message' => "VehicleController@update placeholder for ID: $id"]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Placeholder: Delete vehicle
        return response()->json(['message' => "VehicleController@destroy placeholder for ID: $id"]);
    }

    /**
     * Check if a vehicle is authorized.
     * API endpoint for ESP32.
     */
    public function checkAuthorization(string $lora_id)
    {
        // TODO: Consider logging this request attempt for security/audit purposes if needed, even if vehicle not found.
        $vehicle = \App\Models\Vehicle::where('lora_id', $lora_id)->first();

        if ($vehicle) {
            if ($vehicle->is_authorized) {
                return response()->json(['authorized' => true, 'lora_id' => $lora_id, 'name' => $vehicle->name]);
            } else {
                // Vehicle found but not authorized
                return response()->json(['authorized' => false, 'lora_id' => $lora_id, 'reason' => 'Vehicle not authorized'], 403);
            }
        } else {
            // Vehicle not found in the database
            return response()->json(['authorized' => false, 'lora_id' => $lora_id, 'reason' => 'Vehicle not found'], 404);
        }
    }
}
