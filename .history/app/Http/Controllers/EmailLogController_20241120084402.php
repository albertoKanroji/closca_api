<?php

namespace App\Http\Controllers;

use App\Models\Auto;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    /**
     * Muestra la lista de logs de emails.
     */
    public function index()
    {
        $logs = Auto::orderBy('created_at', 'desc')->get();
        return view('email_logs.index', compact('logs'));
    }
}
