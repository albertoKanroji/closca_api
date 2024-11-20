<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    /**
     * Muestra la lista de logs de emails.
     */
    public function index()
    {
        $logs = EmailLog::orderBy('received_at', 'desc')->get();
        return view('email_logs.index', compact('logs'));
    }
}
