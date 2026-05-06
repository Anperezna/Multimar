<template>
    <div class="gestion-page">
        <Navbar />

        <main class="gestion-layout">
            <header class="gestion-header">
                <div>
                    <h1>Gestión de Incoterms</h1>
                    <p>Crear, editar, consultar y eliminar incoterms guardados en la base de datos.</p>
                </div>

                <Botones @click="abrirNuevoIncoterm">
                    + Añadir incoterm
                </Botones>
            </header>

            <div class="gestion-content">
                <section class="listado-panel">
                    <div class="listado-top">
                        <h2>Listado de incoterms</h2>
                        <span class="listado-total">{{ incoterms.length }} registros</span>
                    </div>

                    <div v-if="isLoading" class="estado-vacio estado-vacio--loading">
                        <span class="spinner"></span>
                        <p>Cargando incoterms...</p>
                    </div>

                    <div v-else-if="!incoterms.length" class="estado-vacio">
                        <p>No hay incoterms guardados todavía.</p>
                    </div>

                    <div v-else class="incoterm-grid">
                        <article v-for="incoterm in incoterms" :key="incoterm.id" class="incoterm-card">
                            <div class="incoterm-card__header">
                                <div>
                                    <p class="card-kicker">Incoterm #{{ incoterm.id }}</p>
                                    <h3>{{ construirEtiquetaIncoterm(incoterm) }}</h3>
                                </div>

                                <span class="card-badge">Paso {{ incoterm.tracking_step?.ordre ?? 'N/D' }}</span>
                            </div>

                            <dl class="incoterm-meta">
                                <div>
                                    <dt>Tipo</dt>
                                    <dd>{{ incoterm.tipus?.label || incoterm.tipusIncoterm?.label || 'Sin tipo asignado' }}</dd>
                                </div>
                                <div>
                                    <dt>Paso</dt>
                                    <dd>{{ incoterm.tracking_step?.nom || incoterm.trackingStep?.nom || 'Sin paso asignado' }}</dd>
                                </div>
                            </dl>

                            <div class="card-actions">
                                <button class="action-btn action-btn--edit" type="button" @click="editarIncoterm(incoterm)">
                                    Editar
                                </button>
                                <button class="action-btn action-btn--delete" type="button" @click="eliminarIncoterm(incoterm.id)">
                                    Eliminar
                                </button>
                            </div>
                        </article>
                    </div>
                </section>

                <aside class="form-panel">
                    <h2>{{ modoEdicion ? 'Editar incoterm' : 'Añadir incoterm' }}</h2>
                    <p class="form-help">
                        Selecciona el tipo de incoterm y el paso de seguimiento que quieres asociar.
                    </p>

                    <form class="incoterm-form" @submit.prevent="guardarIncoterm">
                        <div class="field">
                            <label for="tipo-incoterm">Tipo de incoterm</label>
                            <Desplegable
                                id="tipo-incoterm"
                                v-model="form.tipus_inconterm_id"
                                :options="tiposIncotermOptions"
                                placeholder="Selecciona un tipo"
                                selectClass="form-select"
                            />
                        </div>

                        <div class="field">
                            <label for="tracking-step">Paso de seguimiento</label>
                            <Desplegable
                                id="tracking-step"
                                v-model="form.tracking_steps_id"
                                :options="trackingStepOptions"
                                placeholder="Selecciona un paso"
                                selectClass="form-select"
                            />
                        </div>

                        <div class="form-actions">
                            <button type="button" class="secondary-btn" @click="limpiarFormulario">
                                Limpiar
                            </button>
                            <Botones type="submit" :disabled="isSaving">
                                {{ isSaving ? 'Guardando...' : (modoEdicion ? 'Actualizar' : 'Crear') }}
                            </Botones>
                        </div>

                        <p v-if="feedback" class="form-feedback">{{ feedback }}</p>
                    </form>
                </aside>
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import Navbar from '@/components/Navbar.vue';
import Botones from '@/components/Botones.vue';
import Desplegable from '@/components/Desplegable.vue';
import api from '@/lib/api';

const incoterms = ref([]);
const tiposIncoterm = ref([]);
const trackingSteps = ref([]);
const isLoading = ref(false);
const isSaving = ref(false);
const feedback = ref('');
const editingId = ref(null);

const form = reactive({
    tipus_inconterm_id: '',
    tracking_steps_id: '',
});

