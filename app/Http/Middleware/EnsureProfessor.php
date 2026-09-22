<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfessor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Usuario::find($request->session()->get('usuario_id'));

        if (! $usuario) {
            $request->session()->forget('usuario_id');

            return redirect()->route('login');
        }

        abort_unless($usuario->is_professor, 403, 'Acesso exclusivo para professores.');

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
