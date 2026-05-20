<template>
    <div class="pagina-edicion">
        <Navbar />

        <main class="contenedor">
            <!-- SECCIÃ“N: LISTA DE INCOTERMS -->
            <section class="seccion-listado">
                <div class="encabezado-listado">
                    <div>
                        <h1>Gestión de Incoterms</h1>
                        <p>Selecciona un incoterm para editar sus pasos de seguimiento.</p>
                    </div>

                    <div class="acciones-encabezado">
                        <Botones class="btn btn-crear-incoterm" @click="abrirFormularioNuevoIncoterm">
                            + Añadir Incoterm
                        </Botones>

                        <p v-if="mensaje" class="mensaje" :class="{ error: esError }">
                            {{ mensaje }}
                        </p>
                    </div>
                </div>

                <div v-if="cargando" class="estado-carga">
                    <span class="spinner"></span>
                    <p>Cargando incoterms...</p>
                </div>

                <div v-else-if="sinIncoterms" class="estado-vacio">
                    <p>No hay incoterms en la base de datos.</p>
                </div>

                <div v-else class="grid-tarjetas">
                    <!-- Render con v-for: un card por incoterm -->
                    <article v-for="incoterm in incoterms" :key="incoterm.id" class="tarjeta-incoterm">
                        <div class="cabecera-tarjeta">
                            <p class="numero">Incoterm #{{ incoterm.id }}</p>
                            <h3>{{ obtenerLabelIncoterm(incoterm) }}</h3>
                        </div>
                        <div class="acciones-tarjeta">
                            <Botones class="btn btn-editar-incoterm" @click="abrirFormularioEditarIncoterm(incoterm)">
                                Editar incoterm
                            </Botones>
                            <Botones class="btn btn-editar" @click="seleccionarIncoterm(incoterm)">
                                Editar pasos
                            </Botones>
                            <Botones class="btn btn-eliminar" @click="eliminarIncoterm(incoterm.id)">
                                Eliminar
                            </Botones>
                        </div>
                    </article>
                </div>
            </section>

            <!-- SECCIÃ“N: EDITOR DE PASOS -->
            <section v-if="incotermSeleccionado" class="seccion-editor">
                <div class="cabecera-editor">
                    <h2>{{ obtenerLabelIncoterm(incotermSeleccionado) }}</h2>
                    <Botones class="btn btn-cerrar" @click="cerrarEditor">X</Botones>
                </div>

                <div class="contenedor-pasos">
                    <h3>Pasos de seguimiento</h3>

                    <div v-if="sinPasos" class="sin-pasos">
                        <p>Este incoterm no tiene pasos asociados.</p>
                    </div>

                    <div v-else class="lista-pasos">
                        <!-- Render con v-for: un row por paso -->
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
                        <!-- Alternancia v-if / v-else para mostrar formulario o botón -->
                        <div v-if="mostrarFormularioPaso" class="formulario-paso">
                            <Input
                                v-model="nuevoPaso.nom"
                                type="text"
                                placeholder="Nombre del paso"
                                inputClass="entrada-paso"
                            />
                            <Botones class="btn btn-pequeno btn-agregar" @click="crearPaso">
                                Agregar
                            </Botones>
                            <Botones class="btn btn-pequeno btn-cancelar" @click="mostrarFormularioPaso = false">
                                Cancelar
                            </Botones>
                        </div>
                        <Botones v-else class="btn btn-pequeno btn-nuevo-paso" @click="mostrarFormularioPaso = true">
                            + Nuevo paso
                        </Botones>
                    </div>

                    <div class="acciones-editor">
                        <Botones class="btn btn-guardar" @click="guardarCambios" :disabled="guardando">
                            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
                        </Botones>
                        <Botones class="btn btn-cancelar" @click="cerrarEditor">
                            Cancelar
                        </Botones>
                    </div>

                </div>
            </section>
        </main>

        <div v-if="mostrarFormularioNuevoIncoterm" class="modal-overlay">
            <div class="modal-incoterm">
                <div class="modal-header">
                    <h2>Añadir Incoterm</h2>
                    <Botones class="btn btn-cerrar" @click="cerrarFormularioNuevoIncoterm">X</Botones>
                </div>

                <form @submit.prevent="crearIncoterm">
                    <FormularioNombreIncoterm
                        :codigoActual="nuevoIncoterm.codi"
                        :nombreActual="nuevoIncoterm.nom"
                        @actualizar-codigo="nuevoIncoterm.codi = $event"
                        @actualizar-nombre="nuevoIncoterm.nom = $event"
                    />

                    <div class="modal-actions">
                        <Botones type="button" class="btn btn-cancelar-modal" @click="cerrarFormularioNuevoIncoterm">
                            Cancelar
                        </Botones>
                        <Botones type="submit" class="btn btn-guardar" :disabled="creandoIncoterm">
                            {{ creandoIncoterm ? 'Creando...' : 'Crear incoterm' }}
                        </Botones>
                    </div>

                </form>
            </div>
        </div>

        <div v-if="mostrarFormularioEditarIncoterm" class="modal-overlay">
            <div class="modal-incoterm">
                <div class="modal-header">
                    <h2>Editar Incoterm</h2>
                    <Botones class="btn btn-cerrar" @click="cerrarFormularioEditarIncoterm">X</Botones>
                </div>

                <form @submit.prevent="editarIncoterm">
                    <FormularioNombreIncoterm
                        :codigoActual="incotermEditando.codi"
                        :nombreActual="incotermEditando.nom"
                        @actualizar-codigo="incotermEditando.codi = $event"
                        @actualizar-nombre="incotermEditando.nom = $event"
                    />

                    <div class="modal-actions">
                        <Botones type="button" class="btn btn-cancelar-modal" @click="cerrarFormularioEditarIncoterm">
                            Cancelar
                        </Botones>
                        <Botones type="submit" class="btn btn-guardar" :disabled="guardandoEdicionIncoterm">
                            {{ guardandoEdicionIncoterm ? 'Guardando...' : 'Guardar cambios' }}
                        </Botones>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import Navbar from '@/components/Navbar.vue';
