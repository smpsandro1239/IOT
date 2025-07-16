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
            'macs.*.mac' => 'required|string|size:12|regex:/^[0-9A-Fa-f]{12}$/|unique:macs_autorizados,mac',
            'macs.*.placa' => 'required|string|max:10',
        ]);

        $inserted = 0;
        foreach ($validated['macs'] as $macData) {
            MacsAutorizados::create([
                'mac' => $macData['mac'],
                'placa' => $macData['placa'],
                'data_adicao' => now(),
            ]);
            $inserted++;
        }

        return response()->json(['message' => "$inserted MACs autorizados adicionados com sucesso"], 201);
    }

    public function destroy($mac)
    {
        $macRecord = MacsAutorizados::where('mac', $mac)->firstOrFail();
        $macRecord->delete();
        return response()->json(['message' => 'MAC autorizado removido com sucesso']);
    }

    public function download()
    {
        $macs = MacsAutorizados::all();
        $content = '';
        foreach ($macs as $mac) {
            $content .= "{$mac->mac},{$mac->placa}\n";
        }

        return response($content, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="macs_autorizados.txt"');
    }

    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $macs = MacsAutorizados::where('mac', 'like', "%$search%")
            ->orWhere('placa', 'like', "%$search%")
            ->paginate(10);
        return response()->json($macs);
    }

    public function checkAuthorization(Request $request)
    {
        $mac = $request->query('mac');
        $authorized = MacsAutorizados::where('mac', $mac)->exists();
        
        return response()->json(['authorized' => $authorized]);
    }
}
