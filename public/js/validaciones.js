document.addEventListener("DOMContentLoaded", function () {
    // Validar formulario de registro
    const registroForm = document.getElementById("registroForm");
    if (registroForm) {
        registroForm.addEventListener("submit", function (event) {
            const email = document.getElementById("email").value.trim();
            const nombreUsuario = document.getElementById("nombre_usuario").value.trim();
            const contrasena = document.getElementById("contrasena").value.trim();

            if (!email.includes("@") || email.length < 5) {
                alert("Por favor, introduce un correo electrónico válido.");
                event.preventDefault();
                return;
            }

            if (nombreUsuario.length < 3) {
                alert("El nombre de usuario debe tener al menos 3 caracteres.");
                event.preventDefault();
                return;
            }

            if (contrasena.length < 6) {
                alert("La contraseña debe tener al menos 6 caracteres.");
                event.preventDefault();
                return;
            }
        });
    }
});
