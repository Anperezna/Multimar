<template>
    <div class="gestion-page">
        <Navbar />

        <main class="gestion-layout">
            <header class="gestion-header">
                <h1>Gestion de Usuarios</h1>
                <p>Administracion del sistema y control de accesos.</p>
            </header>

            <div class="gestion-content">

                <!-- Listado -->
                <section class="listado-panel">
                    <div class="listado-top">
                        <h2>Listado de Usuarios</h2>
                        <Botones @click="mostrarFormulario = true">
                            <img
                                src="/icons_multimar/icons-simex/daw/crearuser.svg"
                                alt=""
                                aria-hidden="true"
                                class="crear-icon"
                            />
                            Nuevo Usuario
                        </Botones>
                    </div>

                    <table class="listado-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Pais</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in usuarios" :key="u.id">
                                <td>{{ u.nombre }} &nbsp; {{ u.apellidos }}</td>
                                <td>{{ u.correo }}</td>
                                <td>{{ u.pais }}</td>
                                <td>
                                    <span class="rol-badge" :class="`rol-${u.rol}`">
                                        <img
                                            src="/icons_multimar/icons-simex/daw/escudo.svg"
                                            alt=""
                                            aria-hidden="true"
                                            class="rol-icon"
                                        />
                                        {{ u.rol.charAt(0).toUpperCase() + u.rol.slice(1) }}
                                    </span>
                                </td>
                                <td class="acciones-cell">
                                    <button class="btn-accion" title="Editar">✎</button>
                                    <button class="btn-accion btn-accion--danger" title="Eliminar" @click="eliminarUsuario(u.id)">🗑</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Formulario Crear Usuario -->
                <aside v-if="mostrarFormulario" class="crear-panel">
                    <h2>Crear Usuario</h2>

                    <form class="crear-form" @submit.prevent="crearUsuario">
                        <div class="field">
                            <label for="cu-nombre">Nombre</label>
                            <Input id="cu-nombre" v-model="form.nombre" name="nombre" inputClass="cu-input" />
                        </div>

                        <div class="field">
                            <label for="cu-apellidos">Apellidos</label>
                            <Input id="cu-apellidos" v-model="form.apellidos" name="apellidos" inputClass="cu-input" />
                        </div>

                        <div class="field">
                            <label for="cu-dni">DNI</label>
                            <Input id="cu-dni" v-model="form.dni" name="dni" inputClass="cu-input" />
                        </div>

                        <div class="field">
                            <label for="cu-pais">Pais</label>
                            <Input id="cu-pais" v-model="form.pais" name="pais" inputClass="cu-input" />
                        </div>

                        <div class="field">
                            <label for="cu-correo">Correo</label>
                            <Input id="cu-correo" v-model="form.correo" type="email" name="correo" inputClass="cu-input" />
                        </div>

                        <div class="field">
                            <label for="cu-password">Contrasena</label>
                            <Input id="cu-password" v-model="form.password" type="password" placeholder="Contrasena" inputClass="cu-input" />
                        </div>

                        <div class="field">
                            <label for="cu-rol">Rol</label>

                            <!-- Operador: solo puede crear clientes, campo bloqueado -->
                            <v-if v-if="false" />
                            <Input
                                v-if="usuarioRol === 'operador'"
                                id="cu-rol"
                                :modelValue="'Cliente'"
                                :disabled="true"
                                inputClass="cu-input cu-input--disabled"
                            />
                            <small v-if="usuarioRol === 'operador'" class="field-hint">
                                Los operadores solo pueden crear usuarios con rol Cliente.
                            </small>

                            <!-- Admin: puede elegir cualquier rol -->
                            <select
                                v-if="usuarioRol === 'admin'"
                                id="cu-rol"
                                v-model="form.rol"
                                class="cu-input cu-select"
                            >
                                <option value="4">Cliente</option>
                                <option value="1">Admin</option>
                                <option value="2">Operador</option>
                                <option value="3">Agente Comercial</option>
                            </select>
                        </div>

                        <div class="crear-actions">
                            <button type="button" class="btn-cancelar" @click="cancelar">Cancelar</button>
                            <Botones type="submit">Crear</Botones>
                        </div>

                        <small v-if="errorCrear" class="field-hint">{{ errorCrear }}</small>
                    </form>
                </aside>

            </div>
        </main>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import Navbar from '@/components/Navbar.vue';
