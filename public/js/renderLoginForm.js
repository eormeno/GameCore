function renderLoginForm(formData) {
    const container = document.getElementById('gamesContainer');
    container.innerHTML = '';

    // Crear contenedor principal
    const loginContainer = document.createElement('div');
    loginContainer.className = 'login-container';

    // Crear formulario
    const form = document.createElement('form');
    form.action = formData.action;
    form.method = formData.method;

    // Manejar el evento submit
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Crear objeto con los datos del formulario
        const formValues = Object.fromEntries(new FormData(form));

        setPageState('trying_login', {
            action: formData.action,
            method: formData.method,
            body: JSON.stringify(formValues)
        });

        // Llamar a la función de manejo de respuesta
        // try {
        //     const response = await fetch(formData.action, {
        //         method: formData.method,
        //         body: JSON.stringify(formValues),
        //         headers: { 'Content-Type': 'application/json' }
        //     });
        //     const data = await response.json();
        //     handleFormResponse(data);
        // } catch (error) {
        //     handleFormResponse({
        //         status: 'error',
        //         message: error.message
        //     });
        // }
    });

    // Crear campos del formulario
    Object.entries(formData).forEach(([key, value]) => {
        if (typeof value === 'object' && !['login', 'register'].includes(key)) {
            const formGroup = document.createElement('div');
            formGroup.className = 'form-group';

            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = value.label;
            label.htmlFor = key;

            const input = document.createElement('input');
            input.type = value.type;
            input.id = key;
            input.name = key;
            input.className = 'form-input';
            input.required = true;

            formGroup.appendChild(label);
            formGroup.appendChild(input);
            form.appendChild(formGroup);
        }
    });

    // Crear grupo de botones
    const buttonGroup = document.createElement('div');
    buttonGroup.className = 'button-group';

    // Botón de login
    const loginButton = document.createElement('button');
    loginButton.type = formData.login.type;
    loginButton.className = 'primary-btn';
    loginButton.textContent = formData.login.text;

    // Botón de registro
    const registerButton = document.createElement('button');
    registerButton.type = formData.register.type;
    registerButton.className = 'secondary-btn';
    registerButton.textContent = formData.register.text;

    buttonGroup.appendChild(loginButton);
    buttonGroup.appendChild(registerButton);
    form.appendChild(buttonGroup);

    // Ensamblar componentes
    loginContainer.appendChild(form);
    container.appendChild(loginContainer);
}

function handleFormResponse(response) {
    if (response.status === 'success') {
        const token = response.token;
        localStorage.setItem('token', token);
    } else if (response.action === 'register') {
        window.location.href = '/register'; // Ejemplo de redirección
    }
}
