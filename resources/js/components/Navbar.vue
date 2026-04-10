<template>
    <nav v-if="usuarioRol !== ''" class="navbar">
        <div class="navbar__logo"><img :src="logo" alt="Logo" class="logo"></div>
        <div class="navbar__nav-center">
            <router-link to="/home" class="navbar__item" active-class="active">
                <img :src="dashboardIcon" alt="icons" class="navbar__icon">
                Dashboard
            </router-link>

            <router-link v-if="usuarioRol === 'Admin'" to="/clientes" class="navbar__item" active-class="active">
                <img :src="usersIcon" alt="icons" class="navbar__icon">
                Clientes
            </router-link>

            <router-link v-if="usuarioRol === 'Usuari'" to="/solicitudOferta" class="navbar__item" active-class="active">
                <img :src="solicitudOfertaIcon" alt="icons" class="navbar__icon">
                Solicitud Oferta
            </router-link>

            <template v-if="usuarioRol === 'Operador'">
                <router-link to="/clientes" class="navbar__item" active-class="active">
                    <img :src="usersIcon" alt="icons" class="navbar__icon">
                    Clientes
                </router-link>

                <router-link to="/ofertas" class="navbar__item" active-class="active">
                    <img :src="ofertaIcon" alt="icons" class="navbar__icon">
                    Ofertas
                </router-link>
            </template>
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
            <p v-if="usuarioNombre" class="navbar__username">{{ usuarioNombre }}</p>
            <button class="navbar__settings" type="button" @click="logout">
                <img src="../../../public/icons_multimar/icons-simex/compartidos/light_icons/logout-w.svg" alt="" class="navbar__icon">
            </button>
        </div>
    </nav>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import logo from '../../../prueba.png';
import settingsIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/settings-w.svg';
import notificationIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/bell-w.svg';
import usersIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/users-w.svg';
import dashboardIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/dashboard-w.svg';
import solicitudOfertaIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/solicitud-w.svg';
import ofertaIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/oferta-w.svg';
import api from '@/lib/api';

const usuarioRol = ref(localStorage.getItem('user_rol') || '');
const usuarioNombre = ref(localStorage.getItem('user_name') || '');
const router = useRouter();

// Extrae las páginas de la derecha (Ajustes y Notificaciones)
const paginasDerecha = computed(() => {
    return [
        { id: 2, pagina: 'Notificaciones', route: '/notificaciones', icon: notificationIcon },
        { id: 3, pagina: 'Ajustes', route: '/ajustes', icon: settingsIcon }
    ];
});

async function logout() {
    try {
        await api.post('/logout');
    } catch {
        // Clear local session even if API logout fails.
    } finally {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_rol');
        localStorage.removeItem('user_name');
        usuarioRol.value = '';
        usuarioNombre.value = '';
        await router.push('/login');
    }
}


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

.navbar__settings {    cursor: pointer;
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

.navbar__username {
    margin: 0;
    font-size: 0.9rem;
    white-space: nowrap;
}
</style>