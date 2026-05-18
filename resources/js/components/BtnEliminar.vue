<template>
    <button class="btn-accion btn-eliminar" title="Eliminar" @click="$emit('click')">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
        </svg>
    </button>
</template>

<script setup>
defineEmits(['click']);
</script>

<style scoped>
.btn-accion {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
}

.btn-eliminar {
    background-color: #dc2626;
    color: white;
}

.btn-eliminar:hover {
    background-color: #b91c1c;
    transform: scale(1.05);
}

.btn-eliminar svg {
    width: 1.125rem;
    height: 1.125rem;
    stroke: currentColor;
}
</style>
