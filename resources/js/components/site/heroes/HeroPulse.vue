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

// Service nodes for the "system status" panel.
const nodes = [
    { name: 'api-gateway', region: 'edge', state: 'ok', load: 72, latency: '38ms' },
    { name: 'settlement-core', region: 'eu-west', state: 'ok', load: 64, latency: '21ms' },
    { name: 'event-stream', region: 'us-east', state: 'ok', load: 88, latency: '12ms' },
    { name: 'inference-pool', region: 'gpu', state: 'warn', load: 94, latency: '54ms' },
] as const;

const stateColor: Record<string, string> = {
    ok: 'var(--ak-grad-1)',
    warn: 'var(--ak-grad-3)',
};

// Live-feeling uptime counter - gated behind reduced-motion.
const uptime = ref(99.98);
let timer: number | undefined;
let reduced = false;

onMounted(() => {
    reduced = !!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    if (reduced) {
return;
}

    timer = window.setInterval(() => {
        const drift = (Math.random() - 0.5) * 0.02;
        uptime.value = Math.min(99.999, Math.max(99.95, uptime.value + drift));
    }, 2400);
});

onBeforeUnmount(() => {
    if (timer !== undefined) {
window.clearInterval(timer);
}
});
</script>

<template>
    <section class="ak-pulse relative overflow-hidden">
        <!-- ambient teal wash, calm + engineered -->
        <div
            class="pointer-events-none absolute inset-0"
            :style="{
                background:
                    'radial-gradient(1000px 520px at 78% -6%, color-mix(in srgb, var(--ak-grad-1) 22%, transparent), transparent 58%), radial-gradient(760px 460px at 6% 14%, color-mix(in srgb, var(--ak-grad-2) 14%, transparent), transparent 60%)',
            }"
        />
        <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-50" />
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-44" :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }" />

        <div class="ak-container relative grid items-center gap-12 pb-20 pt-36 sm:pt-44 lg:grid-cols-[1.02fr_0.98fr] lg:gap-16 lg:pb-28">
            <!-- Copy -->
            <div>
                <div
                    class="ak-fade-up inline-flex items-center gap-2.5 rounded-full border border-ak-glass-border bg-[var(--ak-glass)] px-4 py-1.5 text-xs font-medium text-ak-text-2 backdrop-blur"
                    style="animation-delay: 0s"
                >
                    <span class="relative flex h-2 w-2">
                        <span class="ak-pulse-dot absolute inline-flex h-full w-full rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                        <span class="relative inline-flex h-2 w-2 rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                    </span>
                    {{ hero.badge ?? 'Engineering you can watch live' }}
                </div>

                <p v-if="hero.eyebrow" class="ak-fade-up ak-eyebrow mt-7" style="animation-delay: 0.06s">{{ hero.eyebrow }}</p>

                <h1
                    class="ak-fade-up mt-4 font-display text-[2.7rem] font-semibold leading-[1.04] tracking-tight text-ak-text sm:text-6xl lg:text-[4.1rem]"
                    style="animation-delay: 0.1s"
                >
                    <span>
                        <template v-for="(part, i) in line1Parts" :key="i">
                            <span v-if="part.accent" class="ak-gradient-text">{{ part.text }}</span>
                            <span v-else>{{ part.text }}</span>
                        </template>
                    </span>
                    <span class="block text-ak-text-2">{{ hero.headline_line2 }}</span>
                </h1>

                <p class="ak-fade-up mt-6 max-w-xl text-lg leading-relaxed text-ak-text-2" style="animation-delay: 0.2s">
                    {{ hero.subhead }}
                </p>

                <div class="ak-fade-up mt-8 flex flex-wrap items-center gap-3" style="animation-delay: 0.3s">
                    <AkButton :href="hero.primary_url ?? '/contact'" variant="gradient" size="lg">
                        {{ hero.primary_label ?? 'Start a project' }}
                        <Icon name="arrow-right" :size="18" />
                    </AkButton>
                    <AkButton :href="hero.secondary_url ?? '/work'" variant="ghost" size="lg">
                        {{ hero.secondary_label ?? 'View the platform' }}
                    </AkButton>
                </div>

                <!-- stat strip -->
                <div v-if="stats.length" class="ak-fade-up mt-12 flex flex-wrap gap-x-10 gap-y-5" style="animation-delay: 0.42s">
                    <div v-for="(stat, i) in stats" :key="i" class="relative pl-5">
                        <span class="absolute left-0 top-1 h-10 w-px ak-gradient-bg opacity-60" />
                        <div class="font-display text-2xl font-bold text-ak-text">
                            <span v-if="stat.prefix">{{ stat.prefix }}</span>{{ stat.value }}<span v-if="stat.suffix">{{ stat.suffix }}</span>
                        </div>
                        <div class="mt-0.5 text-xs text-ak-text-3">{{ stat.label }}</div>
                    </div>
                </div>
            </div>

            <!-- System status panel -->
            <div class="ak-fade-up relative" style="animation-delay: 0.24s">
                <!-- soft glow behind panel -->
                <div
                    class="pointer-events-none absolute -inset-6 -z-10 rounded-[2rem] opacity-70 blur-2xl"
                    :style="{ background: 'radial-gradient(60% 60% at 70% 20%, var(--ak-glow), transparent 70%)' }"
                />

                <div class="ak-glass relative overflow-hidden rounded-2xl p-6 shadow-2xl sm:p-7">
                    <!-- faint internal grid -->
                    <div
                        class="pointer-events-none absolute inset-0 opacity-[0.55]"
                        :style="{
                            backgroundImage:
                                'linear-gradient(var(--ak-hairline) 1px, transparent 1px), linear-gradient(90deg, var(--ak-hairline) 1px, transparent 1px)',
                            backgroundSize: '34px 34px',
                            maskImage: 'radial-gradient(120% 100% at 80% 0%, #000 35%, transparent 78%)',
                            WebkitMaskImage: 'radial-gradient(120% 100% at 80% 0%, #000 35%, transparent 78%)',
                        }"
                    />

                    <!-- panel header -->
                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="grid h-8 w-8 place-items-center rounded-lg" :style="{ background: 'color-mix(in srgb, var(--ak-grad-1) 16%, transparent)' }">
                                <Icon name="zap" :size="16" :style="{ color: 'var(--ak-grad-1)' }" />
                            </span>
                            <div>
                                <p class="font-mono text-xs uppercase tracking-[0.18em] text-ak-text-3">System Status</p>
                                <p class="font-display text-sm font-semibold text-ak-text">akh · production cluster</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 font-mono text-[11px]"
                            :style="{
                                color: 'var(--ak-grad-1)',
                                background: 'color-mix(in srgb, var(--ak-grad-1) 12%, transparent)',
                                border: '1px solid color-mix(in srgb, var(--ak-grad-1) 28%, transparent)',
                            }"
                        >
                            <span class="ak-pulse-dot h-1.5 w-1.5 rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                            LIVE
                        </span>
                    </div>

                    <!-- uptime / scanning readout -->
                    <div class="relative mt-5 overflow-hidden rounded-xl border border-ak-hairline bg-[var(--ak-surface-2)] p-4">
                        <div class="ak-pulse-scan pointer-events-none absolute inset-y-0 -left-1/3 w-1/3" :style="{ background: 'linear-gradient(90deg, transparent, color-mix(in srgb, var(--ak-grad-1) 14%, transparent), transparent)' }" />
                        <div class="relative flex items-end justify-between">
                            <div>
                                <p class="font-mono text-[11px] uppercase tracking-wider text-ak-text-3">Uptime · 90d</p>
                                <p class="mt-1 font-display text-3xl font-bold text-ak-text tabular-nums">{{ uptime.toFixed(2) }}<span class="text-ak-text-3">%</span></p>
                            </div>
                            <div class="flex items-end gap-1">
                                <span
                                    v-for="n in 14"
                                    :key="n"
                                    class="ak-pulse-eq w-1 rounded-sm"
                                    :style="{
                                        height: `${10 + ((n * 7) % 26)}px`,
                                        background: 'var(--ak-grad-1)',
                                        opacity: 0.35 + ((n % 4) * 0.16),
                                        animationDelay: `${(n % 7) * 0.16}s`,
                                    }"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- service nodes -->
                    <ul class="relative mt-5 space-y-3.5">
                        <li v-for="(node, i) in nodes" :key="node.name" class="ak-pulse-row group" :style="{ animationDelay: `${0.4 + i * 0.12}s` }">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span class="relative flex h-2.5 w-2.5 shrink-0">
                                        <span class="ak-pulse-dot absolute inline-flex h-full w-full rounded-full opacity-70" :style="{ background: stateColor[node.state] }" />
                                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full" :style="{ background: stateColor[node.state] }" />
                                    </span>
                                    <span class="truncate font-mono text-[13px] text-ak-text">{{ node.name }}</span>
                                    <span class="hidden rounded border border-ak-hairline px-1.5 py-px font-mono text-[10px] text-ak-text-3 sm:inline">{{ node.region }}</span>
                                </div>
                                <span class="shrink-0 font-mono text-[11px] tabular-nums text-ak-text-3">{{ node.latency }}</span>
                            </div>
                            <!-- animated throughput bar -->
                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full" :style="{ background: 'color-mix(in srgb, var(--ak-surface-2) 80%, var(--ak-bg))' }">
                                <div
                                    class="ak-pulse-bar relative h-full rounded-full"
                                    :style="{
                                        width: `${node.load}%`,
                                        background: 'linear-gradient(90deg, color-mix(in srgb, var(--ak-grad-2) 70%, transparent), var(--ak-grad-1))',
                                        animationDelay: `${i * 0.5}s`,
                                    }"
                                />
                            </div>
                        </li>
                    </ul>

                    <!-- footer line -->
                    <div class="relative mt-6 flex items-center justify-between border-t border-ak-hairline pt-4">
                        <span class="inline-flex items-center gap-2 font-mono text-[11px] text-ak-text-3">
                            <Icon name="check" :size="13" :style="{ color: 'var(--ak-grad-1)' }" />
                            deploy #4821 · green
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-mono text-[11px] text-ak-text-3">
                            <Icon name="sparkles" :size="13" :style="{ color: 'var(--ak-grad-3)' }" />
                            auto-scaled · 0 incidents
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Throughput bars: subtle breathing scale on the X axis, anchored left. */
.ak-pulse-bar {
    transform-origin: left center;
    animation: ak-pulse-bar 3.6s ease-in-out infinite;
}
.ak-pulse-bar::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background: linear-gradient(90deg, transparent, color-mix(in srgb, #fff 28%, transparent), transparent);
    transform: translateX(-100%);
    animation: ak-pulse-shine 3.2s ease-in-out infinite;
}

@keyframes ak-pulse-bar {
    0%,
    100% {
        transform: scaleX(0.92);
        opacity: 0.85;
    }
    50% {
        transform: scaleX(1);
        opacity: 1;
    }
}

@keyframes ak-pulse-shine {
    0% {
        transform: translateX(-100%);
    }
    55%,
    100% {
        transform: translateX(220%);
    }
}

/* Equalizer ticks on the uptime readout. */
.ak-pulse-eq {
    transform-origin: bottom center;
    animation: ak-pulse-eq 1.8s ease-in-out infinite;
}
@keyframes ak-pulse-eq {
    0%,
    100% {
        transform: scaleY(0.5);
    }
    50% {
        transform: scaleY(1);
    }
}

/* Horizontal scan sweep across the uptime card. */
.ak-pulse-scan {
    animation: ak-pulse-scan 4.5s linear infinite;
}
@keyframes ak-pulse-scan {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(450%);
    }
}

/* Node rows reveal in sequence. */
.ak-pulse-row {
    opacity: 0;
    transform: translateY(8px);
    animation: ak-pulse-row 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes ak-pulse-row {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .ak-pulse-bar,
    .ak-pulse-bar::after,
    .ak-pulse-eq,
    .ak-pulse-scan {
        animation: none;
    }
    .ak-pulse-bar {
        transform: scaleX(1);
        opacity: 1;
    }
    .ak-pulse-row {
        opacity: 1;
        transform: none;
        animation: none;
    }
}
</style>
