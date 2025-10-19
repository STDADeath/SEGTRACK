// Espera a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    form.addEventListener('submit', function (event) {
        event.preventDefault(); // ❌ Evita el envío normal (no recarga la página)

        // Obtenemos los valores
        const placa = document.getElementById('PlacaVehiculo').value;
        const descripcion = document.getElementById('DescripcionVehiculo').value;
        const tarjeta = document.getElementById('TarjetaPropiedad').value;
        const idSede = document.getElementById('IdSede').value;

        // Expresiones regulares
        const regexPlacaTarjeta = /^[a-zA-Z0-9\s-]*$/;
        const regexDescripcion = /^[a-zA-Z0-9\s.,-]*$/;
        const regexIdSede = /^\d+$/;

        fetch('../Controller/parqueadero_vehiculo/ControladorParqueadero.php', {
    method: 'POST',
    body: new FormData(form)
})
        // Validaciones
        if (!regexPlacaTarjeta.test(placa)) {
            alert('❌ Error: El campo Placa solo puede contener letras, números, espacios y guiones.');
            return;
        }

        if (!regexDescripcion.test(descripcion)) {
            alert('❌ Error: El campo Descripción contiene caracteres no válidos.');
            return;
        }

        if (tarjeta.length > 0 && !regexPlacaTarjeta.test(tarjeta)) {
            alert('❌ Error: El campo Tarjeta de Propiedad solo puede contener letras, números, espacios y guiones.');
            return;
        }

        if (!regexIdSede.test(idSede)) {
            alert('❌ Error: El campo ID de Sede solo puede contener números.');
            return;
        }

        // ✅ Si pasa las validaciones, enviamos con fetch (AJAX)
        const formData = new FormData(form);

        fetch(form.action, {
            method: form.method, // normalmente POST
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data); // Puedes revisar lo que responde PHP

            // ✅ Notificación de éxito
            alert('✅ Vehículo agregado con éxito');

            // Opcional: limpiar el formulario
            form.reset();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Hubo un error al agregar el vehículo');
        });
    });
});
