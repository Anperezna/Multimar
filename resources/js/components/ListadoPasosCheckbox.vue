<template>
    <div class="campo-pasos">
        <label>Pasos de seguimiento</label>
        <p class="ayuda-pasos">Selecciona los pasos que aplicarán a este incoterm</p>

        <div v-if="pasosDisponibles.length === 0" class="sin-pasos">
            <p>No hay pasos disponibles en la base de datos.</p>
        </div>

        <div v-else class="lista-checkboxes">
            <div v-for="paso in pasosDisponibles" :key="paso.id" class="elemento-checkbox">
                <input
                    :id="`paso-${paso.id}`"
                    type="checkbox"
                    class="checkbox"
                    :value="paso.id"
                    @change="manejarCambioPaso"
                />
                <label :for="`paso-${paso.id}`" class="etiqueta-checkbox">
                    <span class="numero-paso">{{ paso.ordre }}</span>
                    <span class="nombre-paso">{{ paso.nom }}</span>
                </label>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    pasosDisponibles: {
        type: Array,
        default: () => [],
    },
    pasosSeleccionados: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['actualizar-pasos']);

function manejarCambioPaso(evento) {
    const pasoId = parseInt(evento.target.value, 10);
    
    let pasoIdsActualizados;
    
    if (evento.target.checked) {
        // Agregar el paso a la lista
        pasoIdsActualizados = [...props.pasosSeleccionados, pasoId];
    } else {
        // Remover el paso de la lista
        pasoIdsActualizados = props.pasosSeleccionados.filter(id => id !== pasoId);
    }

    emit('actualizar-pasos', pasoIdsActualizados);
}
</script>

<style lang="scss" scoped>
.campo-pasos {
    margin-bottom: 18px;
}

.campo-pasos > label {
    display: block;
    font-size: 0.9rem;
    font-weight: 700;
    color: #25415f;
    margin-bottom: 6px;
}

.ayuda-pasos {
    margin: 6px 0 12px;
    font-size: 0.85rem;
    color: #7a8fa0;
}

.sin-pasos {
    padding: 16px;
    background: #f0f5fa;
    border-radius: 10px;
    border: 1px solid #dde7f0;
    color: #5b6f88;
    text-align: center;
    font-size: 0.9rem;
}

.lista-checkboxes {
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f8fafd;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #dde7f0;
}

.elemento-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #0ea5e9;
}

.etiqueta-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
    flex: 1;
}

.numero-paso {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background: rgba(14, 165, 233, 0.12);
    color: #0b5f87;
    font-weight: 700;
    font-size: 0.8rem;
}

.nombre-paso {
    color: #10243f;
    font-weight: 500;
    font-size: 0.95rem;
}
</style>