const modoEdicion = computed(() => editingId.value !== null);

const tiposIncotermOptions = computed(() => tiposIncoterm.value.map((item) => ({
    value: item.id,
    label: item.label || `${item.codi || ''} - ${item.nom || ''}`.replace(/^\s*-\s*|\s*-\s*$/g, '').trim(),
})));

const trackingStepOptions = computed(() => trackingSteps.value.map((item) => ({
    value: item.id,
    label: `${item.ordre}. ${item.nom}`,
})));

function construirEtiquetaIncoterm(incoterm) {
    if (incoterm?.tipus?.label) {
        return incoterm.tipus.label;
    }

    if (incoterm?.tipusIncoterm?.label) {
        return incoterm.tipusIncoterm.label;
    }

    if (incoterm?.tipusIncoterm) {
        return `${incoterm.tipusIncoterm.codi || ''} - ${incoterm.tipusIncoterm.nom || ''}`.replace(/^\s*-\s*|\s*-\s*$/g, '').trim();
    }

    return incoterm?.label || `Incoterm ${incoterm?.id || ''}`;
}

async function cargarDatos() {
    isLoading.value = true;

    try {
        const [incotermsResponse, tiposResponse, trackingResponse] = await Promise.all([
            api.get('/incoterms'),
            api.get('/tipos-incoterm'),
            api.get('/tracking-steps'),
        ]);

        incoterms.value = (incotermsResponse.data || []).map((item) => ({
            ...item,
            label: construirEtiquetaIncoterm(item),
        }));
        tiposIncoterm.value = tiposResponse.data || [];
        trackingSteps.value = trackingResponse.data || [];
    } catch (error) {
        feedback.value = error?.response?.data?.message || 'No se pudo cargar la información de incoterms.';
    } finally {
        isLoading.value = false;
    }
}

import { useRouter } from 'vue-router';

const router = useRouter();

function abrirNuevoIncoterm() {
    // Navegar a la pantalla de detalle vacía para crear un nuevo incoterm
    router.push('/incoterms/0');
}

function limpiarFormulario() {
    editingId.value = null;
    form.tipus_inconterm_id = tiposIncoterm.value[0]?.id ? String(tiposIncoterm.value[0].id) : '';
    form.tracking_steps_id = trackingSteps.value[0]?.id ? String(trackingSteps.value[0].id) : '';
    feedback.value = '';
}

function editarIncoterm(incoterm) {
    // Ir a la pantalla de detalle para editar/visualizar pasos
    router.push(`/incoterms/${incoterm.id}`);
}

async function guardarIncoterm() {
    feedback.value = '';
    isSaving.value = true;

    const payload = {
        tipus_inconterm_id: form.tipus_inconterm_id,
        tracking_steps_id: form.tracking_steps_id,
    };

    try {
        if (modoEdicion.value) {
            const { data } = await api.put(`/incoterms/${editingId.value}`, payload);
            const updated = data?.data || data;

            incoterms.value = incoterms.value.map((item) => (
                item.id === editingId.value
                    ? { ...updated, label: construirEtiquetaIncoterm(updated) }
                    : item
            ));

            feedback.value = data?.message || 'Incoterm actualizado correctamente.';
        } else {
            const { data } = await api.post('/incoterms', payload);
            const created = data?.data || data;

            incoterms.value = [{ ...created, label: construirEtiquetaIncoterm(created) }, ...incoterms.value];
            feedback.value = data?.message || 'Incoterm creado correctamente.';
        }

        limpiarFormulario();
        await cargarDatos();
    } catch (error) {
        feedback.value =
            error?.response?.data?.message ||
            Object.values(error?.response?.data?.errors || {}).flat()?.[0] ||
            error?.message ||
            'No se pudo guardar el incoterm.';
    } finally {
        isSaving.value = false;
    }
}

async function eliminarIncoterm(id) {
    const confirmar = window.confirm('¿Quieres eliminar este incoterm?');

    if (!confirmar) {
        return;
    }

    feedback.value = '';

    const previousIncoterms = [...incoterms.value];
    incoterms.value = incoterms.value.filter((item) => item.id !== id);

    try {
        await api.delete(`/incoterms/${id}`);
        feedback.value = 'Incoterm eliminado correctamente.';
    } catch (error) {
        incoterms.value = previousIncoterms;
        feedback.value = error?.response?.data?.message || 'No se pudo eliminar el incoterm.';
    }
}

