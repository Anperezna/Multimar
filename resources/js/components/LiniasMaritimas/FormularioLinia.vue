<template>
    <div v-if="mostrarModal" class="modal-overlay" @click.self="cerrar">
        <div class="modal-content">
            <div class="modal-header">
                <h3>{{ editando ? 'Editar Línia Marítima' : 'Crear Línia Marítima' }}</h3>
                <button @click="cerrar" class="btn-close" aria-label="Cerrar modal">✕</button>
            </div>

            <form @submit.prevent="manejarEnvio" class="modal-form">
                <!-- Campo Nombre -->
                <div class="form-group">
                    <label for="nom-input">Nom de la Línia</label>
                    <input 
                        id="nom-input"
                        v-model="formulario.nom" 
                        type="text" 
                        placeholder="Ej: Ruta Mediterráneo"
                        class="input-field"
                        required
                    />
                </div>

                <!-- Campo Ciudad -->
                <div class="form-group">
                    <label for="ciutat-input">Ciutat</label>
                    <select 
                        id="ciutat-input"
                        v-model="formulario.ciutat_id" 
                        class="input-field"
                        required
                    >
                        <option value="">Selecciona una ciutat</option>
                        <option v-for="ciutat in ciutats" :key="ciutat.id" :value="ciutat.id">
                            {{ ciutat.nom }}
                        </option>
                    </select>
                </div>

                <!-- Campo Puertos -->
                <div class="form-group">
                    <label class="ports-label">Ports (Selecciona al menos uno)</label>
                    <div class="ports-grid">
                        <label 
                            v-for="port in ports" 
                            :key="port.id" 
                            class="port-checkbox"
                        >
                            <input 
                                type="checkbox" 
                                :value="port.id"
                                v-model="formulario.ports"
                                class="checkbox-input"
                            />
                            <span class="checkbox-label-text">{{ port.nom }}</span>
                        </label>
                    </div>
                    <div v-if="formulario.ports.length === 0" class="ports-helper">
                        Debes seleccionar al menos un puerto
                    </div>
                </div>

                <!-- Error -->
                <div v-if="errorFormulario" class="error-message">
                    <span class="error-icon">⚠️</span>
                    {{ errorFormulario }}
                </div>

                <!-- Acciones -->
                <div class="modal-actions">
                    <button 
                        @click="cerrar" 
                        type="button" 
                        class="btn-secundario"
                        :disabled="guardando"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="btn-primario"
                        :disabled="guardando || formulario.ports.length === 0"
                    >
                        <span v-if="!guardando">{{ editando ? 'Actualizar' : 'Crear' }}</span>
                        <span v-else>
                            <span class="spinner-mini"></span>
                            {{ editando ? 'Actualizar...' : 'Crear...' }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    mostrarModal: Boolean,
    editando: Boolean,
    liniaEnEdicion: Object,
    ports: {
        type: Array,
        default: () => []
    },
    ciutats: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['cerrar', 'guardar']);

const formulario = ref({
    nom: '',
    ciutat_id: '',
    ports: []
});

const guardando = ref(false);
const errorFormulario = ref(null);

// Observar cambios en el modal
watch(() => props.mostrarModal, (nuevoVal) => {
    if (nuevoVal) {
        console.log('Modal abierto - Editando:', props.editando, 'Puertos disponibles:', props.ports);
        if (props.editando && props.liniaEnEdicion) {
            formulario.value = {
                nom: props.liniaEnEdicion.nom,
                ciutat_id: props.liniaEnEdicion.ciutat_id || '',
                ports: props.liniaEnEdicion.ports.map(p => p.id)
            };
            console.log('Cargado para edición:', formulario.value);
        } else {
            formulario.value = { nom: '', ciutat_id: '', ports: [] };
            console.log('Nuevo formulario');
        }
        errorFormulario.value = null;
    }
});

const cerrar = () => {
    emit('cerrar');
};

