document.addEventListener("DOMContentLoaded", () => {
    const formulario = document.querySelector(".IniciarSesion");

    formulario.addEventListener("submit", async (e) => {
        e.preventDefault();

        const cedula = document.getElementById("cedula").value.trim();
        const password = document.getElementById("password").value.trim();

        if (!cedula || !password) {
            alert("Por favor complete todos los campos.");
            return;
        }

        const datos = new FormData();
        datos.append("cedula", cedula);
        datos.append("password", password);

        try {
            // Sube un nivel desde /pages/ e ingresa a /php/login.php
            const respuesta = await fetch("../php/login.php", {
                method: "POST",
                body: datos
            });

            const resultado = await respuesta.json();

            if (resultado.status === "success") {
                alert("¡Inicio de sesión exitoso!");
                window.location.href = "../index.html";
            } else {
                alert(resultado.message);
            }
        } catch (error) {
            console.error("Error en la solicitud:", error);
            alert("Ocurrió un error al intentar conectar con el servidor.");
        }
    });
});