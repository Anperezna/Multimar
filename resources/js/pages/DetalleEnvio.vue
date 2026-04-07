<template>
    <Navbar />
    <section class="detalle-page">
        <button class="back-link" type="button" @click="router.push('/home')">
            ← Volver al Dashboard
        </button>

        <header class="shipment-header">
            <div class="shipment-header__left">
                <h1>{{ envio.code }}</h1>
                <div class="badges">
                    <span class="badge badge--operation">{{ envio.operation }}</span>
                    <span class="badge badge--status">{{ envio.status }}</span>
                </div>
                <p class="carrier">Transportista: {{ envio.carrier }}</p>
            </div>

            <div class="shipment-header__actions">
                <button type="button" class="btn" :disabled="!envio.canEdit" @click="isEditing = !isEditing">
                    {{ isEditing ? 'Cancelar' : 'Editar' }}
                </button>
                <button type="button" class="btn btn--primary">QR</button>
                <button type="button" class="btn">Descargar Documentos</button>
                <button type="button" class="btn btn--primary" :disabled="!envio.canEdit || !isEditing || isSaving" @click="saveChanges">
                    {{ isSaving ? 'Guardando...' : 'Guardar Cambios' }}
                </button>
            </div>
        </header>

        <div class="detail-grid">
            <div class="left-column">
                <article class="panel">
                    <header class="panel__title">1. Informacion Mercancia</header>
                    <div class="panel__body">
                        <div class="field full">
                            <label>Nombre de la mercancia</label>
                            <Input :modelValue="envio.mercanciaNombre || 'No disponible'" type="text" inputClass="detail-input" />
                        </div>

                        <div class="field full">
                            <label>Comentarios de la oferta</label>
                            <Input v-if="isEditing" v-model="editableDescription" type="text" inputClass="detail-input" />
                            <Input v-else :modelValue="envio.description || 'No disponible'" type="text" inputClass="detail-input" />
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label>Tipo de mercancia</label>
                                <Input :modelValue="envio.tipusCarregaNom || 'No disponible'" type="text" inputClass="detail-input" />
                            </div>
                            <div class="field">
                                <label>Tipo de contenedor</label>
                                <Input :modelValue="envio.tipusContenidorNom || 'No disponible'" type="text" inputClass="detail-input" />
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label>Peso bruto (kg)</label>
                                <Input :modelValue="envio.pesoBrut || 'No disponible'" type="text" inputClass="detail-input" />
                            </div>
                            <div class="field">
                                <label>Volumen (M³)</label>
                                <Input :modelValue="envio.volum || 'No disponible'" type="text" inputClass="detail-input" />
                            </div>
                        </div>
                    </div>
                </article>

                <article class="panel">
                    <header class="panel__title">2. Incoterm</header>
                    <div class="panel__body">
                        <div class="field-row">
                            <div class="field">
                                <label>Tipo de Incoterm</label>
                                <Input :modelValue="envio.incotermText || 'No disponible'" type="text" inputClass="detail-input" />
                            </div>
                            <button type="button" class="guide-btn">Ver guia de Incoterms</button>
                        </div>
                    </div>
                </article>

                <article class="panel">
                    <header class="panel__title">3. Informacion Transporte</header>
                    <div class="panel__body">
                        <div class="field-row">
                            <div class="field">
                                <label>Origen</label>
                                <Input :modelValue="envio.origenNom || 'No disponible'" type="text" inputClass="detail-input" />
                            </div>
                            <div class="field">
                                <label>Destino</label>
                                <Input :modelValue="envio.destinoNom || 'No disponible'" type="text" inputClass="detail-input" />
                            </div>
                        </div>

                        <div class="field full">
                            <label>Representante de Venta</label>
                            <Input :modelValue="envio.operadorNom || 'No disponible'" type="text" inputClass="detail-input" />
                        </div>
                    </div>
                </article>
            </div>

            <aside class="panel map-panel">
                <header class="panel__title">Resumen de oferta</header>
                <div class="panel__body summary-list">
                    <div>
                        <small>Estado</small>
                        <p>{{ envio.status || 'No disponible' }}</p>
                    </div>
                    <div>
                        <small>Tipo de transporte</small>
                        <p>{{ envio.tipusTransportNom || 'No disponible' }}</p>
                    </div>
                    <div>
                        <small>Fecha de creación</small>
                        <p>{{ envio.dataCreacio || 'No disponible' }}</p>
                    </div>
                    <div>
                        <small>Última actualización</small>
                        <p>{{ envio.lastUpdate || 'No disponible' }}</p>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Input from '@/components/Input.vue';
