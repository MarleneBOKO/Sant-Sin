<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Obtenir les notifications de l'utilisateur connecté
     */
public function index()
{
    $notifications = auth()->user()->notifications()
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

    return response()->json($notifications->map(function($n) {
        return [
            'id' => $n->id,
            'titre' => $n->titre,
            'message' => $n->message,
            'lue' => (bool)$n->lue,
            'priorite' => $n->priorite,
            'facture_id' => $n->facture_id,
            'created_at' => $n->created_at->toIso8601String(), // Indispensable pour Moment.js
        ];
    }));
}
    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $notification->update(['lue' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Marquer toutes comme lues
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('lue', false)
            ->update(['lue' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('lue', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}

