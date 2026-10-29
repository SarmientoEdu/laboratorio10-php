<?php
/**
 * Ejercicio 3: Procesamiento GET, Tabla de Amortizacion y Cifrado OpenSSL
 */
require_once 'includes/seguridad.php';
require_once 'includes/CalculadoraFinanciera.php';

// Sanitizar y validar entradas recibidas por GET (sin valores fijos)
$semilla = htmlspecialchars(trim($_GET['cedula_semilla'] ?? ''));
$monto   = filter_var(trim($_GET['monto'] ?? ''), FILTER_VALIDATE_FLOAT);
$tasa    = filter_var(trim($_GET['tasa'] ?? ''), FILTER_VALIDATE_FLOAT);
$plazo   = filter_var(trim($_GET['plazo'] ?? ''), FILTER_VALIDATE_INT);

if ($semilla === '' || $monto === false || $tasa === false || $plazo === false
    || $monto <= 0 || $tasa <= 0 || $plazo <= 0) {
    http_response_code(400);
    exit('Parametros invalidos. Use: ?cedula_semilla=892&monto=5000&tasa=12.5&plazo=12');
}

$calc  = new CalculadoraFinanciera($monto, $tasa, $plazo);
$cuota = $calc->calcularCuotaMensual();

// Cifrado Simetrico con OpenSSL (AES-256-CBC)
$claveCifrado = hash('sha256', $semilla);
$iv = substr(hash('sha256', 'UTP_IV_' . $semilla), 0, 16);
$textoPlano = "Monto: {$monto} | Cuota: {$cuota}";
$textoCifrado = openssl_encrypt($textoPlano, 'AES-256-CBC', $claveCifrado, 0, $iv);

// Tabla de Amortizacion con bucle for
$saldo = $monto;
$r = $calc->getTasaMensual();
$tablaAmortizacion = [];

for ($mes = 1; $mes <= $plazo; $mes++) {
    $interesMes   = round($saldo * $r, 2);
    $principalMes = round($cuota - $interesMes, 2);

    // Ultimo mes: se paga todo el saldo pendiente para cerrar exactamente en 0
    if ($mes === $plazo) {
        $principalMes = round($saldo, 2);
    }

    $saldo = round($saldo - $principalMes, 2);
    if ($saldo < 0) {
        $saldo = 0.0;
    }

    $tablaAmortizacion[] = [
        'mes'       => $mes,
        'cuota'     => round($interesMes + $principalMes, 2),
        'interes'   => $interesMes,
        'principal' => $principalMes,
        'saldo'     => $saldo
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Laboratorio #10 - Ejercicio 3</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f8f9fa; }
        .container { max-width: 800px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 8px; text-align: center; }
        th { background-color: #0056b3; color: white; }
        .cifrado-box { background: #e2e3e5; padding: 10px; word-break: break-all; font-family: monospace; border-radius: 4px; margin-top: 15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Tabla de Amortizacion (Procesamiento GET)</h2>
    <p><strong>Semilla de Cedula:</strong> <?php echo $semilla; ?></p>
    <p><strong>Resumen Cifrado (AES-256-CBC):</strong></p>
    <div id="cifrado-resultado" class="cifrado-box"><?php echo htmlspecialchars($textoCifrado); ?></div>

    <table>
        <thead>
            <tr>
                <th>Mes</th>
                <th>Cuota ($)</th>
                <th>Interes ($)</th>
                <th>Principal ($)</th>
                <th>Saldo Restante ($)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tablaAmortizacion as $row): ?>
            <tr>
                <td><?php echo $row['mes']; ?></td>
                <td><?php echo number_format($row['cuota'], 2, '.', ''); ?></td>
                <td><?php echo number_format($row['interes'], 2, '.', ''); ?></td>
                <td><?php echo number_format($row['principal'], 2, '.', ''); ?></td>
                <td><?php echo number_format($row['saldo'], 2, '.', ''); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>