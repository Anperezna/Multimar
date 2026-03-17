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
                                        {{ u.rol.charAt(0).toUpperCase() + u.rol.slice(1) }}
                                    </span>
                                </td>
                                <td class="acciones-cell">
                                    <button class="btn-accion" title="Editar">✎</button>
                                    <button class="btn-accion btn-accion--danger" title="Eliminar">🗑</button>
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
                                <option value="cliente">Cliente</option>
                                <option value="operador">Operador</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="crear-actions">
                            <button type="button" class="btn-cancelar" @click="cancelar">Cancelar</button>
                            <Botones type="submit">Crear</Botones>
                        </div>
                    </form>
                </aside>

            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import Navbar from '@/components/Navbar.vue';
import Botones from '@/components/Botones.vue';
import Input from '@/components/Input.vue';

// TODO: Reemplazar por el estado real de autenticacion cuando se conecte la BD.
// Debe coincidir con el valor de usuarioRol en Navbar.vue.
const usuarioRol = ref('operador');

const mostrarFormulario = ref(false);

const form = reactive({
    nombre: '',
    apellidos: '',
    dni: '',
    pais: '',
    correo: '',
    password: '',
    // Operador siempre crea clientes; admin puede elegir antes de enviar
    rol: usuarioRol.value === 'admin' ? 'cliente' : 'cliente',
});

// TODO: Reemplazar por usuarios reales llamando a la API.
const usuarios = ref([
    { id: 1, nombre: 'Admin',    apellidos: 'User',         correo: 'admin@simex.com',    pais: 'Espana',  rol: 'admin' },
    { id: 2, nombre: 'Operador', apellidos: 'Logistico',    correo: 'operador@simex.com', pais: 'Espana',  rol: 'operador' },
    { id: 3, nombre: 'Cliente',  apellidos: 'Regular',      correo: 'usuario@simex.com',  pais: 'Mexico',  rol: 'cliente' },
    { id: 4, nombre: 'Maria',    apellidos: 'Garcia Lopez', correo: 'maria@simex.com',    pais: 'Espana',  rol: 'cliente' },
    { id: 5, nombre: 'Pedro',    apellidos: 'Sanchez Ruiz', correo: 'pedro@simex.com',    pais: 'Colombia',rol: 'operador' },
]);

function crearUsuario() {
    const nuevoRol = usuarioRol.value === 'operador' ? 'cliente' : form.rol;
    usuarios.value.push({
        id: Date.now(),
        nombre:    form.nombre,
        apellidos: form.apellidos,
        correo:    form.correo,
        pais:      form.pais,
        rol:       nuevoRol,
    });
    cancelar();
}

function cancelar() {
    mostrarFormulario.value = false;
    Object.assign(form, { nombre: '', apellidos: '', dni: '', pais: '', correo: '', password: '', rol: 'cliente' });
}
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
    gap: 4px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
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
