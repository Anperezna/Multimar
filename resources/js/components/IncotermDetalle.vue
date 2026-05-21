<template>
    <div class="detalle-inputs">
        <div class="grupo-campo">
            <label for="input-codi">Código del incoterm</label>
            <input
                id="input-codi"
                type="text"
                class="entrada-texto"
                placeholder="Ej: FOB, CIF, EXW..."
                :value="codigo"
                @input="$emit('actualizar-codigo', $event.target.value)"
            />
        </div>

        <div class="grupo-campo">
            <label for="input-nom">Nombre del incoterm</label>
            <input
                id="input-nom"
                type="text"
                class="entrada-texto"
                placeholder="Nombre descriptivo del incoterm"
                :value="nombre"
                @input="$emit('actualizar-nombre', $event.target.value)"
            />
        </div>
    </div>
</template>

<script setup>
// Recibimos los datos actuales desde el padre
defineProps({
    codigo: {
        type: String,
        default: ''
    },
    nombre: {
        type: String,
        default: ''
    }
});

// Definimos los eventos para avisar al padre cuando el usuario escriba
defineEmits(['actualizar-codigo', 'actualizar-nombre']);
</script>

<style lang="scss" scoped>
.detalle-inputs {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 18px;
}

.grupo-campo {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.grupo-campo label {
    font-size: 0.9rem;
    font-weight: 700;
    color: #25415f;
}

.entrada-texto {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d8e3ef;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #10243f;
    background: #fff;
    transition: all 0.2s ease;

    &:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }
}
</style>