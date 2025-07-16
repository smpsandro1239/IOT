<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\GateControl;

class GateController extends Controller
{
    public function control(Request $request)
    {
        $validated = $request->validate([
            'gate' => 'required|in:north,south',
            'action' => 'required|in:open,close'
        ]);

        event(new GateControl($validated['gate'], $validated['action'] === 'open'));
        return response()->json(['message' => 'Comando enviado para a barreira']);
    }
}
