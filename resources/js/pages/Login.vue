<template>
    <div id="container">
        <h1>Login</h1>
        <Input placeholder="Email" />
    </div>
</template>

<script setup>
import Input from '@/components/Input.vue';

</script>

<style scoped>
#container {
    min-height: 100vh;
    display: grid;
    place-content: center;
    gap: 12px;
}
</style>