import FormularioNombreIncoterm from '@/components/FormularioNombreIncoterm.vue';
import Botones from '@/components/Botones.vue';
import Input from '@/components/Input.vue';
import api from '@/lib/api';

// ESTADO
const incoterms = ref([]);
const tiposIncoterms = ref([]);
const incotermInstancias = ref([]);
const pasosDelIncoterm = ref([]);
const incotermSeleccionado = ref(null);
const cargando = ref(true);
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
const sinIncoterms = computed(() => incoterms.value.length === 0);
const sinPasos = computed(() => pasosDelIncoterm.value.length === 0);

const obtenerLabelIncoterm = (incoterm) => {
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
const cargarTiposIncoterm = async () => {
    try {
        const respuesta = await api.get('/tipos-incoterm');
        tiposIncoterms.value = respuesta.data || [];
    } catch (error) {
        console.error('No se pudieron cargar los tipos de incoterm:', error);
        tiposIncoterms.value = [];
    }
}

// CARGAR INCOTERMS
const cargarIncoterms = async () => {
    cargando.value = true;
    try {
        const tiposRespuesta = await api.get('/tipos-incoterm');
        const incotermsRespuesta = await api.get('/incoterms');

        const tipos = tiposRespuesta.data || [];
        incotermInstancias.value = incotermsRespuesta.data || [];
        incoterms.value = [];

        for (const tipo of tipos) {
            const instancia = incotermInstancias.value.find(item => item && item.tipus_inconterm_id == tipo.id);
            incoterms.value.push({
                id: tipo.id,
                tipus_inconterm_id: tipo.id,
                tipusIncoterm: tipo,
                _incoterm_instance_id: instancia ? instancia.id : null,
            });
        }
    } catch (error) {
        mostrarMensaje('No se pudieron cargar los incoterms.', true);
        console.error(error);
    } finally {
        cargando.value = false;
    }
}

// CARGAR PASOS DE UN INCOTERM
const cargarPasosDelIncoterm = async (incotermId) => {
    try {
        const response = await api.get('/tracking-steps', {
            params: {
                incoterm_id: incotermId
            }
        });

        const data = response.data || [];
        pasosDelIncoterm.value = [];
        for (const paso of data) {
            pasosDelIncoterm.value.push(normalizarPaso(paso));
        }

    } catch (error) {
        console.error('Error al cargar pasos:', error);

        let mensaje = 'No se pudieron cargar los pasos.';

        if (error.response && error.response.data) {
            mensaje = error.response.data;
        }

        mostrarMensaje(mensaje, true);
    }
}

const normalizarPaso = (paso) => {
    return {
        id: paso.id,
        nom: paso.nom,
        ordre: paso.ordre,
        incoterm_id: paso.incoterm_id,
        activo: convertirABoolean(paso.activo)
    };
}

const convertirABoolean = (valor) => {
    return (
        valor === true ||
        valor === 1 ||
        valor === '1' ||
        valor === 'true'
    );
}

// SELECCIONAR INCOTERM
const seleccionarIncoterm = async (incoterm) => {
    const instanciaId = await asegurarInstanciaIncoterm(incoterm);

    if (!instanciaId) {
        mostrarMensaje('No se pudo preparar el incoterm para editar pasos.', true);
        return;
    }

    incotermSeleccionado.value = incoterm;
    incotermSeleccionado.value._incoterm_instance_id = instanciaId;
    mensaje.value = '';
    await cargarPasosDelIncoterm(instanciaId);
}

// CERRAR EDITOR
const cerrarEditor = () => {
    incotermSeleccionado.value = null;
    pasosDelIncoterm.value = [];
    mensaje.value = '';
}

// GUARDAR CAMBIOS
const guardarCambios = async () => {
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
            incoterm_id: incotermSeleccionado.value._incoterm_instance_id || incotermSeleccionado.value.id,
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
const eliminarIncoterm = async (id) => {
    const confirmar = window.confirm('Â¿EstÃ¡s seguro que quieres eliminar este incoterm?');
    if (!confirmar) return;

    try {
        const card = incoterms.value.find(item => item.id == id);
        if (!card?._incoterm_instance_id) {
            mostrarMensaje('No hay instancia de incoterm para eliminar en este tipo.', true);
            return;
        }

        await api.delete(`/incoterms/${card._incoterm_instance_id}`);
        mostrarMensaje('Incoterm eliminado correctamente.', false);
        await cargarIncoterms();
    } catch (error) {
        const mensaje = error?.response?.data?.message || 'No se pudo eliminar el incoterm.';
        mostrarMensaje(mensaje, true);
        console.error('Error al eliminar incoterm:', error);
    }
}

// MOSTRAR MENSAJE
const mostrarMensaje = (texto, esErrorMensaje = false) => {
    mensaje.value = texto;
    esError.value = esErrorMensaje;
}

const abrirFormularioNuevoIncoterm = () => {
    mensaje.value = '';
    esError.value = false;
    nuevoIncoterm.value = { nom: '', codi: '' };
    mostrarFormularioNuevoIncoterm.value = true;
}

const cerrarFormularioNuevoIncoterm = () => {
    mostrarFormularioNuevoIncoterm.value = false;
    nuevoIncoterm.value = { nom: '', codi: '' };
}

const abrirFormularioEditarIncoterm = async (incoterm) => {
    const instanciaId = await asegurarInstanciaIncoterm(incoterm);
    if (!instanciaId) {
        mostrarMensaje('No se pudo abrir edición para este tipo de incoterm.', true);
        return;
    }

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
        id: instanciaId,
        nom: tipusLocal.nom || '',
        codi: tipusLocal.codi || '',
    };
    mostrarFormularioEditarIncoterm.value = true;

    try {
        const { data } = await api.get(`/incoterms/${instanciaId}`);
        const tipus = data?.tipusIncoterm || data?.tipus;

        incotermEditando.value = {
            id: instanciaId,
            nom: tipus.nom || incotermEditando.value.nom || '',
            codi: tipus.codi || incotermEditando.value.codi || '',
        };
    } catch (error) {
        console.error('No se pudieron refrescar los datos del incoterm a editar:', error);
    }
}

const cerrarFormularioEditarIncoterm = () => {
    mostrarFormularioEditarIncoterm.value = false;
    incotermEditando.value = { id: null, nom: '', codi: '' };
}

const crearIncoterm = async () => {
    const codi = nuevoIncoterm.value.codi.trim();
    const nom = nuevoIncoterm.value.nom.trim();

    if (!codi || !nom) {
        mostrarMensaje('El código y el nombre son obligatorios.', true);
        return;
    }

    const tipusSeleccionat = tiposIncoterms.value.find(item => {
        const itemCodi = String(item?.codi ?? '').trim().toUpperCase();
        const itemNom = String(item?.nom ?? '').trim().toUpperCase();
        return itemCodi === codi.toUpperCase() && itemNom === nom.toUpperCase();
    });

    creandoIncoterm.value = true;
    mensaje.value = '';

    try {
        const payload = {
            codi: codi,
            nom: nom,
            tracking_steps_id: null,
        };

        if (tipusSeleccionat && tipusSeleccionat.id) {
            payload.tipus_incoterm_id = tipusSeleccionat.id;
        }

        const respuesta = await api.post('/incoterms', payload);

        await cargarTiposIncoterm();
        await cargarIncoterms();

        const tipusIdCreado = respuesta.data ? respuesta.data.tipus_inconterm_id : null;
        const incotermCreado = incoterms.value.find(item => item.id == tipusIdCreado) || null;
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

const asegurarInstanciaIncoterm = async (incoterm) => {
    if (incoterm?._incoterm_instance_id) {
        return incoterm._incoterm_instance_id;
    }

    const tipus = incoterm && incoterm.tipusIncoterm ? incoterm.tipusIncoterm : {};
    const payload = {
        tipus_incoterm_id: incoterm ? (incoterm.tipus_inconterm_id || incoterm.id) : null,
        codi: (tipus.codi || '').trim(),
        nom: (tipus.nom || '').trim(),
    };

    if (!payload.tipus_incoterm_id) {
        return null;
    }

    const respuesta = await api.post('/incoterms', payload);
    const createdId = respuesta.data ? respuesta.data.id : null;

    if (createdId) {
        incoterm._incoterm_instance_id = createdId;
    }

    return createdId;
}
const editarIncoterm = async () => {
    if (!incotermEditando.value.codi.trim() || !incotermEditando.value.nom.trim()) {
        mostrarMensaje('El cÃƒÂ³digo y el nombre son obligatorios.', true);
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
const crearPaso = async () => {

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

        let maxOrden = 0;

        for (const paso of pasosDelIncoterm.value) {
            if (paso.ordre > maxOrden) {
                maxOrden = paso.ordre;
            }
        }

        const respuesta = await api.post('/tracking-steps', {
            nom: nuevoPaso.value.nom,
            incoterm_id: incotermSeleccionado.value.id,
            ordre: maxOrden + 1
        });

        const data = respuesta.data;

        const pasoCreado = {
            id: data.id,
            nom: data.nom,
            ordre: data.ordre,
            incoterm_id: data.incoterm_id,
            activo:
                data.activo === true ||
                data.activo === 1 ||
                data.activo === '1' ||
                data.activo === 'true'
        };

        pasosDelIncoterm.value.push(pasoCreado);

        nuevoPaso.value.nom = '';

        mostrarFormularioPaso.value = false;

        mostrarMensaje('Paso creado correctamente.', false);

    } catch (error) {

        let mensaje = 'No se pudo crear el paso.';

        if (
            error &&
            error.response &&
            error.response.data &&
            error.response.data.message
        ) {
            mensaje = error.response.data.message;
        }

        mostrarMensaje(mensaje, true);

        console.error(error);

    } finally {

        guardando.value = false;
    }
}

// INICIALIZAR
onMounted(async () => {
    cargando.value = true;
    await cargarTiposIncoterm();
    await cargarIncoterms();
    cargando.value = false;
});
</script>

<style lang="scss" scoped>
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

.acciones-encabezado {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}

.acciones-encabezado .mensaje {
    width: min(420px, 100%);
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

    box-shadow: none;
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

.btn-pequeno {
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

    .acciones-editor {
        flex-direction: column;
    }
}
</style>


