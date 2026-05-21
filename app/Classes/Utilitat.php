<?php

namespace App\Classes;

class Utilitat
{
    public static function errorMessage($error, ?string $fallbackMessage = null)
    {
        $missatge = 'Error desconegut';

        // Si es un objeto con errorInfo del driver (PDO), usarlo
        if (is_object($error) && isset($error->errorInfo[1]) && !empty($error->errorInfo[1])) {
            switch ($error->errorInfo[1]) {
                case 2601:
                case 1062:
                    $missatge = '1062 - Registre duplicat';
                    break;
                case 2628:
                case 8152:
                    $missatge = '1495 - El valor introduit es massa llarg per al camp.';
                    break;
                case 547:
                case 1451:
                    $missatge = $error->errorInfo[1] . ' - Registre amb elements relacionats';
                    break;
                default:
                    $missatge = $error->errorInfo[1] . ' - ' . ($error->errorInfo[2] ?? $error->getMessage());
                    break;
            }
            return $missatge;
        }

        $code = null;

        // Si es una Exception, usar su codigo
        if (is_object($error) && method_exists($error, 'getCode')) {
            $code = (int) $error->getCode();
            $fallbackMessage = $fallbackMessage ?? (method_exists($error, 'getMessage') ? $error->getMessage() : null);
        }

        // Si se pasa directamente un codigo
        if (!is_object($error) && is_numeric($error)) {
            $code = (int) $error;
        }

        if ($code !== null) {
            switch ($code) {
                case 1044:
                    $missatge = '1044 - Usuari i/o password incorrectes';
                    break;
                case 1492:
                    $missatge = '1492 - ID es requerido';
                    break;
                case 1493:
                    $missatge = '1493 - No se puede insertar un ID manual en una columna identity';
                    break;
                case 1494:
                    $missatge = '1494 - No se puede eliminar este incoterm porque hay solicitudes que lo utilizan.';
                    break;
                case 1495:
                    $missatge = '1495 - El valor introduit es massa llarg per al camp.';
                    break;
                case 1496:
                    $missatge = '1496 - Ya existe un paso con ese orden para este incoterm.';
                    break;
                case 1497:
                    $missatge = '1497 - El incoterm debe conservar al menos un paso.';
                    break;
                case 1498:
                    $missatge = '1498 - Paso de tracking no encontrado.';
                    break;
                case 1499:
                    $missatge = '1499 - Debes indicar un tipo de incoterm existente.';
                    break;
                case 1500:
                    $missatge = '1500 - El tipo de incoterm indicado no existe en BBDD.';
                    break;
                case 1501:
                    $missatge = '1501 - El tipo de incoterm no tiene pasos configurados en tipus_tracking.';
                    break;
                case 1502:
                    $missatge = '1502 - No hay pasos por defecto en tracking_steps.';
                    break;
                case 1503:
                    $missatge = '1503 - No se encontró el tipo de incoterm para actualizar pasos.';
                    break;
                case 1504:
                    $missatge = '1504 - Incoterm no encontrado.';
                case 1049:
                    $missatge = '1049 - Base de dades desconeguda';
                    break;
                case 2002:
                    $missatge = '2002 - No es troba el servidor';
                    break;
                default:
                    $missatge = $code . ' - ' . ($fallbackMessage ?? 'Error desconegut');
                    break;
            }
            return $missatge;
        }

        // Si nos pasan un string, devolverlo tal cual
        return (string) $error;
    }
}
