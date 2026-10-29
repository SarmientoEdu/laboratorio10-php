<?php
/**
 * Biblioteca de Clases Financieras
 */

// TODO: Ejercicio 1.1 - Implementar verificacion de constante ACCESO_PERMITIDO
if (!defined('ACCESO_PERMITIDO')) {
    http_response_code(403);
    die("Acceso directo no permitido.");
}

class CalculadoraFinanciera {
    // TODO: Ejercicio 1.2 - Declarar miembros privados ($montoBase, $tasaAnual, $plazoMeses)
    private float $montoBase;
    private float $tasaAnual;
    private int $plazoMeses;

    // TODO: Ejercicio 1.3 - Implementar Constructor con validacion
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

      // Cuota para un plazo cualquiera (sin redondear)
    private function cuotaParaPlazo(int $n): float {
        $r = ($this->tasaAnual / 100) / 12;
        $factor = pow(1 + $r, $n);
        return $this->montoBase * ($r * $factor) / ($factor - 1);
    }

    public function calcularCuotaMensual(): float {
        return round($this->cuotaParaPlazo($this->plazoMeses), 2);
    }

       public function __call(string $name, array $arguments) {
        if ($name === 'calcularInteresTotal') {
            $mesesExtra = isset($arguments[0]) ? (int)$arguments[0] : 0;
            $n = $this->plazoMeses + $mesesExtra;
            $totalPagado = $this->cuotaParaPlazo($n) * $n;
            return round($totalPagado - $this->montoBase, 2);
        }

        throw new BadMethodCallException("El metodo '$name' no existe en la clase CalculadoraFinanciera.");
    }
}