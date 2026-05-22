<template>
    <div class="chatbot-page">
        <Navbar />

        <main class="chatbot-main">
            <section class="chatbot-shell">
                <header class="chatbot-header">
                    <div class="chatbot-header__top">
                        <router-link to="/home" class="chatbot-back" aria-label="Volver al dashboard">
                            <img :src="backIcon" alt="" aria-hidden="true" class="chatbot-back__icon" />
                            <span>Dashboard</span>
                        </router-link>
                    </div>

                    <h1>Asistente Multimar</h1>
                    <p>Preguntame sobre procesos logísticos, ofertas y dudas operativas.</p>
                </header>

                <div ref="messagesContainer" class="chatbot-messages">
                    <article v-for="(msg, index) in messages" :key="index" class="message"
                        :class="msg.role === 'user' ? 'message--user' : 'message--bot'">
                        <p>{{ msg.content }}</p>
                    </article>

                    <article v-if="isLoading" class="message message--bot message--typing">
                        <p>Escribiendo respuesta...</p>
                    </article>
                </div>

                <div class="chatbot-form">
                    <Input v-model="input" placeholder="Escribe tu mensaje..." inputClass="chatbot-input"
                        :disabled="isLoading" @keydown.enter.prevent="sendMessage"
                        />
                        <button type="button" class="chatbot-send" :disabled="isLoading || !input.trim()"
                            @click.prevent="sendMessage">
                            {{ isLoading ? 'Enviando...' : 'Enviar' }}
                        </button>
                </div>
            </section>
        </main>
    </div>
</template>

<script setup>
import { nextTick, onMounted, ref, onUnmounted } from 'vue';
import Input from '@/components/Input.vue';
import Navbar from '@/components/Navbar.vue';
import { chatbotApi } from '@/lib/api';
import backIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/arrow-left-w.svg';

const input = ref('');
const isLoading = ref(false);
const messagesContainer = ref(null);
const sessionStorageKey = 'multimar-chatbot-session-id';
const sessionId = ref('');

const messages = ref([
    {
        role: 'assistant',
        content: 'Hola. Soy el asistente de Multimar. ¿Qué necesitas resolver hoy?',
    },
]);

const createSessionId = () => {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }
    return `chat-${Date.now()}-${Math.random().toString(16).slice(2)}`;
};

onMounted(() => {
    sessionId.value = window.localStorage.getItem(sessionStorageKey) || createSessionId();
    window.localStorage.setItem(sessionStorageKey, sessionId.value);
    
    // 🔥 Prevenir cualquier submit de formulario dentro del chatbot
    const chatbotShell = document.querySelector('.chatbot-shell');
    if (chatbotShell) {
        const forms = chatbotShell.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                console.log('Submit prevenido');
                return false;
            });
        });
    }
});

// 🔥 Limpiar al desmontar (por si acaso)
onUnmounted(() => {
    console.log('Chatbot page unmounted');
});

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const sendMessage = async () => {
    const text = input.value.trim();
    if (!text || isLoading.value) return;
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (!text || isLoading.value) return;

    console.log("=== ENVIANDO MENSAJE ===");
    console.log("=== ENVIANDO MENSAJE ===");
    console.log("Texto:", text);
    console.log("Token:", localStorage.getItem('auth_token'));
    console.log("Session ID:", sessionId.value);

    messages.value.push({ role: 'user', content: text });
    input.value = '';
    isLoading.value = true;
    await scrollToBottom();

    try {
        console.log("Haciendo petición a /chatbot/message...");

        const response = await chatbotApi.post('/chatbot/message', {
            message: text,
            sessionId: sessionId.value,
        });

        console.log("Respuesta COMPLETA:", response);
        console.log("response.data:", response.data);
        console.log("response.data.reply:", response.data?.reply);
        console.log("response.status:", response.status);

        // Intentar extraer la respuesta
        let replyText = 'Respuesta vacía';

        if (response.data) {
            if (typeof response.data === 'string') {
                replyText = response.data;
            } else if (response.data.reply) {
                replyText = response.data.reply;
            } else if (response.data.output) {
                replyText = response.data.output;
            } else {
                console.warn("Formato inesperado:", response.data);
                replyText = JSON.stringify(response.data);
            }
        }

        console.log("Reply extraído:", replyText);

        messages.value.push({
            role: 'assistant',
            content: replyText,
        });

    } catch (error) {
        console.error("=== ERROR DETALLADO ===");
        console.error("Error completo:", error);

        if (error.response) {
            console.error("Status:", error.response.status);
            console.error("Headers:", error.response.headers);
            console.error("Data:", error.response.data);
            console.error("Config:", error.response.config);
        } else if (error.request) {
            console.error("No se recibió respuesta:", error.request);
        } else {
            console.error("Error config:", error.message);
        }

        messages.value.push({
            role: 'assistant',
            content: `Error: ${error.message || 'Hubo un problema'}`,
        });
    } finally {
        isLoading.value = false;
        await scrollToBottom();
    }
};
</script>

<style scoped>
/* Estilos idénticos a los tuyos */
.chatbot-page {
    min-height: 100vh;
    background: radial-gradient(circle at 15% 20%, rgba(27, 42, 74, 0.18), transparent 34%), radial-gradient(circle at 85% 15%, rgba(0, 161, 155, 0.18), transparent 32%), #f3f7fc;
}

.chatbot-main {
    display: flex;
    justify-content: center;
    padding: 32px 16px;
}

.chatbot-shell {
    width: 100%;
    max-width: 860px;
    min-height: 75vh;
    border-radius: 22px;
    background: #ffffff;
    border: 1px solid #d9e3f0;
    box-shadow: 0 18px 42px rgba(26, 38, 61, 0.12);
    display: grid;
    grid-template-rows: auto 1fr auto;
    overflow: hidden;
}

.chatbot-header {
    background: linear-gradient(120deg, #1b2a4a, #00a19b);
    color: #ffffff;
    padding: 22px 24px;
}

.chatbot-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
}

.chatbot-messages {
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.message {
    max-width: 78%;
    padding: 12px 14px;
    border-radius: 14px;
    line-height: 1.45;
}

.message--user {
    align-self: flex-end;
    background: #1b2a4a;
    color: #ffffff;
    border-bottom-right-radius: 6px;
}

.message--bot {
    align-self: flex-start;
    background: #edf3fa;
    color: #16233e;
    border-bottom-left-radius: 6px;
}

.chatbot-form {
    border-top: 1px solid #dde7f5;
    padding: 14px;
    display: grid;
    gap: 10px;
    background: #f8fbff;
}

.chatbot-input {
    width: 100%;
    min-height: 44px;
    border: 1px solid #c8d8eb;
    border-radius: 10px;
    padding: 10px 12px;
    font: inherit;
}

.chatbot-send {
    justify-self: end;
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    background: #1b2a4a;
    color: #ffffff;
    font-weight: 700;
    cursor: pointer;
}

.chatbot-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>