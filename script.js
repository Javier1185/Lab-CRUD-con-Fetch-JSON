/**
 * script.js — Lógica frontend del CRUD de Productos
 * Captura eventos, construye FormData, llama a registrar.php con fetch
 * y procesa las respuestas mediante SweetAlert2.
 */

document.addEventListener("DOMContentLoaded", () => {

  const form        = document.getElementById("formProducto");
  const btnGuardar  = document.getElementById("btnGuardar");
  const btnBuscar   = document.getElementById("btnBuscar");
  const inputId     = document.getElementById("id");
  const editBanner  = document.getElementById("editBanner");

  // ════════════════════════════════════════════════
  // FUNCIÓN: Enviar datos a registrar.php con fetch
  // ════════════════════════════════════════════════
  async function enviarAccion(accion) {
    const fd = new FormData(form);
    fd.append("Accion", accion);

    try {
      const respuesta = await fetch("registrar.php", {
        method: "POST",
        body: fd
      });

      if (!respuesta.ok) throw new Error(`Error HTTP: ${respuesta.status}`);

      const data = await respuesta.json();

      // Switch JS sobre la acción para manejar la respuesta
      switch (accion) {

        case "Guardar":
          if (data.success) {
            await Swal.fire({
              icon: "success",
              title: "¡Guardado!",
              text: data.message,
              background: "#111827",
              color: "#e2e8f0",
              confirmButtonColor: "#7c3aed",
              iconColor: "#4ade80"
            });
            limpiarFormulario();
            listarProductos();
          } else {
            mostrarErrores(data);
          }
          break;

        case "Modificar":
          if (data.success) {
            await Swal.fire({
              icon: "success",
              title: "¡Actualizado!",
              text: data.message,
              background: "#111827",
              color: "#e2e8f0",
              confirmButtonColor: "#7c3aed",
              iconColor: "#4ade80"
            });
            limpiarFormulario();
            listarProductos();
          } else {
            mostrarErrores(data);
          }
          break;

        case "Buscar":
          if (data.success) {
            const prod = data.data;

            // Cargar datos en el formulario
            inputId.value                              = prod.id;
            document.getElementById("Codigo").value   = prod.codigo;
            document.getElementById("Producto").value = prod.producto;
            document.getElementById("Precio").value   = prod.precio;
            document.getElementById("Cantidad").value = prod.cantidad;

            // Activar modo edición
            activarModoEdicion();

            Swal.fire({
              icon: "info",
              title: "Producto encontrado",
              text: `${prod.producto} — $${parseFloat(prod.precio).toFixed(2)}`,
              timer: 2000,
              showConfirmButton: false,
              background: "#111827",
              color: "#e2e8f0",
              iconColor: "#a78bfa"
            });

          } else {
            mostrarErrores(data);
          }
          break;

        default:
          console.warn("Acción no manejada en switch JS:", accion);
          break;
      }

    } catch (error) {
      Swal.fire({
        icon: "error",
        title: "Error de conexión",
        text: error.message,
        background: "#111827",
        color: "#e2e8f0",
        confirmButtonColor: "#7c3aed",
        iconColor: "#f87171"
      });
    }
  }

  // ════════════════════════════════════════════════
  // FUNCIÓN: Mostrar errores con SweetAlert2
  // ════════════════════════════════════════════════
  function mostrarErrores(data) {
    const lista = data.errors
      ? data.errors.map(e => `• ${e}`).join("\n")
      : data.message;

    Swal.fire({
      icon: "error",
      title: "Error",
      text: lista || "Ocurrió un error inesperado.",
      background: "#111827",
      color: "#e2e8f0",
      confirmButtonColor: "#7c3aed",
      iconColor: "#f87171"
    });
  }

  // ════════════════════════════════════════════════
  // FUNCIÓN: Activar modo edición (botón + banner)
  // ════════════════════════════════════════════════
  function activarModoEdicion() {
    btnGuardar.textContent    = "";
    btnGuardar.dataset.accion = "Modificar";
    btnGuardar.className      = "btn btn-warning";

    // Ícono + texto del botón
    btnGuardar.innerHTML = `
      <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round">
        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
      </svg>
      Actualizar`;

    // Mostrar banner de modo edición
    if (editBanner) editBanner.classList.add("visible");
  }

  // ════════════════════════════════════════════════
  // FUNCIÓN: Limpiar formulario y resetear modo
  // ════════════════════════════════════════════════
  function limpiarFormulario() {
    form.reset();
    inputId.value             = "";
    btnGuardar.dataset.accion = "Guardar";
    btnGuardar.className      = "btn btn-primary";
    btnGuardar.innerHTML      = `
      <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round">
        <path d="M5 12l5 5L20 7"/>
      </svg>
      Registrar`;

    if (editBanner) editBanner.classList.remove("visible");
  }

  // ════════════════════════════════════════════════
  // FUNCIÓN: Badge de stock según cantidad
  // ════════════════════════════════════════════════
  function badgeStock(cantidad) {
    const n = parseInt(cantidad);
    if (n === 0)  return `<span class="badge-stock badge-out">Agotado</span>`;
    if (n <= 5)   return `<span class="badge-stock badge-low">${n}</span>`;
    return              `<span class="badge-stock badge-ok">${n}</span>`;
  }

  // ════════════════════════════════════════════════
  // FUNCIÓN: Listar todos los productos en la tabla
  // ════════════════════════════════════════════════
  async function listarProductos() {
    try {
      const fd = new FormData();
      fd.append("Accion", "Listar");

      const respuesta = await fetch("registrar.php", { method: "POST", body: fd });
      const data      = await respuesta.json();
      const tbody     = document.getElementById("tablaBody");
      const contador  = document.getElementById("contadorRegistros");

      tbody.innerHTML = "";

      if (!data.success || data.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="td-empty">No hay productos registrados.</td></tr>`;
        if (contador) contador.textContent = "0 registros";
        return;
      }

      data.data.forEach(prod => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
          <td class="td-id">${prod.id}</td>
          <td><span class="td-codigo">${prod.codigo}</span></td>
          <td class="td-nombre">${prod.producto}</td>
          <td class="td-precio">$${parseFloat(prod.precio).toFixed(2)}</td>
          <td>${badgeStock(prod.cantidad)}</td>
          <td>
            <button class="btn-edit" onclick="cargarParaEditar('${prod.codigo}')">
              ✏ Editar
            </button>
          </td>`;
        tbody.appendChild(fila);
      });

      if (contador) contador.textContent = data.data.length + " registros";

    } catch (error) {
      console.error("Error al listar:", error);
    }
  }

  // Exponer para los botones generados dinámicamente
  window.cargarParaEditar = function(codigo) {
    document.getElementById("Codigo").value = codigo;
    enviarAccion("Buscar");
  };

  // ════════════════════════════════════════════════
  // EVENTOS
  // ════════════════════════════════════════════════
  btnGuardar.addEventListener("click", () => {
    const accion = btnGuardar.dataset.accion || "Guardar";
    enviarAccion(accion);
  });

  btnBuscar.addEventListener("click", () => enviarAccion("Buscar"));

  document.getElementById("btnLimpiar").addEventListener("click", limpiarFormulario);

  // Cargar tabla al inicio
  listarProductos();

});