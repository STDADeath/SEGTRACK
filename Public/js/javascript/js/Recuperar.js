let userData = null;

function closeModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    document.getElementById('passwordModal').style.display = 'none';
}

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.classList.remove('hidden');
}

function hideError() {
    document.getElementById('errorMessage').classList.add('hidden');
}

function validateEmail() {
    const email = document.getElementById('correo').value;
    
    hideError();

    if (!email) {
        showError('Por favor, ingrese un correo electrónico.');
        return;
    }

    // Llama al script PHP para validar el correo
    fetch('controlador/validar_correo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ correo: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            userData = data.data; // Almacena los datos del usuario
            document.getElementById('correo').disabled = true;
            document.getElementById('cargo').value = userData.cargo;
            document.getElementById('sede').value = 'Sede ID: ' + userData.IdSede;
            document.getElementById('successUserName').textContent = userData.Nombre;
            document.getElementById('info-usuario').style.display = 'block';
            document.getElementById('btn-validar-correo').style.display = 'none';
            document.getElementById('btn-cambiar-pass').style.display = 'inline-block';
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error de conexión. Intente nuevamente.');
    });
}

function changePassword() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    hideError();

    if (!newPassword || !confirmPassword) {
        showError('Por favor, complete todos los campos.');
        return;
    }

    if (newPassword.length < 6) {
        showError('La contraseña debe tener al menos 6 caracteres.');
        return;
    }

    if (newPassword !== confirmPassword) {
        showError('Las contraseñas no coinciden.');
        return;
    }

    if (!userData) {
        showError('No se han cargado los datos del usuario. Valide su correo primero.');
        return;
    }

    // Llama al script PHP para actualizar la contraseña
    fetch('controlador/actualizar_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            // Aquí está el cambio
            idFuncionario: userData.IdFuncionario,
            newPassword: newPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('successModal').classList.remove('hidden');
            // Opcional: limpiar los campos del formulario
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
        } else {
            showError('Error al actualizar la contraseña: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Error de conexión. Intente nuevamente.');
    });
}