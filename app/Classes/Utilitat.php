<?php

namespace App\Classes;

use Exception;
use Illuminate\Database\QueryException;

class Utilitat
{
    /**
     * Método estático para manejar mensajes de error
     * 
     * @param Exception $e
     * @return string
     */
    public static function errorMessage(Exception $e): string
    {
        if ($e instanceof QueryException) {
            $errorInfo = $e->errorInfo;
            
            if (!empty($errorInfo[1])) {
                switch ($errorInfo[1]) {
                    case 1048:
                        return 'El campo no puede estar vacío';
                    case 1062:
                        return 'Registre duplicat';
                    case 1451:
                        return 'No es possible eliminar, hi ha elements relacionats';
                    case 1452:
                        return 'El registre relacionat no existeix';
                    case 1054:
                        return 'Camp no trobat a la base de dades';
                    case 1064:
                        return 'Error de sintaxi en la consulta';
                    default:
                        return 'Error en la base de dades: ' . $errorInfo[2] ?? 'Error desconegut';
                }
            }
        }

        // Errores genéricos
        if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
            return 'Registre duplicat';
        }

        if (strpos($e->getMessage(), 'FOREIGN KEY constraint failed') !== false) {
            return 'No es possible procesar, hi ha dependències en altres registres';
        }

        return $e->getMessage();
    }
}
