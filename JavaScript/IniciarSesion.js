document.addEventListener("DOMContentLoaded", function () {
    const formInicio = document.getElementById("formInicioSesion");

    if (formInicio) {
        formInicio.addEventListener("submit", function (e) {
            e.preventDefault(); // Evita la recarga tradicional de la página

            const formData = new FormData(formInicio);

            fetch(formInicio.action, {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    // Redirige al usuario a la URL provista por el PHP (index.html o PanelAdmin.php)
                    window.location.href = data.redirect;
                } else {
                    // Muestra el mensaje de error (credenciales incorrectas o cuenta pendiente)
                    alert(data.error || "Ocurrió un error al iniciar sesión.");
                }
            })
            .catch(error => {
                console.error("Error en la solicitud:", error);
                alert("Hubo un problema de conexión con el servidor.");
            });
        });
    }
});