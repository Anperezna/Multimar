<template>
    <Navbar/>
    <section class="dashboard">
        <header class="dashboard__header">
            <h1>Dashboard General</h1>
            <p>Resumen de tus operaciones de importacion y exportacion.</p>
            <p v-if="apiError" class="api-error">{{ apiError }}</p>
        </header>

        <div class="summary-grid">
            <article
                v-for="card in summaryCards"
                :key="card.id"
                class="summary-card"
            >
                <div class="summary-card__top">
                    <div class="summary-card__icon" :class="`is-${card.tone}`">
                        {{ card.icon }}
                    </div>
                    <span class="summary-card__change" :class="`is-${card.tone}`">
                        {{ card.change }}
                    </span>
                </div>
                <p class="summary-card__label">{{ card.label }}</p>
                <p class="summary-card__value">{{ card.value }}</p>
            </article>
        </div>

        <section class="table-panel">
            <div class="table-panel__top">
                <h2>Envios Recientes</h2>

                <div class="table-actions">
                    <div class="search-wrap">
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Buscar ID, descripcion..."
                            aria-label="Buscar envios"
                        />
                    </div>
                    <button class="btn-filter" type="button">Filtros</button>
                </div>
            </div>

            <div class="table-wrap">
                <div v-if="isLoading" class="table-loading">
                    <span class="table-loading__spinner" aria-label="Cargando envios"></span>
                    <p>Cargando envios...</p>
                </div>

                <table v-else>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Descripcion</th>
                            <th>Estado</th>
                            <th>Transportista</th>
                            <th>Actualizaciones</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="shipment in paginatedShipments" :key="shipment.rowKey">
                            <td class="mono">{{ shipment.code }}</td>
                            <td>
                                <span class="type-badge" :class="`is-${shipment.kindTone}`">
                                    {{ shipment.kind }}
                                </span>
                            </td>
                            <td>
                                <p class="desc-title">{{ shipment.description }}</p>
                                <p class="desc-type">{{ shipment.operation }}</p>
                            </td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="`is-${shipment.statusTone}`"
                                >
                                    {{ shipment.status }}
                                </span>
                            </td>
                            <td>{{ shipment.carrier }}</td>
                            <td>{{ shipment.lastUpdate }}</td>
                            <td>
                                <button
                                    type="button"
                                    class="detail-btn"
                                    :aria-label="shipment.kind === 'Solicitud' ? 'Ver solicitud y decidir accion' : 'Ver detalles del envio'"
                                    @click="openShipmentDetail(shipment)"
                                >
                                    Ver
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer class="table-panel__footer">
                <p>
                    Mostrando
                    <strong>{{ paginatedShipments.length }}</strong>
                    de
                    <strong>{{ filteredShipments.length }}</strong>
                    resultados
                </p>

                <div class="pagination">
                    <button
                        type="button"
                        class="btn-page"
                        :disabled="currentPage === 1"
                        @click="currentPage -= 1"
                    >
                        Anterior
                    </button>
                    <button
                        type="button"
                        class="btn-page"
                        :disabled="currentPage >= totalPages"
                        @click="currentPage += 1"
                    >
                        Siguiente
                    </button>
                </div>
            </footer>
        </section>
        </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import Navbar from './Navbar.vue';
import api from '../lib/api';

type StatusTone = 'info' | 'success' | 'warning' | 'danger';

type OfertaApi = {
    id: number | string;
    code: string;
    kind: 'Oferta' | 'Solicitud';
    description: string;
    operation: string;
    status: string;
    carrier: string;
    lastUpdate?: string | null;
};

interface ShipmentRow {
    id: number;
    rowKey: string;
    code: string;
    kind: 'Oferta' | 'Solicitud';
    kindTone: StatusTone;
    description: string;
    operation: string;
    status: string;
    statusTone: StatusTone;
    carrier: string;
    lastUpdate: string;
}

interface SummaryCard {
    id: string;
    label: string;
    value: string;
    change: string;
    tone: StatusTone;
    icon: string;
}

const search = ref('');
const currentPage = ref(1);
const pageSize = 8;
const router = useRouter();
const ofertas = ref<OfertaApi[]>([]);
const apiError = ref('');
const isLoading = ref(false);

