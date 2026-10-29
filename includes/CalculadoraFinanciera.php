<?php
/**
 * Biblioteca de Clases Financieras
 */

// Ejercicio 1.1 - Verificar acceso permitido
if (!defined('ACCESO_PERMITIDO')) {
    http_response_code(403);
    die("Acceso directo no permitido.");
}

class CalculadoraFinanciera {

    // Ejercicio 1.2 - Propiedades privadas
    private float $montoBase;
    private float $tasaAnual;
    private int $plazoMeses;

    // Ejercicio 1.3 - Constructor
    public function __construct(float $monto, float $tasa, int $plazo) {

        if (
            !is_finite($monto) ||
            !is_finite($tasa) ||
            $monto <= 0 ||
            $tasa <= 0 ||
            $plazo <= 0
        ) {
            throw new InvalidArgumentException(
                "Todos los parametros deben ser mayores a cero."
            );
        }

        $this->montoBase = $monto;
        $this->tasaAnual = $tasa;
        $this->plazoMeses = $plazo;
    }

    // Ejercicio 1.4 - Getters
    public function getMontoBase(): float {
        return $this->montoBase;
    }

    public function getTasaAnual(): float {
        return $this->tasaAnual;
    }

    public function getPlazoMeses(): int {
        return $this->plazoMeses;
    }

    // Calcular cuota para un plazo determinado
    private function calcularCuota(int $meses): float {

        $r = $this->tasaAnual / (12 * 100);

        $factor = pow(1 + $r, $meses);

        $cuota = $this->montoBase *
            (($r * $factor) / ($factor - 1));

        return $cuota;
    }

    // Calcular cuota mensual original
    public function calcularCuotaMensual(): float {

        return round(
            $this->calcularCuota($this->plazoMeses),
            2
        );
    }

    // Ejercicio 1.5 - Sobrecarga dinamica
    public function __call(string $name, array $arguments) {

        if ($name !== 'calcularInteresTotal') {
            throw new BadMethodCallException(
                "El metodo '$name' no existe."
            );
        }

        // Solo se permiten 0 o 1 argumentos
        if (count($arguments) > 1) {
            throw new InvalidArgumentException(
                "Solo se permite un argumento opcional."
            );
        }

        // Plazo original
        $plazoEfectivo = $this->plazoMeses;

        // Si existen meses adicionales
        if (count($arguments) === 1) {

            $mesesExtra = $arguments[0];

            if (!is_int($mesesExtra) || $mesesExtra <= 0) {
                throw new InvalidArgumentException(
                    "Los meses extra deben ser un entero positivo."
                );
            }

            $plazoEfectivo += $mesesExtra;
        }

        // Calcular cuota con el plazo efectivo
        $cuota = $this->calcularCuota($plazoEfectivo);

        // Calcular interes total
        $interesTotal =
            ($cuota * $plazoEfectivo) -
            $this->montoBase;

        return round($interesTotal, 2);
    }
}