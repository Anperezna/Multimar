<template>
    <select
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        :name="name"
        :id="id"
        :class="selectClass"
        :disabled="disabled"
    >
        <option value="" disabled>{{ placeholder }}</option>
        <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>

<script setup lang="ts">
interface SelectOption {
    value: string | number;
    label: string;
}

defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    options: {
        type: Array<SelectOption>,
        required: true,
    },
    placeholder: {
        type: String,
        default: 'Selecciona una opción',
    },
    name: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: '',
    },
    selectClass: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:modelValue']);
</script>

<style lang="scss" scoped>
select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    background-color: #fff;
    color: #1f2937;
    transition: border-color 0.2s, background-color 0.2s;

    &:hover:not(:disabled) {
        border-color: #0ea5e9;
    }

    &:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f3f4f6;
    }

    option {
        color: #1f2937;
    }
}
</style>
