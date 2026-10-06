<?php

namespace App\Http\Middleware;

use App\Models\TokenUsuario;
use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class LogadoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('token_usuario', $request->session()->get('token_usuario'));

        if ($token !== null) {
            $tokenUsuario = TokenUsuario::where('token', $token)
                ->where('valido_ate', '>', now())
                ->first();

            $usuario = $tokenUsuario ? Usuario::find($tokenUsuario->usuario_id) : null;
        } else {
            $usuario = Usuario::find($request->session()->get('usuario_id'));
        }

        if (! $usuario) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() && $request->isMethod('POST')) {
                return response()->json(['mensagem' => 'Entre na sua conta para continuar.'], 403)
                    ->withCookie(Cookie::forget('token_usuario'));
            }

            return redirect()->route('login')->withCookie(Cookie::forget('token_usuario'));
        }

        if ($request->session()->get('usuario_id') !== $usuario->id) {
            $request->session()->regenerate();
        }

        $request->session()->put('usuario_id', $usuario->id);
        if ($token !== null) {
            $request->session()->put('token_usuario', $token);
        }

        $request->attributes->set('usuario', $usuario);
        $request->setUserResolver(fn () => $usuario);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
