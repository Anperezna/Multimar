<template>
    <Navbar/>
    <div class="solicitud-container">
        <div class="solicitud-header">
            <div class="solicitud-header__content">
                <h1 class="solicitud-header__title">Crear Solicitud de Oferta</h1>
                <p class="solicitud-header__subtitle">Complete los detalles para solicitar una nueva cotización de transporte.</p>
            </div>
            <button class="solicitud-header__button">
                <img :src="avionIcon" alt="avión">
                Crear Solicitud
            </button>
        </div>

        <div class="solicitud-content">
            <!-- Sección 1: Información Mercancía -->
            <div class="solicitud-section">
                <div class="solicitud-section__header" @click="toggleSection('mercancia')">
                    <h2 class="solicitud-section__title">1. Información Mercancía</h2>
                    <span class="solicitud-section__toggle">{{ expandedSections.mercancia ? '−' : '+' }}</span>
                </div>
                <div v-if="expandedSections.mercancia" class="solicitud-section__content">
                    <div class="form-group">
                        <label class="form-label">Nombre de la mercancía</label>
                        <Input 
                            v-model="formData.nombreMercancia"
                            type="text" 
                            placeholder="Ej. Componentes electrónicos"
                            inputClass="form-input"
                        />
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tipo de mercancía</label>
                            <Desplegable
                                v-model="formData.tipoMercancia"
                                :options="tiposMercancia"
                                placeholder="Seleccionar tipo..."
                                selectClass="form-select"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo de contenedor</label>
                            <Desplegable
                                v-model="formData.tipoContenedor"
                                :options="tiposContenedor"
                                placeholder="Seleccionar contenedor..."
                                selectClass="form-select"
                            />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Peso bruto (kg)</label>
                            <Input 
                                v-model="formData.pesoBruto"
                                type="number" 
                                placeholder="0.00"
                                inputClass="form-input"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Volumen (M³)</label>
                            <Input 
                                v-model="formData.volumen"
                                type="number" 
                                placeholder="0.00"
                                inputClass="form-input"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Incoterm -->
            <div class="solicitud-section">
                <div class="solicitud-section__header" @click="toggleSection('incoterm')">
                    <h2 class="solicitud-section__title">2. Incoterm</h2>
                    <span class="solicitud-section__toggle">{{ expandedSections.incoterm ? '−' : '+' }}</span>
                </div>
                <div v-if="expandedSections.incoterm" class="solicitud-section__content">
                    <div class="form-group">
                        <label class="form-label">Tipo de Incoterm</label>
                        <Desplegable
                            v-model="formData.incoterm"
                            :options="incotermOptions"
                            placeholder="Seleccionar incoterm..."
                            selectClass="form-select"
                        />
                    </div>
                    <a href="/incoterm" class="solicitud-link">Ver guía de Incotherms</a>
                </div>
            </div>

            <!-- Sección 3: Información Transporte -->
            <div class="solicitud-section">
                <div class="solicitud-section__header" @click="toggleSection('transporte')">
                    <h2 class="solicitud-section__title">3. Información Transporte</h2>
                    <span class="solicitud-section__toggle">{{ expandedSections.transporte ? '−' : '+' }}</span>
                </div>
                <div v-if="expandedSections.transporte" class="solicitud-section__content">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Origen</label>
                            <Input 
                                v-model="formData.origen"
                                type="text" 
                                placeholder="Ciudad, País"
                                inputClass="form-input"
                            />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Destino</label>
                            <Input 
                                v-model="formData.destino"
                                type="text" 
                                placeholder="Ciudad, País"
                                inputClass="form-input"
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Representante de Venta (Operador Logístico)</label>
                        <Desplegable
                            v-model="formData.operador"
                            :options="operadores"
                            placeholder="Seleccionar operador..."
                            selectClass="form-select"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Desplegable from '@/components/Desplegable.vue';
import Input from '@/components/Input.vue';
import Navbar from '@/components/Navbar.vue';
import avionIcon from '../../../public/icons_multimar/icons-simex/compartidos/light_icons/plane-w.svg';

// Estado de las secciones expandidas (todas abiertas por defecto)
const expandedSections = ref({
    mercancia: true,
    incoterm: true,
    transporte: true,
});

// Función para alternar la apertura/cierre de una sección
const toggleSection = (sectionName) => {
    expandedSections.value[sectionName] = !expandedSections.value[sectionName];
};

