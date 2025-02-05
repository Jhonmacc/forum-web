<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        $user = auth()->user();  // Obtém o usuário autenticado
        $notifications = $user->notifications()->latest()->get();  // Obtém as notificações do usuário, ordenadas por data

        return response()->json($notifications);  // Retorna as notificações em formato JSON
    }
}
