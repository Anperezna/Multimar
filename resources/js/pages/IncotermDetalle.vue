<template>
    <div class="detalle-page">
        <Navbar />

        <main class="detalle-layout">
            <header class="detalle-header">
                <button class="back-btn" @click="volverListado">← Volver</button>
                <h1>Detalle del Incoterm</h1>
            </header>

            <section class="detalle-content" v-if="isLoading">
                <p>Cargando...</p>
            </section>

            <section class="detalle-content" v-else>
                <div class="incoterm-info">
                    <h2>Incoterm #{{ incoterm.id }}</h2>
                    <p><strong>Tipo:</strong> {{ incoterm.tipus?.label || incoterm.tipusIncoterm?.label || 'Sin tipo' }}</p>
                </div>

                <div class="pasos">
                    <h3>Pasos de seguimiento</h3>

                    <div v-if="trackingSteps.length === 0">
                        <p>No hay pasos de seguimiento disponibles.</p>
                    </div>

                    <ul v-else>
                        <li v-for="step in trackingSteps" :key="step.id" class="paso-item">
                            <div class="paso-info">
                                <span class="paso-orden">{{ step.ordre }}</span>
                                <span class="paso-nombre">{{ step.nom }}</span>
                            </div>
                            <div class="paso-actions">
                                <button
                                    class="tertiary-btn"
                                    type="button"
                                    @click="abrirModalEditarPaso(step)"
                                    :disabled="isUpdating"
                                >
                                    Editar
                                </button>
                                <button
                                    class="danger-btn"
                                    type="button"
                                    @click="eliminarPaso(step)"
                                    :disabled="isDeletingId === step.id"
                                >
                                    {{ isDeletingId === step.id ? 'Eliminando...' : 'Eliminar' }}
                                </button>
                            </div>
                        </li>
                    </ul>

                    <div class="pasos-actions">
                        <button class="primary-btn" @click="abrirModalNuevoPaso" :disabled="isCreating">
                            {{ isCreating ? 'Creando...' : '+ Añadir paso' }}
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <div v-if="modalOpen" class="modal-overlay">
            <div class="modal">
                <h4>Añadir nuevo paso</h4>
                <form @submit.prevent="crearPaso">
                    <div class="field">
                        <label>Nombre</label>
                        <input v-model="nuevoPaso.nom" required />
                    </div>
                    <div class="field">
                        <label>Orden</label>
                        <input type="number" v-model.number="nuevoPaso.ordre" required />
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="secondary-btn" @click="cerrarModal">Cancelar</button>
                        <button type="submit" class="primary-btn">Crear paso</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="editModalOpen" class="modal-overlay">
            <div class="modal">
                <h4>Editar paso</h4>
                <form @submit.prevent="actualizarPaso">
                    <div class="field">
                        <label>Nombre</label>
                        <input v-model="pasoEditando.nom" required />
                    </div>
                    <div class="field">
                        <label>Orden</label>
                        <input type="number" v-model.number="pasoEditando.ordre" required />
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="secondary-btn" @click="cerrarModalEditar">Cancelar</button>
                        <button type="submit" class="primary-btn" :disabled="isUpdating">
                            {{ isUpdating ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Navbar from '@/components/Navbar.vue';
import api from '@/lib/api';

const route = useRoute();
const router = useRouter();
const incotermId = Number(route.params.id);

const incoterm = ref({});
const trackingSteps = ref([]);
const isLoading = ref(true);
const isCreating = ref(false);
const isUpdating = ref(false);
const isDeletingId = ref(null);
const modalOpen = ref(false);
const editModalOpen = ref(false);
const nuevoPaso = ref({ nom: '', ordre: 1 });
const pasoEditando = ref({ id: null, nom: '', ordre: 1 });

function volverListado() {
    router.push('/incoterms/editar');
}

function normalizarTrackingSteps(steps) {
    const seen = new Set();
    const sorted = [...(steps || [])].sort((a, b) => {
        if (a.ordre === b.ordre) {
            return (a.id ?? 0) - (b.id ?? 0);
        }
        return (a.ordre ?? 0) - (b.ordre ?? 0);
    });

    const deduped = sorted.filter((step) => {
        const key = `${step.ordre ?? ''}`.trim();
        if (seen.has(key)) {
            return false;
        }
        seen.add(key);
        return true;
    });

    return deduped;
}

async function cargarIncoterm() {
    isLoading.value = true;

    try {
        const { data } = await api.get(`/incoterms/${incotermId}`);

        incoterm.value = data || {};
        trackingSteps.value = normalizarTrackingSteps(data?.trackingSteps || []);
    } catch (err) {
        console.error(err);
        incoterm.value = {};
        trackingSteps.value = [];
    } finally {
        isLoading.value = false;
    }
}

function abrirModalNuevoPaso() {
    modalOpen.value = true;
    // Calcular el siguiente orden basándose en los pasos actuales
    const maxOrdre = trackingSteps.value.length > 0 
        ? Math.max(...trackingSteps.value.map(s => s.ordre)) 
        : 0;
    nuevoPaso.value = { nom: '', ordre: maxOrdre + 1 };
}

function cerrarModal() {
    modalOpen.value = false;
    nuevoPaso.value = { nom: '', ordre: 1 };
}

function abrirModalEditarPaso(step) {
    pasoEditando.value = { id: step.id, nom: step.nom, ordre: step.ordre };
    editModalOpen.value = true;
}

function cerrarModalEditar() {
    editModalOpen.value = false;
    pasoEditando.value = { id: null, nom: '', ordre: 1 };
}

async function crearPaso() {
    isCreating.value = true;

    try {
        const { data } = await api.post('/tracking-steps', {
            ...nuevoPaso.value,
            incoterm_id: incotermId,
        });
        const created = data;

        // Asociar el nuevo paso al incoterm actual
        await api.put(`/incoterms/${incotermId}`, {
            tracking_steps_id: created.id,
            tipus_inconterm_id: incoterm.value.tipus_inconterm_id || incoterm.value.tipus?.id || incoterm.value.tipusIncoterm?.id,
        });

        // Recargar datos
        await cargarIncoterm();
        cerrarModal();
    } catch (err) {
        console.error(err);
        alert('No se pudo crear el paso.');
    } finally {
        isCreating.value = false;
    }
}

async function actualizarPaso() {
    if (!pasoEditando.value.id) {
        return;
    }

    isUpdating.value = true;

    try {
        await api.put(`/tracking-steps/${pasoEditando.value.id}`, {
            nom: pasoEditando.value.nom,
            ordre: pasoEditando.value.ordre,
        });

        await cargarIncoterm();
        cerrarModalEditar();
    } catch (err) {
        console.error(err);
        alert('No se pudo actualizar el paso.');
    } finally {
        isUpdating.value = false;
    }
}

async function eliminarPaso(step) {
    const confirmar = window.confirm('¿Quieres eliminar este paso?');
    if (!confirmar) {
        return;
    }

    isDeletingId.value = step.id;

    try {
        await api.delete(`/tracking-steps/${step.id}`);
        await cargarIncoterm();
    } catch (err) {
        console.error(err);
        alert('No se pudo eliminar el paso.');
    } finally {
        isDeletingId.value = null;
    }
}

onMounted(() => {
    cargarIncoterm();
});
</script>

<style lang="scss" scoped>
.detalle-page {
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(14, 165, 233, 0.16), transparent 28%),
        radial-gradient(circle at right 20%, rgba(59, 130, 246, 0.12), transparent 32%),
        #eef4fb;
}

.detalle-layout {
    width: min(1280px, calc(100% - 32px));
    margin: 0 auto;
    padding: 28px 0 40px;
}

.detalle-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
}

