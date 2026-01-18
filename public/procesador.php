<?php 
session_start();
require_once '../vendor/autoload.php';
require_once '../app/Models/Usuario.php';
require_once '../app/Config/Database.php';
require_once '../app/Models/Proyecto.php';
require_once '../app/Controllers/ProyectoController.php';
require_once '../app/Controllers/UsuarioController.php';
require_once '../app/Controllers/PostController.php';

$action = $_GET['action'] ?? '';

$accionesPublicas = ['login', 'registrar'];

if (!in_array($action, $accionesPublicas) && !isset($_SESSION['usuario_id'])) {
    header('Location: index.php?error=sesion_expirada');
    exit();
}

if ($action === 'registrar') {
    $controller = new UsuarioController();
    $controller->registrar();
}

if ($action === 'login') {
    $controller = new UsuarioController();
    $controller->iniciarSesion();
}

if ($action === 'logout') {
    session_unset(); 
    session_destroy(); 
    header("Location: index.php?mensaje=sesion_cerrada");
    exit();
}

if ($action === 'actualizar_perfil') {
    try {
        require_once '../app/Models/RedSocial.php';
        
        $usuario_id = $_SESSION['usuario_id'];

        $avatarFile = null;
        $bannerFile = null;

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarFile = $_FILES['avatar'];
        }

        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $bannerFile = $_FILES['banner'];
        }

        $redesRecibidas = $_POST['redes'] ?? [];
        $validacion = RedSocial::validarMultiples($redesRecibidas);
        
        if (!empty($validacion['errores'])) {
            error_log("Errores en redes sociales: " . json_encode($validacion['errores']));
        }
        
        $redesSociales = $validacion['validas'];

        $modelo = new Usuario();
        $exito = $modelo->actualizarPerfil(
            $usuario_id,
            $_POST['nombre_artistico'],
            $_POST['biografia'] ?? '',
            $redesSociales,
            $avatarFile,
            $bannerFile
        );

        if ($exito) {
            $mensaje = 'perfil_actualizado';
            if (!empty($validacion['errores'])) {
                $mensaje .= '&redes_con_errores=1';
            }
            header('Location: dashboard_artista.php?mensaje=' . $mensaje);
            exit();
        } else {
            header('Location: editar_perfil.php?error=actualizar_perfil');
            exit();
        }

    } catch (Exception $e) {
        error_log("Error en procesador actualizar_perfil: " . $e->getMessage());
        header('Location: editar_perfil.php?error=error_inesperado');
        exit();
    }
}

if ($action === 'go_home') {
    if ($_SESSION['rol_id'] == 1) {
        header('Location: dashboard_artista.php');
    } else {
        header('Location: explorar.php');
    }
    exit();
}

if ($action === 'crear_proyecto') {
    $controller = new ProyectoController();
    $controller->crear();
}

if ($action === 'editar_proyecto') {
    $controller = new ProyectoController();
    $controller->editar();
}

if ($action === 'eliminar_proyecto') {
    $controller = new ProyectoController();
    $controller->eliminar();
}

if ($action === 'crear_post') {
    $controller = new PostController();
    $controller->crear();
}

if ($action === 'crear_post_rapido') {
    $controller = new PostController();
    $controller->crearRapido();
}

if ($action === 'toggle_destacado') {
    $controller = new PostController();
    $controller->alternarDestacado();
}

if ($action === 'eliminar_post') {
    $controller = new PostController();
    $controller->eliminar();
}

if ($action === 'convertir_a_miniproyecto') {
    if (!isset($_GET['post_id'])) {
        header('Location: dashboard_artista.php?error=post_no_especificado');
        exit();
    }
    
    require_once '../app/Models/Post.php';
    $modeloPost = new Post();
    
    $exito = $modeloPost->convertirAMiniproyecto($_GET['post_id'], $_SESSION['usuario_id']);
    
    if ($exito) {
        header('Location: ver_post.php?id=' . $_GET['post_id'] . '&mensaje=convertido_a_miniproyecto');
    } else {
        header('Location: ver_post.php?id=' . $_GET['post_id'] . '&error=error_convertir');
    }
    exit();
}

if ($action === 'crear_miniproyecto') {
    require_once '../app/Models/Miniproyecto.php';
    $modeloMini = new Miniproyecto();
    
    $datos = [
        'creador_id' => $_SESSION['usuario_id'],
        'proyecto_id' => !empty($_POST['proyecto_id']) ? $_POST['proyecto_id'] : null,
        'titulo' => $_POST['titulo'],
        'descripcion' => $_POST['descripcion'] ?? ''
    ];

    $id = $modeloMini->crear($datos);

    if ($id) {
        if ($datos['proyecto_id']) {
            header('Location: ver_proyecto.php?id=' . $datos['proyecto_id'] . '&mensaje=miniproyecto_creado');
        } else {
            header('Location: dashboard_artista.php?mensaje=miniproyecto_creado');
        }
    } else {
        header('Location: crear_miniproyecto.php?error=db_error');
    }
    exit();
}

if ($action === 'crear_iteracion') {
    require_once '../app/Controllers/IteracionController.php';
    
    if (isset($_POST['post_id'])) {
        $post_id = $_POST['post_id'];
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM imagenes_iteracion ii
            INNER JOIN iteraciones i ON ii.iteracion_id = i.id
            WHERE i.post_id = ?
        ");
        $stmt->execute([$post_id]);
        $totalImagenes = $stmt->fetchColumn();
        
        $nuevasImagenes = 0;
        if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['name'])) {
            $nuevasImagenes = count($_FILES['imagenes']['name']);
        }
        
        if (($totalImagenes + $nuevasImagenes) > 50) {
            header('Location: crear_iteracion.php?post_id=' . $post_id . '&error=limite_excedido');
            exit();
        }
        
        if ($nuevasImagenes > 20) {
            header('Location: crear_iteracion.php?post_id=' . $post_id . '&error=limite_excedido');
            exit();
        }
    }
    
    $controller = new IteracionController();
    $controller->crear();
}

if ($action === 'eliminar_iteracion') {
    require_once '../app/Controllers/IteracionController.php';
    $controller = new IteracionController();
    $controller->eliminar();
}

if ($action === 'actualizar_iteracion') {
    require_once '../app/Controllers/IteracionController.php';
    $controller = new IteracionController();
    $controller->actualizar();
}

if ($action === 'eliminar_cuenta') {
    $controller = new UsuarioController();
    $controller->eliminarCuenta();
}