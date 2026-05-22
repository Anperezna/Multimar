<template>
    <div class="pagina-edicion">
        <Navbar />

        <main class="contenedor">
            <!-- SECCIÓN: LISTA DE INCOTERMS -->
            <section class="seccion-listado">
                <div class="encabezado-listado">
                    <div>
                        <h1>Gestión de Incoterms</h1>
                        <p>Selecciona un incoterm para editar sus pasos de seguimiento.</p>
                    </div>

                    <button class="btn btn-crear-incoterm" @click="abrirFormularioNuevoIncoterm">
                        + Añadir Incoterm
                    </button>
                </div>

                <div v-if="cargando" class="estado-carga">
                    <span class="spinner"></span>
                    <p>Cargando incoterms...</p>
                </div>

                <div v-else-if="incoterms.length === 0" class="estado-vacio">
                    <p>No hay incoterms en la base de datos.</p>
                </div>

                <div v-else class="grid-tarjetas">
                    <article v-for="incoterm in incoterms" :key="incoterm.id" class="tarjeta-incoterm">
                        <div class="cabecera-tarjeta">
                            <p class="numero">Incoterm #{{ incoterm.id }}</p>
                            <h3>{{ obtenerLabelIncoterm(incoterm) }}</h3>
                        </div>
                        <div class="acciones-tarjeta">
                            <button class="btn btn-editar-incoterm" @click="abrirFormularioEditarIncoterm(incoterm)">
                                Editar incoterm
                            </button>
                            <button class="btn btn-editar" @click="seleccionarIncoterm(incoterm)">
                                Editar pasos
                            </button>
                            <button class="btn btn-eliminar" @click="eliminarIncoterm(incoterm.id)">
                                Eliminar
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <!-- SECCIÓN: EDITOR DE PASOS -->
            <section v-if="incotermSeleccionado" class="seccion-editor">
                <div class="cabecera-editor">
                    <h2>{{ obtenerLabelIncoterm(incotermSeleccionado) }}</h2>
                    <button class="btn btn-cerrar" @click="cerrarEditor">✕</button>
                </div>

                <div class="contenedor-pasos">
                    <h3>Pasos de seguimiento</h3>

                    <div v-if="pasosDelIncoterm.length === 0" class="sin-pasos">
                        <p>Este incoterm no tiene pasos asociados.</p>
                    </div>

                    <div v-else class="lista-pasos">
                        <div v-for="paso in pasosDelIncoterm" :key="paso.id" class="fila-paso">
                            <div class="datos-paso">
                                <span class="numero-paso">{{ paso.ordre }}</span>
                                <span class="nombre-paso">{{ paso.nom }}</span>
                            </div>
                            <label class="contenedor-checkbox">
                                <input type="checkbox" v-model="paso.activo" />
                                <span class="texto-checkbox">{{ paso.activo ? 'Activo' : 'Inactivo' }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="agregar-paso">
                        <div v-if="mostrarFormularioPaso" class="formulario-paso">
                            <input
                                v-model="nuevoPaso.nom"
                                type="text"
                                placeholder="Nombre del paso"
                                class="entrada-paso"
                            />
                            <button class="btn btn-pequeño btn-agregar" @click="crearPaso">
                                Agregar
                            </button>
                            <button class="btn btn-pequeño btn-cancelar" @click="mostrarFormularioPaso = false">
                                Cancelar
                            </button>
                        </div>
                        <button v-else class="btn btn-pequeño btn-nuevo-paso" @click="mostrarFormularioPaso = true">
                            + Nuevo paso
                        </button>
                    </div>

                    <div class="acciones-editor">
                        <button class="btn btn-guardar" @click="guardarCambios" :disabled="guardando">
                            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                        <button class="btn btn-cancelar" @click="cerrarEditor">
                            Cancelar
                        </button>
                    </div>

                    <p v-if="mensaje" class="mensaje" :class="{ error: esError }">
                        {{ mensaje }}
                    </p>
                </div>
            </section>
        </main>

        <div v-if="mostrarFormularioNuevoIncoterm" class="modal-overlay">
            <div class="modal-incoterm">
                <div class="modal-header">
                    <h2>Añadir Incoterm</h2>
                    <button class="btn btn-cerrar" @click="cerrarFormularioNuevoIncoterm">✕</button>
                </div>

                <form @submit.prevent="crearIncoterm">
                    <FormularioNombreIncoterm
                        :codigoActual="nuevoIncoterm.codi"
                        :nombreActual="nuevoIncoterm.nom"
                        @actualizar-codigo="nuevoIncoterm.codi = $event"
                        @actualizar-nombre="nuevoIncoterm.nom = $event"
                    />

                    <div class="modal-actions">
                        <button type="button" class="btn btn-cancelar-modal" @click="cerrarFormularioNuevoIncoterm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-guardar" :disabled="creandoIncoterm">
                            {{ creandoIncoterm ? 'Creando...' : 'Crear incoterm' }}
                        </button>
                    </div>

                    <p v-if="mensaje" class="mensaje" :class="{ error: esError }">
                        {{ mensaje }}
                    </p>
                </form>
            </div>
        </div>

        <div v-if="mostrarFormularioEditarIncoterm" class="modal-overlay">
            <div class="modal-incoterm">
                <div class="modal-header">
                    <h2>Editar Incoterm</h2>
                    <button class="btn btn-cerrar" @click="cerrarFormularioEditarIncoterm">âœ•</button>
                </div>

                <form @submit.prevent="editarIncoterm">
                    <FormularioNombreIncoterm
                        :codigoActual="incotermEditando.codi"
                        :nombreActual="incotermEditando.nom"
                        @actualizar-codigo="incotermEditando.codi = $event"
                        @actualizar-nombre="incotermEditando.nom = $event"
                    />

                    <div class="modal-actions">
                        <button type="button" class="btn btn-cancelar-modal" @click="cerrarFormularioEditarIncoterm">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-guardar" :disabled="guardandoEdicionIncoterm">
                            {{ guardandoEdicionIncoterm ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import Navbar from '@/components/Navbar.vue';
import FormularioNombreIncoterm from '@/components/FormularioNombreIncoterm.vue';
import api from '@/lib/api';

// ESTADO
const incoterms = ref([]);
const tiposIncoterms = ref([]);
const pasosDelIncoterm = ref([]);
const incotermSeleccionado = ref(null);
const cargando = ref(false);
const guardando = ref(false);
const creandoIncoterm = ref(false);
const mensaje = ref('');
const esError = ref(false);
const mostrarFormularioPaso = ref(false);
const mostrarFormularioNuevoIncoterm = ref(false);
const mostrarFormularioEditarIncoterm = ref(false);
const guardandoEdicionIncoterm = ref(false);
const nuevoPaso = ref({
    nom: ''
});
const nuevoIncoterm = ref({
    nom: '',
    codi: ''
});
const incotermEditando = ref({
    id: null,
    nom: '',
    codi: ''
});

function obtenerLabelIncoterm(incoterm) {
    const tipoId =
        incoterm?.tipus_inconterm_id ??
        incoterm?.tipusIncoterm?.id ??
        incoterm?.tipus?.id;

    const tipus =
        incoterm?.tipusIncoterm ||
        incoterm?.tipus ||
        tiposIncoterms.value.find(item => item.id == tipoId);

    if (!tipus) {
        return `Incoterm ${incoterm?.id ?? ''}`.trim();
    }

    return `${tipus.codi ?? ''} - ${tipus.nom ?? ''}`.trim().replace(/^\s*-\s*|\s*-\s*$/g, '');
}

// CARGAR TIPOS DE INCOTERM
async function cargarTiposIncoterm() {
    try {
        const respuesta = await api.get('/tipos-incoterm');
        tiposIncoterms.value = respuesta.data || [];
    } catch (error) {
        console.error('No se pudieron cargar los tipos de incoterm:', error);
        tiposIncoterms.value = [];
    }
}

// CARGAR INCOTERMS
async function cargarIncoterms() {
    cargando.value = true;
    try {
        const respuesta = await api.get('/incoterms');
        incoterms.value = respuesta.data || [];
        console.log('Incoterms cargados:', incoterms.value);
    } catch (error) {
        mostrarMensaje('No se pudieron cargar los incoterms.', true);
        console.error(error);
    } finally {
        cargando.value = false;
    }
}

// CARGAR PASOS DE UN INCOTERM
async function cargarPasosDelIncoterm(incotermId) {
    try {
        const respuesta = await api.get('/tracking-steps', {
            params: { incoterm_id: incotermId }
        });
        console.log('Respuesta completa del API:', respuesta);
        console.log('Data del API:', respuesta.data);
        
        // Normalizar `activo` a booleano para que `v-model` en el checkbox funcione
        pasosDelIncoterm.value = Array.isArray(respuesta.data)
            ? respuesta.data.map(p => ({
                  ...p,
                  activo: p.activo === true || p.activo === 1 || p.activo === '1' || p.activo === 'true'
              }))
            : [];
        console.log('Pasos del incoterm asignados:', pasosDelIncoterm.value);
    } catch (error) {
        console.error('Error al cargar pasos:', error);
        console.error('Error response:', error.response?.data);
        console.error('Error status:', error.response?.status);
        console.error('Error message:', error.message);
        mostrarMensaje('No se pudieron cargar los pasos.', true);
    }
}

// SELECCIONAR INCOTERM
async function seleccionarIncoterm(incoterm) {
    incotermSeleccionado.value = incoterm;
    mensaje.value = '';
    await cargarPasosDelIncoterm(incoterm.id);
}

// CERRAR EDITOR
function cerrarEditor() {
    incotermSeleccionado.value = null;
    pasosDelIncoterm.value = [];
    mensaje.value = '';
}

// GUARDAR CAMBIOS
async function guardarCambios() {
    if (!incotermSeleccionado.value || pasosDelIncoterm.value.length === 0) {
        mostrarMensaje('No hay pasos para guardar.', true);
        return;
    }

    guardando.value = true;
    mensaje.value = '';

    const datosParaGuardar = pasosDelIncoterm.value.map(paso => ({
        id: paso.id,
        activo: paso.activo
    }));

    console.log('Datos a guardar:', datosParaGuardar);
    console.log('Pasos del incoterm actual:', pasosDelIncoterm.value);

    try {
        const respuesta = await api.post('/tracking-steps/actualizar-estados', {
            pasos: datosParaGuardar
        });

        console.log('Respuesta del servidor:', respuesta);
        mostrarMensaje('Pasos actualizados correctamente.', false);
        
        setTimeout(() => {
            cerrarEditor();
            cargarIncoterms();
        }, 1500);
    } catch (error) {
        console.error('Error al guardar:', error);
        console.error('Response data:', error.response?.data);
        mostrarMensaje(
            error?.response?.data?.message || 'No se pudieron guardar los cambios.',
            true
        );
    } finally {
        guardando.value = false;
    }
}

// ELIMINAR INCOTERM
async function eliminarIncoterm(id) {
    const confirmar = window.confirm('¿Estás seguro que quieres eliminar este incoterm?');
    if (!confirmar) return;

    try {
        await api.delete(`/incoterms/${id}`);
        mostrarMensaje('Incoterm eliminado correctamente.', false);
        await cargarIncoterms();
    } catch (error) {
        const mensaje = error?.response?.data?.message || 'No se pudo eliminar el incoterm.';
        mostrarMensaje(mensaje, true);
        console.error('Error al eliminar incoterm:', error);
    }
}

// MOSTRAR MENSAJE
function mostrarMensaje(texto, esErrorMensaje = false) {
    mensaje.value = texto;
    esError.value = esErrorMensaje;
}

function abrirFormularioNuevoIncoterm() {
    mensaje.value = '';
    esError.value = false;
    nuevoIncoterm.value = { nom: '', codi: '' };
    mostrarFormularioNuevoIncoterm.value = true;
}

function cerrarFormularioNuevoIncoterm() {
    mostrarFormularioNuevoIncoterm.value = false;
    nuevoIncoterm.value = { nom: '', codi: '' };
}

async function abrirFormularioEditarIncoterm(incoterm) {
    const tipoId =
        incoterm?.tipus_inconterm_id ??
        incoterm?.tipusIncoterm?.id ??
        incoterm?.tipus?.id;
    const tipusLocal =
        incoterm?.tipusIncoterm ||
        incoterm?.tipus ||
        tiposIncoterms.value.find(item => item.id == tipoId) ||
        {};

    incotermEditando.value = {
        id: incoterm.id,
        nom: tipusLocal.nom || '',
        codi: tipusLocal.codi || '',
    };
    mostrarFormularioEditarIncoterm.value = true;

    try {
        const { data } = await api.get(`/incoterms/${incoterm.id}`);
        const tipus = data?.tipusIncoterm || data?.tipus || {};

        incotermEditando.value = {
            id: incoterm.id,
            nom: tipus.nom || incotermEditando.value.nom || '',
            codi: tipus.codi || incotermEditando.value.codi || '',
        };
    } catch (error) {
        console.error('No se pudieron refrescar los datos del incoterm a editar:', error);
    }
}

function cerrarFormularioEditarIncoterm() {
    mostrarFormularioEditarIncoterm.value = false;
    incotermEditando.value = { id: null, nom: '', codi: '' };
}

async function crearIncoterm() {
    if (!nuevoIncoterm.value.codi.trim() || !nuevoIncoterm.value.nom.trim()) {
        mostrarMensaje('El código y el nombre son obligatorios.', true);
        return;
    }

    creandoIncoterm.value = true;
    mensaje.value = '';

    try {
        const respuesta = await api.post('/incoterms', {
            codi: nuevoIncoterm.value.codi.trim(),
            nom: nuevoIncoterm.value.nom.trim(),
            tracking_steps_id: null,
        });

        await cargarTiposIncoterm();
        await cargarIncoterms();

        const incotermCreado = incoterms.value.find(item => item.id === respuesta.data?.id) || respuesta.data;
        cerrarFormularioNuevoIncoterm();

        if (incotermCreado) {
            await seleccionarIncoterm(incotermCreado);
        }

        mostrarMensaje('Incoterm creado correctamente.', false);
    } catch (error) {
        mostrarMensaje(
            error?.response?.data?.message || error?.response?.data || 'No se pudo crear el incoterm.',
            true
        );
        console.error(error);
    } finally {
        creandoIncoterm.value = false;
    }
}

async function editarIncoterm() {
    if (!incotermEditando.value.codi.trim() || !incotermEditando.value.nom.trim()) {
        mostrarMensaje('El cÃ³digo y el nombre son obligatorios.', true);
        return;
    }

    guardandoEdicionIncoterm.value = true;

    try {
        await api.put(`/incoterms/${incotermEditando.value.id}`, {
            codi: incotermEditando.value.codi.trim(),
            nom: incotermEditando.value.nom.trim(),
        });

        await cargarTiposIncoterm();
        await cargarIncoterms();
        cerrarFormularioEditarIncoterm();
        mostrarMensaje('Incoterm actualizado correctamente.', false);
    } catch (error) {
        mostrarMensaje(
            error?.response?.data?.message || error?.response?.data || 'No se pudo editar el incoterm.',
            true
        );
        console.error(error);
    } finally {
        guardandoEdicionIncoterm.value = false;
    }
}

// CREAR NUEVO PASO
async function crearPaso() {
    if (!nuevoPaso.value.nom.trim()) {
        mostrarMensaje('El nombre del paso es obligatorio.', true);
        return;
    }

    if (!incotermSeleccionado.value) {
        mostrarMensaje('No hay incoterm seleccionado.', true);
        return;
    }

    guardando.value = true;

    try {
        const maxOrden = Math.max(...pasosDelIncoterm.value.map(p => p.ordre), 0);

        const respuesta = await api.post('/tracking-steps', {
            nom: nuevoPaso.value.nom,
            incoterm_id: incotermSeleccionado.value.id,
            ordre: maxOrden + 1
        });

        // Normalizar el paso creado (activo puede venir como '1'/'0')
        const pasoCreado = {
            ...respuesta.data,
            activo: respuesta.data.activo === true || respuesta.data.activo === 1 || respuesta.data.activo === '1' || respuesta.data.activo === 'true'
        };
        pasosDelIncoterm.value.push(pasoCreado);
        nuevoPaso.value.nom = '';
        mostrarFormularioPaso.value = false;
        mostrarMensaje('Paso creado correctamente.', false);
    } catch (error) {
        mostrarMensaje(
            error?.response?.data?.message || 'No se pudo crear el paso.',
            true
        );
        console.error(error);
    } finally {
        guardando.value = false;
    }
}

// INICIALIZAR
onMounted(async () => {
    await cargarTiposIncoterm();
    await cargarIncoterms();
});
</script>

<style lang="scss" scoped>
.pagina-edicion {
    min-height: 100vh;
    background: radial-gradient(circle at top left, rgba(14, 165, 233, 0.16), transparent 28%),
                radial-gradient(circle at right 20%, rgba(59, 130, 246, 0.12), transparent 32%),
                #eef4fb;
}

.contenedor {
    width: min(1400px, calc(100% - 32px));
    margin: 0 auto;
    padding: 28px 0 40px;
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 20px;
    align-items: start;

    @media (max-width: 1100px) {
        grid-template-columns: 1fr;
    }
}

.gestion-header {
    display: none;
}

.seccion-listado {
    position: relative;

    h1 {
        margin: 0 0 8px;
        font-size: clamp(1.8rem, 2.5vw, 2.4rem);
        color: #10243f;
    }

    > p {
        margin: 0 0 24px;
        color: #5b6f88;
        font-size: 1rem;
    }
}

.encabezado-listado {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 24px;

    p {
        margin: 0;
        color: #5b6f88;
        font-size: 1rem;
    }
}

.estado-carga,
.estado-vacio {
    display: grid;
    place-items: center;
    min-height: 300px;
    text-align: center;
    color: #6b7d93;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #d8e3ef;
    border-radius: 20px;
    padding: 40px;
}

.estado-carga {
    gap: 16px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #d6e2f0;
    border-top-color: #0ea5e9;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.grid-tarjetas {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.tarjeta-incoterm {
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #d8e3ef;
    border-radius: 16px;
    padding: 18px;
    box-shadow: 0 4px 12px rgba(16, 36, 63, 0.08);
    transition: all 0.3s ease;

    &:hover {
        border-color: #0ea5e9;
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.12);
        transform: translateY(-2px);
    }
}

.cabecera-tarjeta {
    margin-bottom: 16px;

    .numero {
        margin: 0 0 4px;
        font-size: 0.8rem;
        color: #7a8fa0;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #10243f;
    }
}

.acciones-tarjeta {
    display: flex;
    gap: 8px;
}

.seccion-editor {
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid #d8e3ef;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 20px 40px rgba(16, 36, 63, 0.12);
    position: sticky;
    top: 20px;
    max-height: 90vh;
    overflow-y: auto;

    @media (max-width: 1100px) {
        position: static;
        max-height: none;
    }
}

.cabecera-editor {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e2eaf3;

    h2 {
        margin: 0;
        font-size: 1.3rem;
        color: #152b47;
    }
}

.btn-cerrar {
    width: 32px;
    height: 32px;
    border: none;
    background: #f0f5fa;
    border-radius: 8px;
    color: #5b6f88;
    font-size: 1.2rem;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
        background: #e2eaf3;
        color: #10243f;
    }
}

.contenedor-pasos {
    margin-bottom: 20px;

    h3 {
        margin: 0 0 16px;
        font-size: 1.05rem;
        color: #152b47;
    }
}

.sin-pasos {
    padding: 16px;
    background: #f8fafd;
    border: 1px solid #dde7f0;
    border-radius: 10px;
    color: #5b6f88;
    text-align: center;
}

.lista-pasos {
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f8fafd;
    padding: 12px;
    border: 1px solid #dde7f0;
    border-radius: 12px;
    margin-bottom: 16px;
}

.fila-paso {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2eaf3;
    transition: all 0.2s ease;

    &:hover {
        border-color: #0ea5e9;
        box-shadow: 0 2px 8px rgba(14, 165, 233, 0.08);
    }
}

.datos-paso {
    display: flex;
    align-items: center;
    gap: 10px;
}

.numero-paso {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: rgba(14, 165, 233, 0.12);
    color: #0b5f87;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.85rem;
}

.nombre-paso {
    color: #10243f;
    font-weight: 500;
    font-size: 0.95rem;
}

.contenedor-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;

    input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #0ea5e9;
    }
}

