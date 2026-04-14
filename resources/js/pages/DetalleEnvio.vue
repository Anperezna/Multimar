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
                <button type="button" class="btn" v-on:click="generateQR">QR</button>
                <button type="button" class="btn">Descargar Documentos</button>
                <button type="button" class="btn btn--danger" :disabled="isDeleting || isAccepting" @click="deleteOferta">
                    {{ isDeleting ? 'Cancelando...' : 'Cancelar oferta' }}
                </button>
                <button type="button" class="btn btn--success" :disabled="isDeleting || isAccepting" @click="acceptOferta">
                    {{ isAccepting ? 'Aceptando...' : 'Aceptar oferta' }}
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
                            <Input :modelValue="envio.mercanciaNombre || 'No disponible'" type="text" inputClass="detail-input" readonly />
                        </div>

                        <div class="field full">
                            <label>Comentarios de la oferta</label>
                            <Input :modelValue="envio.description || 'No disponible'" type="text" inputClass="detail-input" readonly />
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label>Tipo de mercancia</label>
                                <Input :modelValue="envio.tipusCarregaNom || 'No disponible'" type="text" inputClass="detail-input" readonly />
                            </div>
                            <div class="field">
                                <label>Tipo de contenedor</label>
                                <Input :modelValue="envio.tipusContenidorNom || 'No disponible'" type="text" inputClass="detail-input" readonly />
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label>Peso bruto (kg)</label>
                                <Input :modelValue="envio.pesoBrut || 'No disponible'" type="text" inputClass="detail-input" readonly />
                            </div>
                            <div class="field">
                                <label>Volumen (M³)</label>
                                <Input :modelValue="envio.volum || 'No disponible'" type="text" inputClass="detail-input" readonly />
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
                                <Input :modelValue="envio.incotermText || 'No disponible'" type="text" inputClass="detail-input" readonly />
                            </div>
                            <a href="/incoterm" class="guide-btn">Ver guía de Incotherms</a>
                        </div>
                    </div>
                </article>

                <article class="panel">
                    <header class="panel__title">3. Informacion Transporte</header>
                    <div class="panel__body">
                        <div class="field-row">
                            <div class="field">
                                <label>Origen</label>
                                <Input :modelValue="envio.origenNom || 'No disponible'" type="text" inputClass="detail-input" readonly />
                            </div>
                            <div class="field">
                                <label>Destino</label>
                                <Input :modelValue="envio.destinoNom || 'No disponible'" type="text" inputClass="detail-input" readonly />
                            </div>
                        </div>

                        <div class="field full">
                            <label>Representante de Venta</label>
                            <Input :modelValue="envio.operadorNom || 'No disponible'" type="text" inputClass="detail-input" readonly />
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
                    
                    <div class="tracking-steps-container">
                        <div v-for="section in trackingStepSections" :key="section.title" class="tracking-step-section">
                            <small>{{ section.title }}</small>
                            <div class="tracking-steps">
                                <template v-if="canEditTracking">
                                    <button
                                        v-for="step in section.steps"
                                        :key="step.id"
                                        type="button"
                                        class="tracking-step"
                                        :class="{ 'tracking-step--active': step.id === currentTrackingStepId }"
                                        :disabled="isUpdatingTracking"
                                        @click="updateTrackingStep(step.id)"
                                    >
                                        {{ step.nom }}
                                    </button>
                                </template>
                                <template v-else>
                                    <span
                                        v-for="step in section.steps"
                                        :key="step.id"
                                        class="tracking-step"
                                        :class="{ 'tracking-step--active': step.id === currentTrackingStepId }"
                                    >
                                        {{ step.nom }}
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <div v-if="showQrDialog" class="qr-overlay" @click.self="closeQrDialog">
            <div class="qr-dialog">
                <h3>QR con datos del envio</h3>
                <p class="qr-hint">El QR contiene JSON para que la app móvil gestione el cambio de estado.</p>

                <vue-qr
                    :text="qrPayloadText"
                    :size="220"
                    :margin="10"
                    colorDark="#000000"
                    colorLight="#ffffff"
                />

                <p class="qr-url">{{ qrPayloadText }}</p>
                <button type="button" class="btn" @click="closeQrDialog">Cerrar</button>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import VueQr from 'vue-qr';
import Input from '@/components/Input.vue';
import Navbar from '@/components/Navbar.vue';
import api from '@/lib/api';

type QrStatusPayload = {
    current_status: string;
    next_status: string;
    oferta_id: number | null;
    solicitud_id: number | null;
    generated_at: string;
};

type TrackingStepApi = {
    id: number;
    nom: string;
    ordre: number;
};

type OfertaDetalleApi = {
    id: number | string;
    ofertaId?: number | null;
    solicitudId?: number | null;
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
    trackingStepId?: number | null;
};