import Botones from '@/components/Botones.vue';
import Input from '@/components/Input.vue';
import api from '@/lib/api';

// TODO: Reemplazar por el estado real de autenticacion cuando se conecte la BD.
// Debe coincidir con el valor de usuarioRol en Navbar.vue.
const usuarioRol = ref('admin');
const usuarioCreadorId = ref(3);

const mostrarFormulario = ref(false);
const errorCrear = ref('');

const form = reactive({
    nombre: '',
    apellidos: '',
    dni: '',
    pais: '',
    correo: '',
    password: '',
    rol: usuarioRol.value === 'admin' ? '4' : '4',
});

const usuarios = ref([]);

async function cargarUsuarios() {
    try {
        const { data } = await api.get('/usuaris', {
            params: { creador_id: usuarioCreadorId.value },
        });

        usuarios.value = (data || []).map((u) => ({
            id: u.id,
            nombre: u.nombre,
            apellidos: u.apellidos,
            correo: u.correo,
            pais: u.pais || '',
            rol: String(u.rol || '').toLowerCase(),
        }));
    } catch (error) {
        errorCrear.value =
            error?.response?.data?.message ||
            error?.response?.data?.error ||
            Object.values(error?.response?.data?.errors || {}).flat()?.[0] ||
            error?.message ||
            'No se pudo cargar el listado de usuarios';
    }
}

async function crearUsuario() {
    errorCrear.value = '';

    const nuevoRol = usuarioRol.value === 'operador' ? '4' : form.rol;

    try {
        const { data } = await api.post('/usuaris', {
            creador_id: usuarioCreadorId.value,
            nombre: form.nombre,
            apellidos: form.apellidos,
            dni: form.dni,
            pais: form.pais,
            correo: form.correo,
            password: form.password,
            rol: nuevoRol,
        });

        await cargarUsuarios();

        cancelar();
    } catch (error) {
        errorCrear.value =
            error?.response?.data?.message ||
            error?.response?.data?.error ||
            Object.values(error?.response?.data?.errors || {}).flat()?.[0] ||
            error?.message ||
            'No se pudo crear el usuario';
    }
}

async function eliminarUsuario(id) {
    errorCrear.value = '';

    const previousUsuarios = [...usuarios.value];
    usuarios.value = usuarios.value.filter((u) => u.id !== id);

    try {
        await api.delete(`/usuaris/${id}`);
    } catch (error) {
        usuarios.value = previousUsuarios;
        errorCrear.value =
            error?.response?.data?.message ||
            error?.response?.data?.error ||
            Object.values(error?.response?.data?.errors || {}).flat()?.[0] ||
            error?.message ||
            'No se pudo eliminar el usuario';
    }
}

function cancelar() {
    mostrarFormulario.value = false;
    Object.assign(form, { nombre: '', apellidos: '', dni: '', pais: '', correo: '', password: '', rol: '4' });
}

onMounted(() => {
    cargarUsuarios();
});
</script>

<style scoped>
.gestion-page {
    min-height: 100vh;
    background: #e9eef5;
}

.gestion-layout {
    margin: 0 auto;
    width: min(1200px, 100%);
    padding: 28px 24px;
}

.gestion-header h1 {
    margin: 0;
    font-size: 1.8rem;
    color: #192a46;
}

.gestion-header p {
    margin: 6px 0 20px;
    color: #6d7f96;
    font-size: 0.92rem;
}

.gestion-content {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    align-items: start;
}

/* Listado */
.listado-panel {
    background: #fff;
    border: 1px solid #d9e3ef;
    border-radius: 10px;
    overflow: hidden;
}

