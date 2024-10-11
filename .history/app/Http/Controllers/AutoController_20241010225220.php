<?php

namespace App\Http\Controllers;

use App\Models\Auto;
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
        // Buscar el auto por el VIN
        $auto = Auto::where('vin', $vin)->first();

        // Verificar si se encontró el auto
        if (!$auto) {
            return response()->json([
                'message' => 'Auto no encontrado'
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
    }
}
