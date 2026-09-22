const form = document.querySelector('#login-form');
const errorMessage = document.querySelector('#login-error');
const submitButton = document.querySelector('#entrar');

const registeredEmail = sessionStorage.getItem('emailCadastro');
if (registeredEmail) {
    form.elements.email.value = registeredEmail;
    form.elements.perfil.value = sessionStorage.getItem('perfilCadastro') || 'aluno';
    sessionStorage.removeItem('emailCadastro');
    sessionStorage.removeItem('perfilCadastro');
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    errorMessage.textContent = '';
    submitButton.disabled = true;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                Accept: 'application/json',
            },
            body: new FormData(form),
        });
        const result = await response.json();

        if (!response.ok || result.erro !== 'n') {
            errorMessage.textContent = result.mensagem || (response.status === 429
                ? 'Muitas tentativas. Aguarde um minuto e tente novamente.'
                : 'Confira seus dados e tente novamente.');
            return;
        }

        window.location.assign(result.redirect);
    } catch {
        errorMessage.textContent = 'Não foi possível entrar. Tente novamente.';
    } finally {
        submitButton.disabled = false;
    }
});
