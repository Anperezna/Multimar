<template>
    <nav class="navbar">
        <div class="navbar__logo"><img :src="logo" alt="Logo" class="logo"></div>
        <div class="navbar__nav">
            <router-link
                v-for="item in paginasSinAjustes"
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
        <div class="navbar__settings-wrapper">
            <router-link
                v-if="ajustesPagina"
                :to="ajustesPagina.route"
            >
                <button class="navbar__settings">Ajustes</button>
            </router-link>
        </div>
    </nav>
</template>

<script setup>
import { computed, ref } from 'vue';
import logo from '../../../prueba.png';

// Rutas comunes para todos los roles
const paginasEnComun = [
    { id: 1, pagina: 'Dashboard', route: '/home' },
    { id: 2, pagina: 'Ajustes', route: '/ajustes' },
    { id: 3, pagina: 'Notificaciones', route: '/notificaciones' }
];

// Rutas específicas por rol
const paginasPorRol = {
    admin: [
        { id: 4, pagina: 'Clientes', route: '/clientes' }
    ],
    usuario: [
        { id: 5, pagina: 'Solicitud Oferta', route: '/' }
    ],
    operador: [
        { id: 6, pagina: 'Ofertas', route: '/ofertas' }
    ]
};

// Función para obtener las páginas según el rol
function obtenerPaginasPorRol(rol) {
    return [
        ...paginasEnComun,
        ...(paginasPorRol[rol] || [])
    ];
}

const usuarioRol = ref('admin'); // Cambia dinámicamente según el usuario

const paginas = computed(() => obtenerPaginasPorRol(usuarioRol.value));

// Extrae la página de Ajustes para mostrarla aparte
const ajustesPagina = computed(() => {
    return paginas.value.find(p => p.pagina === 'Ajustes');
});

const paginasSinAjustes = computed(() => {
    return paginas.value.filter(p => p.pagina !== 'Ajustes');
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

.navbar__settings-wrapper {
    margin-left: auto;
    display: flex;
    align-items: center;
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