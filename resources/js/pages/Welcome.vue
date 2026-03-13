<template>
    <main class="welcome-page">
        <h1>Inicio</h1>
        <p>Vue Router esta funcionando.</p>
        <router-link to="/login">Ir a Login</router-link>
    </main>
</template>

<style scoped>
.welcome-page {
    min-height: 100vh;
    display: grid;
    place-content: center;
    gap: 12px;
    text-align: center;
}
</style>
