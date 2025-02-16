function renderLoginForm(formData) {
    const container = document.getElementById('gamesContainer');
    container.innerHTML = '';
    const formConfig = formData.form;

    // Estilos dinámicos actualizados
    const style = document.createElement('style');
    style.textContent = `
        .login-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
            font-family: 'Inter', system-ui, sans-serif;
        }

        .form-group {
            margin-bottom: 1.8rem;
            margin-right: 1.8rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.6rem;
            color: #2d3748;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .primary-btn {
            flex: 1;
            padding: 1rem;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.3px;
        }

        .primary-btn:hover {
            background: #4f46e5;
            transform: translateY(-1px);
        }

        .secondary-btn {
            flex: 1;
            padding: 1rem;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .secondary-btn:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
    `;
    document.head.appendChild(style);

    // Crear contenedor principal
    const loginContainer = document.createElement('div');
    loginContainer.className = 'login-container';

    // Crear formulario
    const form = document.createElement('form');
    form.action = formConfig.action;
    form.method = formConfig.method;

    // Manejar el evento submit
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Crear objeto con los datos del formulario
        const formValues = Object.fromEntries(new FormData(form));

        // Llamar a la función de manejo de respuesta
        try {
            const response = await fetch(formConfig.action, {
                method: formConfig.method,
                body: JSON.stringify(formValues),
                headers: { 'Content-Type': 'application/json' }
            });
            const data = await response.json();
            handleFormResponse(data);
        } catch (error) {
            handleFormResponse({
                status: 'error',
                message: error.message
            });
        }
    });

    // Crear campos del formulario
    Object.entries(formConfig).forEach(([key, value]) => {
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
    loginButton.type = formConfig.login.type;
    loginButton.className = 'primary-btn';
    loginButton.textContent = formConfig.login.text;

    // Botón de registro
    const registerButton = document.createElement('button');
    registerButton.type = formConfig.register.type;
    registerButton.className = 'secondary-btn';
    registerButton.textContent = formConfig.register.text;

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