const manejarEnvio = async () => {
    try {
        guardando.value = true;
        errorFormulario.value = null;

        if (formulario.value.ports.length === 0) {
            errorFormulario.value = 'Debes seleccionar al menos un puerto';
            return;
        }

        emit('guardar', formulario.value);
    } catch (err) {
        errorFormulario.value = 'Error: ' + err.message;
    } finally {
        guardando.value = false;
    }
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
    overflow-y: auto;
}

.modal-content {
    background: white;
    border-radius: 0.5rem;
    width: 100%;
    max-width: 700px;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e0e0e0;
    background: linear-gradient(135deg, #f5f5f5 0%, #fafafa 100%);
}

.modal-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.25rem;
    font-weight: 600;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
    transition: all 0.2s;
}

.btn-close:hover {
    background-color: #f0f0f0;
    color: #333;
}

.modal-form {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.75rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.75rem;
    color: #333;
    font-weight: 600;
    font-size: 0.95rem;
}

.input-field,
.select-field {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d0d0d0;
    border-radius: 0.375rem;
    font-size: 0.95rem;
    font-family: inherit;
    transition: all 0.3s;
    background-color: #fff;
}

.input-field:focus,
.select-field:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 4px rgba(0, 102, 204, 0.1);
    background-color: #fafbff;
}

.ports-label {
    display: block;
    margin-bottom: 1rem;
    color: #333;
    font-weight: 600;
    font-size: 0.95rem;
}

.ports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 0.75rem;
    padding: 1.25rem;
    background-color: #f9f9f9;
    border: 2px solid #e0e0e0;
    border-radius: 0.375rem;
    max-height: 400px;
    overflow-y: auto;
}

.port-checkbox {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    padding: 0.75rem 0.875rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
    border: 1px solid transparent;
    background-color: white;
}

.port-checkbox:hover {
    background-color: #f0f5ff;
    border-color: #d0e8ff;
}

.port-checkbox:has(.checkbox-input:checked) {
    background-color: #e6f0ff;
    border-color: #0066cc;
    box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.15);
}

.checkbox-input {
    width: 18px;
    height: 18px;
    margin-right: 0.75rem;
    margin-top: 1px;
    cursor: pointer;
    accent-color: #0066cc;
    flex-shrink: 0;
}

.checkbox-label-text {
    font-size: 0.9rem;
    color: #333;
    word-break: break-word;
    line-height: 1.4;
}

.ports-helper {
    margin-top: 0.75rem;
    font-size: 0.85rem;
    color: #999;
    font-style: italic;
}

.error-message {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background-color: #fee;
    color: #c33;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #fcc;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.error-icon {
    flex-shrink: 0;
    margin-top: 2px;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e0e0e0;
    justify-content: flex-end;
}

.btn-primario,
.btn-secundario {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 40px;
}

.btn-primario {
    background-color: #0066cc;
    color: white;
}

.btn-primario:hover:not(:disabled) {
    background-color: #0052a3;
    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
}

.btn-primario:disabled {
    background-color: #ccc;
    cursor: not-allowed;
    opacity: 0.6;
}

.btn-secundario {
    background-color: #e0e0e0;
    color: #333;
}

.btn-secundario:hover:not(:disabled) {
    background-color: #d0d0d0;
}

.btn-secundario:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.spinner-mini {
    display: inline-block;
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Scroll bar personalizado */
.ports-grid::-webkit-scrollbar {
    width: 8px;
}

.ports-grid::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.ports-grid::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.ports-grid::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsivo */
@media (max-width: 768px) {
    .modal-content {
        max-width: 95vw;
    }

    .modal-form {
        padding: 1.5rem;
    }

    .ports-grid {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        max-height: 350px;
    }
}

@media (max-width: 480px) {
    .modal-header,
    .modal-form {
        padding: 1rem;
    }

    .ports-grid {
        grid-template-columns: 1fr;
        max-height: 300px;
    }

    .modal-actions {
        flex-direction: column-reverse;
    }

    .btn-primario,
    .btn-secundario {
        width: 100%;
        justify-content: center;
    }
}
</style>
