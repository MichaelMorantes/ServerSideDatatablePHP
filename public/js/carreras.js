var db_tabla_carreras = 'rc_abconta_carreras';
var db_columnas_carreras = Array('plan_estudio_id', 'facultad_id', 'descripcion', 'semestre', 'plan_estudio_origen_id', 'tipo_carrera', 'institucion_convenio', 'periodo_abconta');
var id;
var table_carreras;

window.addEventListener('DOMContentLoaded', (event) => {
    var modal = new bootstrap.Modal(document.getElementById('modal'), {
        keyboard: false
    })
    var modal_titulo = document.getElementById('modal-titulo');
    var modal_contenido = $("#modal-contenido");
    var modal_accion = document.getElementById('modal-accion');
    var modal_form = document.getElementById('modal-form');

    if (table_carreras instanceof $.fn.dataTable.Api) {
        return
    }

    table_carreras = $("#table_carreras").DataTable({
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
                tabla: db_tabla_carreras,
                columnas: db_columnas_carreras,
            },
            type: 'POST',
        },
        columns: [
            { data: 'plan_estudio_id' },
            { data: 'facultad_id' },
            { data: 'descripcion' },
            { data: 'semestre' },
            { data: 'plan_estudio_origen_id' },
            { data: 'tipo_carrera' },
            { data: 'institucion_convenio' },
            { data: 'periodo_abconta' },
            {
                orderable: false,
                data: 'plan_estudio_id',
                render: function (data) {
                    return `<div class="text-center">
                                <div class="btn-group">
                                    <button data_id="${data}" type="button" class="btn btn-warning btn-editar-carreras">Editar</button>
                                    <button data_id="${data}" type="button" class="btn btn-danger btn-eliminar-carreras">Eliminar</button>
                                </div>
                            </div>`;
                },
            },
        ]
    });

    $('#table_carreras').on('init.dt', function () {
        filtro(table_carreras);
    });

    $('#table_carreras').on('click', 'button.btn-eliminar-carreras', function (event) {
        eliminar(event, db_tabla_carreras, 'table_carreras');
    });

    document.getElementById('btn-crear-carreras').addEventListener("click", function (event) {
        modal_parametros('carreras', 'Nuevo', 'Crear');
    });

    $('#table_carreras').on('click', 'button.btn-editar-carreras', function (event) {
        buscar(event, db_tabla_carreras, 'car_');
        modal_parametros('carreras', 'Modificar', 'Editar');
    });

    modal_form.addEventListener("submit", function (e) {
        let formulario = modal_accion.getAttribute("formulario");
        let accion = modal_accion.getAttribute("accion");
        let array = {};
        document.querySelectorAll("input[name]").forEach(element => {
            array[`${element.name}`] = element.value
        });
        if (formulario == 'carreras') {
            e.preventDefault();
            $.ajax({
                url: "src/Actions/control_tables.php",
                type: 'POST',
                data: {
                    accion: accion,
                    tabla: db_tabla_carreras,
                    array: array,
                    buscar: id,
                },
            }).done(function (res) {
                $('#table_carreras').DataTable().ajax.reload();
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

