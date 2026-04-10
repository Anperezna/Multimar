<template>
    <div class="login-page">
        <div class="login-card">
            <div class="card-header">
                <img src="/img/logo.png" alt="Multimar" class="brand-logo" />
                <h1 class="brand-title">MULTIMAR</h1>
                <p class="brand-caption">Gestion Logistica Integral</p>
            </div>

            <form class="card-form" @submit.prevent="submitLogin">
                <label for="email" class="input-label">Correo electronico</label>
                <Input
                    id="email"
                    v-model="email"
                    type="email"
                    name="email"
                    inputClass="login-input"
                />

                <label for="password" class="input-label">Contrasena</label>
                <Input
                    id="password"
                    v-model="password"
                    type="password"
                    name="password"
                    inputClass="login-input"
                />

                <Botones class="login-button" :disabled="isLoading">
                    <span v-if="!isLoading">Iniciar Sesion</span>
                    <span v-else class="spinner-inline" aria-label="Cargando"></span>
                </Botones>
                <small v-if="errorMsg" class="login-error">{{ errorMsg }}</small>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import Botones from '@/components/Botones.vue';
import Input from '@/components/Input.vue';
import api from '@/lib/api';

const email = ref('');
const password = ref('');
const errorMsg = ref('');
const isLoading = ref(false);
const router = useRouter();

const submitLogin = async () => {
    errorMsg.value = '';
    isLoading.value = true;

    try {
        const { data } = await api.post('/login', {
            correu: email.value,
            password: password.value,
        });

        if (data?.token) {
            localStorage.setItem('auth_token', data.token);
        }

        if (data?.rol) {
            localStorage.setItem('user_rol', data.rol);
        }

        let nombreCompleto = '';
        if (data?.nombre) {
            nombreCompleto = data.nombre;
        }
        if (data?.apellidos) {
            nombreCompleto = nombreCompleto + ' ' + data.apellidos;
        }
        if (nombreCompleto) {
            localStorage.setItem('user_name', nombreCompleto.trim());
        }

        await router.push('/home');
    } catch (error) {
        if (axios.isAxiosError(error)) {
            errorMsg.value =
                error.response?.data?.error ||
                Object.values(error.response?.data?.errors || {}).flat()?.[0] as string ||
                'Credenciales inválidas';

            return;
        }

        errorMsg.value = 'Credenciales inválidas';
    } finally {
        isLoading.value = false;
    }
};
</script>

<style>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 50% 90%, #103f61 0%, #072a43 45%, #032137 100%);
    padding: 24px;
}

.login-card {
    width: 100%;
    max-width: 420px;
    background: #f6f7f9;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
}

.card-header {
    padding: 30px 32px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #eceff3;
}

.brand-logo {
    width: 190px;
    height: auto;
    object-fit: contain;
}

.brand-caption {
    margin-top: 6px;
    font-size: 0.88rem;
    color: #6e7783;
}

.brand-title {
    margin-top: 8px;
    margin-bottom: 0;
    font-size: 2rem;
    line-height: 1;
    letter-spacing: 0.18em;
    color: #132941;
    font-weight: 700;
}

.card-form {
    display: flex;
    flex-direction: column;
    padding: 24px 24px 28px;
    gap: 10px;
}

.input-label {
    font-size: 0.9rem;
    color: #28344a;
    margin-top: 2px;
}

.login-input {
    height: 44px;
    border: 1px solid #cdd6e0;
    border-radius: 8px;
    padding: 0 14px;
    font-size: 0.95rem;
    color: #1a2b40;
    background: #ffffff;
    outline: none;
}

.login-input:focus {
    border-color: #2ea8de;
    box-shadow: 0 0 0 3px rgba(46, 168, 222, 0.15);
}

.login-button {
    margin-top: 8px;
    width: 100%;
    height: 44px;
    border-radius: 8px;
    font-size: 0.98rem;
}

.login-error {
    margin-top: 4px;
    color: #b42318;
    font-size: 0.84rem;
}

.spinner-inline {
    width: 18px;
    height: 18px;
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
