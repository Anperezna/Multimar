<template>
    <div class="pagina-edicion">
        <Navbar />

        <main class="contenedor">
            <section class="seccion-listado">
                <div class="encabezado-listado">
                    <div>
                        <h1>Gestión de Incoterms</h1>
                        <p>Administra los incoterms del sistema y sus pasos de seguimiento asociados.</p>
                    </div>

                    <div class="acciones-encabezado">
                        <Botones class="btn btn-crear-incoterm" @click="abrirModalNuevo">
                            + Añadir Incoterm
                        </Botones>

                        <p v-if="mensaje" class="mensaje" :class="{ error: esError }">
                            {{ mensaje }}
                        </p>
                    </div>
                </div>

                <div v-if="cargandoLista" class="estado-carga">
                    <span class="spinner"></span>
                    <p>Cargando incoterms...</p>
                </div>

                <div v-else-if="sinIncoterms" class="estado-vacio">
                    <p>No hay incoterms en la base de datos.</p>
                </div>

                <div v-else class="grid-tarjetas">
                    <article v-for="incoterm in incoterms" :key="incoterm.id" class="tarjeta-incoterm">
                        <div class="cabecera-tarjeta">
                            <p class="numero">Incoterm #{{ incoterm.id }}</p>
                            <h3>{{ incoterm.codi }} - {{ incoterm.nom }}</h3>
                        </div>
                        <div class="acciones-tarjeta">
                            <Botones class="btn btn-editar-incoterm" @click="abrirModalEditar(incoterm)">
                                Editar Datos y Pasos
                            </Botones>
                            <Botones class="btn btn-editar" @click="visualizarPasos(incoterm)">
                                Ver pasos asignados ({{ incoterm.tracking_steps?.length || 0 }})
                            </Botones>
                            <Botones class="btn btn-eliminar" @click="eliminarIncoterm(incoterm.id)">
                                Eliminar
                            </Botones>
                        </div>
                    </article>
                </div>
            </section>

            <section v-if="incotermSeleccionado" class="seccion-editor">
                <div class="cabecera-editor">
                    <h2>Pasos de: {{ incotermSeleccionado.codi }}</h2>
                    <Botones class="btn btn-cerrar" @click="cerrarVisor">X</Botones>
                </div>

                <div class="contenedor-pasos">
                    <h3>Pasos de seguimiento asignados</h3>

                    <div v-if="incotermSeleccionado.tracking_steps?.length === 0" class="sin-pasos">
                        <p>Este incoterm no tiene ningún paso de seguimiento asociado actualmente.</p>
                    </div>

                    <div v-else class="lista-pasos">
                        <div v-for="paso in incotermSeleccionado.tracking_steps" :key="paso.id" class="fila-paso">
                            <div class="datos-paso">
                                <span class="numero-paso">{{ paso.ordre }}</span>
                                <span class="nombre-paso">{{ paso.nom }}</span>
                            </div>
                            <span class="texto-checkbox" style="color: #0ea5e9; font-weight: bold;">✓ Vinculado</span>
                        </div>
                    </div>

                    <div class="acciones-editor">
                        <Botones class="btn btn-cancelar" @click="cerrarVisor">
                            Cerrar Vista
                        </Botones>
                    </div>
                </div>
            </section>
        </main>

        <div v-if="modalFormulario" class="modal-overlay">
            <div class="modal-incoterm" style="max-height: 85vh; overflow-y: auto;">
                <div class="modal-header">
                    <h2>{{ formId ? 'Editar Incoterm e Hitos' : 'Añadir Nuevo Incoterm' }}</h2>
                    <Botones class="btn btn-cerrar" @click="cerrarModales">X</Botones>
                </div>

                <form @submit.prevent="guardarIncoterm">
                    <FormularioNombreIncoterm 
                        :codigoActual="formCodi"
                        :nombreActual="formNom"
                        @actualizar-codigo="formCodi = $event.trim()"
                        @actualizar-nombre="formNom = $event.trim()" 
                    />

                    <ListadoPasosCheckbox 
                        :pasosDisponibles="todosLosPasosDisponibles"
                        :pasosSeleccionados="formPasos"
                        @actualizar-pasos="formPasos = $event"
                    />

                    <div class="modal-actions">
                        <Botones type="button" class="btn btn-cancelar-modal" @click="cerrarModales">
                            Cancelar
                        </Botones>
                        <Botones type="submit" class="btn btn-guardar" :disabled="guardando">
                            {{ guardando ? 'Guardando...' : 'Guardar' }}
                        </Botones>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import Navbar from '@/components/Navbar.vue';
import FormularioNombreIncoterm from '@/components/FormularioNombreIncoterm.vue';
import ListadoPasosCheckbox from '@/components/ListadoPasosCheckbox.vue'; // 👈 Asegúrate de que la ruta sea correcta
import Botones from '@/components/Botones.vue';
import api from '@/lib/api';

// ==========================================
// ESTADO GLOBAL Y DATOS
// ==========================================
const incoterms = ref([]);
const todosLosPasosDisponibles = ref([]);
const incotermSeleccionado = ref(null);

const sinIncoterms = computed(() => incoterms.value.length === 0);

