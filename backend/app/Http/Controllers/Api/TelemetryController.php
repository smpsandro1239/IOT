<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Barrier;

class TelemetryController extends Controller
{
    /**
     * Store telemetry data for a barrier device.
     *
     * This endpoint is called by the "Base" station, which forwards telemetry
     * data received from a "Direction" (ESP32 at a barrier) via LoRa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeBarrierTelemetry(Request $request)
    {
        // 1. Validar o payload recebido da "Base"
        $validator = Validator::make($request->all(), [
            'barrier_mac' => 'required|string|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            'firmware_version' => 'sometimes|required|string|max:50',
            'battery_level' => 'sometimes|required|integer|min:0|max:100',
            'cycle_count' => 'sometimes|required|integer|min:0',
            'lora_rssi_to_base' => 'sometimes|required|integer', // RSSI da "Direção" para a "Base" (medido pela Direção)
            'internal_temperature' => 'sometimes|required|numeric',
            'rssi_at_base' => 'sometimes|required|integer', // RSSI da "Direção" na "Base" (medido pela Base)
            'timestamp_at_base' => 'sometimes|required|date_format:Y-m-d\TH:i:s\Z',
            // Podemos adicionar mais campos de telemetria aqui conforme necessário
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $barrierMac = $validatedData['barrier_mac'];

        // 2. Encontrar a barreira correspondente
        $barrier = Barrier::where('base_station_mac_address', $barrierMac)->first();

        if (!$barrier) {
            Log::channel('telemetry')->warning('Received telemetry for unknown barrier MAC.', ['mac_address' => $barrierMac]);
            return response()->json(['status' => 'error', 'message' => 'Barrier with the specified MAC address not found.'], 404);
        }

        // 3. Formatar a mensagem de log
        $logData = [
            'server_timestamp' => now()->toIso8601String(),
            'barrier_id' => $barrier->id,
            'barrier_name' => $barrier->name,
            'barrier_mac' => $barrierMac,
            'telemetry' => $validatedData,
        ];

        // Remove 'barrier_mac' do array de telemetria, pois já está no nível superior do log
        unset($logData['telemetry']['barrier_mac']);

        // 4. Escrever no ficheiro de log dedicado
        // Para isto funcionar, é preciso configurar um canal 'telemetry' em config/logging.php
        // Se não estiver configurado, podemos usar o canal default e especificar um ficheiro.
        // Por simplicidade, usaremos o Facade Log com um canal customizado.
        try {
            Log::channel('telemetry')->info('Barrier telemetry received.', $logData);
        } catch (\InvalidArgumentException $e) {
            // Fallback para o log default se o canal 'telemetry' não existir.
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/barrier_telemetry.log'),
            ])->info(json_encode($logData));
        }

        // 5. Opcional (passo futuro quando a BD for modificada): Atualizar campos na tabela `barriers`
        // Ex:
        // $barrier->update([
        //     'last_heartbeat_at' => now(),
        //     'reported_firmware_version' => $validatedData['firmware_version'] ?? $barrier->reported_firmware_version,
        //     'reported_battery_level' => $validatedData['battery_level'] ?? $barrier->reported_battery_level,
        //     'base_received_rssi' => $validatedData['rssi_at_base'] ?? $barrier->base_received_rssi,
        //     'device_status_message' => 'Online',
        // ]);

        // 6. Retornar uma resposta de sucesso
        return response()->json(['status' => 'success', 'message' => 'Telemetry data received successfully.'], 200);
    }

    /**
     * Display a simple log viewer for barrier telemetry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showLogViewer(Request $request)
    {
        // Autorização: Apenas SuperAdmin pode ver este log
        $this->authorize('roles:manage'); // Usando uma permissão de SuperAdmin existente

        $logPath = storage_path('logs/barrier_telemetry.log');
        $logEntries = [];

        if (file_exists($logPath)) {
            // Ler o ficheiro ao contrário para mostrar os logs mais recentes primeiro
            $file = new \SplFileObject($logPath, 'r');
            $file->seek(PHP_INT_MAX); // Vai para o fim
            $lastLine = $file->key();

            $linesToRead = 200; // Limitar a leitura às últimas 200 linhas
            $iterator = new \LimitIterator($file, max(0, $lastLine - $linesToRead), $lastLine);

            $rawLines = iterator_to_array($iterator);
            $reversedLines = array_reverse($rawLines);

            foreach ($reversedLines as $line) {
                if (trim($line) === '') continue;

                // O nosso log de fallback escreve JSON, o canal de log do Laravel escreve texto.
                // Precisamos de uma forma de extrair o JSON.
                $jsonPart = strstr($line, '{"server_timestamp"');
                if ($jsonPart !== false) {
                    $decodedLine = json_decode(trim($jsonPart), true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $logEntries[] = $decodedLine;
                    }
                } else {
                    // Se a linha não contiver o nosso JSON esperado, podemos tentar decodificá-la inteira
                    // ou registá-la como uma linha de texto simples.
                    $decodedLine = json_decode(trim($line), true);
                     if (json_last_error() === JSON_ERROR_NONE) {
                        $logEntries[] = $decodedLine;
                    } else {
                        // Linha de log não-JSON, talvez um erro ou log de texto simples. Ignorar por agora.
                    }
                }
            }
        }

        return view('admin.telemetry.index', compact('logEntries'));
    }
}
