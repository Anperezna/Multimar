<?php

namespace App\Http\Controllers;

use App\Models\ChatbotMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class ChatbotController extends Controller
{
    public function message(Request $request)
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
            'text' => ['nullable', 'string', 'max:2000'],
            'sessionId' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = $request->user()?->id;
        $message = trim((string) ($validated['message'] ?? $validated['text'] ?? ''));

        if ($message === '') {
            return response()->json([
                'reply' => 'Falta el mensaje del usuario.',
            ], 422);
        }

        $provider = config('services.chatbot.provider', 'n8n');
        $systemPrompt = 'Eres un asistente de logistica maritima para la plataforma Multimar. Responde en espanol, de forma clara y breve.';
        $sessionId = $validated['sessionId'] ?? ($userId ? "user-{$userId}" : (string) $request->ip());

        try {
            if ($provider === 'n8n') {
                return $this->sendToN8n($userId, $message, $systemPrompt, $sessionId);
            }

            if ($provider === 'ollama') {
                return $this->sendToOllama($userId, $message, $systemPrompt);
            }

            $this->persistMessageSafely($userId, $provider, null, $message, null, 'error', 'Proveedor no configurado');

            return response()->json([
                'reply' => 'No hay un proveedor de chatbot configurado. Usa CHATBOT_PROVIDER=n8n u CHATBOT_PROVIDER=ollama en tu .env.',
            ], 422);
        } catch (Throwable $e) {
            report($e);
            $this->persistMessageSafely($userId, $provider, null, $message, null, 'error', $e->getMessage());

            return response()->json([
                'reply' => 'El servicio del chatbot fallo de forma inesperada. Revisa logs y configuracion del proveedor.',
            ], 502);
        }
    }

    private function sendToN8n(?int $userId, string $message, string $systemPrompt, string $sessionId)
    {
        try {
            $webhookUrl = config('services.chatbot.n8n_webhook_url');

            if (!$webhookUrl) {
                $this->persistMessageSafely($userId, 'n8n', null, $message, null, 'error', 'N8N_CHAT_WEBHOOK_URL no configurado');

                return response()->json([
                    'reply' => 'Falta configurar N8N_CHAT_WEBHOOK_URL en tu .env.',
                ], 422);
            }

            $response = Http::timeout(60)->acceptJson()->post($webhookUrl, [
                'text' => $message,
                'message' => $message,
                'sessionId' => $sessionId,
                'userId' => $userId,
                'systemPrompt' => $systemPrompt,
            ]);

            if ($response->failed()) {
                $error = $response->json('message') ?? $response->body();
                $this->persistMessageSafely($userId, 'n8n', null, $message, null, 'error', is_string($error) ? $error : 'Error en llamada a N8N');

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
            $this->persistMessageSafely($userId, 'n8n', null, $message, $normalizedReply, 'ok', null);

            return response()->json([
                'reply' => $normalizedReply,
            ]);
        } catch (Throwable $e) {
            report($e);
            $this->persistMessageSafely($userId, 'n8n', null, $message, null, 'error', $e->getMessage());

            return response()->json([
                'reply' => 'N8N no esta disponible temporalmente. Intentalo de nuevo en unos minutos.',
            ], 502);
        }
    }

    private function sendToOllama(?int $userId, string $message, string $systemPrompt)
    {
        try {
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
                $this->persistMessageSafely($userId, 'ollama', $model, $message, null, 'error', is_string($error) ? $error : 'Error en llamada a Ollama');

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
            $this->persistMessageSafely($userId, 'ollama', $model, $message, $normalizedReply, 'ok', null);

            return response()->json([
                'reply' => $normalizedReply,
            ]);
        } catch (Throwable $e) {
            report($e);
            $this->persistMessageSafely($userId, 'ollama', config('services.chatbot.ollama_model', 'qwen2.5-coder:1.5b'), $message, null, 'error', $e->getMessage());

            return response()->json([
                'reply' => 'Ollama no esta disponible temporalmente. Verifica servicio y modelo.',
            ], 502);
        }
    }

    private function persistMessageSafely(
        ?int $userId,
        string $provider,
        ?string $model,
        string $prompt,
        ?string $reply,
        string $status,
        ?string $error
    ): void {
        try {
            $this->persistMessage($userId, $provider, $model, $prompt, $reply, $status, $error);
        } catch (Throwable $persistException) {
            report($persistException);
        }
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
