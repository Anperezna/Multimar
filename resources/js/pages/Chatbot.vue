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
                    <p>Preguntame sobre procesos logisticos, ofertas y dudas operativas.</p>
                </header>

                <div ref="messagesContainer" class="chatbot-messages">
                    <article
                        v-for="(msg, index) in messages"
                        :key="index"
                        class="message"
                        :class="msg.role === 'user' ? 'message--user' : 'message--bot'"
                    >
                        <p>{{ msg.content }}</p>
                    </article>

                    <article v-if="isLoading" class="message message--bot message--typing">
                        <p>Escribiendo respuesta...</p>
                    </article>
                </div>

                <form class="chatbot-form" @submit.prevent="sendMessage">
                    <Input
                        v-model="input"
                        placeholder="Escribe tu mensaje..."
                        inputClass="chatbot-input"
                        :disabled="isLoading"
                    />
                    <button type="submit" class="chatbot-send" :disabled="isLoading || !input.trim()">
                        Enviar
                    </button>
                </form>
            </section>
        </main>
    </div>
</template>

<script setup>
import { nextTick, ref } from 'vue';
import Navbar from '@/components/Navbar.vue';
import Input from '@/components/Input.vue';
import api from '@/lib/api';
import backIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/arrow-left-w.svg';

const input = ref('');
const isLoading = ref(false);
const messagesContainer = ref(null);
const messages = ref([
    {
        role: 'assistant',
        content: 'Hola. Soy el asistente de Multimar. Que necesitas resolver hoy?',
    },
]);

const scrollToBottom = async () => {
    await nextTick();

    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const sendMessage = async () => {
    const text = input.value.trim();

    if (!text || isLoading.value) {
        return;
    }

    messages.value.push({ role: 'user', content: text });
    input.value = '';
    isLoading.value = true;
    await scrollToBottom();

    try {
        const { data } = await api.post('/chatbot/message', {
            message: text,
        });

        messages.value.push({
            role: 'assistant',
            content: data?.reply || 'No recibi respuesta del asistente.',
        });
    } catch (error) {
        const serverMessage =
            error?.response?.data?.reply ||
            error?.response?.data?.error ||
            '';

        messages.value.push({
            role: 'assistant',
            content: serverMessage || 'Se produjo un error al consultar el chatbot. Intentalo de nuevo.',
        });
    } finally {
        isLoading.value = false;
        await scrollToBottom();
    }
};
</script>

<style scoped>
.chatbot-page {
    min-height: 100vh;
    background:
        radial-gradient(circle at 15% 20%, rgba(27, 42, 74, 0.18), transparent 34%),
        radial-gradient(circle at 85% 15%, rgba(0, 161, 155, 0.18), transparent 32%),
        #f3f7fc;
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

.chatbot-header__top {
    margin-bottom: 14px;
}

.chatbot-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
}

.chatbot-back__icon {
    width: 18px;
    height: 18px;
}

.chatbot-header h1 {
    margin: 0;
    font-size: 1.4rem;
}

.chatbot-header p {
    margin: 6px 0 0;
    opacity: 0.9;
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

.message p {
    margin: 0;
    white-space: pre-wrap;
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

.message--typing {
    opacity: 0.75;
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

.chatbot-input:focus {
    outline: 2px solid #00a19b;
    border-color: #00a19b;
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

@media (max-width: 768px) {
    .chatbot-main {
        padding: 16px 8px;
    }

    .chatbot-shell {
        min-height: 82vh;
        border-radius: 14px;
    }

    .message {
        max-width: 90%;
    }
}
</style>
