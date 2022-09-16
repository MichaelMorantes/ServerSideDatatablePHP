var db_tabla_materias = 'rc_abconta_materias';
var db_columnas_materias = Array('materia_id', 'facultad_id', 'plan_estudio_id', 'jornada_id', 'semestre', 'orden', 'materia_desc', 'materia_prerequisito_id', 'materia_corequisito_id', 'materia_equivalente_id', 'hora_semanal', 'periodo_abconta');
var id;
var table_materias;

window.addEventListener('DOMContentLoaded', (event) => {
    var modal = new bootstrap.Modal(document.getElementById('modal'), {
        keyboard: false
    })
    var modal_titulo = document.getElementById('modal-titulo');
    var modal_contenido = $("#modal-contenido");
    var modal_accion = document.getElementById('modal-accion');
    var modal_form = document.getElementById('modal-form');

    if (table_materias instanceof $.fn.dataTable.Api) {
        return
    }

    table_materias = $("#table_materias").DataTable({
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
                tabla: db_tabla_materias,
                columnas: db_columnas_materias,
            },
            type: 'POST',
        },
        columns: [
            { data: 'materia_id' },
            { data: 'facultad_id' },
            { data: 'plan_estudio_id' },
            { data: 'jornada_id' },
            { data: 'semestre' },
            { data: 'orden' },
            { data: 'materia_desc' },
            { data: 'materia_prerequisito_id' },
            { data: 'materia_corequisito_id' },
            { data: 'materia_equivalente_id' },
            { data: 'hora_semanal' },
            { data: 'periodo_abconta' },
            {
                orderable: false,
                data: 'materia_id',
                render: function (data) {
                    return `<div class="text-center">
                                <div class="btn-group">
                                    <button data_id="${data}" type="button" class="btn btn-warning btn-editar-materias">Editar</button>
                                    <button data_id="${data}" type="button" class="btn btn-danger btn-eliminar-materias">Eliminar</button>
                                </div>
                            </div>`;
                },
            },
        ]
    });

    $('#table_materias').on('init.dt', function () {
        filtro(table_materias);
    });

    $('#table_materias').on('click', 'button.btn-eliminar-materias', function (event) {
        eliminar(event, db_tabla_materias, 'table_materias');
    });

    document.getElementById('btn-crear-materias').addEventListener("click", function (event) {
        modal_parametros('materias', 'Nuevo', 'Crear');
    });

    $('#table_materias').on('click', 'button.btn-editar-materias', function (event) {
        buscar(event, db_tabla_materias, 'mat_');
        modal_parametros('materias', 'Modificar', 'Editar');
    });

    modal_form.addEventListener("submit", function (e) {
        let formulario = modal_accion.getAttribute("formulario");
        let accion = modal_accion.getAttribute("accion");
        let array = {};
        document.querySelectorAll("input[name]").forEach(element => {
            array[`${element.name}`] = element.value
        });
        if (formulario == 'materias') {
            e.preventDefault();
            $.ajax({
                url: "src/Actions/control_tables.php",
                type: 'POST',
                data: {
                    accion: accion,
                    tabla: db_tabla_materias,
                    array: array,
                    buscar: id,
                },
            }).done(function (res) {
                $('#table_materias').DataTable().ajax.reload();
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

