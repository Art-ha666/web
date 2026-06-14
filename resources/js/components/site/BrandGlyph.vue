<script setup lang="ts">
withDefaults(defineProps<{ size?: number; animated?: boolean }>(), {
    size: 34,
    animated: true,
});
</script>

<template>
    <span class="ak-brand-glyph relative inline-flex shrink-0" :style="{ width: `${size}px`, height: `${size}px` }">
        <svg
            :width="size"
            :height="size"
            viewBox="0 0 64 64"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            :class="animated ? 'ak-brand-glyph__svg' : ''"
            aria-hidden="true"
        >
            <defs>
                <linearGradient id="akGlyphGrad" x1="4" y1="14" x2="60" y2="50" gradientUnits="userSpaceOnUse">
                    <stop stop-color="var(--ak-grad-1, #28baf3)" />
                    <stop offset="0.5" stop-color="var(--ak-grad-2, #5778f8)" />
                    <stop offset="1" stop-color="var(--ak-grad-3, #7e2cfd)" />
                </linearGradient>
            </defs>
            <!-- interlocking brackets forming an infinity / handshake -->
            <path
                d="M30 16 L10 16 Q4 16 4 24 L4 40 Q4 48 10 48 L30 48 L20 32 Z"
                stroke="url(#akGlyphGrad)"
                stroke-width="4.5"
                stroke-linejoin="round"
                fill="none"
            />
            <path
                d="M34 16 L54 16 Q60 16 60 24 L60 40 Q60 48 54 48 L34 48 L44 32 Z"
                stroke="url(#akGlyphGrad)"
                stroke-width="4.5"
                stroke-linejoin="round"
                fill="none"
            />
            <path d="M28 24 L32 32 L28 40" stroke="url(#akGlyphGrad)" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M36 24 L32 32 L36 40" stroke="url(#akGlyphGrad)" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </span>
</template>

<style scoped>
.ak-brand-glyph__svg {
    filter: drop-shadow(0 0 10px var(--ak-glow, rgba(87, 120, 248, 0.35)));
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.ak-brand-glyph:hover .ak-brand-glyph__svg {
    transform: rotate(-6deg) scale(1.06);
}
@media (prefers-reduced-motion: reduce) {
    .ak-brand-glyph__svg {
        transition: none;
    }
}
</style>