import Navbar from '@/components/Navbar.vue';
import api from '@/lib/api';

type OfertaDetalleApi = {
    id: number | string;
    ofertaId?: number | null;
    solicitudId?: number | null;
    canEdit?: boolean;
    comentaris?: string | null;
    operacio_nom?: string | null;
    transportista_origen_nom?: string | null;
    estat_oferta_nom?: string | null;
    code?: string | null;
    description?: string | null;
    operation?: string | null;
    carrier?: string | null;
    status?: string | null;
    mercanciaNombre?: string | null;
    pesoBrut?: string | number | null;
    volum?: string | number | null;
    tipusTransportNom?: string | null;
    tipusContenidorNom?: string | null;
    tipusCarregaNom?: string | null;
    origenNom?: string | null;
    destinoNom?: string | null;
    operadorNom?: string | null;
    incotermText?: string | null;
    dataCreacio?: string | null;
    lastUpdate?: string | null;
};

const route = useRoute();
const router = useRouter();
const oferta = ref<OfertaDetalleApi | null>(null);
const isEditing = ref(false);
const isSaving = ref(false);
const editableDescription = ref('');

const loadOferta = async () => {
    try {
        const id = Number(route.params.id);
        const { data } = await api.get(`/ofertes/${id}`);
        oferta.value = data;
        editableDescription.value = data.description || data.comentaris || '';
    } catch (error) {
        console.error('Error cargando detalle de oferta:', error);
    }
};

const envio = computed(() => {
    const id = Number(route.params.id);
    const ofertaId = oferta.value?.ofertaId ?? null;
    const canEdit = Boolean(oferta.value?.canEdit && ofertaId);

    return {
        id: ofertaId ?? id,
        canEdit,
        code: String(oferta.value?.code ?? ofertaId ?? oferta.value?.id ?? id),
        operation: oferta.value?.operation?.trim() || oferta.value?.operacio_nom?.trim() || 'No disponible',
        status: oferta.value?.status?.trim() || oferta.value?.estat_oferta_nom?.trim() || 'No disponible',
        carrier: oferta.value?.carrier?.trim() || oferta.value?.transportista_origen_nom?.trim() || 'No disponible',
        description: oferta.value?.description?.trim() || oferta.value?.comentaris?.trim() || 'No disponible',
        mercanciaNombre: oferta.value?.mercanciaNombre?.trim() || 'No disponible',
        pesoBrut: oferta.value?.pesoBrut ?? 'No disponible',
        volum: oferta.value?.volum ?? 'No disponible',
        tipusTransportNom: oferta.value?.tipusTransportNom?.trim() || 'No disponible',
        tipusContenidorNom: oferta.value?.tipusContenidorNom?.trim() || 'No disponible',
        tipusCarregaNom: oferta.value?.tipusCarregaNom?.trim() || 'No disponible',
        origenNom: oferta.value?.origenNom?.trim() || 'No disponible',
        destinoNom: oferta.value?.destinoNom?.trim() || 'No disponible',
        operadorNom: oferta.value?.operadorNom?.trim() || 'No disponible',
        incotermText: oferta.value?.incotermText?.trim() || 'No disponible',
        dataCreacio: oferta.value?.dataCreacio?.trim() || 'No disponible',
        lastUpdate: oferta.value?.lastUpdate?.trim() || 'No disponible',
    };
});

onMounted(loadOferta);

const saveChanges = async () => {
    if (!oferta.value?.ofertaId || !oferta.value?.canEdit) return;

    isSaving.value = true;

    try {
        const id = Number(oferta.value.ofertaId);
        const { data } = await api.put(`/ofertes/${id}`, {
            comentaris: editableDescription.value,
        });

        oferta.value = data;
        editableDescription.value = data.description || data.comentaris || '';
        isEditing.value = false;
    } catch (error) {
        console.error('Error guardando oferta:', error);
    } finally {
        isSaving.value = false;
    }
};
</script>

