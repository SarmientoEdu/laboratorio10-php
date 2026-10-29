<?php
require_once 'includes/seguridad.php';
require_once 'includes/CalculadoraFinanciera.php';

session_start();

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$resultado = null;
$errores   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Validación CSRF (antes de tocar cualquier otro dato)
    $tokenEnviado = $_POST['csrf_token'] ?? '';
    if (!is_string($tokenEnviado) || !hash_equals($_SESSION['csrf_token'], $tokenEnviado)) {
        exit('Error: token CSRF inválido.');
    }

    // 2) Sanitización
    $semilla = htmlspecialchars(trim((string)($_POST['cedula_semilla'] ?? '')), ENT_QUOTES, 'UTF-8');
    $monto   = filter_var(trim((string)($_POST['monto'] ?? '')), FILTER_VALIDATE_FLOAT);
    $tasa    = filter_var(trim((string)($_POST['tasa']  ?? '')), FILTER_VALIDATE_FLOAT);
    $plazo   = filter_var(trim((string)($_POST['plazo'] ?? '')), FILTER_VALIDATE_INT);

    // 3) Validación de reglas
    if ($semilla === '') {
        $errores[] = 'La semilla (cédula) es obligatoria.';
    }
    if ($monto === false || $monto < 100) {
        $errores[] = 'El monto debe ser un número mayor o igual a 100.';
    }
    if ($tasa === false || $tasa < 1) {
        $errores[] = 'La tasa debe ser un número mayor o igual a 1.';
    }
    if ($plazo === false || $plazo < 1) {
        $errores[] = 'El plazo debe ser un entero mayor o igual a 1.';
    }

    // 4) Cálculo
    if (empty($errores)) {
        try {
            $calc = new CalculadoraFinanciera((float)$monto, (float)$tasa, (int)$plazo);
            $resultado = [
                'cuota'         => round($calc->calcularCuotaMensual(), 2),
                'interes_total' => round($calc->calcularInteresTotal(), 2),
                'semilla'       => $semilla,
            ];
        } catch (InvalidArgumentException $e) {
            $errores[] = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ejercicio 2 - Calculadora financiera</title>
</head>
<body>
    <h1>Calculadora financiera</h1>

    <form method="POST" action="">
        <p>
            <label>Semilla (3 últimos dígitos de la cédula):
                <input type="text" name="cedula_semilla" required>
            </label>
        </p>
        <p>
            <label>Monto:
                <input type="number" name="monto" step="any" min="100" required>
            </label>
        </p>
        <p>
            <label>Tasa anual (%):
                <input type="number" name="tasa" step="any" min="1" required>
            </label>
        </p>
        <p>
            <label>Plazo (meses):
                <input type="number" name="plazo" step="1" min="1" required>
            </label>
        </p>
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Calcular</button>
    </form>

    <?php if (!empty($errores)): ?>
        <ul>
            <?php foreach ($errores as $e): ?>
                <li><?= $e ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($resultado !== null): ?>
        <h2>Resumen</h2>
        <p>Semilla: <?= $resultado['semilla'] ?></p>
        <p>Cuota mensual: <?= number_format($resultado['cuota'], 2) ?></p>
        <p>Interés total: <?= number_format($resultado['interes_total'], 2) ?></p>

        <script type="application/json" id="resultado-ej2"><?= json_encode($resultado, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
</body>
</html>