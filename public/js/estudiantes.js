var db_tabla_estudiantes = 'rc_abconta_estudiantes';
var db_columnas_estudiantes = Array('estudiante_id', 'nombre_completo', 'periodo_abconta');
var id;
var table_estudiantes;

window.addEventListener('DOMContentLoaded', (event) => {
    var modal = new bootstrap.Modal(document.getElementById('modal'), {
        keyboard: false
    })
    var modal_titulo = document.getElementById('modal-titulo');
    var modal_contenido = $("#modal-contenido");
    var modal_accion = document.getElementById('modal-accion');
    var modal_form = document.getElementById('modal-form');

    if (table_estudiantes instanceof $.fn.dataTable.Api) {
        return
    }

    table_estudiantes = $("#table_estudiantes").DataTable({
        dom: 'lfrtip',
        language: {
            url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json",
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: 'src/Actions/control_tables.php',
            data: {
                accion: 'lista',
                tabla: db_tabla_estudiantes,
                columnas: db_columnas_estudiantes,
            },
            type: 'POST',
        },
        columns: [
            { data: 'estudiante_id' },
            { data: 'nombre_completo' },
            { data: 'periodo_abconta' },
            {
                orderable: false,
                data: 'estudiante_id',
                render: function (data) {
                    return `<div class="text-center">
                                <div class="btn-group">
                                    <button data_id="${data}" type="button" class="btn btn-warning btn-editar-estudiantes">Editar</button>
                                    <button data_id="${data}" type="button" class="btn btn-danger btn-eliminar-estudiantes">Eliminar</button>
                                </div>
                            </div>`;
                },
            },
        ]
    });

    $('#table_estudiantes').on('init.dt', function () {
        filtro(table_estudiantes);
    });

    $('#table_estudiantes').on('click', 'button.btn-eliminar-estudiantes', function (event) {
        eliminar(event, db_tabla_estudiantes, 'table_estudiantes');
    });

    document.getElementById('btn-crear-estudiantes').addEventListener("click", function (event) {
        modal_parametros('estudiantes', 'Nuevo', 'Crear');
    });

    $('#table_estudiantes').on('click', 'button.btn-editar-estudiantes', function (event) {
        buscar(event, db_tabla_estudiantes, 'est_');
        modal_parametros('estudiantes', 'Modificar', 'Editar');
    });

    modal_form.addEventListener("submit", function (e) {
        let formulario = modal_accion.getAttribute("formulario");
        let accion = modal_accion.getAttribute("accion");
        let array = {};
        document.querySelectorAll("input[name]").forEach(element => {
            array[`${element.name}`] = element.value
        });
        if (formulario == 'estudiantes') {
            e.preventDefault();
            $.ajax({
                url: "src/Actions/control_tables.php",
                type: 'POST',
                data: {
                    accion: accion,
                    tabla: db_tabla_estudiantes,
                    array: array,
                    buscar: id,
                },
            }).done(function (res) {
                $('#table_estudiantes').DataTable().ajax.reload();
                if (res) {
                    alert('AlertPlaceholder', `Se ha guardado el registro`, 'success')
                } else {
                    alert('AlertPlaceholder', `Error al guardar el registro`, 'danger')
                }
            });
        }
    });

    function modal_parametros(formulario, titulo, accion) {
        modal_form.reset();
        modal_titulo.innerHTML = `${titulo} registro 'Tabla ${formulario}'`;
        modal_contenido.load(`template/main/forms/${formulario}.html`);
        modal_accion.innerHTML = `${accion}`;
        modal_accion.setAttribute('formulario', `${formulario}`);
        modal_accion.setAttribute('accion', `${accion}`);
        modal.toggle();
    }
});