<style scoped>
.detalle-page {
    background: #eef2f7;
    min-height: 100vh;
    padding: 24px 56px;
    color: #10253f;
}

.back-link {
    border: none;
    background: transparent;
    color: #5c708c;
    font-size: 13px;
    margin-bottom: 16px;
    cursor: pointer;
}

.shipment-header {
    background: #fff;
    border: 1px solid #d9e2ee;
    border-radius: 10px;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.shipment-header__left h1 {
    margin: 0;
    font-size: 28px;
}

.badges {
    display: flex;
    gap: 8px;
    margin: 8px 0;
}

.badge {
    border-radius: 999px;
    padding: 3px 10px;
    font-size: 11px;
    font-weight: 700;
}

.badge--operation { background: #ece9ff; color: #5840d9; }
.badge--status { background: #d9f3ff; color: #0a85be; }

.carrier {
    margin: 0;
    color: #617790;
    font-size: 13px;
}

.shipment-header__actions {
    display: flex;
    gap: 8px;
}

.btn {
    border: 1px solid #d6e0ec;
    background: #fff;
    color: #32506f;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.btn--primary {
    background: #1997d5;
    border-color: #1997d5;
    color: #fff;
}

.detail-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 1.3fr);
    gap: 14px;
}

.left-column {
    display: grid;
    gap: 12px;
}

.panel {
    background: #fff;
    border: 1px solid #d9e2ee;
    border-radius: 10px;
    overflow: hidden;
}

.panel__title {
    padding: 14px 16px;
    border-bottom: 1px solid #e2e9f2;
    font-size: 13px;
    font-weight: 700;
}

.panel__body {
    padding: 14px 16px;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 10px;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field.full {
    margin-bottom: 10px;
}

.field label {
    font-size: 11px;
    color: #4e637d;
}

.field input {
    height: 38px;
    border: 1px solid #d6e0ec;
    border-radius: 7px;
    padding: 0 10px;
    color: #1f3a59;
    background: #fdfefe;
    font-size: 13px;
}

:deep(.detail-input) {
    width: 100%;
    height: 38px;
    border: 1px solid #d6e0ec;
    border-radius: 7px;
    padding: 0 10px;
    color: #1f3a59;
    background: #fdfefe;
    font-size: 13px;
    box-sizing: border-box;
}

.summary-list {
    display: grid;
    gap: 12px;
}

.summary-list small {
    display: block;
    color: #8ba1b8;
    font-size: 10px;
    margin-bottom: 4px;
}

.summary-list p {
    margin: 0;
    font-size: 13px;
    color: #1f3a59;
    font-weight: 600;
}

.guide-btn {
    border: none;
    height: 38px;
    border-radius: 7px;
    background: #e9f5fc;
    color: #0b8ecd;
    font-weight: 600;
    font-size: 12px;
    padding: 0 12px;
    margin-top: 22px;
    cursor: pointer;
}

.map-panel .map-box {
    height: 300px;
    background: #dde6ef;
}

.map-panel img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.7;
}

.route-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    border-top: 1px solid #e2e9f2;
}

.route-line small {
    color: #8ba1b8;
    font-size: 10px;
}

.route-line p {
    margin: 2px 0 0;
    font-size: 12px;
}

.timeline-panel .panel__title {
    color: #0e89ca;
}

.timeline {
    list-style: none;
    margin: 0;
    padding: 14px 16px;
    display: grid;
    gap: 14px;
}

.timeline li {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.timeline .dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-top: 4px;
    border: 2px solid #b6c6d7;
}

.timeline li p {
    margin: 0;
    font-size: 12px;
}

.timeline li small {
    color: #95a8bb;
    font-size: 10px;
}

.timeline .is-done .dot {
    background: #1daf73;
    border-color: #1daf73;
}

.timeline .is-active .dot {
    background: #1f9be1;
    border-color: #1f9be1;
}

@media (max-width: 1100px) {
    .detalle-page {
        padding: 20px;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .shipment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .field-row {
        grid-template-columns: 1fr;
    }
}
</style>
