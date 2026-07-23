<?php

class ValController
{

    public function sanitizacion($valor)
    {
        $valor = trim($valor);
        $valor = stripslashes($valor);
        $valor = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
        return $valor;
    }

    public function validarRequeridos($valor)
    {
        if ($valor != "") {
            return true;
        } else {
            return false;
        }
    }

    public function validarLongitudes($valor, $options)
    {
        $longitud = strlen($valor);
        if (filter_var($longitud, FILTER_VALIDATE_INT, $options) === false) {
            return false;
        } else {
            return true;
        }
    }

    public function validarCorreo($valor)
    {
        if (filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    public function validarFecha($valor)
    {
        $formato = 'Y-m-d\TH:i';
        $fecha = DateTime::createFromFormat($formato, $valor);
        return $fecha && $fecha->format($formato) === $valor;
    }
}
