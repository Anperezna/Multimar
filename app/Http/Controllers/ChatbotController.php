<?php

namespace App\Http\Controllers;

use App\Models\ChatbotMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function message(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $userId = $request->user()?->id;
        $provider = config('services.chatbot.provider', 'n8n');
        $systemPrompt = 'Eres un asistente de logistica maritima para la plataforma Multimar. Responde en espanol, de forma clara y breve.';

        if ($provider === 'n8n') {
            return $this->sendToN8n($userId, $validated['message'], $systemPrompt);
        }

        if ($provider === 'ollama') {
            return $this->sendToOllama($userId, $validated['message'], $systemPrompt);
        }

        $this->persistMessage($userId, $provider, null, $validated['message'], null, 'error', 'Proveedor no configurado');

        return response()->json([
            'reply' => 'No hay un proveedor de chatbot configurado. Usa CHATBOT_PROVIDER=n8n u CHATBOT_PROVIDER=ollama en tu .env.',
        ], 422);
    }

    private function sendToN8n(?int $userId, string $message, string $systemPrompt)
    {
        $webhookUrl = config('services.chatbot.n8n_webhook_url');

        if (!$webhookUrl) {
            $this->persistMessage($userId, 'n8n', null, $message, null, 'error', 'N8N_CHAT_WEBHOOK_URL no configurado');

            return response()->json([
                'reply' => 'Falta configurar N8N_CHAT_WEBHOOK_URL en tu .env.',
            ], 422);
        }

        $response = Http::timeout(60)->post($webhookUrl, [
            'message' => $message,
            'systemPrompt' => $systemPrompt,
        ]);

        if ($response->failed()) {
            $error = $response->json('message') ?? $response->body();
            $this->persistMessage($userId, 'n8n', null, $message, null, 'error', is_string($error) ? $error : 'Error en llamada a N8N');

            return response()->json([
                'reply' => 'N8N no respondio correctamente. Revisa tu workflow y el webhook.',
                'error' => $error,
            ], 502);
        }

        $reply = data_get($response->json(), 'reply')
            ?? data_get($response->json(), 'message')
            ?? data_get($response->json(), 'output')
            ?? $response->body();

        $normalizedReply = is_string($reply) && trim($reply) !== '' ? $reply : 'N8N devolvio una respuesta vacia.';
        $this->persistMessage($userId, 'n8n', null, $message, $normalizedReply, 'ok', null);

        return response()->json([
            'reply' => $normalizedReply,
        ]);
    }

    private function sendToOllama(?int $userId, string $message, string $systemPrompt)
    {
        $baseUrl = rtrim(config('services.chatbot.ollama_base_url', 'http://localhost:11434'), '/');
        $model = config('services.chatbot.ollama_model', 'qwen2.5-coder:1.5b');

        $response = Http::timeout(60)->post($baseUrl . '/api/chat', [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $message,
                ],
            ],
            'stream' => false,
        ]);

        if ($response->failed()) {
            $error = $response->json('error') ?? $response->body();
            $this->persistMessage($userId, 'ollama', $model, $message, null, 'error', is_string($error) ? $error : 'Error en llamada a Ollama');

            return response()->json([
                'reply' => 'Ollama no respondio correctamente. Revisa que el contenedor este activo y el modelo descargado.',
                'error' => $error,
            ], 502);
        }

        $reply = data_get($response->json(), 'message.content')
            ?? data_get($response->json(), 'response')
            ?? data_get($response->json(), 'content')
            ?? $response->body();

        $normalizedReply = is_string($reply) && trim($reply) !== '' ? $reply : 'Ollama devolvio una respuesta vacia.';
        $this->persistMessage($userId, 'ollama', $model, $message, $normalizedReply, 'ok', null);

        return response()->json([
            'reply' => $normalizedReply,
        ]);
    }

    private function persistMessage(
        ?int $userId,
        string $provider,
        ?string $model,
        string $prompt,
        ?string $reply,
        string $status,
        ?string $error
    ): void {
        ChatbotMessage::create([
            'usuari_id' => $userId,
            'provider' => $provider,
            'model' => $model,
            'prompt' => $prompt,
            'reply' => $reply,
            'status' => $status,
            'error' => $error,
        ]);
    }
}
