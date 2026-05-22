<template>
    <Navbar />
    <div id="incoterm">
        <div class="incoterm-hero">
            <img src="/img/incoterm_picture.jpg" alt="Guía visual de incoterms">
            <div class="incoterm-hero__overlay">
                <h1>Guía de Incoterms</h1>
                <p>Consulta la referencia visual y entra al CRUD para gestionar los incoterms de la base de datos.</p>

                <RouterLink to="/incoterms/editar" class="admin-btn">
                    Administrar incoterms
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import Navbar from '@/components/Navbar.vue';

</script>

<style lang="scss" scoped>
#incoterm {
    display: grid;
    place-items: center;
    padding: 2rem 1rem;
    min-height: calc(100vh - 72px);
    background: radial-gradient(circle at top left, rgba(14, 165, 233, 0.18), transparent 32%), #eef4fb;
}

.incoterm-hero {
    position: relative;
    width: min(1040px, 100%);
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 28px 60px rgba(16, 36, 63, 0.16);
    background: #10243f;
}

.incoterm-hero img {
    display: block;
    width: 100%;
    height: auto;
    object-fit: cover;
    opacity: 0.76;
}

.incoterm-hero__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    gap: 10px;
    padding: 24px;
    background: linear-gradient(180deg, rgba(16, 36, 63, 0.04) 0%, rgba(16, 36, 63, 0.72) 100%);
    color: #fff;
}

.incoterm-hero__overlay h1 {
    margin: 0;
    font-size: clamp(1.8rem, 3vw, 3rem);
}

.incoterm-hero__overlay p {
    margin: 0;
    max-width: 48rem;
    color: rgba(255, 255, 255, 0.88);
}

.admin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    margin-top: 6px;
    padding: 0.85rem 1.2rem;
    border-radius: 999px;
    background: #0ea5e9;
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    transition: transform 0.2s ease, background-color 0.2s ease;
}

.admin-btn:hover {
    transform: translateY(-1px);
    background: #0284c7;
}

@media (max-width: 640px) {
    .incoterm-hero__overlay {
        padding: 18px;
    }
}
</style>