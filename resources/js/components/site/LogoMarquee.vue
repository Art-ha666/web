<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ logos: Array<{ name: string; type: string }> }>();

// Duplicate for a seamless loop.
const loop = computed(() => [...props.logos, ...props.logos]);
</script>

<template>
    <div class="ak-mask-fade-x relative overflow-hidden">
        <div class="ak-marquee flex w-max items-center gap-12">
            <div
                v-for="(logo, i) in loop"
                :key="i"
                class="flex items-center gap-2 whitespace-nowrap font-display text-lg font-semibold text-ak-text-3 transition-colors hover:text-ak-text-2"
            >
                <span class="h-1.5 w-1.5 rounded-full ak-gradient-bg opacity-70" />
                {{ logo.name }}
            </div>
        </div>
    </div>
</template>
