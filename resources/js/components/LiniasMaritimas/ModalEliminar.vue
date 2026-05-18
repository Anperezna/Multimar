<template>
    <div v-if="mostrar" class="modal-overlay" @click.self="cerrar">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3>Confirmar eliminación</h3>
            </div>
            <div class="modal-body">
                <p class="modal-text">¿Estás seguro de que deseas eliminar esta línia marítima?</p>
                <p class="modal-warning">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-actions">
                <button @click="cerrar" type="button" class="btn-cancelar" :disabled="eliminando">
                    Cancelar
                </button>
                <button @click="confirmarEliminar" :disabled="eliminando" type="button" class="btn-eliminar">
                    {{ eliminando ? 'Eliminando...' : 'Eliminar' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    mostrar: Boolean
});

const emit = defineEmits(['cerrar', 'confirmar']);

const eliminando = ref(false);

const cerrar = () => {
    emit('cerrar');
};

const confirmarEliminar = async () => {
    eliminando.value = true;
    try {
        emit('confirmar');
    } finally {
        eliminando.value = false;
    }
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 0.5rem;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
}

.modal-small {
    max-width: 400px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
}

.modal-body {
    padding: 1.5rem;
}

.modal-text {
    margin: 0 0 0.5rem 0;
    color: #333;
    text-align: center;
    font-size: 0.95rem;
}

.modal-warning {
    margin: 0;
    color: #d32f2f;
    text-align: center;
    font-size: 0.85rem;
    font-weight: 500;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid #e0e0e0;
    justify-content: flex-end;
}

.btn-cancelar,
.btn-eliminar {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: background-color 0.3s;
}

.btn-cancelar {
    background-color: #e0e0e0;
    color: #333;
}

.btn-cancelar:hover:not(:disabled) {
    background-color: #d0d0d0;
}

.btn-cancelar:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.btn-eliminar {
    background-color: #d32f2f;
    color: white;
}

.btn-eliminar:hover:not(:disabled) {
    background-color: #b71c1c;
}

.btn-eliminar:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
</style>
