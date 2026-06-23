<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <!-- AKH brand glyph: interlocking gradient brackets (matches the site header,
         the favicon, and the active design theme via --ak-grad-* vars). -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 64 64"
        fill="none"
        :class="className"
        v-bind="$attrs"
    >
        <defs>
            <linearGradient id="akLogoGrad" x1="4" y1="14" x2="60" y2="50" gradientUnits="userSpaceOnUse">
                <stop stop-color="var(--ak-grad-1, #28baf3)" />
                <stop offset="0.5" stop-color="var(--ak-grad-2, #5778f8)" />
                <stop offset="1" stop-color="var(--ak-grad-3, #7e2cfd)" />
            </linearGradient>
        </defs>
        <path d="M30 16 L10 16 Q4 16 4 24 L4 40 Q4 48 10 48 L30 48 L20 32 Z" stroke="url(#akLogoGrad)" stroke-width="4.5" stroke-linejoin="round" fill="none" />
        <path d="M34 16 L54 16 Q60 16 60 24 L60 40 Q60 48 54 48 L34 48 L44 32 Z" stroke="url(#akLogoGrad)" stroke-width="4.5" stroke-linejoin="round" fill="none" />
        <path d="M28 24 L32 32 L28 40" stroke="url(#akLogoGrad)" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M36 24 L32 32 L36 40" stroke="url(#akLogoGrad)" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
</template>
