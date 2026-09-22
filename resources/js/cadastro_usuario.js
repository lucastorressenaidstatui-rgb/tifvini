const registrationForm = document.querySelector('#registration-form');
const registrationButton = document.querySelector('#cadastro_usuario');

registrationForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    registrationButton.disabled = true;

    try {
        const response = await fetch(registrationForm.action, {
            method: 'POST',
            headers: { Accept: 'application/json' },
            body: new FormData(registrationForm),
        });
        const result = await response.json();

        if (!response.ok || result.erro !== 'n') {
            const errors = result.errors ? Object.values(result.errors).flat().join('\n') : null;
            await Swal.fire({ icon: 'error', title: 'Erro!', text: errors || result.mensagem || 'Confira os dados informados.', background: '#14243a', color: '#f5f9ff', confirmButtonColor: '#13a9ce' });
            return;
        }

        await Swal.fire({ icon: 'success', title: 'Sucesso!', text: 'Cadastro realizado com sucesso! Entre com sua conta.', background: '#14243a', color: '#f5f9ff', confirmButtonColor: '#13a9ce' });
        sessionStorage.setItem('emailCadastro', registrationForm.elements.email.value);
        sessionStorage.setItem('perfilCadastro', registrationForm.elements.perfil.value);
        window.location.assign(registrationForm.dataset.loginUrl);
    } catch {
        await Swal.fire({ icon: 'error', title: 'Erro!', text: 'Não foi possível realizar o cadastro. Tente novamente.', background: '#14243a', color: '#f5f9ff', confirmButtonColor: '#13a9ce' });
    } finally {
        registrationButton.disabled = false;
    }
});
