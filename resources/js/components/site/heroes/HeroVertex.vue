<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AkButton from '../AkButton.vue';
import Icon from '../Icon.vue';

interface HeroData {
    badge?: string;
    eyebrow?: string;
    headline_line1?: string;
    headline_line2?: string;
    accent_word?: string;
    subhead?: string;
    primary_label?: string;
    primary_url?: string;
    secondary_label?: string;
    secondary_url?: string;
}

interface StatItem {
    value: string;
    prefix?: string | null;
    suffix?: string | null;
    label: string;
}

const props = defineProps<{ hero: HeroData; stats: StatItem[] }>();

// Subtle pointer-driven drift for the oversized background word only.
const drift = ref({ x: 0, y: 0 });
const reduced = ref(false);

function onMove(e: MouseEvent) {
    drift.value = {
        x: (e.clientX / window.innerWidth - 0.5) * 26,
        y: (e.clientY / window.innerHeight - 0.5) * 18,
    };
}

onMounted(() => {
    reduced.value = !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    if (!reduced.value) {
        window.addEventListener('mousemove', onMove, { passive: true });
    }
});
onBeforeUnmount(() => window.removeEventListener('mousemove', onMove));

const line1Parts = computed(() => {
    const line = props.hero.headline_line1 ?? '';
    const accent = props.hero.accent_word ?? '';

    if (!accent || !line.includes(accent)) {
return [{ text: line, accent: false }];
}

    const [before, after] = line.split(accent);

    return [
        { text: before, accent: false },
        { text: accent, accent: true },
        { text: after, accent: false },
    ];
});

// The faint oversized background word: the accent word, or a sensible fallback.
const ghostWord = computed(() => {
    const w = props.hero.accent_word || props.hero.headline_line2 || props.hero.headline_line1 || 'BUILD';

    return w.trim().split(/\s+/).slice(-1)[0]?.toUpperCase() ?? 'BUILD';
});

const driftStyle = computed(() =>
    reduced.value ? {} : { transform: `translate3d(${drift.value.x}px, ${drift.value.y}px, 0)` },
);
</script>

