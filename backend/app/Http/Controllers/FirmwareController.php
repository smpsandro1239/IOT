<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Firmware; // Uncomment when Model is created
// use Illuminate\Support\Facades\Storage; // Uncomment for file operations

class FirmwareController extends Controller
{
    /**
     * Display a listing of the resource for admin.
     */
    public function index()
    {
        // Placeholder
        return response()->json(['message' => 'FirmwareController@index placeholder']);
    }

    /**
     * Store a newly uploaded firmware file.
     */
    public function store(Request $request)
    {
        // Placeholder: Validate and store firmware
        // $request->validate([
        //     'version' => 'required|string|unique:firmwares,version',
        //     'firmware_file' => 'required|file|mimetypes:application/octet-stream,application/macbinary,application/bin', // Adjust mimetypes as needed
        //     'description' => 'nullable|string',
        // ]);
        // $path = $request->file('firmware_file')->store('firmwares');
        // Firmware::create([
        //     'version' => $request->version,
        //     'filename' => $path,
        //     'description' => $request->description,
        //     'size' => $request->file('firmware_file')->getSize(),
        //     'is_active' => false, // Or true, depending on logic
        // ]);
        // return response()->json(['message' => 'Firmware uploaded successfully'], 201);
        return response()->json(['message' => 'FirmwareController@store placeholder'], 201);
    }

    /**
     * API endpoint for ESP32 to check for updates.
     */
    public function checkForUpdate(Request $request)
    {
        // Placeholder:
        // $currentVersion = $request->query('current_version');
        // $boardId = $request->query('board_id'); // Could be used for targeted updates
        // $latestFirmware = Firmware::where('is_active', true)->orderBy('version', 'desc')->first();
        // if ($latestFirmware && version_compare($latestFirmware->version, $currentVersion, '>')) {
        //     return response()->json([
        //         'update_available' => true,
        //         'new_version' => $latestFirmware->version,
        //         'download_url' => route('firmware.download', ['firmware_id' => $latestFirmware->id]) // Assuming a named route
        //     ]);
        // }
        // return response()->json(['update_available' => false]);
        return response()->json(['message' => 'FirmwareController@checkForUpdate placeholder']);
    }

    /**
     * API endpoint for ESP32 to download firmware.
     */
    public function download(string $firmware_id)
    {
        // Placeholder:
        // $firmware = Firmware::findOrFail($firmware_id);
        // return Storage::download($firmware->filename, 'firmware.bin');
        return response()->json(['message' => "FirmwareController@download placeholder for ID: $firmware_id"]);
    }
}
