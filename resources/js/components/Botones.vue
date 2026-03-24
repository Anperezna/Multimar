<template>
    <button class="btn-login" :disabled="disabled" @click="$emit('click')">
        <slot>Iniciar sesión</slot>
    </button>
</template>

<script setup lang="ts">
defineProps({
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['click']);
</script>

<style lang="scss" scoped>
.btn-login {
    padding: 0.5rem 1.25rem;
    border: none;
    border-radius: 6px; 
    font-size: 1rem;
    cursor: pointer;
    background-color: #0ea5e9;
    color: #fff;
    transition: background-color 0.2s;

    &:hover:not(:disabled) {
        background-color: #0284c7;
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
}
</style>
