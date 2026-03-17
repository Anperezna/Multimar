<template>
    <Navbar/>
    <section class="dashboard">
        <header class="dashboard__header">
            <h1>Dashboard General</h1>
            <p>Resumen de tus operaciones de importacion y exportacion.</p>
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
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripcion</th>
                            <th>Estado</th>
                            <th>Transportista</th>
                            <th>Actualizaciones</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="shipment in paginatedShipments" :key="shipment.id">
                            <td class="mono">{{ shipment.code }}</td>
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
                                    aria-label="Ver detalles del envio"
                                    @click="router.push({ name: 'detalle-envio', params: { id: shipment.id } })"
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
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import Navbar from './Navbar.vue';

type StatusTone = 'info' | 'success' | 'warning' | 'danger' | 'violet';

interface ShipmentRow {
    id: number;
    code: string;
    description: string;
    operation: 'Importacion' | 'Exportacion';
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
    tone: Exclude<StatusTone, 'violet'>;
    icon: string;
}

const search = ref('');
const currentPage = ref(1);
const pageSize = 8;
const router = useRouter();

// Reemplaza este ref por datos traidos de API cuando conectes backend.
const shipments = ref<ShipmentRow[]>([
    {
        id: 1,
        code: 'EXP-2024-001',
        description: 'Electronica',
        operation: 'Exportacion',
        status: 'En Transito',
        statusTone: 'info',
        carrier: 'SEUR',
        lastUpdate: 'Salida de almacen Madrid',
    },
    {
        id: 2,
        code: 'IMP-2024-042',
        description: 'Textiles',
        operation: 'Importacion',
        status: 'Completado',
        statusTone: 'success',
        carrier: 'UPS',
        lastUpdate: 'Entregado en destino',
    },
    {
        id: 3,
        code: 'EXP-2024-003',
        description: 'Alimentacion',
        operation: 'Exportacion',
        status: 'En Aduana',
        statusTone: 'violet',
        carrier: 'DHL',
        lastUpdate: 'Revision aduanera en curso',
    },
    {
        id: 4,
        code: 'IMP-2024-015',
        description: 'Maquinaria',
        operation: 'Importacion',
        status: 'Pendiente',
        statusTone: 'warning',
        carrier: 'FedEx',
        lastUpdate: 'Esperando documentacion',
    },
    {
        id: 5,
        code: 'EXP-2024-007',
        description: 'Automocion',
        operation: 'Exportacion',
        status: 'En Transito',
        statusTone: 'info',
        carrier: 'Maersk',
        lastUpdate: 'En puerto de Barcelona',
    },
    {
        id: 6,
        code: 'IMP-2024-023',
        description: 'Quimicos',
        operation: 'Importacion',
        status: 'Completado',
        statusTone: 'success',
        carrier: 'SEUR',
        lastUpdate: 'Entregado y verificado',
    },
    {
        id: 7,
        code: 'EXP-2024-011',
        description: 'Farmaceutica',
        operation: 'Exportacion',
        status: 'Cancelado',
        statusTone: 'danger',
        carrier: 'UPS',
        lastUpdate: 'Cancelado por cliente',
    },
    {
        id: 8,
        code: 'IMP-2024-031',
        description: 'Electronica',
        operation: 'Importacion',
        status: 'En Transito',
        statusTone: 'info',
        carrier: 'DHL',
        lastUpdate: 'En transito desde Shenzhen',
    },
    {
        id: 9,
        code: 'EXP-2024-061',
        description: 'Madera',
        operation: 'Exportacion',
        status: 'Pendiente',
        statusTone: 'warning',
        carrier: 'MSC',
        lastUpdate: 'Pendiente de despacho',
    },
]);

const filteredShipments = computed(() => {
    const term = search.value.trim().toLowerCase();

    if (!term) {
        return shipments.value;
    }

    return shipments.value.filter((item) => {
        return (
            item.code.toLowerCase().includes(term) ||
            item.description.toLowerCase().includes(term) ||
            item.carrier.toLowerCase().includes(term) ||
            item.status.toLowerCase().includes(term)
        );
    });
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
    const inTransit = shipments.value.filter((item) => item.status === 'En Transito').length;
    const completed = shipments.value.filter((item) => item.status === 'Completado').length;
    const pending = shipments.value.filter((item) => item.status === 'Pendiente').length;

    return [
        {
            id: 'total',
            label: 'Total Envios',
            value: total.toLocaleString('es-ES'),
            change: '+12%',
            tone: 'info',
            icon: '[]',
        },
        {
            id: 'transit',
            label: 'En Transito',
            value: inTransit.toLocaleString('es-ES'),
            change: '+5%',
            tone: 'success',
            icon: '=>',
        },
        {
            id: 'completed',
            label: 'Completados',
            value: completed.toLocaleString('es-ES'),
            change: '+18%',
            tone: 'success',
            icon: 'OK',
        },
        {
            id: 'pending',
            label: 'Pendientes',
            value: pending.toLocaleString('es-ES'),
            change: '-2%',
            tone: 'danger',
            icon: 'O',
        },
    ];
});

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