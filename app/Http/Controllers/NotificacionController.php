<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function getMyNotifications(Request $request)
    {
        $usuari = $request->user();

        if (!$usuari) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $notificaciones = Notificacion::query()
            ->where('usuari_id', $usuari->id)
            ->orderByDesc('id')
            ->get();

        $resultado = [];

        foreach ($notificaciones as $notif) {
            $resultado[] = [
                'id' => $notif->id,
                'titulo' => $notif->titulo,
                'descripcion' => $notif->descripcion,
                'visto' => $notif->visto,
            ];
        }

        return response()->json($resultado);
    }

    public function markAsRead(Request $request, $notificacionId)
    {
        $usuari = $request->user();

        if (!$usuari) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $notificacion = Notificacion::query()
            ->where('id', $notificacionId)
            ->where('usuari_id', $usuari->id)
            ->first();

        if (!$notificacion) {
            return response()->json(['error' => 'Notificación no encontrada'], 404);
        }

        $notificacion->visto = 1;
        $notificacion->save();

        return response()->json(['message' => 'Marcada como leída']);
    }

    public function deleteNotification(Request $request, $notificacionId)
    {
        $usuari = $request->user();

        if (!$usuari) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $notificacion = Notificacion::query()
            ->where('id', $notificacionId)
            ->where('usuari_id', $usuari->id)
            ->first();

        if (!$notificacion) {
            return response()->json(['error' => 'Notificación no encontrada'], 404);
        }

        $notificacion->delete();

        return response()->json(['message' => 'Eliminada']);
    }

    public function getUnreadCount(Request $request)
    {
        $usuari = $request->user();

        if (!$usuari) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $count = Notificacion::query()
            ->where('usuari_id', $usuari->id)
            ->where('visto', 0)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