const route = useRoute();
const router = useRouter();
const oferta = ref<OfertaDetalleApi | null>(null);
const entityKind = ref<'Oferta' | 'Solicitud'>('Oferta');
const isAccepting = ref(false);
const isDeleting = ref(false);
const showQrDialog = ref(false);
const qrPayloadText = ref('');
const trackingSteps = ref<TrackingStepApi[]>([]);
const canEditTracking = ref(false);
const isUpdatingTracking = ref(false);

const currentTrackingStepId = computed(() => {
    return oferta.value?.trackingStepId ?? trackingSteps.value[0]?.id ?? null;
});

const trackingStepSections = computed(() => {
    return [
        {
            title: 'Proceso/Previo',
            steps: trackingSteps.value.filter((step) => step.ordre <= 5),
        },
        {
            title: 'Transporte',
            steps: trackingSteps.value.filter((step) => step.ordre > 5 && step.ordre <= 10),
        },
        {
            title: 'Cliente',
            steps: trackingSteps.value.filter((step) => step.ordre > 10),
        },
    ].filter((section) => section.steps.length > 0);
});

const loadOferta = async () => {
    const id = Number(route.params.id);
    const preferredKind = String(route.query.kind || '').toLowerCase();

    if (preferredKind === 'solicitud') {
        try {
            const { data: solicitudData } = await api.get(`/solicitudes/${id}`);
            oferta.value = solicitudData;
            entityKind.value = 'Solicitud';
            return;
        } catch (error) {
            // fallback below
        }
    }

    if (preferredKind === 'oferta') {
        try {
            const { data: ofertaData } = await api.get(`/ofertes/${id}`);
            oferta.value = ofertaData;
            entityKind.value = 'Oferta';
            return;
        } catch (error) {
            // fallback below
        }
    }

    try {
        const { data: ofertaData } = await api.get(`/ofertes/${id}`);
        oferta.value = ofertaData;
        entityKind.value = 'Oferta';
    } catch (error) {
        try {
            const { data: solicitudData } = await api.get(`/solicitudes/${id}`);
            oferta.value = solicitudData;
            entityKind.value = 'Solicitud';
        } catch (fallbackError) {
            console.error('Error cargando detalle de envio:', fallbackError);
        }
    }
};

const normalizeStatusLabel = (status?: string | null): string => {
    const value = (status || '').toLowerCase();

    if (value.includes('accept') || value.includes('acept')) return 'Aceptada';
    if (value.includes('cancel')) return 'Cancelada';
    if (value.includes('pend')) return 'Pendiente';

    return (status || '').trim() || 'No disponible';
};