<template>
    <section class="relative overflow-hidden" :style="{ background: 'var(--ak-bg)' }">
        <!-- High-contrast editorial backdrop -->
        <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-40" />
        <div
            class="pointer-events-none absolute inset-0"
            :style="{
                background:
                    'radial-gradient(1200px 620px at 88% -12%, color-mix(in srgb, var(--ak-grad-2) 24%, transparent), transparent 58%), radial-gradient(820px 520px at -6% 14%, color-mix(in srgb, var(--ak-grad-1) 16%, transparent), transparent 60%)',
            }"
        />

        <!-- Oversized faint gradient word in the background -->
        <div
            aria-hidden="true"
            class="pointer-events-none absolute -right-[6%] top-[34%] select-none whitespace-nowrap font-display text-[28vw] font-black leading-none tracking-tighter lg:text-[24vw]"
            :class="{ 'transition-transform duration-700 ease-out': !reduced }"
            :style="{
                ...driftStyle,
                background:
                    'linear-gradient(120deg, color-mix(in srgb, var(--ak-grad-1) 22%, transparent), color-mix(in srgb, var(--ak-grad-3) 14%, transparent))',
                WebkitBackgroundClip: 'text',
                backgroundClip: 'text',
                color: 'transparent',
                opacity: '0.5',
            }"
        >
            {{ ghostWord }}
        </div>

        <!-- left edge hairline accent -->
        <div class="pointer-events-none absolute inset-y-0 left-0 hidden w-px lg:block" :style="{ background: 'linear-gradient(to bottom, transparent, var(--ak-hairline) 18%, var(--ak-hairline) 82%, transparent)' }" />
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-32" :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }" />

        <div class="ak-container relative pb-20 pt-36 sm:pt-44 lg:pb-28">
            <!-- Eyebrow / badge: small, mono, editorial -->
            <div class="ak-fade-up flex flex-wrap items-center gap-x-5 gap-y-3" style="animation-delay: 0s">
                <span class="inline-flex items-center gap-2.5">
                    <span class="relative flex h-2 w-2">
                        <span class="ak-pulse-dot absolute inline-flex h-full w-full rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                        <span class="relative inline-flex h-2 w-2 rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                    </span>
                    <span class="font-mono text-[0.72rem] uppercase tracking-[0.28em] text-ak-text-2">{{ hero.eyebrow ?? hero.badge ?? 'Software · AI · Product' }}</span>
                </span>
                <span class="hidden h-3.5 w-px bg-ak-hairline sm:block" />
                <span class="inline-flex items-center gap-1.5 font-mono text-[0.72rem] uppercase tracking-[0.22em] text-ak-text-3">
                    <Icon name="sparkles" :size="13" class="text-ak-text-2" />
                    {{ hero.badge ?? 'Software · AI · IT engineering' }}
                </span>
            </div>

            <!-- The enormous left-aligned headline -->
            <h1
                class="ak-fade-up mt-9 max-w-[16ch] font-display font-black leading-[0.92] tracking-[-0.035em] text-ak-text"
                style="animation-delay: 0.08s; font-size: clamp(3rem, 9.5vw, 6rem)"
            >
                <span class="block">
                    <template v-for="(part, i) in line1Parts" :key="i">
                        <span v-if="part.accent" class="ak-gradient-text">{{ part.text }}</span>
                        <span v-else>{{ part.text }}</span>
                    </template>
                </span>
                <span v-if="hero.headline_line2" class="block text-ak-text-3">{{ hero.headline_line2 }}</span>
            </h1>

            <!-- Minimal supporting copy + CTAs -->
            <div class="ak-fade-up mt-8 flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between" style="animation-delay: 0.18s">
                <p class="max-w-md border-l-2 pl-5 text-base leading-relaxed text-ak-text-2" :style="{ borderColor: 'var(--ak-grad-2)' }">
                    {{ hero.subhead }}
                </p>

                <div class="flex flex-shrink-0 flex-wrap items-center gap-3">
                    <AkButton :href="hero.primary_url ?? '/contact'" variant="gradient" size="lg">
                        {{ hero.primary_label ?? 'Start a project' }}
                        <Icon name="arrow-right" :size="18" />
                    </AkButton>
                    <AkButton :href="hero.secondary_url ?? '/work'" variant="ghost" size="lg">
                        <Icon name="zap" :size="17" />
                        {{ hero.secondary_label ?? 'See our work' }}
                    </AkButton>
                </div>
            </div>

            <!-- Thin horizontal metric row -->
            <div v-if="stats.length" class="ak-fade-up mt-16 border-t border-ak-hairline pt-7" style="animation-delay: 0.3s">
                <dl class="grid grid-cols-2 gap-x-8 gap-y-8 sm:grid-cols-4">
                    <div v-for="(stat, i) in stats" :key="i" class="group relative">
                        <span
                            class="absolute -top-7 left-0 h-px w-0 transition-all duration-500 ease-out ak-gradient-bg group-hover:w-12"
                            :class="{ 'w-8': !reduced }"
                            aria-hidden="true"
                        />
                        <dt class="flex items-baseline gap-0.5 font-display text-3xl font-extrabold tracking-tight text-ak-text sm:text-4xl">
                            <span v-if="stat.prefix" class="text-ak-text-2">{{ stat.prefix }}</span>
                            <span>{{ stat.value }}</span>
                            <span v-if="stat.suffix" class="ak-gradient-text text-2xl sm:text-3xl">{{ stat.suffix }}</span>
                        </dt>
                        <dd class="mt-2 font-mono text-[0.72rem] uppercase tracking-[0.16em] text-ak-text-3">{{ stat.label }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>
