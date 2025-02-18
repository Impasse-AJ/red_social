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
            if (data.error) {
                alert("Error al agregar comentario: " + data.error);
                return;
            }

            if (!data.id) {
                alert("Error crítico: No se recibió un ID válido para el comentario.");
                return;
            }

            // ✅ Crear nuevo comentario dinámicamente con estilos
            const nuevoComentario = document.createElement("li");
            nuevoComentario.id = `comentario-${data.id}`;
            nuevoComentario.classList.add("draggable-comentario");
            nuevoComentario.setAttribute("draggable", true);
            nuevoComentario.innerHTML = `<strong>${data.usuario}</strong>: ${data.contenido} 
                <small>(${data.fecha})</small>`;

            // 🎨 Estilos para los comentarios
            nuevoComentario.style.backgroundColor = "#fff";
            nuevoComentario.style.padding = "10px";
            nuevoComentario.style.marginBottom = "10px";
            nuevoComentario.style.borderRadius = "5px";
            nuevoComentario.style.boxShadow = "0 2px 5px rgba(0, 0, 0, 0.1)";
            nuevoComentario.style.textAlign = "left";
            nuevoComentario.style.cursor = "grab";

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
        // 🎨 Estilos de la papelera
        papelera.style.backgroundColor = "#f8d7da";
        papelera.style.color = "#721c24";
        papelera.style.padding = "10px";
        papelera.style.border = "2px dashed #721c24";
        papelera.style.textAlign = "center";
        papelera.style.marginTop = "15px";
        papelera.style.fontWeight = "bold";
        papelera.style.transition = "background 0.3s ease";
        
        papelera.addEventListener("dragover", function (event) {
            event.preventDefault();
            papelera.style.backgroundColor = "#dc3545";
            papelera.style.color = "#fff";
        });

        papelera.addEventListener("dragleave", function () {
            papelera.style.backgroundColor = "#f8d7da";
            papelera.style.color = "#721c24";
        });

        papelera.addEventListener("drop", function (event) {
            event.preventDefault();
            papelera.style.backgroundColor = "#f8d7da";
            papelera.style.color = "#721c24";

            const comentarioIdCompleto = event.dataTransfer.getData("text");

            if (!comentarioIdCompleto.startsWith("comentario-")) {
                alert("Error: El elemento arrastrado no es un comentario válido.");
                return;
            }

            const idNumerico = comentarioIdCompleto.split("-")[1];
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
        fetch(`/EliminarComentario/${comentarioId}`, {
            method: "DELETE",
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                elemento.remove();
            } else {
                alert("Error al eliminar el comentario: " + data.error);
            }
        })
        .catch(error => {
            alert("Hubo un error eliminando el comentario.");
        });
    }
});
