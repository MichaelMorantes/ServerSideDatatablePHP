var db_tabla_notas = 'rc_abconta_historico_notas';
var db_columnas_notas = Array('historico_notas_id', 'estudiante_id', 'periodo_id', 'facultad_id', 'plan_estudio_id', 'jornada_id', 'semestre', 'materia_id', 'grupo', 'nota_corte_1', 'nota_corte_2', 'nota_corte_3', 'nota_habilitacion', 'nota_homologacion', 'nota_validacion', 'nota_curso_verano', 'nota_definitiva', 'periodo_abconta');
var id;
var table_notas;

window.addEventListener('DOMContentLoaded', (event) => {
    var modal = new bootstrap.Modal(document.getElementById('modal'), {
        keyboard: false
    })
    var modal_titulo = document.getElementById('modal-titulo');
    var modal_contenido = $("#modal-contenido");
    var modal_accion = document.getElementById('modal-accion');
    var modal_form = document.getElementById('modal-form');

    if (table_notas instanceof $.fn.dataTable.Api) {
        return
    }

    table_notas = $("#table_notas").DataTable({
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
                tabla: db_tabla_notas,
                columnas: db_columnas_notas,
            },
            type: 'POST',
        },
        columns: [
            // { data: 'historico_notas_id' },
            { data: 'estudiante_id' },
            { data: 'periodo_id' },
            { data: 'facultad_id' },
            { data: 'plan_estudio_id' },
            { data: 'jornada_id' },
            { data: 'semestre' },
            { data: 'materia_id' },
            { data: 'grupo' },
            { data: 'nota_corte_1' },
            { data: 'nota_corte_2' },
            { data: 'nota_corte_3' },
            { data: 'nota_habilitacion' },
            { data: 'nota_homologacion' },
            { data: 'nota_validacion' },
            { data: 'nota_curso_verano' },
            { data: 'nota_definitiva' },
            { data: 'periodo_abconta' },
            {
                orderable: false,
                data: 'historico_notas_id',
                render: function (data) {
                    return `<div class="text-center">
                                <div class="btn-group">
                                    <button data_id="${data}" type="button" class="btn btn-warning btn-editar-notas">Editar</button>
                                    <button data_id="${data}" type="button" class="btn btn-danger btn-eliminar-notas">Eliminar</button>
                                </div>
                            </div>`;
                },
            },
        ]
    });

    $('#table_notas').on('init.dt', function () {
        filtro(table_notas);
    });

    $('#table_notas').on('click', 'button.btn-eliminar-notas', function (event) {
        eliminar(event, db_tabla_notas, 'table_notas');
    });

    document.getElementById('btn-crear-notas').addEventListener("click", function (event) {
        modal_parametros('notas', 'Nuevo', 'Crear');
    });

    $('#table_notas').on('click', 'button.btn-editar-notas', function (event) {
        buscar(event, db_tabla_notas, 'not_');
        modal_parametros('notas', 'Modificar', 'Editar');
    });

    modal_form.addEventListener("submit", function (e) {
        let formulario = modal_accion.getAttribute("formulario");
        let accion = modal_accion.getAttribute("accion");
        let array = {};
        document.querySelectorAll("input[name]").forEach(element => {
            array[`${element.name}`] = element.value
        });
        if (formulario == 'notas') {
            e.preventDefault();
            $.ajax({
                url: "src/Actions/control_tables.php",
                type: 'POST',
                data: {
                    accion: accion,
                    tabla: db_tabla_notas,
                    array: array,
                    buscar: id,
                },
            }).done(function (res) {
                $('#table_notas').DataTable().ajax.reload();
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

