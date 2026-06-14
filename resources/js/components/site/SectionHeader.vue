<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import EyebrowLabel from './EyebrowLabel.vue';

withDefaults(
    defineProps<{
        eyebrow?: string;
        title?: string;
        subtitle?: string;
        align?: 'left' | 'center';
        viewAllLabel?: string;
        viewAllHref?: string;
    }>(),
    { align: 'left' },
);
</script>

<template>
    <div
        class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between"
        :class="align === 'center' ? 'items-center text-center md:flex-col' : ''"
    >
        <div :class="align === 'center' ? 'mx-auto max-w-2xl' : 'max-w-2xl'">
            <EyebrowLabel v-if="eyebrow">{{ eyebrow }}</EyebrowLabel>
            <h2
                v-if="title"
                class="mt-4 font-display text-3xl font-semibold leading-[1.08] tracking-tight text-ak-text sm:text-4xl"
            >
                {{ title }}
            </h2>
            <p v-if="subtitle" class="mt-4 text-ak-text-2 sm:text-lg">{{ subtitle }}</p>
        </div>
        <Link
            v-if="viewAllHref"
            :href="viewAllHref"
            class="ak-focusable group inline-flex items-center gap-1.5 self-start rounded-full border border-ak-glass-border px-4 py-2 text-sm font-medium text-ak-text transition hover:border-ak-primary hover:text-ak-primary md:self-auto"
        >
            {{ viewAllLabel ?? 'View all' }}
            <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
        </Link>
    </div>
</template>
