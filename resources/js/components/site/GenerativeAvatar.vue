<script setup lang="ts">
import { computed } from 'vue';

/**
 * Deterministic, fully synthetic avatar. Renders an on-brand "mesh gradient"
 * SVG seeded from the person's name + their initials. No real photos, no real
 * people - every team member is fictional.
 */
const props = defineProps<{ name: string }>();

function hashString(s: string): number {
    let h = 2166136261;

    for (let i = 0; i < s.length; i++) {
        h ^= s.charCodeAt(i);
        h = Math.imul(h, 16777619);
    }

    return h >>> 0;
}

const blobs = computed(() => {
    let x = hashString(props.name || 'AKH') || 1;
    const rnd = () => {
        x ^= x << 13;
        x ^= x >>> 17;
        x ^= x << 5;
        x >>>= 0;

        return x / 4294967296;
    };
    const vars = ['var(--ak-grad-1)', 'var(--ak-grad-2)', 'var(--ak-grad-3)'];

    return [0, 1, 2].map((i) => ({
        cx: 18 + rnd() * 64,
        cy: 18 + rnd() * 64,
        r: 32 + rnd() * 28,
        fill: vars[i],
    }));
});

const initials = computed(() =>
    (props.name || '')
        .split(' ')
        .map((p) => p[0])
        .filter(Boolean)
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);

const filterId = computed(() => 'ak-av-' + (hashString(props.name || 'AKH') % 1000000));
</script>

<template>
    <svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice" class="h-full w-full" role="img" :aria-label="`Avatar for ${name}`">
        <defs>
            <filter :id="filterId" x="-40%" y="-40%" width="180%" height="180%">
                <feGaussianBlur stdDeviation="10" />
            </filter>
        </defs>
        <rect x="0" y="0" width="100" height="100" :style="{ fill: 'var(--ak-surface-2)' }" />
        <g :style="{ filter: `url(#${filterId})` }" opacity="0.95">
            <circle v-for="(b, i) in blobs" :key="i" :cx="b.cx" :cy="b.cy" :r="b.r" :style="{ fill: b.fill }" />
        </g>
        <rect x="0" y="0" width="100" height="100" :style="{ fill: 'var(--ak-bg)' }" opacity="0.16" />
        <text
            x="50"
            y="52"
            text-anchor="middle"
            dominant-baseline="central"
            font-size="33"
            font-weight="700"
            :style="{ fill: '#ffffff', fontFamily: 'var(--ak-font-display), sans-serif' }"
            opacity="0.92"
        >
            {{ initials }}
        </text>
    </svg>
</template>
