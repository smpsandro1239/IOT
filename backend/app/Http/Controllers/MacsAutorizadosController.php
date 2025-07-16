<?php

namespace App\Http\Controllers;

use App\Models\MacsAutorizados;
use Illuminate\Http\Request;

class MacsAutorizadosController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mac' => 'required|string|size:12|unique:macs_autorizados,mac',
            'placa' => 'required|string|max:10',
        ]);

        $mac = MacsAutorizados::create([
            'mac' => $validated['mac'],
            'placa' => $validated['placa'],
            'data_adicao' => now(),
        ]);

        return response()->json(['message' => 'MAC autorizado adicionado com sucesso', 'data' => $mac], 201);
    }

    public function storeBulk(Request $request)
    {
        $validated = $request->validate([
            'macs' => 'required|array',
            'macs.*.mac' => 'required|string|size:12',
            'macs.*.placa' => 'required|string|max:10',
        ]);

        $count = 0;
        foreach ($validated['macs'] as $macData) {
            MacsAutorizados::firstOrCreate(
                ['mac' => $macData['mac']],
                [
                    'placa' => $macData['placa'],
                    'data_adicao' => now(),
                ]
            );
            $count++;
        }

        return response()->json(['message' => "$count MACs adicionados com sucesso"], 201);
    }

    public function index()
    {
        $macs = MacsAutorizados::all();
        return response()->json(['data' => $macs]);
    }

    public function checkAuthorization(Request $request)
    {
        $mac = $request->query('mac');
        $authorized = MacsAutorizados::where('mac', $mac)->exists();
        
        return response()->json(['authorized' => $authorized]);
    }
}
