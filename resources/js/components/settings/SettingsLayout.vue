<template>
    <div class="settings-page">
        <Navbar />

        <main class="settings-layout">
            <header class="settings-header">
                <h1>Ajustes</h1>
                <p>Configura tu cuenta y preferencias.</p>
            </header>

            <section class="settings-content">
                <aside class="settings-nav" aria-label="Navegacion de ajustes">
                    <router-link
                        v-for="item in menuItems"
                        :key="item.id"
                        :to="item.to"
                        class="settings-nav__item"
                        :class="{ 'is-active': item.active }"
                    >
                        <img :src="item.icon" :alt="''" class="settings-nav__icon" aria-hidden="true" />
                        <span>{{ item.label }}</span>
                    </router-link>
                </aside>

                <article class="settings-panel">
                    <slot />
                </article>
            </section>
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import Navbar from '@/components/Navbar.vue';

type SettingsTab = 'perfil' | 'contrasena' | 'accesibilidad';

const props = defineProps<{
    currentTab: SettingsTab;
}>();

const menuItems = computed(() => {
    return [
        {
            id: 1,
            label: 'Editar Perfil',
            icon: '/icons_multimar/icons-simex/compartidos/dark_icons/user-b.svg',
            to: '/ajustes',
            active: props.currentTab === 'perfil',
        },
        {
            id: 2,
            label: 'Editar Contrasena',
            icon: '/icons_multimar/icons-simex/daw/candado.svg',
            to: '/ajustes/contrasena',
            active: props.currentTab === 'contrasena',
        },
        {
            id: 3,
            label: 'Accesibilidad',
            icon: '/icons_multimar/icons-simex/compartidos/dark_icons/eye-b.svg',
            to: '/ajustes/accesibilidad',
            active: props.currentTab === 'accesibilidad',
        },
    ];
});
</script>

<style scoped>
.settings-page {
    min-height: 100vh;
    background: #e9eef5;
}

.settings-layout {
    margin: 0 auto;
    width: min(1100px, 100%);
    padding: 24px;
}

.settings-header {
    margin-bottom: 16px;
}

.settings-header h1 {
    margin: 0;
    font-size: 2rem;
    color: #192a46;
}

.settings-header p {
    margin: 6px 0 0;
    color: #6d7f96;
}

.settings-content {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 24px;
    align-items: start;
}

.settings-nav {
    background: #ffffff;
    border: 1px solid #d9e3ef;
    border-radius: 10px;
    overflow: hidden;
}

.settings-nav__item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 12px;
    color: #44566d;
    text-decoration: none;
    border-bottom: 1px solid #ecf1f7;
    font-size: 0.92rem;
}

.settings-nav__item:last-child {
    border-bottom: 0;
}

.settings-nav__item.is-active {
    color: #0ea5e9;
    background: #eaf6fd;
    font-weight: 600;
}

.settings-nav__icon {
    width: 16px;
    height: 16px;
    object-fit: contain;
    flex: 0 0 16px;
}

.settings-panel {
    background: #ffffff;
    border: 1px solid #d9e3ef;
    border-radius: 10px;
    min-height: 430px;
    padding: 22px;
}

@media (max-width: 880px) {
    .settings-content {
        grid-template-columns: 1fr;
    }

    .settings-layout {
        padding: 16px;
    }
}
</style>
