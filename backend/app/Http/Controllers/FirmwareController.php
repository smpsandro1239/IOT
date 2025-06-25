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

        // Se chegou aqui, o arquivo não foi enviado ou não era válido
        \Illuminate\Support\Facades\Log::warning('Tentativa de upload de firmware falhou. Request data: ' . json_encode($request->except('firmware_file')));
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
            \Illuminate\Support\Facades\Log::error("Erro ao excluir firmware ID {$firmware->id} (Versão: {$firmware->version}, Arquivo: {$firmware->filename}): " . $e->getMessage());
            return redirect()->route('admin.firmwares.index')->with('error', 'Erro ao excluir firmware. Consulte os logs do sistema.');
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
     * Query parameters: current_version (e.g., "1.0.0"), board_id (optional, e.g., MAC Address)
     */
    public function checkForUpdate(Request $request)
    {
        $request->validate([
            'current_version' => 'required|string|max:50',
            'board_id' => 'nullable|string|max:17', // MAC Address XX:XX:XX:XX:XX:XX
        ]);

        $currentVersion = $request->input('current_version');
        // $boardId = $request->input('board_id'); // Pode ser usado no futuro para firmwares específicos por placa

        // Encontrar o firmware ativo mais recente (poderia haver apenas um ativo)
        // Se houver múltiplos ativos, pegar o de maior versão.
        $activeFirmware = Firmware::where('is_active', true)
                                  ->orderBy('version', 'desc') // Ordena por versão para pegar o mais novo se houver múltiplos ativos
                                  ->first();

        if (!$activeFirmware) {
            return response()->json(['update_available' => false, 'reason' => 'No active firmware found on server.']);
        }

        // Comparar versões (version_compare é útil para semver)
        if (version_compare($activeFirmware->version, $currentVersion, '>')) {
            return response()->json([
                'update_available' => true,
                'new_version' => $activeFirmware->version,
                'description' => $activeFirmware->description,
                'size_bytes' => $activeFirmware->size,
                // Gerar URL de download absoluta. Usar o nome da rota definido em api.php.
                'download_url' => route('api.firmware.download', ['firmware' => $activeFirmware->id], true),
            ]);
        }

        return response()->json(['update_available' => false, 'current_version_is_latest' => true]);
    }

    /**
     * API endpoint for ESP32 to download firmware.
     */
    public function download(Firmware $firmware) // Route Model Binding
    {
        if (!$firmware->is_active) {
             // Por segurança, permitir apenas download de firmwares ativos,
             // ou remover esta verificação se o ESP já recebeu o ID de um firmware específico do /check
             // Se o /check só retorna IDs de firmwares ativos, esta verificação é uma dupla checagem.
            // return response()->json(['error' => 'Firmware not active or not found.'], 403);
        }

        // O nome do arquivo no storage é $firmware->filename
        // O nome que o cliente vai baixar pode ser genérico como 'firmware.bin' ou específico.
        $downloadName = 'firmware_v' . Str::slug($firmware->version) . '.bin'; // Ex: firmware_v1-0-1.bin

        if (Storage::disk('local')->exists($firmware->filename)) {
            return Storage::disk('local')->download($firmware->filename, $downloadName, [
                'Content-Type' => 'application/octet-stream',
            ]);
        } else {
            // Log::error("Arquivo de firmware não encontrado no storage: {$firmware->filename} para Firmware ID: {$firmware->id}");
            return response()->json(['error' => 'Arquivo de firmware não encontrado.'], 404);
        }
    }
}
