<template>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
    <Suspense>
      <template #default>
        <router-view />
      </template>
      <template #fallback>
        Cargando...
      </template>
    </Suspense>
</template>   

<script setup lang="ts">
// No logic needed, router-view muestra las rutas

</script>

<style>
body {
    font-family: Inter;
}
</style>
