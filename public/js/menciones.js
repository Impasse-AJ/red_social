// Autocompletado de menciones @amigo en los cuadros de publicar y comentar.
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('textarea[name="contenido"]').forEach(activarMenciones);

    function activarMenciones(textarea) {
        const panel = document.createElement("div");
        panel.className = "mention-suggest";
        panel.hidden = true;
        textarea.insertAdjacentElement("afterend", panel);

        // El panel se superpone (absolute) respecto al formulario del textarea
        const contenedor = textarea.parentElement;
        if (getComputedStyle(contenedor).position === "static") {
            contenedor.style.position = "relative";
        }

        // Colocar el panel pegado bajo el textarea, con su mismo ancho
        function posicionar() {
            panel.style.top = (textarea.offsetTop + textarea.offsetHeight + 4) + "px";
            panel.style.left = textarea.offsetLeft + "px";
            panel.style.width = textarea.offsetWidth + "px";
        }

        let sugerencias = [];
        let activa = -1;
        let ultimaPeticion = 0;

        textarea.addEventListener("input", async function () {
            const token = tokenActual();
            if (token === null) {
                cerrar();
                return;
            }

            const marca = ++ultimaPeticion;
            try {
                const respuesta = await fetch("/menciones/sugerencias?q=" + encodeURIComponent(token), {
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });
                if (!respuesta.ok || marca !== ultimaPeticion) return;

                sugerencias = await respuesta.json();
                pintar();
            } catch (e) {
                cerrar();
            }
        });

        textarea.addEventListener("keydown", function (evento) {
            if (panel.hidden) return;

            if (evento.key === "ArrowDown") {
                evento.preventDefault();
                mover(1);
            } else if (evento.key === "ArrowUp") {
                evento.preventDefault();
                mover(-1);
            } else if (evento.key === "Enter" && activa >= 0) {
                evento.preventDefault();
                elegir(sugerencias[activa].nombre);
            } else if (evento.key === "Escape") {
                cerrar();
            }
        });

        // Con un pequeño retardo para que el clic en una sugerencia llegue antes
        textarea.addEventListener("blur", function () {
            setTimeout(cerrar, 150);
        });

        // Texto entre la última @ y el cursor, si estamos escribiendo una mención
        function tokenActual() {
            const previo = textarea.value.slice(0, textarea.selectionStart);
            const coincidencia = previo.match(/(?:^|\s)@([a-zA-Z0-9_.-]*)$/);
            return coincidencia ? coincidencia[1] : null;
        }

        function pintar() {
            panel.innerHTML = "";
            activa = -1;

            if (sugerencias.length === 0) {
                cerrar();
                return;
            }

            sugerencias.forEach(function (sugerencia, indice) {
                const opcion = document.createElement("button");
                opcion.type = "button";
                opcion.className = "mention-suggest-item";

                const avatar = document.createElement("img");
                avatar.src = sugerencia.avatar;
                avatar.alt = "";
                avatar.className = "avatar avatar-sm";

                const nombre = document.createElement("span");
                nombre.textContent = "@" + sugerencia.nombre;

                opcion.append(avatar, nombre);
                opcion.addEventListener("mousedown", function (evento) {
                    evento.preventDefault(); // no perder el foco del textarea
                    elegir(sugerencia.nombre);
                });
                opcion.addEventListener("mouseenter", function () {
                    marcar(indice);
                });

                panel.appendChild(opcion);
            });

            posicionar();
            panel.hidden = false;
            marcar(0);
        }

        function marcar(indice) {
            activa = indice;
            panel.querySelectorAll(".mention-suggest-item").forEach(function (elemento, i) {
                elemento.classList.toggle("activa", i === activa);
            });
        }

        function mover(salto) {
            if (sugerencias.length === 0) return;
            marcar((activa + salto + sugerencias.length) % sugerencias.length);
        }

        function elegir(nombre) {
            const cursor = textarea.selectionStart;
            const previo = textarea.value.slice(0, cursor);
            const resto = textarea.value.slice(cursor);
            const sinToken = previo.replace(/@[a-zA-Z0-9_.-]*$/, "");

            const nuevo = sinToken + "@" + nombre + " ";
            textarea.value = nuevo + resto;
            textarea.focus();
            textarea.setSelectionRange(nuevo.length, nuevo.length);

            cerrar();
        }

        function cerrar() {
            panel.hidden = true;
            panel.innerHTML = "";
            sugerencias = [];
            activa = -1;
        }
    }
});
