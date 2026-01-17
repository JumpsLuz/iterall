<?php
require_once '../app/Models/Proyecto.php';

class ProyectoController {
    private $modeloProyecto;

    public function __construct() {
        $this->modeloProyecto = new Proyecto();
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Validaciones básicas
            if (empty($_POST['titulo']) || empty($_POST['categoria_id']) || empty($_POST['estado_id'])) {
                 header('Location: crear_proyecto.php?error=campos_requeridos');
                 exit();
            }

            $datos = [
                'creador_id' => $_SESSION['usuario_id'],
                'categoria_id' => $_POST['categoria_id'],
                'estado_id' => $_POST['estado_id'],
                'titulo' => $_POST['titulo'],
                'descripcion' => $_POST['descripcion'] ?? '',
                'es_publico' => isset($_POST['es_publico']) ? 1 : 0
            ];

            // Intentar crear
            $exito = $this->modeloProyecto->crear($datos);

            if ($exito) {
                // Éxito: Vamos al listado o al detalle
                header('Location: mis_proyectos.php?mensaje=proyecto_creado');
                exit();
            } else {
                header('Location: crear_proyecto.php?error=no_se_pudo_crear');
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
                header('Location: editar_proyecto.php?id=' . $proyecto_id . '&error=1');
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
?>