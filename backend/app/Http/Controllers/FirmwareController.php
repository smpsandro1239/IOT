<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Firmware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FirmwareController extends Controller
{
    /**
     * Display a listing of the resource for admin.
     */
    public function index()
    {
        $firmwares = Firmware::orderBy('version', 'desc')->paginate(15);
        return view('admin.firmwares.index', compact('firmwares'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.firmwares.create');
    }

    /**
     * Store a newly uploaded firmware file.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'version' => 'required|string|max:50|unique:firmwares,version',
            'firmware_file' => 'required|file|mimetypes:application/octet-stream,application/x-binary,application/macbinary,application/x-macbinary', // .bin files, added one more for macbinary
            'description' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('firmware_file') && $request->file('firmware_file')->isValid()) {
            $file = $request->file('firmware_file');
            $originalExtension = $file->getClientOriginalExtension();
            if(empty($originalExtension)) $originalExtension = 'bin'; // Default to .bin if no extension

            $filename = 'firmware_v' . Str::slug($validatedData['version']) . '.' . $originalExtension;
            $path = $file->storeAs('firmwares_storage', $filename, 'local');

            Firmware::create([
                'version' => $validatedData['version'],
                'filename' => $path,
                'description' => $validatedData['description'],
                'size' => $file->getSize(),
                'is_active' => false,
            ]);

            return redirect()->route('admin.firmwares.index')->with('success', 'Firmware carregado com sucesso!');
        }

        return back()->with('error', 'Falha no upload do arquivo de firmware. Verifique o arquivo e tente novamente.')->withInput();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Firmware $firmware)
    {
        return view('admin.firmwares.edit', compact('firmware'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Firmware $firmware)
    {
        $validatedData = $request->validate([
            'version' => 'required|string|max:50|unique:firmwares,version,' . $firmware->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $firmware->update($validatedData);
        return redirect()->route('admin.firmwares.index')->with('success', 'Detalhes do Firmware atualizados!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Firmware $firmware)
    {
        try {
            if (Storage::disk('local')->exists($firmware->filename)) {
                Storage::disk('local')->delete($firmware->filename);
            }
            $firmware->delete();
            return redirect()->route('admin.firmwares.index')->with('success', 'Firmware excluído com sucesso!');
        } catch (\Exception $e) {
            // Log::error("Erro ao excluir firmware {$firmware->id}: " . $e->getMessage()); // Descomentar com Log facade
            return redirect()->route('admin.firmwares.index')->with('error', 'Erro ao excluir firmware.');
        }
    }

    /**
     * Set the specified firmware as active.
     */
    public function setActive(Firmware $firmware)
    {
        DB::transaction(function () use ($firmware) { // Usar transação para garantir atomicidade
            Firmware::where('is_active', true)->update(['is_active' => false]);
            $firmware->is_active = true;
            $firmware->save();
        });

        return redirect()->route('admin.firmwares.index')->with('success', "Firmware v{$firmware->version} marcado como ativo!");
    }


    // Os métodos checkForUpdate e download são para a API e serão implementados no Passo 4.2

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
