function alert(alertPlaceholder, message, type) {
    document.getElementById(alertPlaceholder).innerHTML = "";
    var wrapper = document.createElement('div')
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    document.getElementById(alertPlaceholder).append(wrapper)
}

function fill_input(form, array) {
    for (const [key, value] of Object.entries(array[0])) {
        document.getElementById(form + key).value = value;
    }
}

function filtro(tabla) {
    $(".dataTables_filter input[type='search']")
        .unbind() // Unbind previous default bindings
        .bind("input", function (e) { // Bind our desired behavior
            if (this.value.length >= 3) {
                tabla.search(this.value).draw();
            }
            if (this.value == "") {
                tabla.search("").draw();
            }
            return;
        });
}

function eliminar(event, nombre_tabla, table) {
    if (window.confirm(`Al dar clic en aceptar se eliminará el registro \n ¿Esta seguro de continuar?`)) {
        id = event.currentTarget.getAttribute('data_id');
        let fd = new FormData();
        fd.append('tabla', nombre_tabla);
        fd.append('buscar', id);
        fd.append('accion', 'eliminar');
        axios.post('src/Actions/control_tables.php', fd).then(function (res) {
            $('#' + table).DataTable().ajax.reload();
            if (res) {
                alert('AlertPlaceholder', `Se ha eliminado el registro`, 'success')
            } else {
                alert('AlertPlaceholder', `Error al eliminar el registro`, 'danger')
            }
        });
    }
}

function buscar(event, nombre_tabla, fill) {
    id = event.currentTarget.getAttribute('data_id');
    let fd = new FormData();
    fd.append('tabla', nombre_tabla);
    fd.append('buscar', id);
    fd.append('accion', 'formulario');
    axios.post('src/Actions/control_tables.php', fd).then(function (res) {
        fill_input(fill, res.data.data);
    });
}