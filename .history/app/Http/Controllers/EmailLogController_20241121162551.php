<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use App\Models\LogsBusqueda;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    /**
     * Muestra la lista de logs de emails.
     */
    public function index()
    {
        $logs = Auto::orderBy('created_at', 'desc')->get();
        $logsBusqueda = LogsBusqueda::all();
        return view('email_logs.index', compact('logs'));
    }
}
