import pageState from './modules/PageStateManager.js';

function renderLoginForm(formData) {
    const container = document.getElementById('gamesContainer');
    container.innerHTML = '';

    const loginContainer = document.createElement('div');
    loginContainer.className = 'login-container';

    const form = document.createElement('form');
    form.action = formData.action;
    form.method = formData.method;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formValues = Object.fromEntries(new FormData(form));

        pageState.setPageState('trying_login', {
            action: formData.action,
            method: formData.method,
            body: JSON.stringify(formValues)
        });
    });

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

    const buttonGroup = document.createElement('div');
    buttonGroup.className = 'button-group';

    const loginButton = document.createElement('button');
    loginButton.type = formData.login.type;
    loginButton.className = 'primary-btn';
    loginButton.textContent = formData.login.text;

    const registerButton = document.createElement('button');
    registerButton.type = formData.register.type;
    registerButton.className = 'secondary-btn';
    registerButton.textContent = formData.register.text;

    buttonGroup.appendChild(loginButton);
    buttonGroup.appendChild(registerButton);
    form.appendChild(buttonGroup);

    loginContainer.appendChild(form);
    container.appendChild(loginContainer);
}

export { renderLoginForm };
