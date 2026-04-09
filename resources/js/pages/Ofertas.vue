<template>
    <div class="ofertas-page">
        <Navbar />

        <main class="ofertas-layout">
            <header class="ofertas-header">
                <h1>Gestión de Ofertas</h1>
                <p>Listado de todas las ofertas disponibles.</p>
                <p v-if="apiError" class="api-error">{{ apiError }}</p>
            </header>

            <section class="ofertas-panel">
                <div class="panel-top">
                    <h2>Ofertas</h2>

                    <div class="panel-actions">
                        <div class="search-wrap">
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Buscar ID, usuario, mercancía..."
                                aria-label="Buscar ofertas"
                            />
                        </div>
                    </div>
                </div>

                <div class="table-wrap">
                    <div v-if="isLoading" class="table-loading" aria-label="Cargando ofertas">
                        <span class="table-loading__spinner"></span>
                    </div>

                    <table v-else>
                        <thead>
                            <tr>
                                <th>ID Oferta</th>
                                <th>Usuario</th>
                                <th>Mercancía</th>
                                <th>Detalles Carga</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="oferta in paginatedOfertas" :key="oferta.id">
                                <td class="mono">SOL-{{ String(oferta.id).padStart(3, '0') }}</td>
                                <td>{{ oferta.operadorNom || 'No disponible' }}</td>
                                <td>
                                    <p class="desc-title">{{ oferta.mercanciaNombre || 'No disponible' }}</p>
                                    <p class="desc-type">{{ oferta.operation || 'No disponible' }}</p>
                                </td>
                                <td>
                                    {{ oferta.pesoBrut || 'No disponible' }} kg •
                                    {{ oferta.volum || 'No disponible' }} m³ •
                                    {{ oferta.tipusContenidorNom || 'Contenedor' }}
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="detail-btn"
                                        @click="router.push({ name: 'detalle-oferta', params: { id: oferta.id } })"
                                    >
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <footer class="panel-footer">
                    <p>
                        Mostrando
                        <strong>{{ paginatedOfertas.length }}</strong>
                        de
                        <strong>{{ filteredOfertas.length }}</strong>
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
        </main>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import Navbar from '@/components/Navbar.vue';
import api from '@/lib/api';

type OfertaApi = {
    id: number | string;
    code?: string;
    description?: string;
    operation?: string;
    status?: string;
    carrier?: string;
    lastUpdate?: string | null;
    mercanciaNombre?: string | null;
    pesoBrut?: string | number | null;
    volum?: string | number | null;
    tipusContenidorNom?: string | null;
    tipusCarregaNom?: string | null;
    origenNom?: string | null;
    destinoNom?: string | null;
    operadorNom?: string | null;
    incotermText?: string | null;
};

const router = useRouter();
const search = ref('');
const currentPage = ref(1);
const pageSize = 8;
const isLoading = ref(false);
const apiError = ref('');
const ofertas = ref<OfertaApi[]>([]);

const fetchOfertas = async () => {
    apiError.value = '';
    isLoading.value = true;

    try {
        const { data } = await api.get('/ofertes');
        ofertas.value = Array.isArray(data) ? data : [];
    } catch (error) {
        apiError.value = 'No se pudieron cargar las ofertas.';
        console.error('Error cargando ofertas:', error);
    } finally {
        isLoading.value = false;
    }
};

const filteredOfertas = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return ofertas.value;

    return ofertas.value.filter((oferta) => {
        const haystack = [
            oferta.id,
            oferta.code,
            oferta.operadorNom,
            oferta.mercanciaNombre,
            oferta.operation,
            oferta.status,
            oferta.carrier,
            oferta.tipusContenidorNom,
            oferta.tipusCarregaNom,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return haystack.includes(term);
    });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredOfertas.value.length / pageSize)));

const paginatedOfertas = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredOfertas.value.slice(start, start + pageSize);
});

watch(search, () => {
    currentPage.value = 1;
});

watch(filteredOfertas, () => {
    if (currentPage.value > totalPages.value) {
        currentPage.value = totalPages.value;
    }
});

onMounted(fetchOfertas);
</script>

<style scoped>
.ofertas-page {
    min-height: 100vh;
    background: #eef2f7;
}

.ofertas-layout {
    width: min(1200px, 100%);
    margin: 0 auto;
    padding: 28px 24px;
}

.ofertas-header h1 {
    margin: 0;
    font-size: 1.8rem;
    color: #192a46;
}

.ofertas-header p {
    margin: 6px 0 20px;
    color: #6d7f96;
    font-size: 0.92rem;
}

.api-error {
    color: #dc2626 !important;
    font-weight: 600;
}

.ofertas-panel {
    background: #fff;
    border: 1px solid #d9e3ef;
    border-radius: 10px;
    overflow: hidden;
}

.panel-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 16px 18px;
    border-bottom: 1px solid #e8eef6;
}

.panel-top h2 {
    margin: 0;
    font-size: 1rem;
    color: #1d2d46;
}

.panel-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-wrap input {
    width: 280px;
    max-width: 100%;
    border: 1px solid #cfdbe8;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 0.88rem;
    color: #1d2d46;
    outline: none;
}

.table-wrap {
    overflow-x: auto;
}

.table-loading {
    min-height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.table-loading__spinner {
    width: 34px;
    height: 34px;
    border: 3px solid #d9e3ef;
    border-top-color: #1d4ed8;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 880px;
}

thead th {
    padding: 10px 14px;
    text-align: left;
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748b;
    background: #f3f6fa;
    border-bottom: 1px solid #e8eef6;
}

tbody td {
    padding: 13px 14px;
    font-size: 0.85rem;
    color: #334a67;
    border-bottom: 1px solid #edf1f7;
    vertical-align: middle;
}

.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, Courier New, monospace;
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

.detail-btn,
.btn-page {
    border: 1px solid #d9e3ef;
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

.panel-footer {
    border-top: 1px solid #e8eef6;
    padding: 12px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.panel-footer p {
    margin: 0;
    color: #6d7f96;
    font-size: 0.83rem;
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

@media (max-width: 900px) {
    .panel-top,
    .panel-footer {
        flex-direction: column;
        align-items: stretch;
    }

    .search-wrap input {
        width: 100%;
    }
}
</style>