.texto-checkbox {
    font-size: 0.85rem;
    color: #5b6f88;
    font-weight: 500;
}

.acciones-editor {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
}

.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;

    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    &:hover:not(:disabled) {
        transform: translateY(-1px);
    }

    &:active:not(:disabled) {
        transform: translateY(0);
    }
}

.btn-editar {
    flex: 1;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(14, 165, 233, 0.3);

    &:hover:not(:disabled) {
        box-shadow: 0 6px 16px rgba(14, 165, 233, 0.4);
    }
}

.btn-editar-incoterm {
    flex: 1;
    background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
    color: white;
}

.btn-eliminar {
    flex: 1;
    background: #fee2e2;
    color: #b91c1c;

    &:hover:not(:disabled) {
        background: #fecaca;
    }
}

.btn-crear-incoterm {
    flex: 0 0 auto;
    align-self: flex-start;
    background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
    color: #fff;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);

    &:hover:not(:disabled) {
        box-shadow: 0 12px 28px rgba(29, 78, 216, 0.22);
    }
}

.btn-guardar {
    flex: 1;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);

    &:hover:not(:disabled) {
        box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
    }
}

.btn-cancelar {
    flex: 1;
    background: rgba(255, 255, 255, 0.9);
    color: #10243f;
    border: 1px solid #d8e3ef;

    &:hover:not(:disabled) {
        background: #fff;
        border-color: #0ea5e9;
    }
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(7, 18, 34, 0.55);
    backdrop-filter: blur(6px);
    display: grid;
    place-items: center;
    padding: 20px;
    z-index: 50;
}

