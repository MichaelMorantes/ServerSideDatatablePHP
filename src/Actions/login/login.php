<?php

require_once __DIR__ . "/../../Utils/alert/alert_model.php";
require_once __DIR__ . "/../../Model/model.php";

session_start();

if (
	$_SERVER["REQUEST_METHOD"] !== "POST"
	|| !isset($_POST["usuario"])
	|| !isset($_POST["clave"])
	|| empty($_POST["usuario"])
	|| empty($_POST["clave"])
) {
	alert::add(alert::ERROR, "Acceso denegado");
    header("Location: ../../../"); exit; #index
}
$usuario = strtoupper($_POST["usuario"]);
$clave = $_POST["clave"];

$model = new model();
$exist = $model->doPing($usuario, $clave);

if ($exist) {
	$_SESSION["user"] = $usuario;
	$_SESSION["usuario_id"] = $res[0]['usuario_id'];
	$_SESSION["usuario"] = $res[0]['nombre'];
} else {
	alert::add(alert::ERROR, "Usuario o contraseña incorrecto");
}

header("Location: ../../../"); #index