.listado-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 18px;
    border-bottom: 1px solid #e8eef6;
}

.listado-top h2 {
    margin: 0;
    font-size: 1rem;
    color: #1d2d46;
}

.listado-table {
    width: 100%;
    border-collapse: collapse;
}

.listado-table thead th {
    padding: 10px 14px;
    text-align: left;
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748b;
    background: #f3f6fa;
    border-bottom: 1px solid #e8eef6;
}

.listado-table tbody td {
    padding: 13px 14px;
    font-size: 0.85rem;
    color: #334a67;
    border-bottom: 1px solid #edf1f7;
}

.listado-table tbody tr:last-child td {
    border-bottom: 0;
}

.rol-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.78rem;
    line-height: 1;
    font-weight: 600;
}

.rol-icon {
    width: 12px;
    height: 12px;
    display: block;
    object-fit: contain;
}

.rol-admin .rol-icon {
    filter: invert(17%) sepia(95%) saturate(2727%) hue-rotate(345deg) brightness(88%) contrast(91%);
}

.rol-operador .rol-icon {
    filter: invert(30%) sepia(96%) saturate(1246%) hue-rotate(212deg) brightness(90%) contrast(92%);
}

.rol-cliente .rol-icon {
    filter: invert(26%) sepia(43%) saturate(1648%) hue-rotate(131deg) brightness(89%) contrast(95%);
}

.rol-admin    { background: #fee2e2; color: #b91c1c; }
.rol-operador { background: #dbeafe; color: #1d4ed8; }
.rol-cliente  { background: #d1fae5; color: #065f46; }

.acciones-cell { display: flex; gap: 8px; align-items: center; }

.btn-accion {
    background: none;
    border: 1px solid #d9e3ef;
    border-radius: 6px;
    padding: 4px 8px;
    cursor: pointer;
    color: #5a7494;
    font-size: 0.9rem;
}

.btn-accion--danger { color: #dc2626; }

/* Crear panel */
.crear-panel {
    background: #fff;
    border: 1px solid #d9e3ef;
    border-radius: 10px;
    padding: 20px;
}

.crear-panel h2 {
    margin: 0 0 16px;
    font-size: 1rem;
    color: #1d2d46;
}

.crear-form {
    display: grid;
    gap: 12px;
}

.field {
    display: grid;
    gap: 5px;
}

.field label {
    font-size: 0.88rem;
    font-weight: 600;
    color: #334760;
}

.field-hint {
    font-size: 0.78rem;
    color: #6d7f96;
}

.cu-input {
    width: 100%;
    height: 40px;
    border: 1px solid #cfdbe8;
    border-radius: 8px;
    padding: 0 12px;
    font-size: 0.88rem;
    color: #1d2d46;
    background: #fff;
}

.cu-input:focus {
    outline: none;
    border-color: #97bfdc;
}

.cu-input--disabled {
    background: #f3f6fa;
    color: #7a90a8;
    cursor: not-allowed;
}

.cu-select {
    appearance: none;
}

.crear-actions {
    display: flex;
    gap: 10px;
    margin-top: 4px;
}

.btn-cancelar {
    flex: 1;
    height: 40px;
    border: 1px solid #cfdbe8;
    border-radius: 8px;
    background: #fff;
    font-size: 0.88rem;
    color: #334760;
    cursor: pointer;
}

.btn-cancelar:hover {
    background: #f3f6fa;
}

.crear-icon {
    width: 14px;
    height: 14px;
    display: inline-block;
    object-fit: contain;
    margin-right: 6px;
    position: relative;
    top: 1px;
    filter: brightness(0) invert(1);
}

.crear-actions :deep(.btn-login) {
    flex: 1;
    height: 40px;
    border-radius: 8px;
    font-size: 0.88rem;
}

@media (max-width: 900px) {
    .gestion-content {
        grid-template-columns: 1fr;
    }
}
</style>
