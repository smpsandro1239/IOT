<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class JsonController extends Controller
{
    protected function getJsonData($filename)
    {
        $path = storage_path("app/{$filename}.json");
        if (!file_exists($path)) {
            return [];
        }
        return json_decode(file_get_contents($path), true) ?: [];
    }

    protected function saveJsonData($filename, $data)
    {
        $path = storage_path("app/{$filename}.json");
        return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function getLatest(): JsonResponse
    {
        $systemStatus = $this->getJsonData('system_status');
        $macsAutorizados = $this->getJsonData('macs_autorizados');
        $accessLogs = $this->getJsonData('access_logs');

        return response()->json([
            'status' => 'success',
            'data' => [
                'barrier_status' => $systemStatus['barrier_status'] ?? 'fechada',
                'last_detection' => $systemStatus['last_detection'] ?? null,
                'total_authorized' => count($macsAutorizados),
                'recent_access' => array_slice(array_reverse($accessLogs), 0, 5),
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    public function getMacsAutorizados(): JsonResponse
    {
        $macs = $this->getJsonData('macs_autorizados');
        return response()->json([
            'status' => 'success',
            'data' => $macs
        ]);
    }

    public function storeMac(Request $request): JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'nome' => 'required|string'
        ]);

        $macs = $this->getJsonData('macs_autorizados');
        
        // Verificar se já existe
        foreach ($macs as $mac) {
            if ($mac['mac_address'] === $request->mac_address) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'MAC address já existe'
                ], 409);
            }
        }

        // Adicionar novo MAC
        $newMac = [
            'id' => count($macs) + 1,
            'mac_address' => $request->mac_address,
            'nome' => $request->nome,
            'ultimo_acesso' => null,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString()
        ];

        $macs[] = $newMac;
        $this->saveJsonData('macs_autorizados', $macs);

        return response()->json([
            'status' => 'success',
            'data' => $newMac
        ]);
    }

    public function deleteMac($macAddress): JsonResponse
    {
        $macs = $this->getJsonData('macs_autorizados');
        
        $macs = array_filter($macs, function($mac) use ($macAddress) {
            return $mac['mac_address'] !== $macAddress;
        });

        $this->saveJsonData('macs_autorizados', array_values($macs));

        return response()->json([
            'status' => 'success',
            'message' => 'MAC removido com sucesso'
        ]);
    }

    public function storeBulkMacs(Request $request): JsonResponse
    {
        $request->validate([
            'macs' => 'required|array',
            'macs.*.mac_address' => 'required|string',
            'macs.*.nome' => 'required|string'
        ]);

        $existingMacs = $this->getJsonData('macs_autorizados');
        $newMacs = [];
        $errors = [];

        foreach ($request->macs as $macData) {
            // Verificar se já existe
            $exists = false;
            foreach ($existingMacs as $existing) {
                if ($existing['mac_address'] === $macData['mac_address']) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $newMacs[] = [
                    'id' => count($existingMacs) + count($newMacs) + 1,
                    'mac_address' => $macData['mac_address'],
                    'nome' => $macData['nome'],
                    'ultimo_acesso' => null,
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString()
                ];
            } else {
                $errors[] = "MAC {$macData['mac_address']} já existe";
            }
        }

        if (!empty($newMacs)) {
            $allMacs = array_merge($existingMacs, $newMacs);
            $this->saveJsonData('macs_autorizados', $allMacs);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'added' => count($newMacs),
                'errors' => $errors
            ]
        ]);
    }

    public function downloadMacs(): JsonResponse
    {
        $macs = $this->getJsonData('macs_autorizados');
        
        return response()->json([
            'status' => 'success',
            'data' => $macs,
            'filename' => 'macs_autorizados_' . date('Y-m-d_H-i-s') . '.json'
        ]);
    }
}