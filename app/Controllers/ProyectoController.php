<?php
class ProyectoController {
    private $modeloProyecto;

    public function __construct() {
        $this->modeloProyecto = new Proyecto();
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'creador_id' => $_SESSION['usuario_id'],
                'categoria_id' => $_POST['categoria_id'],
                'estado_id' => $_POST['estado_id'],
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'] ?? '',
                'es_publico' => isset($_POST['es_publico']) ? 1 : 0
            ];

            $exito = $this->modeloProyecto->crear($datos);

            if ($exito) {
                header('Location: mis_proyectos.php?mensaje=proyecto_creado');
                exit();
            } else {
                header('Location: crear_proyecto.php?error=1');
                exit();
            }
        }
    }

    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto_id = $_POST['proyecto_id'];

            $datos = [
                'categoria_id' => $_POST['categoria_id'],
                'estado_id' => $_POST['estado_id'],
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'] ?? '',
                'es_publico' => isset($_POST['es_publico']) ? 1 : 0
            ];

            $exito = $this->modeloProyecto->actualizar($proyecto_id, $datos, $_SESSION['usuario_id']);

            if ($exito) {
                header('Location: ver_proyecto.php?id=' . $proyecto_id . '&mensaje=actualizado');
                exit();
            } else {
                HEADER('Location: editar_proyecto.php?id=' . $proyecto_id . '&error=1');
                exit();
            }
        }
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto_id = $_POST['proyecto_id'];

            $exito = $this->modeloProyecto->eliminar($proyecto_id, $_SESSION['usuario_id']);

            if ($exito) {
                header('Location: mis_proyectos.php?mensaje=proyecto_eliminado');
                exit();
            } else {
                header('Location: mis_proyectos.php?error=no_se_pudo_eliminar');
                exit();
            }
        }
    }
}