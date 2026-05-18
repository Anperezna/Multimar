<template>
    <button class="btn-accion btn-editar" title="Editar" @click="$emit('click')">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
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

.btn-editar {
    background-color: #666;
    color: white;
}

.btn-editar:hover {
    background-color: #4d4d4d;
    transform: scale(1.05);
}

.btn-editar svg {
    width: 1.125rem;
    height: 1.125rem;
    stroke: currentColor;
}
</style>
