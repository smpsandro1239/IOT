<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\GateControl;

class GateController extends Controller
{
    /**
     * Control a gate
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function control(Request $request)
    {
        $validated = $request->validate([
            'gate' => 'required|string|in:north,south',
            'action' => 'required|boolean',
        ]);

        // Broadcast the gate control event
        event(new GateControl($validated['gate'], $validated['action']));

        return response()->json([
            'message' => 'Gate control command sent',
            'gate' => $validated['gate'],
            'action' => $validated['action']
        ]);
    }
}