// ==========================================
// ESTADO DE INTERFAZ (UI)
// ==========================================
const cargandoLista = ref(true);
const guardando = ref(false);
const modalFormulario = ref(false);
const mensaje = ref('');
const esError = ref(false);

// ==========================================
// DATOS DEL FORMULARIO
// ==========================================
const formId = ref(null);
const formCodi = ref('');
const formNom = ref('');
const formPasos = ref([]);

// ==========================================
// CICLO DE VIDA (Carga secuencial)
// ==========================================
onMounted(async () => {
    cargandoLista.value = true;

    try {
        const resPasos = await api.get('/tracking-steps');
        todosLosPasosDisponibles.value = resPasos.data || [];
    } catch (error) {
        console.error('Error loading pasos:', error);
    }

    try {
        const resIncoterms = await api.get('/incoterms');
        incoterms.value = resIncoterms.data || [];
    } catch (error) {
        console.error('Error loading incoterms:', error);
        mostrarMensaje('Error al cargar la información del servidor.', true);
    }

    cargandoLista.value = false;
});

// ==========================================
// UTILIDADES Y FUNCIONES
// ==========================================
const mostrarMensaje = (texto, error = false) => {
    mensaje.value = texto;
    esError.value = error;
    setTimeout(() => { mensaje.value = ''; }, 4000);
};

const limpiarFormulario = () => {
    formId.value = null;
    formCodi.value = '';
    formNom.value = '';
    formPasos.value = [];
};

const refrescarListaIncoterms = async () => {
    try {
        const respuesta = await api.get('/incoterms');
        incoterms.value = respuesta.data || [];
    } catch (error) {
        console.error(error);
    }
};

// ==========================================
// GUARDAR, EDITAR Y ELIMINAR
// ==========================================
const guardarIncoterm = async () => {
    if (guardando.value) return;

    if (!formCodi.value || !formNom.value) {
        mostrarMensaje('Código y nombre del incoterm requeridos.', true);
        return;
    }

    guardando.value = true;

    try {
        if (formId.value !== null) {
            await api.put(`/incoterms/${formId.value}`, {
                id: formId.value,
                codi: formCodi.value,
                nom: formNom.value,
                pasos: formPasos.value
            });
            mostrarMensaje('Incoterm actualizado correctamente.', false);
        } else {
            await api.post('/incoterms', {
                codi: formCodi.value,
                nom: formNom.value,
                pasos: formPasos.value
            });
            mostrarMensaje('Incoterm creado con éxito.', false);
        }

        cerrarModales();
        await refrescarListaIncoterms();
    } catch (error) {
        mostrarMensaje(error?.response?.data?.message ?? 'Fallo al guardar en el servidor.', true);
    } finally {
        guardando.value = false;
    }
};

const eliminarIncoterm = async (id) => {   
    try {
        await api.delete(`/incoterms/${id}`);
        mostrarMensaje('Incoterm eliminado correctamente.', false);
        
        if (incotermSeleccionado.value?.id === id) {
            cerrarVisor();
        }
        await refrescarListaIncoterms();
    } catch (error) {
        mostrarMensaje(error?.response?.data?.message ?? 'Error al procesar la baja.', true);
    }
};

// ==========================================
// MANEJO DE MODALES
// ==========================================
const abrirModalNuevo = () => {
    limpiarFormulario();
    modalFormulario.value = true;
};

const abrirModalEditar = (incoterm) => {
    formId.value = incoterm.id;
    formCodi.value = incoterm.codi;
    formNom.value = incoterm.nom;
    formPasos.value = incoterm.tracking_steps ? incoterm.tracking_steps.map(p => p.id) : [];
    
    modalFormulario.value = true;
};

const cerrarModales = () => {
    modalFormulario.value = false;
    limpiarFormulario();
};

const visualizarPasos = (incoterm) => {
    incotermSeleccionado.value = incoterm;
};

const cerrarVisor = () => {
    incotermSeleccionado.value = null;
};
</script>

<style lang="scss" scoped>
/* Conserva tus estilos originales intactos de tu hoja CSS previa */
.pagina-edicion {
    min-height: 100vh;
    background: #f5f5f5;
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

.seccion-listado {
    position: relative;

    h1 {
        margin: 0 0 8px;
        font-size: clamp(1.8rem, 2.5vw, 2.4rem);
        color: #10243f;
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

.acciones-encabezado {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
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
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 18px;
    box-shadow: 0 4px 12px rgba(16, 36, 63, 0.08);
    transition: border-color 0.2s ease;

    &:hover {
        border-color: #0091d5;
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
    }

    h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #10243f;
    }
}

.acciones-tarjeta {
    display: flex;
    flex-direction: column;
    gap: 8px;

    .btn {
        width: 100%;
        justify-content: center;
    }
}

.seccion-editor {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
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

    &:hover {
        background: #e2eaf3;
        color: #10243f;
    }
}

.contenedor-pasos {
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
    border: 1px solid transparent;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;

    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
}

.btn-editar {
    flex: 1;
    background: #0091d5;
    color: white;
}

.btn-editar-incoterm {
    flex: 1;
    background: #1b2a4a;
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
    background: #0091d5;
    color: #fff;
}

.btn-guardar {
    flex: 1;
    background: #0091d5;
    color: white;
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
</style>