const envio = computed(() => {
    const id = Number(route.params.id);

    return {
        id: Number(oferta.value?.id ?? id),
        code: String(oferta.value?.code ?? oferta.value?.id ?? id),
        operation: oferta.value?.operation?.trim() || oferta.value?.operacio_nom?.trim() || 'No disponible',
        status: normalizeStatusLabel(oferta.value?.status || oferta.value?.estat_oferta_nom),
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

const loadUserRole = async () => {
    try {
        const { data } = await api.get('/user');
        const rol = String(data.rol || data.rol?.rol || '').toLowerCase();
        canEditTracking.value = rol === 'operador' || rol === 'admin';
    } catch (error) {
        console.error('Error cargando rol:', error);
    }
};

const loadTrackingSteps = async () => {
    try {
        const { data } = await api.get('/tracking-steps');
        trackingSteps.value = data;
    } catch (error) {
        console.error('Error cargando pasos:', error);
    }
};

const updateTrackingStep = async (stepId: number) => {
    if (!oferta.value || isUpdatingTracking.value) return;

    isUpdatingTracking.value = true;
    const currentOferta = oferta.value;
    const previousTrackingStepId = currentOferta.trackingStepId ?? null;
    oferta.value = {
        ...currentOferta,
        trackingStepId: stepId,
    };

    try {
        const id = oferta.value.id;
        const endpoint = entityKind.value === 'Oferta' 
            ? `/ofertes/${id}/tracking-step`
            : `/solicitudes/${id}/tracking-step`;

        const { data } = await api.patch(endpoint, { tracking_step_id: stepId });
        oferta.value = data;
    } catch (error) {
        oferta.value = {
            ...currentOferta,
            trackingStepId: previousTrackingStepId,
        };
        console.error('Error actualizando paso:', error);
    } finally {
        isUpdatingTracking.value = false;
    }
};

onMounted(async () => {
    await loadUserRole();
    await loadTrackingSteps();
    await loadOferta();
});

const closeQrDialog = () => {
    showQrDialog.value = false;
};

const getNextStatus = (currentStatus: string): string => {
    const normalized = currentStatus.trim().toLowerCase();

    if (normalized === 'en proceso') return 'En Transporte';
    if (normalized === 'nova') return 'En Proceso';
    if (normalized === 'aceptada') return 'En Transporte';

    return 'No definido';
};

const generateQR = () => {
    const entityId = Number(envio.value.id);

    if (!entityId || isAccepting.value || isDeleting.value) return;

    const ofertaId = entityKind.value === 'Oferta'
        ? entityId
        : Number(oferta.value?.ofertaId ?? 0) || null;

    const solicitudId = entityKind.value === 'Solicitud'
        ? entityId
        : Number(oferta.value?.solicitudId ?? 0) || null;

    const payload: QrStatusPayload = {
        current_status: envio.value.status || 'No disponible',
        next_status: getNextStatus(envio.value.status || ''),
        oferta_id: ofertaId,
        solicitud_id: solicitudId,
        generated_at: new Date().toISOString(),
    };

    qrPayloadText.value = JSON.stringify(payload);
    showQrDialog.value = true;
};

const acceptOferta = async () => {
    const id = envio.value.id;

    if (!id || isAccepting.value || isDeleting.value) return;

    isAccepting.value = true;

    try {
        if (entityKind.value === 'Solicitud') {
            const { data } = await api.post(`/solicitudes/${id}/accept`);
            const ofertaId = Number(data?.oferta_id);

            if (Number.isFinite(ofertaId)) {
                const { data: acceptedOferta } = await api.post(`/ofertes/${ofertaId}/accept`);
                oferta.value = acceptedOferta;
                entityKind.value = 'Oferta';
                await router.replace({
                    path: `/ofertas/${ofertaId}`,
                    query: { kind: 'Oferta' },
                });
            }
        } else {
            const { data: acceptedOferta } = await api.post(`/ofertes/${id}/accept`);
            oferta.value = acceptedOferta;
        }
    } catch (error) {
        console.error('Error aceptando oferta:', error);
    } finally {
        isAccepting.value = false;
    }
};

const deleteOferta = async () => {
    const id = envio.value.id;

    if (!id || isAccepting.value || isDeleting.value) return;

    const confirmed = window.confirm('¿Cancelar esta oferta?');

    if (!confirmed) {
        return;
    }

    isDeleting.value = true;

    try {
        if (entityKind.value === 'Solicitud') {
            const { data } = await api.post(`/solicitudes/${id}/accept`);
            const ofertaId = Number(data?.oferta_id);

            if (Number.isFinite(ofertaId)) {
                const { data: canceledOferta } = await api.post(`/ofertes/${ofertaId}/cancel`);
                oferta.value = canceledOferta;
                entityKind.value = 'Oferta';
                await router.replace({
                    path: `/ofertas/${ofertaId}`,
                    query: { kind: 'Oferta' },
                });
            }
        } else {
            const { data: canceledOferta } = await api.post(`/ofertes/${id}/cancel`);
            oferta.value = canceledOferta;
        }
    } catch (error) {
        console.error('Error eliminando oferta:', error);
    } finally {
        isDeleting.value = false;
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

.qr-overlay {
    position: fixed;
    inset: 0;
    background: rgba(16, 37, 63, 0.42);
    display: grid;
    place-items: center;
    z-index: 1000;
    padding: 16px;
}

.qr-dialog {
    width: min(420px, 100%);
    background: #fff;
    border-radius: 12px;
    border: 1px solid #d9e2ee;
    padding: 16px;
    display: grid;
    gap: 12px;
    justify-items: center;
    text-align: center;
}

.qr-dialog h3 {
    margin: 0;
    color: #123153;
}

.qr-hint {
    margin: 0;
    font-size: 13px;
    color: #536b86;
}

.qr-url {
    margin: 0;
    font-size: 11px;
    color: #58708c;
    word-break: break-all;
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
    flex-wrap: wrap;
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

.btn--danger {
    background: #ffffff;
    border-color: #f2b6b6;
    color: #dc3f3f;
}

.btn--success {
    background: #19a96f;
    border-color: #19a96f;
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
    text-align: center;
    align-items: center;
    display: flex;
    justify-content: center;
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

.tracking-steps-container {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e2e9f2;
}

.tracking-step-section {
    margin-bottom: 14px;
}

.tracking-steps-container small {
    color: #8ba1b8;
    font-size: 10px;
    margin-bottom: 8px;
    display: block;
}

.tracking-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 8px;
}

.tracking-step {
    background: #fff;
    border: 1px solid #222222;
    border-radius: 6px;
    padding: 8px 10px;
    font-size: 11px;
    font-weight: 500;
    color: #111111;
    cursor: pointer;
    opacity: 1;
    transition: all 0.2s ease;
}

.tracking-step:hover:not(:disabled) {
    background: #f2f2f2;
}

.tracking-step--active {
    opacity: 1;
    background: #1997d5;
    border-color: #1997d5;
    color: #ffffff;
    font-weight: 700;
}

.tracking-step:disabled {
    cursor: not-allowed;
    opacity: 1;
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
