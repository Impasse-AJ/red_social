document.addEventListener("DOMContentLoaded", function () {
    // Validar formulario de registro
    const registroForm = document.getElementById("registroForm");
    if (registroForm) {
        registroForm.addEventListener("submit", function (event) {
            const email = document.getElementById("email").value.trim();
            const nombreUsuario = document.getElementById("nombre_usuario").value.trim();
            const contrasena = document.getElementById("contrasena").value.trim();

            // Expresión regular para validar el correo electrónico
            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (!emailRegex.test(email)) {
                alert("Por favor, introduce un correo electrónico válido.");
                event.preventDefault();
                return;
            }

            // Expresión regular para validar el nombre de usuario (al menos 3 caracteres)
            const usuarioRegex = /^.{3,}$/;
            if (!usuarioRegex.test(nombreUsuario)) {
                alert("El nombre de usuario debe tener al menos 3 caracteres.");
                event.preventDefault();
                return;
            }

            // Expresión regular para validar la contraseña (al menos 6 caracteres)
            const contrasenaRegex = /^.{6,}$/;
            if (!contrasenaRegex.test(contrasena)) {
                alert("La contraseña debe tener al menos 6 caracteres.");
                event.preventDefault();
                return;
            }
        });
    }
});

