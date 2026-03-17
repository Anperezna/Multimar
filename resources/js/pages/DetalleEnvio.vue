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
                <button type="button" class="btn btn--primary">QR</button>
                <button type="button" class="btn">Descargar Documentos</button>
                <button type="button" class="btn btn--primary">Guardar Cambios</button>
            </div>
        </header>

        <div class="detail-grid">
            <div class="left-column">
                <article class="panel">
                    <header class="panel__title">1. Informacion Mercancia</header>
                    <div class="panel__body">
                        <div class="field full">
                            <label>Nombre de la mercancia</label>
                            <input :value="envio.description" type="text" readonly />
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label>Tipo de mercancia</label>
                                <input value="General" type="text" readonly />
                            </div>
                            <div class="field">
                                <label>Tipo de contenedor</label>
                                <input value="Contenedor" type="text" readonly />
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field">
                                <label>Peso bruto (kg)</label>
                                <input value="14500" type="text" readonly />
                            </div>
                            <div class="field">
                                <label>Volumen (M³)</label>
                                <input value="32.5" type="text" readonly />
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
                                <input value="FOB - Free On Board" type="text" readonly />
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
                                <input value="Shenzhen, China" type="text" readonly />
                            </div>
                            <div class="field">
                                <label>Destino</label>
                                <input value="Valencia, Espana" type="text" readonly />
                            </div>
                        </div>

                        <div class="field full">
                            <label>Representante de Venta</label>
                            <input value="Carlos Mendoza (Operador Logistico)" type="text" readonly />
                        </div>
                    </div>
                </article>

                <article class="panel timeline-panel">
                    <header class="panel__title">Seguimiento del Envio</header>
                    <ul class="timeline">
                        <li v-for="step in tracking" :key="step.title" :class="`is-${step.state}`">
                            <span class="dot"></span>
                            <div>
                                <p>{{ step.title }}</p>
                                <small>{{ step.time }}</small>
                            </div>
                        </li>
                    </ul>
                </article>
            </div>

            <aside class="panel map-panel">
                <header class="panel__title">Ubicacion Actual</header>
                <div class="map-box">
                    <img src="/img/incoterm_picture.jpg" alt="Mapa de ubicacion" />
                </div>
                <div class="route-line">
                    <div>
                        <small>Origen</small>
                        <p>Shenzhen (CN)</p>
                    </div>
                    <span>🚢</span>
                    <div>
                        <small>Destino</small>
                        <p>Valencia (ES)</p>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Navbar from '@/components/Navbar.vue';

type Shipment = {
    id: number;
    code: string;
    operation: 'Importacion' | 'Exportacion';
    status: string;
    carrier: string;
    description: string;
};

const route = useRoute();
const router = useRouter();

const shipmentSeed: Shipment[] = [
    { id: 1, code: 'EXP-2024-001', operation: 'Exportacion', status: 'En Transito', carrier: 'Maersk Line', description: 'Componentes electronicos de alta precision' },
    { id: 2, code: 'IMP-2024-042', operation: 'Importacion', status: 'Completado', carrier: 'UPS', description: 'Textiles premium' },
    { id: 3, code: 'EXP-2024-003', operation: 'Exportacion', status: 'En Aduana', carrier: 'DHL', description: 'Alimentacion refrigerada' },
    { id: 4, code: 'IMP-2024-015', operation: 'Importacion', status: 'Pendiente', carrier: 'FedEx', description: 'Maquinaria industrial' },
    { id: 5, code: 'EXP-2024-007', operation: 'Exportacion', status: 'En Transito', carrier: 'Maersk', description: 'Piezas de automocion' },
    { id: 6, code: 'IMP-2024-023', operation: 'Importacion', status: 'Completado', carrier: 'SEUR', description: 'Insumos quimicos controlados' },
    { id: 7, code: 'EXP-2024-011', operation: 'Exportacion', status: 'Cancelado', carrier: 'UPS', description: 'Carga farmaceutica' },
    { id: 8, code: 'IMP-2024-031', operation: 'Importacion', status: 'En Transito', carrier: 'DHL', description: 'Electronica de consumo' },
    { id: 9, code: 'EXP-2024-061', operation: 'Exportacion', status: 'Pendiente', carrier: 'MSC', description: 'Madera tratada' },
];

const envio = computed(() => {
    const id = Number(route.params.id);
    return (
        shipmentSeed.find((item) => item.id === id) ?? {
            id,
            code: `ENV-${String(id).padStart(3, '0')}`,
            operation: 'Exportacion',
            status: 'En Transito',
            carrier: 'Por definir',
            description: 'Descripcion pendiente',
        }
    );
});

const tracking = [
    { title: 'Preparacion de mercaderia', time: '12 Oct, 08:30', state: 'done' },
    { title: 'Transporte interior origen', time: '13 Oct, 14:15', state: 'done' },
    { title: 'Terminal / Puerto origen', time: '14 Oct, 09:00', state: 'done' },
    { title: 'Carga a bordo', time: '15 Oct, 11:15', state: 'done' },
    { title: 'Transporte maritimo', time: 'En curso', state: 'active' },
    { title: 'Puerto destino', time: 'Pendiente', state: 'pending' },
    { title: 'Aduana importacion', time: 'Pendiente', state: 'pending' },
    { title: 'Transporte interior destino', time: 'Pendiente', state: 'pending' },
    { title: 'Entrega final', time: 'Pendiente', state: 'pending' },
];
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
