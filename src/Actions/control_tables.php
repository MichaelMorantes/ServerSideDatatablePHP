<?php
require_once __DIR__ . '/../Model/serverside.php';
require_once __DIR__ . '/../Actions/data_model.php';
header('Content-Type: application/json');

$data_model = new data_model();
// $params = array_map('utf8_decode', $_POST);
// var_dump($params);

switch ($_POST['accion']) {
    case 'lista':
        echo SSP::simple($_POST, $_POST['tabla'], $_POST['columnas']);
        break;
    case 'formulario':
        $result = $data_model->formulario($_POST['tabla'], $_POST['buscar']);
        echo json_encode(['data' => $result], JSON_UNESCAPED_UNICODE);
        break;
    case 'Crear':
        $result = $data_model->crear($_POST['tabla'], $_POST['array']);
        echo json_encode(['respuesta' => $result]);
        break;
    case 'Editar':
        $result = $data_model->editar($_POST['tabla'], $_POST);
        echo json_encode(['respuesta' => $result]);
        break;
    case 'eliminar':
        $result = $data_model->eliminar($_POST['tabla'], $_POST['buscar']);
        echo json_encode(['respuesta' => $result]);
        break;
    default:
        echo json_encode(['respuesta' => false]);
        break;
}
