<template>
    <div class="superset-page">
        <Navbar />

        <main class="superset-main">
            <header class="superset-header">
                <div>
                    <h1>Dashboards BI</h1>
                    <p>Los dashboards se cargan desde tu Superset local y se muestran aqui en tiempo real.</p>
                </div>

                <button type="button" class="refresh-btn" @click="fetchDashboards" :disabled="isLoading">
                    {{ isLoading ? 'Cargando...' : 'Actualizar' }}
                </button>
            </header>

            <section class="superset-panel">
                <p v-if="errorMessage" class="superset-error">{{ errorMessage }}</p>

                <div v-if="isLoading && dashboards.length === 0" class="superset-loading">
                    Cargando dashboards de Superset...
                </div>

                <div v-else-if="dashboards.length === 0" class="superset-empty">
                    No se encontraron dashboards en Superset.
                </div>

                <template v-else>
                    <div class="dashboard-grid">
                        <button
                            v-for="dashboard in dashboards"
                            :key="dashboard.id"
                            type="button"
                            class="dashboard-card"
                            :class="{ 'dashboard-card--active': dashboard.url === selectedDashboardUrl }"
                            @click="selectedDashboardUrl = dashboard.url"
                        >
                            <span class="dashboard-card__id">#{{ dashboard.id }}</span>
                            <span class="dashboard-card__title">{{ dashboard.title }}</span>
                        </button>
                    </div>

                    <div class="iframe-wrap">
                        <iframe
                            v-if="selectedDashboardUrl"
                            :src="selectedDashboardUrl"
                            class="superset-frame"
                            title="Superset dashboard"
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                        />
                    </div>
                </template>
            </section>
        </main>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import Navbar from '@/components/Navbar.vue';
import api from '@/lib/api';

type Dashboard = {
    id: number | string;
    title: string;
    url: string;
};

const dashboards = ref<Dashboard[]>([]);
const selectedDashboardUrl = ref('');
const isLoading = ref(false);
const errorMessage = ref('');

const fetchDashboards = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const { data } = await api.get('/superset/dashboards');
        const results = Array.isArray(data?.dashboards) ? data.dashboards : [];

        dashboards.value = results;
        selectedDashboardUrl.value = results[0]?.url || '';
    } catch (error) {
        console.error('Error cargando dashboards de Superset:', error);
        errorMessage.value = 'No se pudieron cargar los dashboards de Superset. Revisa la configuracion del backend.';
        dashboards.value = [];
        selectedDashboardUrl.value = '';
    } finally {
        isLoading.value = false;
    }
};

onMounted(fetchDashboards);
</script>

<style scoped>
.superset-page {
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(27, 42, 74, 0.08), transparent 26%),
        radial-gradient(circle at top right, rgba(0, 161, 155, 0.08), transparent 28%),
        #eef2f7;
}

.superset-main {
    width: min(1360px, 100%);
    margin: 0 auto;
    padding: 28px 24px 36px;
}

.superset-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 16px;
    margin-bottom: 18px;
}

.superset-header h1 {
    margin: 0;
    font-size: 2rem;
    color: #192a46;
}

.superset-header p {
    margin: 6px 0 0;
    color: #6d7f96;
    font-size: 0.95rem;
}

.refresh-btn {
    border: none;
    border-radius: 10px;
    padding: 10px 16px;
    background: #1b2a4a;
    color: #ffffff;
    font-weight: 700;
    cursor: pointer;
}

.refresh-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.superset-panel {
    background: #ffffff;
    border: 1px solid #d9e3ef;
    border-radius: 14px;
    box-shadow: 0 18px 42px rgba(26, 38, 61, 0.08);
    padding: 16px;
}

.superset-loading,
.superset-empty,
.superset-error {
    margin: 0;
    font-weight: 600;
}

.superset-loading {
    color: #335175;
}

.superset-empty {
    color: #6d7f96;
}

.superset-error {
    color: #b42318;
    margin-bottom: 14px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.dashboard-card {
    border: 1px solid #d9e3ef;
    border-radius: 12px;
    padding: 14px;
    background: linear-gradient(180deg, #ffffff, #f8fbff);
    text-align: left;
    cursor: pointer;
    display: grid;
    gap: 6px;
    min-height: 86px;
}

.dashboard-card--active {
    border-color: #00a19b;
    box-shadow: 0 0 0 2px rgba(0, 161, 155, 0.12);
}

.dashboard-card__id {
    font-size: 0.78rem;
    font-weight: 700;
    color: #6d7f96;
}

.dashboard-card__title {
    font-size: 1rem;
    font-weight: 700;
    color: #1d2d46;
}

.iframe-wrap {
    border: 1px solid #d9e3ef;
    border-radius: 12px;
    overflow: hidden;
    background: #f8fbff;
}

.superset-frame {
    width: 100%;
    min-height: 78vh;
    border: none;
    display: block;
}

@media (max-width: 900px) {
    .superset-header {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>
