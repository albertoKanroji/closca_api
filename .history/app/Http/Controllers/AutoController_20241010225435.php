<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use Exception;
use Illuminate\Http\Request;

class AutoController extends Controller
{
    /**
     * Obtener los datos del auto y sus imágenes basados en el VIN.
     *
     * @param  string  $vin
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAutoByVin($vin)
    {
        try {
            // Buscar el auto por el VIN y el id_cliente igual a 5
            $auto = Auto::where('vin', $vin)->where('id_cliente', 5)->first();

            // Verificar si se encontró el auto
            if (!$auto) {
                return response()->json([
                    'message' => 'Auto no encontrado o el cliente no tiene acceso'
                ], 404);
            }

            // Cargar las imágenes relacionadas al auto
            $imagenes = $auto->imagenes;

            // Estructurar la respuesta en el formato inspecciones[auto[imagenes]]
            $response = [
                'inspecciones' => [
                    'auto' => [
                        'datos' => $auto, // Datos del auto
                        'imagenes' => $imagenes // Imágenes del auto
                    ]
                ]
            ];

            // Retornar la respuesta en formato JSON
            return response()->json($response, 200);

        } catch (Exception $e) {
            // Manejar cualquier error inesperado
            return response()->json([
                'message' => 'Ha ocurrido un error al intentar recuperar los datos',
                'error' => $e->getMessage()
            ], 500); // Retornar un código de error 500
        }
    }
}
