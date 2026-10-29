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

    // TODO: Ejercicio 1.4 - Implementar calcularCuotaMensual()
    public function calcularCuotaMensual(): float {
        $r = ($this->tasaAnual / 100) / 12;
        $n = $this->plazoMeses;
        
        $cuota = $this->montoBase * ($r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
        return round($cuota, 2);
    }

    // TODO: Ejercicio 1.5 - Implementar sobrecarga dinamica con __call
    public function __call(string $name, array $arguments) {
        if ($name === 'calcularInteresTotal') {
            // Sin argumentos: interes total sobre el plazo original
            if (count($arguments) === 0) {
                $cuota = $this->calcularCuotaMensual();
                return round($cuota * $this->plazoMeses - $this->montoBase, 2);
            }

            // Con $mesesExtra: simula la extension del plazo y recalcula la cuota
            $mesesExtra = $arguments[0];
            if (!is_int($mesesExtra) || $mesesExtra < 0) {
                throw new InvalidArgumentException("mesesExtra debe ser un entero mayor o igual a cero.");
            }
            $n = $this->plazoMeses + $mesesExtra;
            $r = ($this->tasaAnual / 100) / 12;
            $cuota = round($this->montoBase * ($r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1), 2);
            return round($cuota * $n - $this->montoBase, 2);
        }

        throw new BadMethodCallException("El metodo '$name' no existe en la clase CalculadoraFinanciera.");
    }
}
