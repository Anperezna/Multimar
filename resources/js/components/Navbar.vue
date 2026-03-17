<template>
    <nav class="navbar">
        <div class="navbar__logo"><img :src="logo" alt="Logo" class="logo"></div>
        <div class="navbar__nav-center">
            <router-link
                v-for="item in paginasCentro"
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
                    <img :src="item.icon" alt="icons" class="navbar__icon">
                    {{ item.pagina }}
                </button>
            </router-link>
        </div>
        <div class="navbar__right">
            <router-link
                v-for="item in paginasDerecha"
                :key="item.id"
                :to="item.route"
            >
                <button class="navbar__settings">
                    <img v-if="item.icon" :src="item.icon" alt="icon" class="navbar__icon" />
                </button>
            </router-link>
        </div>
    </nav>
</template>

<script setup>
import { computed, ref } from 'vue';
import logo from '../../../prueba.png';
import settingsIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/settings-w.svg';
import notificationIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/bell-w.svg';
import usersIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/users-w.svg';
import dashboardIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/dashboard-w.svg';
import solicitudOfertaIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/solicitud-w.svg';
import ofertaIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/oferta-w.svg';

// Rutas comunes para todos los roles
const paginasEnComun = [
    { id: 1, pagina: 'Dashboard', route: '/home', icon: dashboardIcon },
    { id: 2, pagina: 'Notificaciones', route: '/notificaciones', icon: notificationIcon },
    { id: 3, pagina: 'Ajustes', route: '/ajustes', icon: settingsIcon}
];

// Rutas específicas por rol
const paginasPorRol = {
    admin: [
        { id: 4, pagina: 'Clientes', route: '/clientes', icon: usersIcon}
    ],
    usuario: [
        { id: 5, pagina: 'Solicitud Oferta', route: '/solicitudOferta', icon: solicitudOfertaIcon }
    ],
    operador: [
        { id: 6, pagina: 'Cliente', route: '/cliente', icon: usersIcon },
        { id: 7, pagina: 'Ofertas', route: '/ofertas', icon: ofertaIcon}
    ]
};

// Función para obtener las páginas según el rol
function obtenerPaginasPorRol(rol) {
    return [
        ...paginasEnComun,
        ...(paginasPorRol[rol] || [])
    ];
}

const usuarioRol = ref('operador'); // Cambia dinámicamente según el usuario

const paginas = computed(() => obtenerPaginasPorRol(usuarioRol.value));

// Extrae las páginas de la derecha (Ajustes y Notificaciones)
const paginasDerecha = computed(() => {
    return paginas.value.filter(p => p.pagina === 'Ajustes' || p.pagina === 'Notificaciones');
});

// Páginas del centro (Dashboard y específicas)
const paginasCentro = computed(() => {
    return paginas.value.filter(p => p.pagina !== 'Ajustes' && p.pagina !== 'Notificaciones');
});
</script>

<style scoped>

.navbar {
    display: flex;
    align-items: center;
    background-color: #1B2A4A;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    position: relative;
}

.navbar__logo {
    color: #f0f0f0;
    font-weight: 600;
    margin-right: 32px;
    z-index: 2;
}

.navbar__nav-center {
    display: flex;
    gap: 16px;
    color: #f0f0f0;
    font-weight: 600;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
}

.navbar__right {
    display: flex;
    align-items: center;
    gap: 20px;
    color: #f0f0f0;
    font-weight: 600;
    margin-left: auto;
    z-index: 2;
}

.navbar__settings {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

.navbar__item {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}

.logo {
    width: 60px;
    height: 60px;
    object-fit: contain;
}

.navbar__icon {
    width: 20px;
    height: 20px;
    object-fit: contain;
    margin-right: 2px;
}
</style>