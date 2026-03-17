<template>
    <SettingsLayout current-tab="contrasena">
        <section class="panel-body">
            <h2>Editar Contrasena</h2>
            <p>Cambia tu contrasena de acceso al sistema.</p>

            <form class="panel-form" @submit.prevent>
                <div class="field">
                    <label for="new-password">Nueva contrasena</label>
                    <Input
                        id="new-password"
                        v-model="passwordForm.newPassword"
                        type="password"
                        placeholder="Minimo 8 caracteres"
                        inputClass="settings-input"
                    />
                </div>

                <div class="field">
                    <label for="confirm-password">Confirmar contrasena</label>
                    <Input
                        id="confirm-password"
                        v-model="passwordForm.confirmPassword"
                        type="password"
                        placeholder="Repite la contrasena"
                        inputClass="settings-input"
                    />
                </div>

                <div class="actions-start">
                    <Botones>Actualizar Contrasena</Botones>
                </div>
            </form>
        </section>
    </SettingsLayout>
</template>

<script setup lang="ts">
import { reactive } from 'vue';
import Botones from '@/components/Botones.vue';
import Input from '@/components/Input.vue';
import SettingsLayout from '@/components/settings/SettingsLayout.vue';

const passwordForm = reactive({
    newPassword: '',
    confirmPassword: '',
});
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

.panel-form {
    max-width: 560px;
    display: grid;
    gap: 16px;
}

.field {
    display: grid;
    gap: 6px;
}

.field label {
    color: #334760;
    font-size: 0.9rem;
    font-weight: 600;
}

:deep(.settings-input) {
    width: 100%;
    border: 1px solid #cfdbe8;
    border-radius: 8px;
    height: 42px;
    padding: 0 12px;
    color: #1d2d46;
    background: #ffffff;
}

:deep(.settings-input:focus) {
    outline: none;
    border-color: #97bfdc;
}

.actions-start {
    display: flex;
    justify-content: flex-start;
}

:deep(.btn-login) {
    min-width: 190px;
    height: 42px;
    border-radius: 8px;
}
</style>
