<template>
    <div class="campo-nombre">
        <label for="nombre-incoterm">Nombre del incoterm</label>
        <input
            id="nombre-incoterm"
            type="text"
            class="entrada-texto"
            :value="nombreActual"
            placeholder="Ej: CIF, FOB, EXW..."
            @input="manejarCambioNombre"
        />
    </div>
</template>

<script setup>
const props = defineProps({
    nombreActual: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['actualizar-nombre']);

function manejarCambioNombre(evento) {
    emit('actualizar-nombre', evento.target.value);
}
</script>

<style lang="scss" scoped>
.campo-nombre {
    display: grid;
    gap: 6px;
    margin-bottom: 18px;
}

.campo-nombre label {
    font-size: 0.9rem;
    font-weight: 700;
    color: #25415f;
}

.entrada-texto {
    width: 100%;
    min-height: 42px;
    border: 1px solid #cfdbe8;
    border-radius: 12px;
    padding: 0.65rem 0.85rem;
    font-size: 0.95rem;
    color: #10243f;
    background: #fff;
    transition: all 0.2s ease;

    &:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }

    &:hover {
        border-color: #b8d4e8;
    }

    &::placeholder {
        color: #9fb0c4;
    }
}
</style>
