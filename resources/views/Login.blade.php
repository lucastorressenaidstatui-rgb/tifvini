<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Entrar | Controle de frequência</title>
    @vite('resources/js/login.js')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <main class="login-shell">
        <div class="artwork">
            <img src="{{ asset('banana-login-art.png') }}" alt="Ilustração digital do controle de entradas e saídas de alunos" fetchpriority="high">
        </div>
        <section class="login-content" aria-labelledby="login-title">
            <h1 id="login-title">Bem-vindo</h1>
            <p class="intro">Entre com sua conta para continuar.</p>
            <form id="login-form" action="{{ url('/login') }}" method="POST">
                @csrf
                <fieldset>
                    <legend>Você é aluno ou professor?</legend>
                    <div class="profile-options">
                        <div class="profile-option"><input type="radio" name="perfil" id="aluno" value="aluno" required><label for="aluno">Aluno</label></div>
                        <div class="profile-option"><input type="radio" name="perfil" id="professor" value="professor" required><label for="professor">Professor</label></div>
                    </div>
                </fieldset>
                <div class="field"><label for="email">Email</label><input type="email" id="email" name="email" placeholder="seu@email.com" autocomplete="username" required></div>
                <div class="field"><label for="senha">Senha</label><input type="password" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required></div>
                <p id="login-error" role="alert" aria-live="polite"></p>
                <button id="entrar" type="submit">Entrar</button>
                <p class="register-link">Ainda não tem conta? <a href="{{ url('/cadastro_usuario') }}">Cadastre-se</a></p>
            </form>
        </section>
    </main>
</body>
</html>
