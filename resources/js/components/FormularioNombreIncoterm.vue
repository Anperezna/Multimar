<template>
    <div class="campos-incoterm">
        <div class="campo-incoterm">
            <label for="codigo-incoterm">Código del incoterm</label>
            <input
                id="codigo-incoterm"
                type="text"
                class="entrada-texto"
                :value="codigoActual"
                placeholder="Ej: CIF"
                @input="manejarCambioCodigo"
            />
        </div>

        <div class="campo-incoterm">
            <label for="nombre-incoterm">Nombre del incoterm</label>
            <input
                id="nombre-incoterm"
                type="text"
                class="entrada-texto"
                :value="nombreActual"
                placeholder="Ej: Cost, Insurance and Freight"
                @input="manejarCambioNombre"
            />
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    codigoActual: {
        type: String,
        default: '',
    },
    nombreActual: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['actualizar-codigo', 'actualizar-nombre']);

function manejarCambioCodigo(evento) {
    emit('actualizar-codigo', evento.target.value);
}

function manejarCambioNombre(evento) {
    emit('actualizar-nombre', evento.target.value);
}
</script>

<style lang="scss" scoped>
.campos-incoterm {
    display: grid;
    gap: 6px;
    margin-bottom: 18px;
}

.campo-incoterm {
    display: grid;
    gap: 6px;
}

.campo-incoterm label {
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
