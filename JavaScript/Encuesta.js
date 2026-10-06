document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");

    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault(); // Evita que la página se recargue

        const formData = new FormData(form);

        try {
            // Envío asíncrono hacia Encuesta.php
            const response = await fetch("../php/Encuesta.php", {
                method: "POST",
                body: formData
            });

            const result = await response.text();

            if (response.ok) {
                alert("¡Muchas gracias por completar la encuesta!");
                form.reset(); // Limpia los campos del formulario
                window.location.href = "../index.html"; // Redirige al inicio
            } else {
                alert("Ocurrió un error al enviar la encuesta. Inténtelo de nuevo.");
            }
        } catch (error) {
            console.error("Error al procesar la encuesta:", error);
            alert("Error de conexión con el servidor.");
        }
    });
});