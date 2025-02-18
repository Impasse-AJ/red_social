document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("comentario-form");
    const comentariosLista = document.getElementById("comentarios-lista");
    const noComentarios = document.getElementById("no-comentarios");
    const textarea = document.getElementById("contenido");
    const papelera = document.getElementById("papelera");
    const storageKey = "comentarioPendiente";

    // ✅ Restaurar comentario guardado si existe
    if (localStorage.getItem(storageKey)) {
        textarea.value = localStorage.getItem(storageKey);
    }

    // ✅ Guardar automáticamente lo que escribe el usuario
    textarea.addEventListener("input", function () {
        localStorage.setItem(storageKey, textarea.value);
    });

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const contenido = textarea.value.trim();
        if (contenido === "") {
            alert("El comentario no puede estar vacío.");
            return;
        }

        fetch(form.action, {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams(new FormData(form))
        })
        .then(response => response.json())
        .then(data => {
            console.log("Respuesta del servidor:", data);

            if (data.error) {
                alert("Error al agregar comentario: " + data.error);
                return;
            }

            if (!data.id) {
                alert("Error crítico: No se recibió un ID válido para el comentario. Recarga la página e intenta nuevamente.");
                return;
            }

            // ✅ Crear nuevo comentario dinámicamente con ID real
            const nuevoComentario = document.createElement("li");
            nuevoComentario.id = `comentario-${data.id}`;
            nuevoComentario.classList.add("draggable-comentario");
            nuevoComentario.setAttribute("draggable", true);
            nuevoComentario.innerHTML = `<strong>${data.usuario}</strong>: ${data.contenido} 
                <small>(${data.fecha})</small>`;

            comentariosLista.appendChild(nuevoComentario);

            // ✅ Habilitar Drag & Drop en el nuevo comentario
            addDragFunctionality(nuevoComentario);

            // ✅ Ocultar mensaje "No hay comentarios" si es necesario
            if (noComentarios) {
                noComentarios.style.display = "none";
            }

            // ✅ Limpiar el textarea y el localStorage después de enviar
            textarea.value = "";
            localStorage.removeItem(storageKey);
        })
        .catch(error => console.error("Error al enviar el comentario:", error));
    });

    if (papelera) {
        papelera.addEventListener("dragover", function (event) {
            event.preventDefault();
            papelera.classList.add("dragover");
        });

        papelera.addEventListener("dragleave", function () {
            papelera.classList.remove("dragover");
        });

        papelera.addEventListener("drop", function (event) {
            event.preventDefault();
            papelera.classList.remove("dragover");

            const comentarioIdCompleto = event.dataTransfer.getData("text");
            console.log("Elemento arrastrado:", comentarioIdCompleto);

            if (!comentarioIdCompleto || !comentarioIdCompleto.startsWith("comentario-")) {
                alert("Error: El elemento arrastrado no es un comentario válido.");
                return;
            }

            const idNumerico = comentarioIdCompleto.split("-")[1];
            console.log("ID numérico extraído:", idNumerico);

            const comentarioElemento = document.getElementById(comentarioIdCompleto);
            if (comentarioElemento) {
                eliminarComentario(idNumerico, comentarioElemento);
            }
        });
    }

    function addDragFunctionality(comentario) {
        comentario.addEventListener("dragstart", function (event) {
            event.dataTransfer.setData("text", event.target.id);
        });
    }

    document.querySelectorAll(".draggable-comentario").forEach(addDragFunctionality);

    function eliminarComentario(comentarioId, elemento) {
        console.log(`Intentando eliminar comentario con ID: ${comentarioId}`);

        fetch(`/EliminarComentario/${comentarioId}`, {
            method: "DELETE",
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(response => {
            console.log("Estado de la respuesta:", response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Respuesta del servidor:", data);

            if (data.success) {
                elemento.remove();
          
            } else {
                alert("Error al eliminar el comentario: " + data.error);
            }
        })
        .catch(error => {
            console.error("Error en la petición AJAX:", error);
            alert("Hubo un error eliminando el comentario.");
        });
    }
});
