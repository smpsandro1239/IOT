<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para obter o usuário autenticado
use Illuminate\Support\Str; // Para gerar nomes de token se necessário

class ApiTokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            // Isso não deveria acontecer se a rota estiver protegida por middleware('auth')
            return redirect('/login')->with('error', 'Você precisa estar logado para gerenciar tokens.');
        }
        $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();

        return view('admin.api-tokens.index', compact('tokens'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        if (!$user) {
            return back()->with('error', 'Usuário não autenticado.');
        }
        $tokenName = $request->input('token_name');
        $abilities = ['api:access']; // Habilidade padrão para acesso geral à API

        // Opcional: verificar se já existe um token com este nome para este usuário
        // if ($user->tokens()->where('name', $tokenName)->exists()) {
        //     return back()->with('error', "Já existe um token com o nome '{$tokenName}'. Escolha outro nome.")->withInput();
        // }

        $newToken = $user->createToken($tokenName, $abilities);
        $plainTextToken = $newToken->plainTextToken;

        return redirect()->route('admin.api-tokens.index')
                         ->with('success', 'Token API criado com sucesso! Anote o token abaixo, ele não será mostrado novamente.')
                         ->with('plainTextToken', $plainTextToken)
                         ->with('tokenNameToDisplay', $tokenName); // Renomeado para evitar conflito com input
    }

    /**
     * Remove the specified resource from storage.
     * O parâmetro $tokenId é o ID do token na tabela personal_access_tokens.
     */
    public function destroy(string $tokenId)
    {
        $user = Auth::user();
        if (!$user) {
            return back()->with('error', 'Usuário não autenticado.');
        }

        $token = $user->tokens()->where('id', $tokenId)->first();

        if ($token) {
            $token->delete();
            return redirect()->route('admin.api-tokens.index')->with('success', 'Token API revogado com sucesso!');
        }

        return redirect()->route('admin.api-tokens.index')->with('error', 'Token API não encontrado ou não pertence a este usuário.');
    }
}