.modal-incoterm {
    width: min(560px, 100%);
    background: #fff;
    border-radius: 22px;
    border: 1px solid #d8e3ef;
    box-shadow: 0 28px 70px rgba(16, 36, 63, 0.22);
    padding: 22px;

    form {
        display: grid;
        gap: 16px;
    }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;

    h2 {
        margin: 0;
        font-size: 1.25rem;
        color: #10243f;
    }
}

.modal-actions {
    display: flex;
    gap: 10px;
}

.btn-cancelar-modal {
    flex: 1;
    background: #f1f5f9;
    color: #10243f;
    border: 1px solid #d8e3ef;

    &:hover:not(:disabled) {
        background: #e2e8f0;
    }
}

.mensaje {
    margin: 0;
    padding: 12px;
    border-radius: 8px;
    font-size: 0.9rem;
    background: #dbeafe;
    color: #1d4ed8;
    border: 1px solid #7dd3fc;

    &.error {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }
}

.agregar-paso {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e2eaf3;
}

.formulario-paso {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.entrada-paso {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d8e3ef;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #10243f;
    background: #fff;
    transition: all 0.2s ease;

    &:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }
}

.btn-pequeño {
    padding: 8px 12px;
    font-size: 0.85rem;
}

.btn-nuevo-paso {
    width: 100%;
    background: rgba(14, 165, 233, 0.1);
    color: #0ea5e9;
    border: 1px dashed #0ea5e9;

    &:hover {
        background: rgba(14, 165, 233, 0.2);
    }
}

@media (max-width: 640px) {
    .contenedor {
        padding: 16px 0 24px;
    }

    .seccion-editor {
        padding: 18px;
        margin-top: 20px;
    }

    .grid-tarjetas {
        grid-template-columns: 1fr;
    }

    .acciones-tarjeta,
    .acciones-editor {
        flex-direction: column;
    }
}
</style>
