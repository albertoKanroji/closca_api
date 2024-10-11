<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\DB;

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
            // Buscar el auto por el VIN y el id_cliente igual a 5
            $auto = Auto::where('vin', $vin)->where('id_cliente', 5)->first();

            // Si no se encuentra el auto en la base de datos, hacer la solicitud a la API externa
            if (!$auto) {
                // Primera petición para obtener la _key
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

                    // Guardar los datos del auto obtenidos en la tabla autos
                    $auto = Auto::create([
                        'id_auto' => $data['id_auto'],
                        'vin' => $data['vin'],
                        'f_ingreso' => $data['f_ingreso'],
                        'f_salida' => $data['f_salida'],
                        'id_cliente' => $data['id_cliente'],
                        '_key' => $key,
                        // otros campos relevantes que quieras almacenar
                    ]);

                    // Segunda petición para obtener las imágenes usando la _key
                    $imageResponse = Http::withOptions(['verify' => false])->get("https://closca.xrom.cc/ajax/ajax_imagen.php", [
                        'opcion' => '21',
                        '_key' => $key,
                        'folder' => 'reportes',
                        '_' => time() // Generar el timestamp dinámicamente
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
                                // otros campos relevantes de las imágenes
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        'message' => 'No se pudo obtener información del VIN en la API externa.'
                    ], 404);
                }
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