// Datos del formulario
const formData = ref({
    nombreMercancia: '',
    tipoMercancia: '',
    tipoContenedor: '',
    pesoBruto: '',
    volumen: '',
    incoterm: '',
    origen: '',
    destino: '',
    operador: '',
});

// Opciones para los desplegables
const tiposMercancia = ref([
    { value: 'general', label: 'General' },
    { value: 'riffer', label: 'Riffer' },
    { value: 'liquidos', label: 'Liquidos' },
    { value: 'immo', label: 'Immo' },
    { value: 'otros', label: 'Otros' },
]);

const tiposMercanciaData = await axios.get('/api/tipos-mercancia');
const tiposMercancia = ref(tiposMercanciaData.data.map(item => ({
  value: item.id,
  label: item.tipus
})));   

// Forma más limpia y funcional
const tiposContenedorData = await axios.get('/api/tipos-contenedor');
const tiposContenedor = ref(tiposContenedorData.data.map(item => ({
  value: item.id,
  label: item.tipus
})));   

const incotermOptions = ref([
    { value: 'exw', label: 'EXW - Ex Works' },
    { value: 'fca', label: 'FCA - Free Carries' },
    { value: 'cpt', label: 'CPT - Carriage Paid To' },
    { value: 'cip', label: 'CIP - Carriage and Insurance Paid' },
    { value: 'dap', label: 'DAP - Delivered at Place' },
    { value: 'dpu', label: 'DPU - Delivered at Place Unloaded' },
    { value: 'ddp', label: 'DDP - Delivered Duty Paid' },
    { value: 'cfr', label: 'CFR - Cost and Freight' },
    { value: 'fob', label: 'FOB - Free on Board' },
    { value: 'fas', label: 'FAS - Free Alongside Ship' },
    { value: 'cif', label: 'CIF - Cost, Insurance and Freight' },
]);

const operadores = ref([
    { value: 'operador1', label: 'Operador Logístico A' },
    { value: 'operador2', label: 'Operador Logístico B' },
    { value: 'operador3', label: 'Operador Logístico C' },
]);
</script>

<style scoped>
.solicitud-container {
    background: #f5f5f5;
    min-height: 100vh;
    padding: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding-top: 20px; /* Ajusta según la altura del header */
}

.solicitud-header {
    background: white;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    width: 97vw;
    max-width: none;
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
}

.solicitud-header__content {
    flex: 1;
}

.solicitud-header__title {
    font-size: 28px;
    font-weight: 700;
    color: #1B2A4A;
    margin: 0 0 8px 0;
}

.solicitud-header__subtitle {
    font-size: 14px;
    color: #7B8BA8;
    margin: 0;
}

.solicitud-header__button {
    background: #0091D5;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.solicitud-header__button:hover {
    background: #0078b1;
}

.solicitud-header__button-icon {
    font-size: 18px;
}

.solicitud-content {
    width: 100%;
    max-width: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.solicitud-section {
    background: white;
    border-radius: 8px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    width: 100%;
}

.solicitud-section__header {
    background: #f9f9f9;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    border-bottom: 1px solid #e5e7eb;
    transition: background 0.2s;
}

.solicitud-section__header:hover {
    background: #f0f0f0;
}

.solicitud-section__title {
    font-size: 16px;
    font-weight: 600;
    color: #1B2A4A;
    margin: 0;
}

.solicitud-section__toggle {
    font-size: 24px;
    color: #7B8BA8;
    cursor: pointer;
}

.solicitud-section__content {
    padding: 24px;
}

/* Formulario */
.form-group {
    margin-bottom: 16px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.form-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #1B2A4A;
    margin-bottom: 8px;
}

.form-input,
.form-select {
    width: 90%;
    margin-left: auto;
    margin-right: auto;
    display: block;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    color: #4B5563;
    background: white;
    font-family: inherit;
}

.form-input::placeholder {
    color: #B8C1D0;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #0091D5;
    box-shadow: 0 0 0 3px rgba(0, 145, 213, 0.1);
}

.solicitud-link {
    color: #0091D5;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 12px;
}

.solicitud-link:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .solicitud-header {
        flex-direction: column;
        gap: 16px;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .solicitud-header__title {
        font-size: 24px;
    }
}
</style>
