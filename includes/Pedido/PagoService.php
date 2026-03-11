<?php

/**
 * Servicio encargado de la logica de validacion y procesamiento de pagos.
 */
class PagoService
{
    public const VISA = 'Visa';
    public const MASTERCARD = 'Mastercard';
    public const AMEX = 'American Express';
    public const OTRO = 'Otro';

    /**
     * Limpia el numero de tarjeta dejando solo los digitos.
     * 
     * @param string $numero El numero de tarjeta original.
     * @return string El numero solo con digitos.
     */
    private static function limpiarNumero(string $numero): string
    {
        $numeroLimpio = '';
        for ($i = 0; $i < strlen($numero); $i++) {
            if ($numero[$i] >= '0' && $numero[$i] <= '9') {
                $numeroLimpio .= $numero[$i];
            }
        }
        return $numeroLimpio;
    }

    /**
     * Valida un numero de tarjeta usando el algoritmo de Luhn.
     * 
     * @param string $numero El numero de tarjeta a validar.
     * @return bool True si es valido matematicamente, false en caso contrario.
     */
    public static function validateLuhn(string $numero): bool
    {
        $numero = self::limpiarNumero($numero);
        $suma = 0;
        $numDigitos = strlen($numero);
        $paridad = $numDigitos % 2;

        for ($i = 0; $i < $numDigitos; $i++) {
            $digito = (int)$numero[$i];
            if ($i % 2 == $paridad) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }
            $suma += $digito;
        }

        return ($suma > 0 && $suma % 10 == 0);
    }

    /**
     * Identifica la red de la tarjeta basandose en el prefijo (IIN).
     * 
     * @param string $numero El numero de tarjeta.
     * @return string El nombre de la red (Visa, Mastercard, Amex, etc).
     */
    public static function identificarRed(string $numero): string
    {
        $numero = self::limpiarNumero($numero);
        if ($numero === '') return self::OTRO;

        // Visa: Empieza con 4
        if ($numero[0] === '4') {
            return self::VISA;
        }

        // American Express: Empieza con 34 o 37
        $prefijo2 = (int)substr($numero, 0, 2);
        if ($prefijo2 === 34 || $prefijo2 === 37) {
            return self::AMEX;
        }

        // Mastercard: 51-55 o 2221-2720
        if ($prefijo2 >= 51 && $prefijo2 <= 55) {
            return self::MASTERCARD;
        }
        $prefijo4 = (int)substr($numero, 0, 4);
        if ($prefijo4 >= 2221 && $prefijo4 <= 2720) {
            return self::MASTERCARD;
        }

        return self::OTRO;
    }

    /**
     * Validacion completa segun los requisitos del proyecto:
     * - Debe pasar el algoritmo de Luhn.
     * - Solo se permiten Visa o Mastercard.
     * - Validacion de longitud segun la red.
     * 
     * @param string $numero El numero de tarjeta.
     * @return array ['valido' => bool, 'error' => string|null]
     */
    public static function validarTarjeta(string $numero): array
    {
        $numeroLimpio = self::limpiarNumero($numero);

        if (empty($numeroLimpio)) {
            return ['valido' => false, 'error' => 'Por favor, introduce un numero de tarjeta.'];
        }

        $red = self::identificarRed($numeroLimpio);

        if ($red === self::AMEX) {
            return ['valido' => false, 'error' => 'American Express no esta soportado actualmente.'];
        }

        if ($red !== self::VISA && $red !== self::MASTERCARD) {
            return ['valido' => false, 'error' => 'Solo se aceptan tarjetas Visa o Mastercard.'];
        }

        // Validacion de longitud
        $longitud = strlen($numeroLimpio);
        if ($red === self::VISA && !($longitud === 13 || $longitud === 16)) {
            return ['valido' => false, 'error' => 'La longitud de la tarjeta Visa no es correcta (debe ser 13 o 16 digitos).'];
        }
        if ($red === self::MASTERCARD && $longitud !== 16) {
            return ['valido' => false, 'error' => 'La longitud de la tarjeta Mastercard no es correcta (debe ser 16 digitos).'];
        }

        if (!self::validateLuhn($numeroLimpio)) {
            return ['valido' => false, 'error' => 'Numero de tarjeta invalido (Error de validacion matematica).'];
        }

        return ['valido' => true, 'error' => null];
    }
}
