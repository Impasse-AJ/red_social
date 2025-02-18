 document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("comentario-form");
            const comentariosLista = document.getElementById("comentarios-lista");
            const noComentarios = document.getElementById("no-comentarios");
            const textarea = document.getElementById("contenido");

            form.addEventListener("submit", function (event) {
                event.preventDefault(); // Evita la recarga de la página

                const contenido = textarea.value.trim();
                if (contenido === "") {
                    alert("El comentario no puede estar vacío.");
                    return;
                }

                // Enviar comentario mediante Fetch API
                fetch(form.action, {
    method: "POST",
    body: new FormData(form)
})
.then(response => response.json()) // Convertimos la respuesta a JSON
.then(data => {
    if (data.error) {
        alert(data.error);
        return;
    }

    // Crear el nuevo comentario en el DOM con los datos del JSON
    const nuevoComentario = document.createElement("li");
    nuevoComentario.innerHTML = `<strong>${data.usuario}</strong>: ${data.contenido} 
        <small>(${data.fecha})</small>`;

    comentariosLista.appendChild(nuevoComentario);

    // Limpiar el textarea después de enviar el comentario
    textarea.value = "";

    // Si antes no había comentarios, ocultar el mensaje
    if (noComentarios) {
        noComentarios.style.display = "none";
    }
})
.catch(error => console.error("Error al enviar el comentario:", error));

            });
        });