const toneFromStatus = (status?: string | null): StatusTone => {
    const value = (status || '').toLowerCase();
    if (value.includes('complet') || value.includes('accept')) return 'success';
    if (value.includes('cancel') || value.includes('rebuig')) return 'danger';
    if (value.includes('pend') || value.includes('revis')) return 'warning';
    return 'info';
};

const normalizeStatusLabel = (status?: string | null): string => {
    const value = (status || '').toLowerCase();

    if (value.includes('accept')) return 'Aceptado';
    if (value.includes('cancel')) return 'Cancelado';
    if (value.includes('pend')) return 'Pendiente';

    return (status || '').trim() || 'Sin estado';
};

const shipments = computed<ShipmentRow[]>(() => {
    return ofertas.value.map((oferta) => {
        return {
            id: Number(oferta.id),
            rowKey: `${oferta.kind}-${oferta.id}`,
            code: oferta.code,
            kind: oferta.kind,
            kindTone: oferta.kind === 'Solicitud' ? 'warning' : 'info',
            description: oferta.description,
            operation: oferta.operation,
            status: normalizeStatusLabel(oferta.status),
            statusTone: toneFromStatus(oferta.status),
            carrier: oferta.carrier,
            lastUpdate: oferta.lastUpdate || '-',
        };
    });
});

const filteredShipments = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return shipments.value;

    return shipments.value.filter((item) =>
        [item.code, item.description, item.carrier, item.status]
            .join(' ')
            .toLowerCase()
            .includes(term)
    );
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredShipments.value.length / pageSize));
});

const paginatedShipments = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredShipments.value.slice(start, start + pageSize);
});

const summaryCards = computed<SummaryCard[]>(() => {
    const total = shipments.value.length;
    const accepted = shipments.value.filter((item) => item.status.toLowerCase().includes('accept')).length;
    const completed = shipments.value.filter((item) => item.status.toLowerCase().includes('complet')).length;
    const pending = shipments.value.filter((item) => item.status.toLowerCase().includes('pend')).length;

    return [
        {
            id: 'total',
            label: 'Total Envios',
            value: String(total),
            change: `${total}`,
            tone: 'info',
            icon: '[]',
        },
        {
            id: 'transit',
            label: 'Aceptadas',
            value: String(accepted),
            change: `${accepted}`,
            tone: 'info',
            icon: '=>',
        },
        {
            id: 'completed',
            label: 'Completadas',
            value: String(completed),
            change: `${completed}`,
            tone: 'success',
            icon: 'OK',
        },
        {
            id: 'pending',
            label: 'Pendientes',
            value: String(pending),
            change: `${pending}`,
            tone: 'warning',
            icon: 'O',
        },
    ];
});

const fetchBackendData = async () => {
    apiError.value = '';
    isLoading.value = true;

    try {
        const [ofertasResponse, solicitudesResponse] = await Promise.all([
            api.get('/ofertes'),
            api.get('/solicitudes'),
        ]);

        const ofertasData = Array.isArray(ofertasResponse.data) ? ofertasResponse.data : [];
        const solicitudesData = Array.isArray(solicitudesResponse.data) ? solicitudesResponse.data : [];

        ofertas.value = [...ofertasData, ...solicitudesData].sort((left, right) => Number(right.id) - Number(left.id));
    } catch (error) {
        apiError.value = 'No se pudieron cargar las ofertas y solicitudes desde backend.';
        console.error('Error Axios:', error);
    } finally {
        isLoading.value = false;
    }
};

const openShipmentDetail = async (shipment: ShipmentRow) => {
    await router.push(`/ofertas/${shipment.id}`);
};

onMounted(fetchBackendData);

watch(filteredShipments, () => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value;
    }
});

watch(search, () => {
    currentPage.value = 1;
});
</script>

<style lang="scss" scoped>
.dashboard {
    --bg-page: #edf1f6;
    --bg-card: #ffffff;
    --bg-table-head: #f3f6fa;
    --text-main: #10253f;
    --text-soft: #74839b;
    --border-soft: #d9e2ee;
    --blue: #3b82f6;
    --green: #10b981;
    --amber: #f59e0b;
    --red: #ef4444;
    --violet: #8b5cf6;

    min-height: 100vh;
    padding: 30px;
    margin-left: 50px;
    margin-right: 50px;
    color: var(--text-main);
}

