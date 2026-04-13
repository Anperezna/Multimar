<template>
    <SettingsLayout current-tab="perfil">
        <section class="panel-body">
            <h2>Editar Perfil</h2>
            <p>Actualiza tu informacion personal.</p>

            <div v-if="isLoading" class="feedback info loading-row" aria-label="Cargando perfil">
                <span class="spinner"></span>
            </div>
            <p v-if="saveSuccess" class="feedback success">Perfil actualizado correctamente.</p>
            <p v-if="saveError" class="feedback error">{{ saveError }}</p>

            <form class="panel-form" @submit.prevent="saveProfile">
                <ProfileAvatarUploader
                    :nombre="profile.nombre"
                    :apellidos="profile.apellidos"
                    :correo="profile.correo"
                    :modelValue="profile.foto_user"
                    @update:modelValue="onAvatarUpdated"
                    @error="onAvatarError"
                />

                <div class="grid-two">
                    <div class="field">
                        <label for="nombre">Nombre</label>
                        <Input
                            id="nombre"
                            v-model="profile.nombre"
                            name="nombre"
                            inputClass="settings-input"
                        />
                    </div>

                    <div class="field">
                        <label for="apellidos">Apellidos</label>
                        <Input
                            id="apellidos"
                            v-model="profile.apellidos"
                            name="apellidos"
                            inputClass="settings-input"
                        />
                    </div>

                    <div class="field">
                        <label for="empresa">Empresa</label>
                        <Input
                            id="empresa"
                            v-model="profile.empresa"
                            name="empresa"
                            placeholder="Nombre de la empresa"
                            inputClass="settings-input"
                        />
                    </div>

                    <div class="field">
                        <label for="pais">Pais</label>
                        <select id="pais" v-model="profile.pais" class="settings-input settings-select">
                            <option disabled value="">Seleccionar pais...</option>
                            <option v-for="country in countryOptions" :key="country">{{ country }}</option>
                        </select>
                    </div>

                    <div class="field field--full">
                        <label for="correo">Correo electronico</label>
                        <Input
                            id="correo"
                            v-model="profile.correo"
                            type="email"
                            name="correo"
                            inputClass="settings-input"
                        />
                    </div>

                </div>

                <div class="actions-end">
                    <Botones :disabled="isLoading || isSaving">
                        <span v-if="!isSaving">Guardar Cambios</span>
                        <span v-else class="spinner spinner--small" aria-label="Guardando"></span>
                    </Botones>
                </div>
            </form>
        </section>
    </SettingsLayout>
</template>

<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, reactive, ref } from 'vue';
import Botones from '@/components/Botones.vue';
import Input from '@/components/Input.vue';
import ProfileAvatarUploader from '@/components/ProfileAvatarUploader.vue';
import SettingsLayout from '@/components/settings/SettingsLayout.vue';
import api from '@/lib/api';

type ProfileResponse = {
    nombre?: string;
    apellidos?: string;
    empresa?: string | null;
    pais?: string | null;
    correo?: string;
    foto_user?: string | null;
};

const profile = reactive({
    nombre: '',
    apellidos: '',
    empresa: '',
    pais: '',
    correo: '',
    foto_user: '',
});

const isLoading = ref(false);
const isSaving = ref(false);
const saveError = ref('');
const saveSuccess = ref(false);

const defaultCountries = ['Espana', 'Portugal', 'Francia'];

const countryOptions = computed(() => {
    const set = new Set(defaultCountries);
    if (profile.pais) {
        set.add(profile.pais);
    }
    return Array.from(set);
});

const applyProfile = (data: ProfileResponse) => {
    profile.nombre = data.nombre || '';
    profile.apellidos = data.apellidos || '';
    profile.empresa = data.empresa || '';
    profile.pais = data.pais || '';
    profile.correo = data.correo || '';
    profile.foto_user = data.foto_user || '';
};

const onAvatarUpdated = (value: string) => {
    profile.foto_user = value;
    saveError.value = '';
    saveSuccess.value = false;
};

const onAvatarError = (message: string) => {
    saveError.value = message;
};

const fetchProfile = async () => {
    isLoading.value = true;
    saveError.value = '';

    try {
        const { data } = await api.get('/user');
        applyProfile(data);
    } catch {
        saveError.value = 'No se pudo cargar la informacion del usuario.';
    } finally {
        isLoading.value = false;
    }
};

const saveProfile = async () => {
    isSaving.value = true;
    saveError.value = '';
    saveSuccess.value = false;

    try {
        const { data } = await api.post('/user', {
            nombre: profile.nombre,
            apellidos: profile.apellidos,
            empresa: profile.empresa,
            pais: profile.pais,
            correo: profile.correo,
            foto_user: profile.foto_user,
        });

        applyProfile(data);
        localStorage.setItem('user_name', [data?.nombre, data?.apellidos].filter(Boolean).join(' ').trim());
        saveSuccess.value = true;
    } catch (error) {
        if (axios.isAxiosError(error)) {
            saveError.value =
                (error.response?.data?.message as string) ||
                (Object.values(error.response?.data?.errors || {}).flat()?.[0] as string) ||
                'No se pudo guardar el perfil.';
        } else {
            saveError.value = 'No se pudo guardar el perfil.';
        }
    } finally {
        isSaving.value = false;
    }
};

onMounted(fetchProfile);
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

.feedback.info {
    color: #334760;
}

.feedback.success {
    color: #0f9f71;
}

.feedback.error {
    color: #b42318;
}

.loading-row {
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

.spinner {
    width: 22px;
    height: 22px;
    border: 3px solid rgba(29, 45, 70, 0.2);
    border-top-color: #1d2d46;
    border-radius: 50%;
    display: inline-block;
    animation: spin 0.8s linear infinite;
}

.spinner--small {
    width: 16px;
    height: 16px;
    border-width: 2px;
    border-color: rgba(255, 255, 255, 0.35);
    border-top-color: #ffffff;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.panel-form {
    display: grid;
    gap: 16px;
}

.grid-two {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px 16px;
}

.field {
    display: grid;
    gap: 6px;
}

.field--full {
    grid-column: span 2;
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

.settings-select {
    appearance: none;
}

.actions-end {
    display: flex;
    justify-content: flex-end;
}

.btn-login {
    min-width: 170px;
    height: 42px;
    border-radius: 8px;
}

@media (max-width: 880px) {
    .grid-two {
        grid-template-columns: 1fr;
    }

    .field--full {
        grid-column: auto;
    }
}
</style>
