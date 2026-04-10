<template>
    <SettingsLayout current-tab="contrasena">
        <section class="panel-body">
            <h2>Editar Contrasena</h2>
            <p>Cambia tu contrasena de acceso al sistema.</p>

            <p v-if="successMsg" class="feedback success">{{ successMsg }}</p>
            <p v-if="errorMsg" class="feedback error">{{ errorMsg }}</p>

            <form class="panel-form" @submit.prevent="updatePassword">
                <div class="field">
                    <label for="current-password">Contrasena actual</label>
                    <Input
                        id="current-password"
                        v-model="passwordForm.currentPassword"
                        type="password"
                        placeholder="Introduce tu contrasena actual"
                        inputClass="settings-input"
                    />
                </div>

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
                    <Botones :disabled="isSaving">
                        <span v-if="!isSaving">Actualizar Contrasena</span>
                        <span v-else class="spinner spinner--small" aria-label="Guardando"></span>
                    </Botones>
                </div>
            </form>
        </section>
    </SettingsLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { reactive, ref } from 'vue';
import Botones from '@/components/Botones.vue';
import Input from '@/components/Input.vue';
import SettingsLayout from '@/components/settings/SettingsLayout.vue';
import api from '@/lib/api';

const passwordForm = reactive({
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
});

const isSaving = ref(false);
const errorMsg = ref('');
const successMsg = ref('');

const updatePassword = async () => {
    errorMsg.value = '';
    successMsg.value = '';
    isSaving.value = true;

    try {
        await api.post('/user/password', {
            current_password: passwordForm.currentPassword,
            new_password: passwordForm.newPassword,
            new_password_confirmation: passwordForm.confirmPassword,
        });

        successMsg.value = 'Contrasena actualizada correctamente.';
        passwordForm.currentPassword = '';
        passwordForm.newPassword = '';
        passwordForm.confirmPassword = '';
    } catch (error) {
        if (axios.isAxiosError(error)) {
            errorMsg.value =
                (error.response?.data?.message as string) ||
                (Object.values(error.response?.data?.errors || {}).flat()?.[0] as string) ||
                'No se pudo actualizar la contrasena.';
        } else {
            errorMsg.value = 'No se pudo actualizar la contrasena.';
        }
    } finally {
        isSaving.value = false;
    }
};
</script>

<style>
.panel-body h2 {
    margin: 0;
    color: #1d2d46;
}

.panel-body p {
    margin: 6px 0 18px;
    color: #6d7f96;
    font-size: 0.92rem;
}

.feedback {
    margin: 6px 0;
    font-size: 0.9rem;
}

.feedback.success {
    color: #0f9f71;
}

.feedback.error {
    color: #b42318;
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

.settings-input {
    width: 100%;
    border: 1px solid #cfdbe8;
    border-radius: 8px;
    height: 42px;
    padding: 0 12px;
    color: #1d2d46;
    background: #ffffff;
}

.settings-input:focus {
    outline: none;
    border-color: #97bfdc;
}

.actions-start {
    display: flex;
    justify-content: flex-start;
}

.btn-login {
    min-width: 190px;
    height: 42px;
    border-radius: 8px;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.35);
    border-top-color: #ffffff;
    border-radius: 50%;
    display: inline-block;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