.dashboard__header {
    margin-bottom: 20px;

    h1 {
        margin: 0;
        font-size: 1.9rem;
        font-weight: 700;
    }

    p {
        margin: 8px 0 0;
        color: var(--text-soft);
        font-size: 0.96rem;
    }
}

.api-error {
    margin: 10px 0 0;
    color: #dc2626;
    font-size: 0.86rem;
    font-weight: 600;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}

.summary-card {
    background: var(--bg-card);
    border: 1px solid var(--border-soft);
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 22px rgba(16, 37, 63, 0.06);

    &__top {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    &__icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-size: 0.75rem;
        font-weight: 700;
        background: #ecf2f9;
        color: #577394;
    }

    &__change {
        font-size: 0.74rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 8px;
        background: #eff5fb;
    }

    &__label {
        margin: 14px 0 6px;
        font-size: 0.82rem;
        color: var(--text-soft);
        font-weight: 600;
    }

    &__value {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1;
    }
}

.is-info {
    color: #2f6de4;
    background: #eaf2ff;
}

.is-success {
    color: #0f9f71;
    background: #e7f9f1;
}

.is-warning {
    color: #c4800a;
    background: #fef4df;
}

.is-danger {
    color: #dc2626;
    background: #feecec;
}

.is-violet {
    color: #6d28d9;
    background: #f0e8ff;
}

.table-panel {
    background: var(--bg-card);
    border: 1px solid var(--border-soft);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 22px rgba(16, 37, 63, 0.06);

    &__top {
        padding: 14px;
        border-bottom: 1px solid var(--border-soft);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;

        h2 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }
    }

    &__footer {
        border-top: 1px solid var(--border-soft);
        padding: 12px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;

        p {
            margin: 0;
            color: var(--text-soft);
            font-size: 0.83rem;
        }
    }
}

.table-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.search-wrap input {
    width: 270px;
    max-width: 100%;
    border: 1px solid var(--border-soft);
    border-radius: 9px;
    padding: 9px 12px;
    font-size: 0.86rem;
    outline: none;
    transition: border-color 0.2s ease;

    &:focus {
        border-color: #99b8e6;
    }
}

.btn-filter,
.btn-page,
.detail-btn {
    border: 1px solid var(--border-soft);
    background: #f8fbff;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #335175;
    cursor: pointer;
}

.btn-page:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.table-wrap {
    overflow-x: auto;
}

.table-loading {
    min-height: 220px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: var(--text-soft);

    &__spinner {
        width: 34px;
        height: 34px;
        border: 3px solid #d7e3f2;
        border-top-color: #3b82f6;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 780px;

    thead {
        background: var(--bg-table-head);

        th {
            text-align: left;
            padding: 12px 14px;
            color: #64748b;
            font-size: 0.76rem;
            font-weight: 700;
            border-bottom: 1px solid var(--border-soft);
        }
    }

    tbody td {
        padding: 14px;
        border-bottom: 1px solid #e8eef6;
        font-size: 0.83rem;
        color: #334a67;
        vertical-align: middle;
    }
}

.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono,
        Courier New, monospace;
    font-weight: 700;
}

.desc-title {
    margin: 0;
    font-weight: 700;
    color: #233851;
}

.desc-type {
    margin: 3px 0 0;
    font-size: 0.74rem;
    color: #91a1b7;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 5px 10px;
    font-size: 0.74rem;
    font-weight: 700;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 5px 10px;
    font-size: 0.74rem;
    font-weight: 700;
}

.pagination {
    display: flex;
    gap: 8px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 1080px) {
    .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .search-wrap input {
        width: 220px;
    }
}

@media (max-width: 720px) {
    .dashboard {
        padding: 18px;
    }

    .summary-grid {
        grid-template-columns: 1fr;
    }

    .table-panel__top,
    .table-panel__footer {
        flex-direction: column;
        align-items: stretch;
    }

    .table-actions {
        width: 100%;
    }

    .search-wrap {
        flex: 1;
    }

    .search-wrap input {
        width: 100%;
    }
}
</style>