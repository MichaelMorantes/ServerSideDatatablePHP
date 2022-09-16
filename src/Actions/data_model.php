<?php

require_once __DIR__ . '/../Model/model.php';
require_once __DIR__ . '/../Utils/alert/alert_model.php';

class data_model
{
    private $model;

    public function __construct()
    {
        $this->model = new model();
    }

    public function crear($tabla, $datos)
    {
        $key = array_keys($datos);
        $val = array_values($datos);
        $query = "INSERT INTO $tabla (" . implode(', ', $key) . ") "
            . "VALUES ('" . implode("', '", $val) . "')";

        // return $query;
        // die;
        return $this->model->executeUpdate($query);
    }

    public function eliminar($tabla, $datos)
    {
        $where = $this->where($tabla, $datos);

        $query = "DELETE FROM $tabla WHERE $where";

        // return $query;
        // die;
        return $this->model->executeUpdate($query);
    }

    public function formulario($tabla, $datos)
    {
        $where = $this->where($tabla, $datos);

        $query = $this->getQuery('form_listar_' . $tabla);
        // return $query . " WHERE {$where}";
        return $this->model->executeQuery($query . " WHERE {$where}");
    }

    public function editar($tabla, $datos)
    {
        $where = $this->where($tabla, $datos['buscar']);
        $cols = array();

        foreach ($datos['array'] as $key => $val) {
            $cols[] = "$key = '$val'";
        }
        $query = "UPDATE $tabla SET " . implode(', ', $cols) . " WHERE $where";

        // return $query;
        // die;
        return $this->model->executeUpdate($query);
    }

    public function where($tabla, $datos, $where = null)
    {
        switch ($tabla) {
            case 'rc_abconta_estudiantes':
                $where = "estudiante_id = '{$datos}'";
                break;
            case 'rc_abconta_carreras':
                $where = "plan_estudio_id = '{$datos}'";
                break;
            case 'rc_abconta_materias':
                $where = "materia_id = '{$datos}'";
                break;
            case 'rc_abconta_historico_notas':
                $where = "historico_notas_id = '{$datos}'";
                break;
            case 'rc_abconta_historico_notas_ex':
                $where = "historico_notas_id = '{$datos}'";
                break;
            default:
                alert::add(alert::ERROR, "¡Error! Parametro incorrecto en la expresión 'Where'.");
                header('Location: ../../');
                die;
                break;
        }

        return $where;
    }
    
    private function getQuery($query)
    {
        $content = file_get_contents(__DIR__ . "/../Database/sql/$query.sql");

        if (!is_string($content)) {
            return '';
        }

        return $content;
    }
}
