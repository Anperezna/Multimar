<template>
    <Navbar />
    
    <section class="linias-page">
        <header class="page-header">
            <h1>Línias de Transporte Marítimo</h1>
            <p>Gestión de rutas marítimas y puertos asociados</p>
        </header>

        <div class="linias-wrapper">
            <div class="linias-section">
                <div class="linias-header">
                    <h2>Línias de Transporte Marítimo</h2>
                    <button @click="abrirModalCrear" class="btn-agregar">+ Agregar Línia</button>
                </div>

                <div v-if="apiError" class="error-banner">{{ apiError }}</div>

                <div class="table-wrap">
                    <div v-if="cargando" class="table-loading">
                        <span class="table-loading__spinner"></span>
                        <p>Cargando línias...</p>
                    </div>

                    <table v-else>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Ports</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="linia in linias" :key="linia.id">
                                <td>{{ linia.nom }}</td>
                                <td>
                                    <div class="ports-list">
                                        <span v-for="puerto in linia.ports" :key="puerto.id" class="puerto-badge">
                                            {{ puerto.nom }}
                                        </span>
                                    </div>
                                </td>
                                <td class="actions-cell">
                                    <BtnEditar @click="abrirModalEditar(linia)" />
                                    <BtnEliminar @click="abrirModalEliminar(linia.id)" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Componentes modales -->
        <FormularioLinia 
            :mostrarModal="mostrarModalFormulario"
            :editando="editando"
            :linia-en-edicion="liniaEnEdicion"
            :ports="ports"
            :ciutats="ciutats"
            @cerrar="cerrarModalFormulario"
            @guardar="guardarLinia"
        />

        <ModalEliminar 
            :mostrar="mostrarModalEliminar"
            @cerrar="cerrarModalEliminar"
            @confirmar="confirmarEliminar"
        />
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Navbar from '@/components/Navbar.vue';
import BtnEditar from '@/components/BtnEditar.vue';
import BtnEliminar from '@/components/BtnEliminar.vue';
import FormularioLinia from '@/components/LiniasMaritimas/FormularioLinia.vue';
import ModalEliminar from '@/components/LiniasMaritimas/ModalEliminar.vue';
import { useLiniaTransportMaritim } from '@/composables/useLiniaTransportMaritim.js';

// Composable
const {
    linias,
    ports,
    ciutats,
    cargando,
    error: apiError,
    cargarLinias,
    cargarPorts,
    cargarCiudades,
    guardarLinia: guardarLinia_api,
    eliminarLinia: eliminarLinia_api
} = useLiniaTransportMaritim();

// Estado del modal de formulario
const mostrarModalFormulario = ref(false);
const editando = ref(false);
const liniaEnEdicion = ref(null);

// Estado del modal de eliminar
const mostrarModalEliminar = ref(false);
const liniaAEliminar = ref(null);

// Cargar datos al montar
onMounted(async () => {
    await cargarLinias();
    await cargarPorts();
    await cargarCiudades();
});

// Funciones del modal de formulario
const abrirModalCrear = () => {
    editando.value = false;
    liniaEnEdicion.value = null;
    mostrarModalFormulario.value = true;
};

const abrirModalEditar = (linia) => {
    editando.value = true;
    liniaEnEdicion.value = linia;
    mostrarModalFormulario.value = true;
};

const cerrarModalFormulario = () => {
    mostrarModalFormulario.value = false;
    editando.value = false;
    liniaEnEdicion.value = null;
};

const guardarLinia = async (formulario) => {
    console.log('Guardando línia:', formulario, 'Editando:', editando.value);
    const resultado = await guardarLinia_api(formulario, editando.value, liniaEnEdicion.value);
    
    console.log('Resultado guardado:', resultado);
    
    if (resultado.success) {
        console.log('Línia guardada exitosamente');
        cerrarModalFormulario();
    } else {
        console.error('Error al guardar línia:', resultado.error);
    }
};

// Funciones del modal de eliminar
const abrirModalEliminar = (id) => {
    liniaAEliminar.value = id;
    mostrarModalEliminar.value = true;
};

const cerrarModalEliminar = () => {
    mostrarModalEliminar.value = false;
    liniaAEliminar.value = null;
};

const confirmarEliminar = async () => {
    if (liniaAEliminar.value) {
        const resultado = await eliminarLinia_api(liniaAEliminar.value);
        
        if (resultado.success) {
            cerrarModalEliminar();
        } else {
            apiError.value = resultado.error;
        }
    }
};
</script>

<style scoped>
.linias-page {
    min-height: 100vh;
    background-color: #f9f9f9;
    padding-bottom: 3rem;
}

.page-header {
    background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%);
    border-bottom: 1px solid #e0e0e0;
    padding: 2rem;
    margin-left: 50px;
    margin-right: 50px;
}

.page-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
    color: #333;
}

.page-header p {
    margin: 0;
    color: #666;
    font-size: 1rem;
}

.linias-wrapper {
    padding: 2rem;
    margin-left: 50px;
    margin-right: 50px;
}

.linias-section {
    background: white;
    border-radius: 0.375rem;
    border: 1px solid #e0e0e0;
    padding: 1.5rem;
}

.linias-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.linias-header h2 {
    margin: 0;
    color: #333;
    font-size: 1.25rem;
    font-weight: 600;
}

.btn-agregar {
    padding: 0.5rem 1rem;
    background-color: #0066cc;
    color: white;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: background-color 0.3s;
}

.btn-agregar:hover {
    background-color: #0052a3;
}

.error-banner {
    background-color: #fee;
    color: #c33;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #fcc;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.table-wrap {
    overflow-x: auto;
}

.table-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    text-align: center;
    color: #666;
}

.table-loading__spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e0e0e0;
    border-top-color: #0066cc;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

thead {
    background-color: #f5f5f5;
    border-bottom: 1px solid #d0d0d0;
}

th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
}

td {
    padding: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

tbody tr:hover {
    background-color: #fafafa;
}

.ports-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.puerto-badge {
    display: inline-block;
    background-color: #e6f0ff;
    color: #0066cc;
    padding: 0.35rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border: 1px solid #b3d9ff;
}

.actions-cell {
    display: flex;
    gap: 0.5rem;
}
</style>
