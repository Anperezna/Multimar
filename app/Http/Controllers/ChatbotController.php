<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class ChatbotController extends Controller
{
    public function message(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'sessionId' => 'required|string|max:255',
        ]);

        $message = $validated['message'];
        $sessionId = $validated['sessionId'];
        $userId = $request->user()?->id;

        $provider = config('services.chatbot.provider', 'n8n');

        try {
            return ($provider === 'n8n')
                ? $this->sendToN8n($userId, $message, $sessionId)
                : $this->sendToOllama($userId, $message);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['reply' => 'Error interno del servicio.'], 500);
        }
    }

    private function sendToN8n(?int $userId, string $message, string $sessionId)
    {
        $response = Http::timeout(300)->acceptJson()->post(config('services.chatbot.n8n_webhook_url'), [
            'text' => $message,
            'sessionId' => $sessionId,
            'userId' => $userId,
        ]);

        if ($response->failed()) {
            \Log::error('n8n error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json(['reply' => 'El asistente no está disponible.'], 502);
        }

        $data = $response->json();

        // 🔥 CLAVE: n8n devuelve { "output": "texto" }
        $reply = $data['output'] ?? $data['reply'] ?? $data['message'] ?? null;

        if (! $reply) {
            \Log::warning('n8n respuesta sin contenido esperado', ['data' => $data]);

            return response()->json(['reply' => 'No se pudo procesar la respuesta.'], 500);
        }

        return response()->json(['reply' => $reply]);
    }

    private function sendToOllama(?int $userId, string $message)
    {
        // Implementación simplificada
        return response()->json(['reply' => 'Ollama activo...']);
    }
}
