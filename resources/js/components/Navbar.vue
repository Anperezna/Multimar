<template>
    <nav class="navbar">
        <div class="navbar__logo"><img :src="logo" alt="Logo" class="logo"></div>
        <div class="navbar__nav">
            <router-link
                v-for="item in adminPages"
                :key="item.id"
                :to="item.route"
                class="navbar__item"
                custom
                v-slot="{ navigate, href, isActive }"
            >
                <button
                    :class="['navbar__item', { 'active': isActive }]"
                    @click="navigate"
                    :aria-current="isActive ? 'page' : undefined"
                >
                    {{ item.pagina }}
                </button>
            </router-link>
        </div>
        <router-link :to="`/ajustes/${userId}`">
            <button class="navbar__settings">Ajustes</button>
        </router-link>
    </nav>
</template>

<script setup>
import { computed, ref } from 'vue';
import logo from '../../../prueba.png';

const fallbackPages = [
    { id: 1, pagina: 'Dashboard', route: '/home' },
    { id: 2, pagina: 'Clientes', route: '/clientes' },
    { id: 3, pagina: 'Envios', route: '/envios' },
];

const opciones = ref([{
    admin: {
        id: 1,
        nombre: [
            { id: 1, pagina: "Dashboard", route: "/home" },
            { id: 2, pagina: "Clientes", route: "/clientes" }
        ]
    },
    operador: {
        id: 1,
        nombre: [
            { id: 1, pagina: "Dashboard", route: "/home" },
            { id: 2, pagina: "Clientes", route: "/clientes" }
        ]
    },
    usuario: {

    }
}])

const adminPages = computed(() => {
    return opciones.value[0]?.admin?.nombre ?? fallbackPages;
});
</script>

<style scoped>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #1B2A4A;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
}

.navbar__logo,
.navbar__nav,
.navbar__settings {
    color: #f0f0f0;
    font-weight: 600;
}

.navbar__settings {
    cursor: pointer;
}

.navbar__nav {
    display: flex;
    gap: 16px;
}

.navbar__item {
    cursor: pointer;
}

.logo {
    width: 60px;
    height: 60px;
    object-fit: contain;
}
</style>