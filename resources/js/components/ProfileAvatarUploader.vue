<template>
    <div class="avatar-row">
        <div class="avatar">
            <img v-if="avatarImageSrc" :src="avatarImageSrc" alt="Foto de perfil" class="avatar__image" />
            <span v-else>{{ avatarInitials }}</span>
        </div>

        <div>
            <strong>{{ fullName }}</strong>
            <small>{{ correoLabel }}</small>
            <label class="avatar-upload" for="foto_user">Subir foto</label>
            <input
                id="foto_user"
                class="avatar-upload__input"
                type="file"
                accept="image/*"
                @change="onAvatarChange"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    nombre: string;
    apellidos: string;
    correo: string;
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'error', value: string): void;
}>();

const fullName = computed(() => {
    const name = [props.nombre, props.apellidos].filter(Boolean).join(' ').trim();

    if (name === '') {
        return 'Usuario';
    }

    return name;
});

const correoLabel = computed(() => {
    if (!props.correo) {
        return 'Sin correo';
    }

    return props.correo;
});

const avatarInitials = computed(() => {
    const first = props.nombre?.trim().charAt(0) || '';
    const second = props.apellidos?.trim().charAt(0) || '';
    const initials = `${first}${second}`.toUpperCase();

    if (initials === '') {
        return 'U';
    }

    return initials;
});

const avatarImageSrc = computed(() => {
    const raw = (props.modelValue || '').trim();

    if (raw === '') {
        return '';
    }

    if (raw.startsWith('data:image/')) {
        return raw;
    }

    return `data:image/jpeg;base64,${raw}`;
});

const onAvatarChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;

    if (!files || files.length === 0) {
        return;
    }

    const file = files[0];

    if (!file.type.startsWith('image/')) {
        emit('error', 'El archivo seleccionado no es una imagen.');
        return;
    }

    const reader = new FileReader();

    reader.onload = () => {
        const result = typeof reader.result === 'string' ? reader.result : '';

        if (result === '') {
            emit('error', 'No se pudo leer la imagen seleccionada.');
            return;
        }

        const base64Parts = result.split(',');

        if (base64Parts.length < 2) {
            emit('error', 'El formato de imagen no es valido.');
            return;
        }

        emit('update:modelValue', base64Parts[1]);
    };

    reader.onerror = () => {
        emit('error', 'No se pudo leer la imagen seleccionada.');
    };

    reader.readAsDataURL(file);
};
</script>

<style scoped>
.avatar-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 2px;
}

.avatar {
    width: 62px;
    height: 62px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 1rem;
    font-weight: 700;
    background: #e4ebf5;
    color: #3b4e66;
    overflow: hidden;
}

.avatar__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-row small {
    display: block;
    color: #6d7f96;
    margin-top: 3px;
}

.avatar-upload {
    margin-top: 8px;
    display: inline-flex;
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #cfdbe8;
    background: #f8fbff;
    color: #2f435e;
    font-size: 0.84rem;
    cursor: pointer;
}

.avatar-upload__input {
    display: none;
}
</style>
