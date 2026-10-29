<?php
/**
 * Biblioteca de Clases Financieras
 */

// 1. Protección de Archivo: Verificar constante de seguridad
if (!defined('ACCESO_PERMITIDO')) {
    http_response_code(403);
    die("Acceso directo no permitido.");
}

class CalculadoraFinanciera {
    // 2.a. Propiedades Privadas con tipos estrictos
    private float $montoBase;
    private float $tasaAnual;
    private int $plazoMeses;

    // 2.b. Constructor con validación estricta
    public function __construct(float $monto, float $tasa, int $plazo) {
        if ($monto <= 0 || $tasa <= 0 || $plazo <= 0) {
            throw new InvalidArgumentException("Los parámetros deben ser estrictamente mayores a cero.");
        }
        $this->montoBase = $monto;
        $this->tasaAnual = $tasa;
        $this->plazoMeses = $plazo;
    }

    // 2.c. Getters públicos
    public function getMontoBase(): float { 
        return $this->montoBase; 
    }
    
    public function getTasaAnual(): float { 
        return $this->tasaAnual; 
    }
    
    public function getPlazoMeses(): int { 
        return $this->plazoMeses; 
    }

    // 2.d. Método calcularCuotaMensual (Fórmula de amortización francesa)
    public function calcularCuotaMensual(): float {
        $r = ($this->tasaAnual / 100) / 12;
        $n = $this->plazoMeses;
        $cuota = $this->montoBase * ($r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
        return round($cuota, 2);
    }

    // 2.e. Sobrecarga dinámica con __call
    public function __call(string $name, array $arguments) {
        if ($name === 'calcularInteresTotal') {
            // Plazo base
            $plazoEfectivo = $this->plazoMeses;
            
            // Si se pasa un argumento entero, se suma como meses extra
            if (isset($arguments[0]) && is_int($arguments[0])) {
                $plazoEfectivo += $arguments[0];
            }

            // Recalculamos la cuota con el nuevo plazo efectivo (matemáticamente correcto)
            $r = ($this->tasaAnual / 100) / 12;
            $n = $plazoEfectivo;
            $nuevaCuota = $this->montoBase * ($r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
            
            $totalPagado = $nuevaCuota * $plazoEfectivo;
            return round($totalPagado - $this->montoBase, 2);
        }

        throw new BadMethodCallException("El método '$name' no existe en la clase CalculadoraFinanciera.");
    }
}