.back-btn {
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #d8e3ef;
    border-radius: 12px;
    padding: 10px 16px;
    cursor: pointer;
    font-size: 1rem;
    color: #10243f;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(16, 36, 63, 0.08);

    &:hover {
        background: rgba(255, 255, 255, 0.98);
        border-color: #0ea5e9;
        box-shadow: 0 6px 16px rgba(14, 165, 233, 0.12);
    }

    &:active {
        transform: scale(0.98);
    }
}

.detalle-header h1 {
    margin: 0;
    font-size: clamp(1.7rem, 2vw, 2.2rem);
    color: #10243f;
    flex: 1;
}

.detalle-content {
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid #d8e3ef;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(16, 36, 63, 0.08);
    backdrop-filter: blur(8px);
    padding: 28px;
}

.incoterm-info {
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e2eaf3;
}

.incoterm-info h2 {
    margin: 0 0 12px 0;
    font-size: 1.6rem;
    color: #152b47;
}

.incoterm-info p {
    margin: 8px 0;
    color: #5b6f88;
    font-size: 1rem;
}

.pasos {
    margin-top: 0;
}

.pasos h3 {
    margin: 0 0 16px 0;
    font-size: 1.2rem;
    color: #152b47;
}

.pasos ul {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
    background: rgba(14, 165, 233, 0.05);
    border-radius: 12px;
    padding: 16px;
    border: 1px solid #d6e2f0;
}

.pasos ul li {
    padding: 12px 0;
    color: #10243f;
    border-bottom: 1px solid #e2eaf3;

    &:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    &:first-child {
        padding-top: 0;
    }
}

.paso-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.paso-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.paso-orden {
    min-width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(14, 165, 233, 0.12);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #0b5f87;
}

.paso-nombre {
    font-weight: 500;
}

.paso-actions {
    display: flex;
    gap: 8px;
}

.pasos-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.primary-btn {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);

    &:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
    }

    &:active:not(:disabled) {
        transform: translateY(0);
    }

    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
}

.secondary-btn {
    background: rgba(255, 255, 255, 0.92);
    color: #10243f;
    border: 1px solid #d8e3ef;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
        background: rgba(255, 255, 255, 0.98);
        border-color: #0ea5e9;
    }
}

.tertiary-btn {
    background: rgba(255, 255, 255, 0.95);
    color: #0b5f87;
    border: 1px solid #cfe4f5;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover:not(:disabled) {
        border-color: #0ea5e9;
        box-shadow: 0 4px 10px rgba(14, 165, 233, 0.12);
    }

    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
}

.danger-btn {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover:not(:disabled) {
        background: #fecaca;
    }

    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
}

.modal-overlay {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 50;
}

.modal {
    background: white;
    padding: 28px;
    border-radius: 16px;
    width: 90%;
    max-width: 420px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal h4 {
    margin: 0 0 20px 0;
    font-size: 1.3rem;
    color: #152b47;
}

.field {
    margin-bottom: 18px;
}

.field label {
    display: block;
    margin-bottom: 8px;
    color: #152b47;
    font-weight: 600;
    font-size: 0.95rem;
}

.field input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d8e3ef;
    border-radius: 10px;
    font-size: 1rem;
    color: #10243f;
    transition: all 0.2s ease;
    box-sizing: border-box;

    &:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }

    &:hover {
        border-color: #b8d4e8;
    }
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}
</style>
