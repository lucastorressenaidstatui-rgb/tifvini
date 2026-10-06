<?php

namespace App\Http\Controllers;

use App\Models\TokenUsuario;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login_web(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
            'perfil' => ['sometimes', 'required', 'in:aluno,professor'],
        ]);

        $usuario = Usuario::where('email', $credentials['email'])->first();

        if (! $usuario || ! $this->senhaValida($usuario, $credentials['senha'])) {
            return response()->json(['erro' => 's', 'mensagem' => 'Email ou senha inválidos'], 422);
        }

        if (isset($credentials['perfil']) && $credentials['perfil'] !== ($usuario->is_professor ? 'professor' : 'aluno')) {
            return response()->json(['erro' => 's', 'mensagem' => 'O perfil selecionado não corresponde à sua conta.'], 422);
        }

        $tokenAnterior = $request->cookie('token_usuario', $request->session()->get('token_usuario'));
        if ($tokenAnterior) {
            TokenUsuario::where('token', $tokenAnterior)->delete();
        }

        $token = TokenUsuario::create([
            'usuario_id' => $usuario->id,
            'token' => Str::random(64),
            'valido_ate' => now()->addDays(2),
        ]);

        $request->session()->regenerate();
        $request->session()->put('usuario_id', $usuario->id);
        $request->session()->put('token_usuario', $token->token);

        return response()->json([
            'erro' => 'n',
            'mensagem' => 'Login realizado com sucesso!',
            'redirect' => $usuario->is_professor ? route('historico') : route('registro'),
        ])->withCookie(Cookie::make('token_usuario', $token->token, 60 * 24 * 2));
    }

    public function logout(Request $request): RedirectResponse
    {
        $tokens = array_filter([
            $request->cookie('token_usuario'),
            $request->session()->get('token_usuario'),
        ]);
        TokenUsuario::whereIn('token', $tokens)->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withCookie(Cookie::forget('token_usuario'));
    }

    public function login_html(Request $request)
    {
        return view('Login');
    }

    public function login_api(Request $request)
    {

        $request->validate([
            'email' => 'required',
            'senha' => 'required',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if ($usuario && $this->senhaValida($usuario, $request->senha)) {
            $token = new TokenUsuario;
            TokenUsuario::where('usuario_id', '=', $usuario->id)->delete();
            $token->usuario_id = $usuario->id;
            $token->token = md5($usuario->email.now());
            $token->valido_ate = now()->addDays(2);
            $token->save();

            return response()->json(['erro' => 'n', 'mensagem' => 'Login realizado com sucesso', 'token' => $token->token], 200);
        } else {
            return response()->json(['erro' => 's', 'mensagem' => 'Email ou senha inválidos'], 200);
        }

    }

    private function senhaValida(Usuario $usuario, string $senha): bool
    {
        if (preg_match('/^[a-f0-9]{32}$/i', $usuario->senha)) {
            if ($usuario->senha !== md5($senha)) {
                return false;
            }

            $usuario->senha = Hash::make($senha);
            $usuario->save();

            return true;
        }

        return Hash::check($senha, $usuario->senha);
    }
}
