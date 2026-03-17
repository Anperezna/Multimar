<template>
    <SettingsLayout current-tab="perfil">
        <section class="panel-body">
            <h2>Editar Perfil</h2>
            <p>Actualiza tu informacion personal.</p>

            <form class="panel-form" @submit.prevent>
                <div class="avatar-row">
                    <div class="avatar">CR</div>
                    <div>
                        <strong>Cliente Regular</strong>
                        <small>usuario@simex.com</small>
                    </div>
                </div>

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
                            <option>Espana</option>
                            <option>Portugal</option>
                            <option>Francia</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="correo">Correo electronico</label>
                        <Input
                            id="correo"
                            v-model="profile.correo"
                            type="email"
                            name="correo"
                            inputClass="settings-input"
                        />
                    </div>

                    <div class="field">
                        <label for="telefono">Telefono</label>
                        <Input
                            id="telefono"
                            v-model="profile.telefono"
                            name="telefono"
                            placeholder="+34 600 000 000"
                            inputClass="settings-input"
                        />
                    </div>
                </div>

                <div class="actions-end">
                    <Botones>Guardar Cambios</Botones>
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

const profile = reactive({
    nombre: 'Cliente',
    apellidos: 'Regular',
    empresa: '',
    pais: '',
    correo: 'usuario@simex.com',
    telefono: '',
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
    display: grid;
    gap: 16px;
}

.avatar-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 2px;
}

.avatar {
    width: 62px;
    height: 62px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 1rem;
    font-weight: 700;
    background: #e4ebf5;
    color: #3b4e66;
}

.avatar-row small {
    display: block;
    color: #6d7f96;
    margin-top: 3px;
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

.settings-select {
    appearance: none;
}

.actions-end {
    display: flex;
    justify-content: flex-end;
}

:deep(.btn-login) {
    min-width: 170px;
    height: 42px;
    border-radius: 8px;
}

@media (max-width: 880px) {
    .grid-two {
        grid-template-columns: 1fr;
    }
}
</style>
