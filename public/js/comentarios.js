document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("comentario-form");
    const comentariosLista = document.getElementById("comentarios-lista");
    const noComentarios = document.getElementById("no-comentarios");
    const textarea = document.getElementById("contenido");
    const papelera = document.getElementById("papelera");
    const storageKey = "comentarioPendiente";

    // Restaurar comentario guardado si existe
    if (localStorage.getItem(storageKey)) {
        textarea.value = localStorage.getItem(storageKey);
    }

    // Guardar automáticamente lo que escribe el usuario
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

            comentariosLista.appendChild(crearComentario(data));

            // Ocultar mensaje "No hay comentarios" si es necesario
            if (noComentarios) {
                noComentarios.style.display = "none";
            }

            // Limpiar el textarea y el localStorage después de enviar
            textarea.value = "";
            localStorage.removeItem(storageKey);
        })
        .catch(error => console.error("Error al enviar el comentario:", error));
    });

    // Construye el <li> del comentario con el mismo marcado que la plantilla.
    // Se usa textContent (no innerHTML) para que el contenido no pueda inyectar HTML.
    function crearComentario(data) {
        const li = document.createElement("li");
        li.id = `comentario-${data.id}`;
        li.classList.add("comment", "draggable-comentario");
        li.setAttribute("draggable", true);

        const avatar = document.createElement("img");
        avatar.className = "avatar avatar-sm";
        avatar.src = form.dataset.avatar;
        avatar.alt = data.usuario;

        const body = document.createElement("div");
        body.className = "post-body";

        const meta = document.createElement("div");
        meta.className = "post-meta";

        const autor = document.createElement("span");
        autor.className = "post-author";
        autor.textContent = data.usuario;

        const fecha = document.createElement("span");
        fecha.className = "post-date";
        fecha.textContent = `· ${data.fecha}`;

        const contenidoEl = document.createElement("p");
        contenidoEl.className = "post-content";
        if (data.contenido_html) {
            // El servidor devuelve el contenido ya escapado y con las menciones enlazadas
            contenidoEl.innerHTML = data.contenido_html;
        } else {
            contenidoEl.textContent = data.contenido;
        }

        meta.append(autor, fecha);
        body.append(meta, contenidoEl);
        li.append(avatar, body);

        addDragFunctionality(li);
        return li;
    }

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
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-Token": papelera ? papelera.dataset.csrf : ""
            }
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
