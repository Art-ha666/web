<script setup lang="ts">
import { computed, defineAsyncComponent  } from 'vue';
import type {Component} from 'vue';
import { useSite } from '@/composables/useSite';
import HeroAurora from './heroes/HeroAurora.vue';

defineProps<{ hero: Record<string, unknown>; stats: Array<Record<string, unknown>> }>();

const { theme } = useSite();

// Distinct design heroes. Aurora is bundled eagerly (default); others load on demand.
const heroes: Record<string, Component> = {
    aurora: HeroAurora,
    nova: defineAsyncComponent(() => import('./heroes/HeroNova.vue')),
    vertex: defineAsyncComponent(() => import('./heroes/HeroVertex.vue')),
    pulse: defineAsyncComponent(() => import('./heroes/HeroPulse.vue')),
    minimal: defineAsyncComponent(() => import('./heroes/HeroMinimal.vue')),
    quantum: defineAsyncComponent(() => import('./heroes/HeroQuantum.vue')),
    flux: defineAsyncComponent(() => import('./heroes/HeroFlux.vue')),
    // New futuristic Three.js designs
    grid: defineAsyncComponent(() => import('./heroes/HeroGrid.vue')),
    singularity: defineAsyncComponent(() => import('./heroes/HeroSingularity.vue')),
    helix: defineAsyncComponent(() => import('./heroes/HeroHelix.vue')),
    prism: defineAsyncComponent(() => import('./heroes/HeroPrism.vue')),
    cosmos: defineAsyncComponent(() => import('./heroes/HeroCosmos.vue')),
    stargate: defineAsyncComponent(() => import('./heroes/HeroStargate.vue')),
};

const current = computed<Component>(() => heroes[theme.value.hero] ?? HeroAurora);
</script>

<template>
    <component :is="current" :hero="hero" :stats="stats" />
</template>
