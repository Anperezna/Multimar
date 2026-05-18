import { ref } from 'vue';
import api from '@/lib/api';

export function useLiniaTransportMaritim() {
    const linias = ref([]);
    const ports = ref([]);
    const ciutats = ref([]);
    const cargando = ref(true);
    const error = ref(null);

    // Cargar todas las línias
    const cargarLinias = async () => {
        try {
            cargando.value = true;
            error.value = null;
            const { data } = await api.get('/linias-maritimas');
            linias.value = data;
        } catch (err) {
            error.value = 'Error al cargar las línias: ' + err.message;
            console.error('Error al cargar línias:', err);
        } finally {
            cargando.value = false;
        }
    };

    // Cargar puertos
    const cargarPorts = async () => {
        try {
            const { data } = await api.get('/port');
            console.log('Puertos cargados:', data);
            ports.value = data;
        } catch (err) {
            console.error('Error cargando puertos:', err);
            error.value = 'Error cargando puertos: ' + err.message;
        }
    };

    // Cargar ciudades
    const cargarCiudades = async () => {
        try {
            const { data } = await api.get('/ciutats');
            console.log('Ciudades cargadas:', data);
            ciutats.value = data;
        } catch (err) {
            console.error('Error cargando ciudades:', err);
            error.value = 'Error cargando ciudades: ' + err.message;
        }
    };

    // Guardar línia (crear o editar)
    const guardarLinia = async (formulario, editando, liniaEnEdicion) => {
        try {
            console.log('Enviando formulario:', { formulario, editando, liniaEnEdicion });
            
            const payload = {
                nom: formulario.nom,
                ports: formulario.ports
            };
            
            console.log('Payload a enviar:', payload);
            
            let response;
            if (editando) {
                console.log('Actualizando línia:', liniaEnEdicion.id);
                response = await api.put(`/linias-maritimas/${liniaEnEdicion.id}`, payload);
            } else {
                console.log('Creando nueva línia');
                response = await api.post('/linias-maritimas', payload);
            }
            
            console.log('Respuesta del servidor:', response.data);
            await cargarLinias();
            return { success: true, data: response.data };
        } catch (err) {
            console.error('Error en guardarLinia:', err);
            const errorMsg = err?.response?.data?.error || err.message || 'Error al guardar la línia';
            console.error('Mensaje de error:', errorMsg);
            return { success: false, error: errorMsg };
        }
    };

    // Eliminar línia
    const eliminarLinia = async (id) => {
        try {
            await api.delete(`/linias-maritimas/${id}`);
            await cargarLinias();
            return { success: true };
        } catch (err) {
            const errorMsg = err?.response?.data?.error || err.message || 'Error al eliminar la línia';
            return { success: false, error: errorMsg };
        }
    };

    return {
        linias,
        ports,
        ciutats,
        cargando,
        error,
        cargarLinias,
        cargarPorts,
        cargarCiudades,
        guardarLinia,
        eliminarLinia
    };
}
