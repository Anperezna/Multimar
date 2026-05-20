<template>
    <div class="detalle-page">
        <Navbar />

        <main class="detalle-layout">
            <header class="detalle-header">
                <Botones class="back-btn" @click="volverListado">Volver</Botones>
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

                    <div v-if="sinTrackingSteps">
                        <p>No hay pasos de seguimiento disponibles.</p>
                    </div>

                    <ul v-else>
                        <!-- Render con v-for: un item por tracking step -->
                        <li v-for="step in trackingSteps" :key="step.id" class="paso-item">
                            <div class="paso-info">
                                <span class="paso-orden">{{ step.ordre }}</span>
                                <span class="paso-nombre">{{ step.nom }}</span>
                            </div>
                            <div class="paso-actions">
                                <Botones
                                    class="tertiary-btn"
                                    type="button"
                                    @click="abrirModalEditarPaso(step)"
                                    :disabled="isUpdating"
                                >
                                    Editar
                                </Botones>
                                <Botones
                                    class="danger-btn"
                                    type="button"
                                    @click="eliminarPaso(step)"
                                    :disabled="isDeletingId === step.id"
                                >
                                    {{ isDeletingId === step.id ? 'Eliminando...' : 'Eliminar' }}
                                </Botones>
                            </div>
                        </li>
                    </ul>

                    <div class="pasos-actions">
                        <Botones class="primary-btn" @click="abrirModalNuevoPaso" :disabled="isCreating">
                            {{ isCreating ? 'Creando...' : '+ Añadir paso' }}
                        </Botones>
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
                        <Input v-model="nuevoPaso.nom" inputClass="field-input" required />
                    </div>
                    <div class="field">
                        <label>Orden</label>
                        <Input type="number" v-model.number="nuevoPaso.ordre" inputClass="field-input" required />
                    </div>

                    <div class="modal-actions">
                        <Botones type="button" class="secondary-btn" @click="cerrarModal">Cancelar</Botones>
                        <Botones type="submit" class="primary-btn">Crear paso</Botones>
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
                        <Input v-model="pasoEditando.nom" inputClass="field-input" required />
                    </div>
                    <div class="field">
                        <label>Orden</label>
                        <Input type="number" v-model.number="pasoEditando.ordre" inputClass="field-input" required />
                    </div>

                    <div class="modal-actions">
                        <Botones type="button" class="secondary-btn" @click="cerrarModalEditar">Cancelar</Botones>
                        <Botones type="submit" class="primary-btn" :disabled="isUpdating">
                            {{ isUpdating ? 'Guardando...' : 'Guardar cambios' }}
                        </Botones>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Navbar from '@/components/Navbar.vue';
import Botones from '@/components/Botones.vue';
import Input from '@/components/Input.vue';
import api from '@/lib/api';

const route = useRoute();
const router = useRouter();
const incotermId = Number(route.params.id);

const incoterm = ref({});
const trackingSteps = ref([]);
const sinTrackingSteps = computed(() => trackingSteps.value.length === 0);
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
        await api.put(`/incoterms/${incotermId}/principal-step`, {
            tracking_steps_id: created.id,
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
    background: #f5f5f5;
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
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 10px 16px;
    cursor: pointer;
    font-size: 1rem;
    color: #10243f;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: none;

    &:hover {
        background: rgba(255, 255, 255, 0.98);
        border-color: #0091d5;
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
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
    background: #0091d5;
    color: white;
    border: 1px solid #0091d5;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: none;

    &:hover:not(:disabled) {
        background: #0078b1;
    }

    &:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
}

.secondary-btn {
    background: #fff;
    color: #10243f;
    border: 1px solid #e5e7eb;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
        background: rgba(255, 255, 255, 0.98);
        border-color: #0091d5;
    }
}

.tertiary-btn {
    background: #fff;
    color: #1b2a4a;
    border: 1px solid #e5e7eb;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover:not(:disabled) {
        border-color: #0091d5;
        box-shadow: none;
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

.field-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 1rem;
    color: #10243f;
    transition: all 0.2s ease;
    box-sizing: border-box;

    &:focus {
        outline: none;
        border-color: #0091d5;
        box-shadow: 0 0 0 3px rgba(0, 145, 213, 0.1);
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
