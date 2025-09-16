const wrapper = document.querySelector('.wrapper');
const registerlink = document.querySelector('.register-link');
const loginlink = document.querySelector('.login-link');
const form = document.getElementById('registroForm');

registerlink.onclick = () => wrapper.classList.add('active');
loginlink.onclick = () => wrapper.classList.remove('active');

document.addEventListener('DOMContentLoaded', () => {
    const campos = {
        nombre: {
            input: document.getElementById('nombre'),
            regex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/  // Solo letras y espacios, sin números
        },

        documento: {
            input: document.getElementById('documento'),
            regex: /^[0-9]{5,15}$/
        },
        telefono: {
            input: document.getElementById('telefono'),
            regex: /^[0-9]{7,15}$/
        },
        correo: {
            input: document.getElementById('email_registro'),
            regex: /^[^@\s]+@[^@\s]+\.[^@\s]+$/
        },
        sede: {
            input: document.getElementById('sede'),
            regex: /.+/
        },
        cargo: {
            input: document.getElementById('cargo'),
            regex: /.+/
        },
        contraseña: {
            input: document.getElementById('contraseña'),
            regex: /^.{6,}$/
        },
        v_contraseña: {
            input: document.getElementById('v_contraseña'),
            custom: () => document.getElementById('v_contraseña').value === document.getElementById('contraseña').value
        }
    };

    const cargoInput = document.getElementById('cargo');
    const passwordFields = ['contraseña', 'v_contraseña'];

    cargoInput.addEventListener('change', () => {
        const necesitaLogin = ['Supervisor', 'personalSeguridad'].includes(cargoInput.value);
        passwordFields.forEach(id => {
            const input = document.getElementById(id);
            input.required = necesitaLogin;
            input.parentElement.style.display = necesitaLogin ? 'block' : 'none';
        });
    });

    for (const key in campos) {
        const campo = campos[key];
        campo.input.addEventListener('input', () => validarCampo(key));
    }

    form.addEventListener('submit', function(e) {
        let errores = [];

        for (const key in campos) {
            const esVisible = campos[key].input.offsetParent !== null;
            if (esVisible) {
                const esValido = validarCampo(key);
                if (!esValido) errores.push(key);
            }
        }

        if (errores.length > 0) {
            e.preventDefault();
            alert("Corrige los campos:\n• " + errores.join("\n• "));
        }
    });

    function validarCampo(key) {
        const campo = campos[key];
        const valor = campo.input.value.trim();
        let esValido = false;

        if (campo.regex) {
            esValido = campo.regex.test(valor);
        } else if (campo.custom) {
            esValido = campo.custom();
        }

        campo.input.classList.remove('valid', 'invalid');
        campo.input.classList.add(esValido ? 'valid' : 'invalid');

        return esValido;
    }
});