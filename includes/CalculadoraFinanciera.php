<?php
/**
 * Biblioteca de Clases Financieras
 */

// Ejercicio 1.1 - Verificacion de constante ACCESO_PERMITIDO
if (!defined('ACCESO_PERMITIDO')) {
    http_response_code(403);
    die("Acceso directo no permitido.");
}

class CalculadoraFinanciera {
    // Ejercicio 1.2 - Miembros privados
    private float $montoBase;
    private float $tasaAnual;
    private int $plazoMeses;

    // Ejercicio 1.3 - Constructor con validacion
    public function __construct(float $monto, float $tasa, int $plazo) {
        if ($monto <= 0 || $tasa <= 0 || $plazo <= 0) {
            throw new InvalidArgumentException("Los parametros deben ser estrictamente mayores a cero.");
        }
        $this->montoBase = $monto;
        $this->tasaAnual = $tasa;
        $this->plazoMeses = $plazo;
    }

    // Getters
    public function getMontoBase(): float { return $this->montoBase; }
    public function getTasaAnual(): float { return $this->tasaAnual; }
    public function getPlazoMeses(): int { return $this->plazoMeses; }

    // Tasa mensual (r = tasaAnual / (12 * 100)), util tambien para el Ejercicio 3
    public function getTasaMensual(): float {
        return $this->tasaAnual / (12 * 100);
    }

    // Cuota exacta (sin redondear) para un plazo dado
    private function cuotaExacta(int $n): float {
        $r = $this->getTasaMensual();
        $factor = pow(1 + $r, $n);
        return $this->montoBase * ($r * $factor) / ($factor - 1);
    }

    // Ejercicio 1.4 - Cuota mensual
    public function calcularCuotaMensual(): float {
        return round($this->cuotaExacta($this->plazoMeses), 2);
    }

    // Ejercicio 1.5 - Sobrecarga dinamica con __call
    public function __call(string $name, array $arguments) {
        if ($name === 'calcularInteresTotal') {
            $mesesExtra = isset($arguments[0]) ? (int)$arguments[0] : 0;
            $n = $this->plazoMeses + $mesesExtra;

            if ($n <= 0) {
                throw new InvalidArgumentException("El plazo total debe ser mayor a cero.");
            }

            $interes = ($this->cuotaExacta($n) * $n) - $this->montoBase;
            return round($interes, 2);
        }

        throw new BadMethodCallException("El metodo '$name' no existe en la clase CalculadoraFinanciera.");
    }
}