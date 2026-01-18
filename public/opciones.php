<?php
require_once '../app/Config/auth_check.php';
require_once '../app/Config/Database.php';

$usuario_id = $_SESSION['usuario_id'];
$db = Database::getInstance();

$stmt = $db->prepare("SELECT * FROM perfiles WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opciones | ITERALL</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .options-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .option-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .option-card h3 {
            margin-top: 0;
            color: var(--text-main);
        }

        .danger-zone {
            border-color: var(--danger);
            background: rgba(220, 53, 69, 0.05);
        }

        .danger-zone h3 {
            color: var(--danger);
        }

        .confirmation-steps {
            display: none;
            margin-top: 1rem;
        }

        .confirmation-steps.active {
            display: block;
        }

        .step {
            margin-bottom: 1rem;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: 4px;
        }

        .step input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        .step label {
            cursor: pointer;
        }

        .final-confirmation {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid var(--danger);
            border-radius: 4px;
        }

        .final-confirmation.active {
            display: block;
        }

        .warning-text {
            color: var(--danger);
            font-weight: bold;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="options-container">
            <h1><i class="fas fa-cog"></i> Opciones de Cuenta</h1>

            <?php if (isset($_GET['error'])): ?>
                <div class="badge badge-status" style="display:block; padding: 10px; margin-bottom: 20px; background: var(--danger);">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php
                    $errores = [
                        'confirmacion_requerida' => 'Debes confirmar la eliminación.',
                        'confirmacion_incorrecta' => 'La confirmación no es correcta.',
                        'error_eliminar_cuenta' => 'Error al eliminar la cuenta. Inténtalo de nuevo.',
                        'error_inesperado' => 'Error inesperado. Contacta al soporte.'
                    ];
                    echo $errores[$_GET['error']] ?? 'Error desconocido.';
                    ?>
                </div>
            <?php endif; ?>

            <div class="option-card">
                <h3><i class="fas fa-arrow-left"></i> Navegación</h3>
                <p>Regresa al dashboard principal.</p>
                <a href="dashboard_artista.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Volver al Dashboard
                </a>
            </div>

            <div class="option-card danger-zone">
                <h3><i class="fas fa-exclamation-triangle"></i> Zona de Peligro</h3>
                <p>Acciones irreversibles que afectan tu cuenta permanentemente.</p>

                <button type="button" class="btn btn-danger" onclick="showDeleteConfirmation()">
                    <i class="fas fa-trash"></i> Eliminar Cuenta
                </button>

                <div id="deleteConfirmation" class="confirmation-steps">
                    <div class="step">
                        <input type="checkbox" id="step1" onchange="checkStep(1)">
                        <label for="step1">Entiendo que esta acción eliminará permanentemente todos mis posts, proyectos, mini-proyectos e iteraciones.</label>
                    </div>

                    <div class="step">
                        <input type="checkbox" id="step2" onchange="checkStep(2)">
                        <label for="step2">Entiendo que todas las imágenes asociadas serán eliminadas de Cloudinary y no podrán recuperarse.</label>
                    </div>

                    <div class="step">
                        <input type="checkbox" id="step3" onchange="checkStep(3)">
                        <label for="step3">Entiendo que mi perfil, incluyendo avatar y banner, será eliminado permanentemente.</label>
                    </div>

                    <div id="finalConfirmation" class="final-confirmation">
                        <div class="warning-text">
                            <i class="fas fa-exclamation-triangle"></i>
                            ¡ATENCIÓN! Esta acción no se puede deshacer.
                        </div>
                        <p>Para confirmar la eliminación permanente de tu cuenta, escribe exactamente: <strong>ELIMINAR_CUENTA_PERMANENTEMENTE</strong></p>

                        <form method="POST" action="procesador.php?action=eliminar_cuenta" onsubmit="return confirmFinalDeletion()">
                            <input type="text" name="confirmacion" id="confirmacionInput" placeholder="Escribe la confirmación aquí" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid var(--border); border-radius: 4px;">
                            <button type="submit" class="btn btn-danger" style="width: 100%;">
                                <i class="fas fa-trash"></i> CONFIRMAR ELIMINACIÓN PERMANENTE
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let stepsCompleted = [false, false, false];

        function showDeleteConfirmation() {
            document.getElementById('deleteConfirmation').classList.add('active');
        }

        function checkStep(stepNumber) {
            stepsCompleted[stepNumber - 1] = document.getElementById('step' + stepNumber).checked;

            if (stepsCompleted.every(step => step)) {
                document.getElementById('finalConfirmation').classList.add('active');
            } else {
                document.getElementById('finalConfirmation').classList.remove('active');
            }
        }

        function confirmFinalDeletion() {
            const input = document.getElementById('confirmacionInput').value;
            if (input !== 'ELIMINAR_CUENTA_PERMANENTEMENTE') {
                alert('La confirmación debe ser exactamente: ELIMINAR_CUENTA_PERMANENTEMENTE');
                return false;
            }

            return confirm('¿Estás ABSOLUTAMENTE seguro? Esta acción eliminará tu cuenta y todos tus datos permanentemente.');
        }
    </script>

</body>
</html>