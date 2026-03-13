<template>
    <input
        :type="type"
        :placeholder="placeholder"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :name="name"
        :id="id"
        :class="inputClass"
    />
</template>

<script setup lang="ts">
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    placeholder: {
        type: String,
        default: '',
    },
    name: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: '',
    },
    inputClass: {
        type: String,
        default: '',
    },
});
</script>

<style lang="scss" scoped></style>
