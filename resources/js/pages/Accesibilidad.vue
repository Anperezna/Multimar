<template>
    <SettingsLayout current-tab="accesibilidad">
        <section class="panel-body">
            <h2>Accesibilidad</h2>
            <p>Personaliza la apariencia de la aplicacion.</p>

            <div class="accessibility-card">
                <div class="accessibility-left">
                    <div class="sun-icon" aria-hidden="true">*</div>
                    <div>
                        <strong>Modo Oscuro</strong>
                        <small>Activa el modo oscuro para reducir la fatiga visual</small>
                    </div>
                </div>

                <label class="switch" for="dark-mode">
                    <input id="dark-mode" v-model="darkMode" type="checkbox" />
                    <span class="switch-slider"></span>
                </label>
            </div>
        </section>
    </SettingsLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import SettingsLayout from '@/components/settings/SettingsLayout.vue';

const darkMode = ref(false);
</script>

<style scoped>
.panel-body h2 {
    margin: 0;
    color: #1d2d46;
}

.panel-body p {
    margin: 6px 0 18px;
    color: #6d7f96;
    font-size: 0.92rem;
}

.accessibility-card {
    border: 1px solid #e1e9f3;
    border-radius: 10px;
    padding: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.accessibility-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sun-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #fff3dc;
    color: #de9a17;
    display: grid;
    place-items: center;
}

.accessibility-left small {
    display: block;
    margin-top: 3px;
    color: #6d7f96;
}

.switch {
    position: relative;
    width: 52px;
    height: 28px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.switch-slider {
    position: absolute;
    inset: 0;
    border-radius: 999px;
    background: #d2dce9;
    cursor: pointer;
    transition: background 0.2s ease;
}

.switch-slider::before {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #ffffff;
    transition: transform 0.2s ease;
}

.switch input:checked + .switch-slider {
    background: #0ea5e9;
}

.switch input:checked + .switch-slider::before {
    transform: translateX(24px);
}
</style>
