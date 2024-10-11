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
                            'Unidad' => [
                                'id_auto' => $auto->id_auto,
                                'vin' => $auto->vin,
                                'f_ingreso' => $auto->f_ingreso,
                                'f_salida' => $auto->f_salida,
                                'id_cliente' => $auto->id_cliente,
                                'id_contacto' => $auto->id_contacto,
                                'id_marca' => $auto->id_marca,
                                'modelo' => $auto->modelo,
                                'color' => $auto->color,
                                'color_ext' => $auto->color_ext,
                                'lavado_presion' => $auto->lavado_presion,
                                'voltajeBateria' => $auto->voltajeBateria,
                                'barco' => $auto->barco,
                                'modelo_ext' => $auto->modelo_ext,
                                'dealer' => $auto->dealer,
                                'firma' => $auto->firma,
                            ],
                            'UBICACION' => [
                                'pais_destino' => $auto->pais_destino,
                                'id_patio' => $auto->id_patio,
                                'fila' => $auto->fila,
                                'viaje' => $auto->viaje,
                                'posicion' => $auto->posicion,
                                '_localizacion' => $auto->_localizacion,
                                'referencia' => $auto->referencia,
                            ],
                            'DMG' => [
                                'dmg_codigo' => $auto->dmg_codigo,
                                'dmg_clasificacion' => $auto->dmg_clasificacion,
                                'dmg_descripcion' => $auto->dmg_descripcion,
                                'dmg_modo' => $auto->dmg_modo,
                                'dmg_maniobra' => $auto->dmg_maniobra,
                                'dmg_transporte' => $auto->dmg_transporte,
                                'dmg_responsable' => $auto->dmg_responsable,
                            ],
                            'REPUVE' => [
                                'rep_reparable' => $auto->rep_reparable,
                                'rep_responsable' => $auto->rep_responsable,
                                'rep_fecha_autorizacion' => $auto->rep_fecha_autorizacion,
                                'rep_fecha_liberacion' => $auto->rep_fecha_liberacion,
                                'rep_dias' => $auto->rep_dias,
                                'rep_orden_servicio' => $auto->rep_orden_servicio,
                                'rep_requiere_partes' => $auto->rep_requiere_partes,
                                'rep_fecha_orden_partes' => $auto->rep_fecha_orden_partes,
                                'rep_control_pedido' => $auto->rep_control_pedido,
                                'rep_fecha_entrega_partes' => $auto->rep_fecha_entrega_partes,
                                'rep_fecha_termino' => $auto->rep_fecha_termino,
                                'rep_estado' => $auto->rep_estado,
                                'rep_comentarios' => $auto->rep_comentarios,
                                'rep_sitio' => $auto->rep_sitio,
                            ],
                            'ACCESORIOS' => [
                                'ac_llave_tarjeta' => $auto->ac_llave_tarjeta,
                                'ac_tarjeta_memoria' => $auto->ac_tarjeta_memoria,
                                'ac_7KW_charger' => $auto->ac_7KW_charger,
                                'ac_chaleco_reflectante' => $auto->ac_chaleco_reflectante,
                                'ac_triangulo_adv' => $auto->ac_triangulo_adv,
                                'ac_montaje_gato' => $auto->ac_montaje_gato,
                                'ac_gancho_remolque' => $auto->ac_gancho_remolque,
                                'ac_gancho_traccion' => $auto->ac_gancho_traccion,
                                'ac_llave_inteligente' => $auto->ac_llave_inteligente,
                                'ac_pinza_desmontaje_dec' => $auto->ac_pinza_desmontaje_dec,
                                'ac_red_neumatico' => $auto->ac_red_neumatico,
                                'ac_limpiaparabrisas_izq' => $auto->ac_limpiaparabrisas_izq,
                                'ac_llave_inglesa' => $auto->ac_llave_inglesa,
                                'ac_cierre' => $auto->ac_cierre,
                                'ac_limpiaparabrisas_der' => $auto->ac_limpiaparabrisas_der,
                                'ac_clips_decorativos' => $auto->ac_clips_decorativos,
                                'ac_manual_usuario' => $auto->ac_manual_usuario,
                                'ac_barra_remota_gato' => $auto->ac_barra_remota_gato,
                            ],
                            'COMENTARIOS' => [
                                'comentario_1' => $auto->comentario_1,
                                'comentario_2' => $auto->comentario_2,
                                'comentario_3' => $auto->comentario_3,
                            ],
                        ],
                        'imagenes' => $imagenes
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

                        // Verificar si la respuesta es un array válido antes de hacer el foreach
                        if (is_array($imagenes) && !empty($imagenes)) {
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
                    }

                    // Confirmar la transacción si todo sale bien
                    DB::commit();
                } catch (Exception $e) {
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
                            'imagenes' => $imagenes ?? [] // Si no hay imágenes, retornar un array vacío
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