onMounted(async () => {
    await cargarDatos();
    if (!editingId.value) {
        limpiarFormulario();
    }
});
</script>

<style lang="scss" scoped>
.gestion-page {
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.16), transparent 28%),
        radial-gradient(circle at right 20%, rgba(59, 130, 246, 0.12), transparent 32%),
        #eef4fb;
}

.gestion-layout {
    width: min(1280px, calc(100% - 32px));
    margin: 0 auto;
    padding: 28px 0 40px;
}

.gestion-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}

.gestion-header h1 {
    margin: 0;
    font-size: clamp(1.7rem, 2.2vw, 2.35rem);
    color: #10243f;
}

.gestion-header p {
    margin: 6px 0 0;
    color: #5b6f88;
}

.gestion-content {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 20px;
    align-items: start;
}

.listado-panel,
.form-panel {
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #d8e3ef;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(16, 36, 63, 0.08);
    backdrop-filter: blur(8px);
}

.listado-panel {
    padding: 18px;
}

.listado-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.listado-top h2,
.form-panel h2 {
    margin: 0;
    color: #152b47;
}

.listado-total {
    font-size: 0.9rem;
    color: #6b7d93;
}

.estado-vacio {
    display: grid;
    place-items: center;
    min-height: 220px;
    text-align: center;
    color: #6b7d93;
}

.estado-vacio--loading {
    gap: 12px;
}

.spinner {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 3px solid #d6e2f0;
    border-top-color: #0ea5e9;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.incoterm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 14px;
}

.incoterm-card {
    padding: 16px;
    border: 1px solid #e2eaf3;
    border-radius: 18px;
    background: linear-gradient(180deg, #ffffff 0%, #f9fbfd 100%);
}

.incoterm-card__header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 14px;
}

.card-kicker {
    margin: 0 0 4px;
    font-size: 0.78rem;
    font-weight: 700;
    color: #6b7d93;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.incoterm-card h3 {
    margin: 0;
    color: #10243f;
    font-size: 1.05rem;
}

.card-badge {
    padding: 6px 10px;
    border-radius: 999px;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
}

.incoterm-meta {
    display: grid;
    gap: 12px;
    margin: 0;
}

.incoterm-meta dt {
    font-size: 0.78rem;
    color: #6b7d93;
    margin-bottom: 2px;
}

.incoterm-meta dd {
    margin: 0;
    color: #16304f;
    font-weight: 600;
}

.card-actions {
    display: flex;
    gap: 10px;
    margin-top: 16px;
}

.action-btn {
    flex: 1;
    border: 0;
    border-radius: 10px;
    padding: 0.7rem 1rem;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
}

.action-btn--edit {
    background: #dbeafe;
    color: #1d4ed8;
}

.action-btn--delete {
    background: #fee2e2;
    color: #b91c1c;
}

.form-panel {
    position: sticky;
    top: 16px;
    padding: 18px;
}

.form-help {
    margin: 8px 0 18px;
    color: #65758a;
    font-size: 0.93rem;
}

.incoterm-form {
    display: grid;
    gap: 14px;
}

.field {
    display: grid;
    gap: 6px;
}

.field label {
    font-size: 0.9rem;
    font-weight: 700;
    color: #25415f;
}

.form-select {
    width: 100%;
    min-height: 42px;
    border: 1px solid #cfdbe8;
    border-radius: 12px;
    padding: 0.65rem 0.85rem;
    font-size: 0.95rem;
    color: #10243f;
    background: #fff;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 4px;
}

.secondary-btn {
    flex: 1;
    border: 1px solid #cfdbe8;
    border-radius: 12px;
    background: #fff;
    color: #29435f;
    font-weight: 700;
    cursor: pointer;
}

.form-feedback {
    margin: 0;
    font-size: 0.9rem;
    color: #36516f;
}

@media (max-width: 980px) {
    .gestion-header,
    .gestion-content {
        grid-template-columns: 1fr;
        display: grid;
    }

    .form-panel {
        position: static;
    }
}

@media (max-width: 640px) {
    .gestion-layout {
        width: min(100% - 20px, 1280px);
        padding-top: 18px;
    }

    .card-actions,
    .form-actions {
        flex-direction: column;
    }

    .gestion-header {
        gap: 12px;
    }
}
</style>
