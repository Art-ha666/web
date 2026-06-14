<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Icon from './Icon.vue';

defineProps<{
    cta: {
        eyebrow?: string;
        headline: string;
        body?: string;
        primary_cta_label?: string;
        primary_cta_url?: string;
    } | null;
}>();
</script>

<template>
    <section v-if="cta" class="relative overflow-hidden py-24 sm:py-32">
        <div
            class="ak-gradient-pan pointer-events-none absolute inset-0 opacity-90"
            :style="{ background: 'linear-gradient(120deg, color-mix(in srgb, var(--ak-grad-3) 22%, var(--ak-bg)), var(--ak-bg) 45%, color-mix(in srgb, var(--ak-grad-2) 18%, var(--ak-bg)))' }"
        />
        <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-40" />
        <div class="ak-container relative text-center">
            <p v-if="cta.eyebrow" class="ak-eyebrow justify-center">{{ cta.eyebrow }}</p>
            <h2 class="mx-auto mt-5 max-w-4xl font-display text-4xl font-semibold leading-[1.1] tracking-tight text-ak-text sm:text-5xl lg:text-6xl">
                {{ cta.headline }}
            </h2>
            <p v-if="cta.body" class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-ak-text-2">{{ cta.body }}</p>
            <Link
                v-if="cta.primary_cta_url"
                :href="cta.primary_cta_url"
                class="ak-focusable group mt-8 inline-flex items-center gap-2 text-base font-semibold text-ak-text"
            >
                <span class="ak-gradient-text">{{ cta.primary_cta_label }}</span>
                <Icon name="arrow-right" :size="18" class="transition-transform group-hover:translate-x-1" :style="{ color: 'var(--ak-primary)' }" />
            </Link>
        </div>
    </section>
</template>
