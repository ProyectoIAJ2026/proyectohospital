document.addEventListener('DOMContentLoaded', () => {
    const formBuscar = document.getElementById('formBuscar');

    if (formBuscar) {
        formBuscar.addEventListener('submit', function(e) {
            e.preventDefault(); // Evita que la página se recargue

            const formData = new FormData(this);

            // Realiza la petición asíncrona al archivo PHP de búsqueda
            fetch('../php/BuscarPaciente.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('resultadoBusqueda').innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('resultadoBusqueda').innerHTML = '<p style="color: red;">Ocurrió un error al realizar la búsqueda.</p>';
            });
        });
    }
});