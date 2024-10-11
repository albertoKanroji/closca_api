<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;
use GuzzleHttp\Client;
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
        // Buscar el auto por el VIN
        $auto = Auto::where('vin', $vin)->first();

        // Si se encuentra el auto, devolverlo directamente con las imágenes
        if ($auto) {
            // Verificar si el cliente es el autorizado (id_cliente = 5)
            if ($auto->id_cliente != 5) {
                return response()->json([
                    'message' => 'Cliente no autorizado'
                ], 403);
            }

            // Si el cliente está autorizado, obtener imágenes
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

            // Verificar si el cliente es el autorizado (id_cliente = 5)
            if ($data['id_cliente'] != 5) {
                return response()->json([
                    'message' => 'Cliente no autorizado'
                ], 403);
            }

            // Usar transacciones para garantizar que los datos se guardan correctamente
            DB::beginTransaction();

            try {
                $key = $data['_key'];

                // Guardar los datos del auto obtenidos en la tabla autos
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
                            // Otros campos relevantes de las imágenes
                        ]);
                    }
                } else {
                    // Si la respuesta de imágenes falla, se puede seguir pero sin imágenes
                    $imagenes = [];
                }

                // Confirmar la transacción si todo sale bien
                DB::commit();
            } catch (\Exception $e) {
                // Si algo falla, deshacer la transacción
                DB::rollBack();
                return response()->json([
                    'message' => 'Error al guardar los datos del auto o imágenes',
                    'error' => $e->getMessage()
                ], 500);
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
            'error' => $e->getMessage()
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
                $client = new Client(['verify' => false]);
                $response = $client->request('GET', 'https://closca.xrom.cc/ajax/ajax_imagen.php', [
                    'query' => [
                        'opcion' => '21',
                        '_key' => $key,
                        'folder' => 'reportes',
                        '_' => time()
                    ]
                ]);

                $body = $response->getBody();
                dd($response);
               // dd( $response->json());
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
