<script setup lang="ts">
import AkButton from './AkButton.vue';
import Icon from './Icon.vue';

defineProps<{
    cta: {
        eyebrow?: string;
        headline: string;
        body?: string;
        primary_cta_label?: string;
        primary_cta_url?: string;
        secondary_cta_label?: string;
        secondary_cta_url?: string;
        microcopy?: string;
    } | null;
}>();
</script>

<template>
    <section v-if="cta" class="ak-container py-20 sm:py-28">
        <div class="relative overflow-hidden rounded-[2rem] border border-ak-glass-border p-10 text-center sm:p-16">
            <div class="ak-gradient-pan pointer-events-none absolute inset-0 opacity-25 ak-gradient-bg" />
            <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-30" />
            <div class="relative">
                <p v-if="cta.eyebrow" class="ak-eyebrow justify-center">{{ cta.eyebrow }}</p>
                <h2 class="mx-auto mt-5 max-w-3xl font-display text-4xl font-semibold leading-tight tracking-tight text-ak-text sm:text-5xl lg:text-6xl">
                    {{ cta.headline }}
                </h2>
                <p v-if="cta.body" class="mx-auto mt-5 max-w-2xl text-lg text-ak-text-2">{{ cta.body }}</p>
                <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                    <AkButton :href="cta.primary_cta_url ?? '/contact'" variant="gradient" size="lg">
                        {{ cta.primary_cta_label ?? 'Book a discovery call' }}
                        <Icon name="arrow-right" :size="18" />
                    </AkButton>
                    <AkButton v-if="cta.secondary_cta_label" :href="cta.secondary_cta_url ?? '/contact'" variant="ghost" size="lg">
                        {{ cta.secondary_cta_label }}
                    </AkButton>
                </div>
                <p v-if="cta.microcopy" class="mt-6 text-sm text-ak-text-3">{{ cta.microcopy }}</p>
            </div>
        </div>
    </section>
</template>
