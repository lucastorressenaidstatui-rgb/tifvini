<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | Controle de frequência</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite('resources/js/cadastro_usuario.js')
    <link rel="stylesheet" href="{{ asset('css/cadastro_usuario.css') }}">
</head>
<body>
    <main class="registration-shell">
        <div class="artwork">
            <img src="{{ asset('banana-login-art.png') }}" alt="Ilustração digital do controle de entradas e saídas de alunos" fetchpriority="high">
        </div>
        <section class="registration-content" aria-labelledby="registration-title">
            <h1 id="registration-title">Criar conta</h1>
            <p class="intro">Preencha seus dados para acessar o sistema.</p>
            <form id="registration-form" class="registration-fields" action="{{ url('/api/cadastro_usuario') }}" method="POST" data-login-url="{{ route('login') }}">
                <fieldset class="field-wide">
                    <legend>Você é aluno ou professor?</legend>
                    <div class="profile-options">
                        <div class="profile-option"><input type="radio" name="perfil" id="aluno" value="aluno" required><label for="aluno">Aluno</label></div>
                        <div class="profile-option"><input type="radio" name="perfil" id="professor" value="professor" required><label for="professor">Professor</label></div>
                    </div>
                </fieldset>
                <div class="field field-wide"><label for="nome">Nome completo</label><input type="text" id="nome" name="nome" placeholder="Digite seu nome" autocomplete="name" maxlength="255" required></div>
                <div class="field field-wide"><label for="email">Email</label><input type="email" id="email" name="email" placeholder="seu@email.com" autocomplete="email" required></div>
                <div class="field field-wide"><label for="senha">Senha</label><input type="password" id="senha" name="senha" placeholder="Mínimo de 6 caracteres" autocomplete="new-password" minlength="6" required></div>
                <div class="field"><label for="data_nascimento">Data de nascimento</label><input type="date" id="data_nascimento" name="data_nascimento" required></div>
                <div class="field"><label for="cpf">CPF</label><input type="text" id="cpf" name="cpf" placeholder="Somente números" inputmode="numeric" maxlength="11" required></div>
                <div class="field-wide"><button id="cadastro_usuario" type="submit">Criar conta</button></div>
                <p class="login-link field-wide">Já tem uma conta? <a href="{{ route('login') }}">Entrar</a></p>
            </form>
        </section>
    </main>
</body>
</html>
