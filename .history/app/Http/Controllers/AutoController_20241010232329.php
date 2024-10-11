<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;

class AutoController extends Controller
{
    /**
     * Obtener los datos del auto y sus imágenes basados en el VIN, o consultar una API externa si no se encuentra.
     *
     * @param  string  $vin
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAutoByVin($vin)
    {
        try {
            // Buscar el auto por el VIN
            $auto = Auto::where('vin', $vin)->first();

            // Si se encuentra el auto, devolverlo directamente con las imágenes
            if ($auto) {
                $imagenes = $auto->imagenes;

                $response = [
                    'inspecciones' => [
                        'auto' => [
                            'datos' => $auto,
                            'imagenes' => $imagenes
                        ]
                    ]
                ];

                return response()->json($response, 200);
            }

            // Si el auto no existe, realizar la primera petición a la API externa
            $response = Http::withOptions(['verify' => false])->get("https://closca.xrom.cc/ajax/app.php", [
                'opcion' => '636363',
                'vin' => $vin
            ]);

            if ($response->ok()) {
                $data = $response->json();

                // Verificar si la respuesta tiene la clave _key
                if (!isset($data['_key'])) {
                    return response()->json([
                        'message' => 'No se pudo obtener la _key del VIN proporcionado.'
                    ], 404);
                }

                $key = $data['_key'];

                // Guardar los datos del auto obtenidos en la tabla autos, solo si no existe
                $auto = Auto::create([
                    'id_auto' => $data['id_auto'],
                    'vin' => $data['vin'],
                    'f_ingreso' => $data['f_ingreso'],
                    'f_salida' => $data['f_salida'],
                    'id_cliente' => $data['id_cliente'],
                    '_key' => $key,
                    // Otros campos relevantes que quieras almacenar
                ]);

                // Segunda petición para obtener las imágenes usando la _key
                $imageResponse = Http::withOptions(['verify' => false])->get("https://closca.xrom.cc/ajax/ajax_imagen.php", [
                    'opcion' => '21',
                    '_key' => $key,
                    'folder' => 'reportes',
                    '_' =>'1728621762489' // Generar el timestamp dinámicamente
                ]);

                if ($imageResponse->ok()) {
                    $imagenes = $imageResponse->json();

                    // Guardar las imágenes obtenidas en la tabla imagenes
                    foreach ($imagenes as $imagen) {
                        Imagen::create([
                            'folio' => $imagen['folio'],
                            'id_imagen' => $imagen['id_imagen'],
                            '_key' => $key,
                            'adjunto' => $imagen['adjunto'],
                            'FileName' => $imagen['FileName'],
                            'link_src' => $imagen['link_src'],
                            'link_thumb' => $imagen['link_thumb'],
                            'descripcion' => $imagen['descripcion'],
                            // Otros campos relevantes de las imágenes
                        ]);
                    }
                }

                // Retornar el auto y sus imágenes en el formato inspecciones[auto[imagenes]]
                $response = [
                    'inspecciones' => [
                        'auto' => [
                            'datos' => $auto,
                            'imagenes' => $imagenes
                        ]
                    ]
                ];

                return response()->json($response, 200);

            } else {
                return response()->json([
                    'message' => 'No se pudo obtener información del VIN en la API externa.'
                ], 404);
            }

        } catch (Exception $e) {
            // Manejar cualquier error inesperado
            return response()->json([
                'message' => 'Ha ocurrido un error al intentar recuperar los datos',
                'error' => $e
            ], 500);
        }
    }
    public function obtenerImagenesPorVin($vin)
    {
        try {
            // Realiza la primera petición a la API externa para obtener la _key usando el VIN
            $response = Http::withOptions(['verify' => false])->get("https://closca.xrom.cc/ajax/app.php", [
                'opcion' => '636363',
                'vin' => $vin
            ]);

            // Verificar si la petición fue exitosa
            if ($response->ok()) {
                $data = $response->json();

                // Verificar si la respuesta contiene la clave _key
                if (!isset($data['_key'])) {
                    return response()->json([
                        'message' => 'No se pudo obtener la _key del VIN proporcionado.'
                    ], 404);
                }

                $key = $data['_key'];

                // Realizar la segunda petición para obtener las imágenes usando la _key
                $imageResponse = Http::withOptions(['verify' => false])->get("https://closca.xrom.cc/ajax/ajax_imagen.php", [
                    'opcion' => '21',
                    '_key' => '7be3b0c9d3a2500c905af057d7a8ce9f',
                    'folder' => 'reportes',
                    // Generar el timestamp dinámicamente
                ]);
                dd($imageResponse->body());
                // Verificar si la petición de las imágenes fue exitosa
                if ($imageResponse->ok()) {
                    $imagenes = $imageResponse->json();

                    // Retornar las imágenes obtenidas
                    return $imagenes;
                } else {
                    return response()->json([
                        'message' => 'No se pudieron obtener las imágenes para la _key proporcionada.'
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => 'No se pudo obtener información del VIN en la API externa.'
                ], 404);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error al intentar recuperar los datos',
                'error' => $e->getMessage()
            ], 500); // Error interno del servidor
        }
    }
}