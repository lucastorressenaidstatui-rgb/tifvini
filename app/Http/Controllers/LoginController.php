<?php

namespace App\Http\Controllers;

use App\Models\TokenUsuario;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login_web(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'senha' => ['required', 'string'],
            'perfil' => ['sometimes', 'required', 'in:aluno,professor'],
        ]);

        $usuario = Usuario::where('email', $credentials['email'])
            ->where('senha', md5($credentials['senha']))->first();

        if (! $usuario) {
            return response()->json(['erro' => 's', 'mensagem' => 'Email ou senha inválidos'], 422);
        }

        if (isset($credentials['perfil']) && $credentials['perfil'] !== ($usuario->is_professor ? 'professor' : 'aluno')) {
            return response()->json(['erro' => 's', 'mensagem' => 'O perfil selecionado não corresponde à sua conta.'], 422);
        }

        $request->session()->regenerate();
        $request->session()->put('usuario_id', $usuario->id);

        return response()->json([
            'erro' => 'n',
            'mensagem' => 'Login realizado com sucesso!',
            'redirect' => $usuario->is_professor ? route('historico') : route('registro'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
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

        $usuario = Usuario::where('email', '=', $request->email)
            ->where('senha', '=', md5($request->senha))->first();

        if ($usuario) {
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
}
