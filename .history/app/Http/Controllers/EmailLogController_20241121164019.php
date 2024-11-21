<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\DmgDetalle;
use App\Models\LogsBusqueda;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    /**
     * Muestra la lista de logs de emails.
     */
    public function index(Request $request)
{
    // Obtener el VIN de la solicitud
    $vin = $request->input('vin');

    if ($vin) {
        // Buscar el auto por VIN
        $auto = Auto::where('vin', $vin)->first();

        if ($auto) {
            // Obtener los DmgDetalle relacionados con el auto encontrado
            $dmgDetalles = $auto->dmgDetalles;

            return view('email_logs.index', [
                'logs' => [], // Los logs de email si se requieren
                'logsBusqueda' => [], // Los logs de búsqueda si se requieren
                'dmgDetalles' => $dmgDetalles,
                'vin' => $vin // Pasar el VIN buscado
            ]);
        } else {
            return view('email_logs.index', [
                'logs' => [],
                'logsBusqueda' => [],
                'dmgDetalles' => [],
                'vin' => $vin,
                'error' => 'No se encontró un auto con el VIN proporcionado.'
            ]);
        }
    }

    // Si no se busca un VIN, mostrar la vista inicial
    return view('email_logs.index', [
        'logs' => Auto::orderBy('created_at', 'desc')->get(),
        'logsBusqueda' => LogsBusqueda::all(),
        'dmgDetalles' => [],
        'vin' => null
    ]);
}
public function buscarPorVin(Request $request)
{
    // Obtener el VIN de la solicitud
    $vin = $request->input('vin');

    if (!$vin) {
        return response()->json([
            'error' => 'El VIN es obligatorio.'
        ], 400);
    }

    // Buscar el auto por VIN
    $auto = Auto::where('vin', $vin)->first();

    if (!$auto) {
        return response()->json([
            'error' => 'No se encontró un auto con el VIN proporcionado.'
        ], 404);
    }

    // Obtener los detalles de DMG relacionados
    $dmgDetalles = $auto->dmgDetalles;

    // Devolver los resultados como JSON
    return response()->json([
        'vin' => $vin,
        'auto' => [
            'id_auto' => $auto->id_auto,
            'modelo' => $auto->modelo,
            'marca' => $auto->id_marca,
            'f_ingreso' => $auto->f_ingreso
        ],
        'dmgDetalles' => $dmgDetalles
    ]);
}
}
