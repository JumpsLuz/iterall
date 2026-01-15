<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM perfiles WHERE usuario_id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Perfil Artista</title>
        <link rel="stylesheet" href="css/dashboard.css">
    </head>
    <body>
        <div class="dashboard-container">
            <header class="profile-header">
                <div class="banner-container" style="background-image: url('<?php echo $perfil['banner_url'] ?? 'img/default-banner.jpg'; ?>');">
                </div>
                
                <div class="avatar-wrapper">
                    <img src="<?php echo $perfil['avatar_url'] ?? 'img/default-avatar.png'; ?>" alt="Avatar" class="avatar-img">
                </div>

                <div class="profile-info">
                    <h1><?php echo $perfil['nombre_artistico'] ?? 'Artista Nuevo'; ?></h1>
                    <p><?php echo $perfil['biografia'] ?? 'Todavía no has escrito una biografía.'; ?></p>
                    <div class="actions">
                        <a href="completar_perfil.php"><button>Editar Perfil</button></a>
                        <a href="procesador.php?action=logout"><button>Cerrar Sesión</button></a>
                    </div>
                </div>
            </header>

            <main>
                <section style="padding: 20px;">
                    <h3>Tus Proyectos</h3>
                    <p>Aún no tienes proyectos creados.</p>
                </section>
            </main>
        </div>
    </body>
</html>