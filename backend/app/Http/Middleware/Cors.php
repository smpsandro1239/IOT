<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
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
    // Responder imediatamente às solicitações OPTIONS (preflight)
    if ($request->isMethod('OPTIONS')) {
      $response = response('', 200);
    } else {
      $response = $next($request);
    }

    // Verificar se a resposta é válida antes de adicionar cabeçalhos
    if ($response && method_exists($response, 'headers') && $response->headers) {
      // Adicionar cabeçalhos CORS a todas as respostas
      $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:8080');
      $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
      $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
      $response->headers->set('Access-Control-Allow-Credentials', 'true');
      $response->headers->set('Access-Control-Max-Age', '86400'); // 24 horas
    }

    return $response;
  }
}
