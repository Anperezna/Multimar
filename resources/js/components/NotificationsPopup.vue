<template>
    <div class="notifications-container">
        <button
            class="notifications-button"
            type="button"
            @click="togglePopup"
        >
            <img
                src="../../../public/icons_multimar/icons-simex/compartidos/light_icons/bell-w.svg"
                alt="Notificaciones"
                class="notifications-icon"
            />
            <span v-if="unreadCount > 0" class="unread-badge">{{ unreadCount }}</span>
        </button>

        <div v-if="isOpen" class="notifications-popup">
            <div class="popup-header">
                <h3 class="popup-title">Notificaciones</h3>
                <button
                    class="close-button"
                    type="button"
                    @click="togglePopup"
                >
                    ✕
                </button>
            </div>

            <div class="popup-content">
                <div v-if="notificaciones.length === 0" class="empty-message">
                    <p>No hay notificaciones</p>
                </div>

                <div v-else class="notifications-list">
                    <div
                        v-for="notif in notificaciones"
                        :key="notif.id"
                        class="notification-item"
                        :class="{ 'unread': esNoLeida(notif) }"
                    >
                        <div class="notification-content">
                            <h4 class="notification-title">{{ notif.titulo }}</h4>
                            <p class="notification-description">{{ notif.descripcion }}</p>
                        </div>
                        <div class="notification-actions">
                            <button
                                v-if="esNoLeida(notif)"
                                class="action-button read-button"
                                type="button"
                                @click="marcarLeida(notif.id)"
                            >
                                ✓
                            </button>
                            <button
                                class="action-button delete-button"
                                type="button"
                                @click="eliminar(notif.id)"
                            >
                                🗑
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/lib/api';

const isOpen = ref(false);
const notificaciones = ref([]);
const unreadCount = ref(0);

function esNoLeida(notif) {
    return notif.visto === 0 || notif.visto === '0' || notif.visto === false;
}

function togglePopup() {
    isOpen.value = !isOpen.value;
}

async function cargar() {
    const resp = await api.get('/notificaciones');
    notificaciones.value = resp.data;
    let contador = 0;
    let i = 0;
    while (i < notificaciones.value.length) {
        if (esNoLeida(notificaciones.value[i])) {
            contador = contador + 1;
        }
        i = i + 1;
    }
    unreadCount.value = contador;
}

async function marcarLeida(notifId) {
    await api.patch(`/notificaciones/${notifId}/read`);
    cargar();
}

async function eliminar(notifId) {
    await api.delete(`/notificaciones/${notifId}`);
    notificaciones.value = notificaciones.value.filter((notif) => notif.id !== notifId);

    let contador = 0;
    let i = 0;
    while (i < notificaciones.value.length) {
        if (esNoLeida(notificaciones.value[i])) {
            contador = contador + 1;
        }
        i = i + 1;
    }
    unreadCount.value = contador;
}

onMounted(() => {
    cargar();
    setInterval(cargar, 30000);
});
</script>

<style scoped>
.notifications-container {
    position: relative;
}

.notifications-button {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
}

.notifications-icon {
    width: 20px;
    height: 20px;
    object-fit: contain;
    filter: brightness(0) invert(1);
}

.unread-badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
}

.notifications-popup {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    width: 360px;
    max-height: 400px;
    display: flex;
    flex-direction: column;
    margin-top: 8px;
}

.popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid #eee;
}

.popup-title {
    margin: 0;
    font-size: 1rem;
    color: #1d2d46;
    font-weight: 600;
}

.close-button {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.2rem;
    color: #999;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-button:hover {
    color: #333;
}

.popup-content {
    flex: 1;
    overflow-y: auto;
}

.empty-message {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100px;
    color: #999;
    font-size: 0.9rem;
}

.empty-message p {
    margin: 0;
}

.notifications-list {
    display: flex;
    flex-direction: column;
}

.notification-item {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f0f0f0;
    background: white;
}

.notification-item.unread {
    background: #f9fbff;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    margin: 0 0 4px 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1d2d46;
}

.notification-description {
    margin: 0;
    font-size: 0.85rem;
    color: #666;
    line-height: 1.4;
    word-break: break-word;
}

.notification-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.action-button {
    background: none;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 4px 8px;
    cursor: pointer;
    font-size: 0.85rem;
    color: #666;
    transition: all 0.2s;
}

.action-button:hover {
    border-color: #999;
    color: #333;
}

.read-button {
    border-color: #4caf50;
    color: #4caf50;
}

.read-button:hover {
    background: #f1f8f4;
}

.delete-button {
    border-color: #e74c3c;
    color: #e74c3c;
}

.delete-button:hover {
    background: #fef0f0;
}
</style>
