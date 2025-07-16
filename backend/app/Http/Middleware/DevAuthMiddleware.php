<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DevAuthMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    // Verificar se o token está presente no header Authorization
    $token = $request->bearerToken();

    // Para desenvolvimento, aceitar qualquer token que comece com 'dev_token_'
    if ($token && str_starts_with($token, 'dev_token_')) {
      // Autenticar o primeiro usuário para desenvolvimento
      $user = User::first();
      if ($user) {
        Auth::login($user);
        return $next($request);
      }
    }

    // Se não houver token ou o token não for válido, retornar erro 401
    return response()->json(['message' => 'Unauthorized'], 401);
  }
}
