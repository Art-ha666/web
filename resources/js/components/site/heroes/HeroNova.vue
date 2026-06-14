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

/* Split headline_line1 around the accent word so it can be gradient-wrapped. */
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

/* Static bar-chart values (rendered with divs) + an animated reveal that
   respects prefers-reduced-motion. */
const bars = [
    { h: 44, label: 'Mon' },
    { h: 62, label: 'Tue' },
    { h: 51, label: 'Wed' },
    { h: 78, label: 'Thu' },
    { h: 66, label: 'Fri' },
    { h: 92, label: 'Sat' },
    { h: 71, label: 'Sun' },
];

const revealed = ref(false);
let raf = 0;

onMounted(() => {
    const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    if (reduce) {
        revealed.value = true;

        return;
    }

    raf = window.requestAnimationFrame(() => {
        raf = window.requestAnimationFrame(() => {
            revealed.value = true;
        });
    });
});

onBeforeUnmount(() => {
    if (raf) {
window.cancelAnimationFrame(raf);
}
});
</script>

<template>
    <section class="relative overflow-hidden">
        <!-- Soft light wash: cool tinted radials over the light theme bg -->
        <div
            class="pointer-events-none absolute inset-0"
            :style="{
                background:
                    'radial-gradient(1000px 520px at 88% -6%, color-mix(in srgb, var(--ak-grad-3) 22%, transparent), transparent 58%), radial-gradient(820px 480px at 4% 4%, color-mix(in srgb, var(--ak-grad-1) 14%, transparent), transparent 52%)',
            }"
        />
        <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-40" />
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-32" :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }" />

        <div class="ak-container relative grid items-center gap-14 pb-24 pt-36 sm:pt-44 lg:grid-cols-[1.02fr_0.98fr] lg:pb-32">
            <!-- ── Left: copy ──────────────────────────────────────────── -->
            <div>
                <div
                    class="ak-fade-up inline-flex items-center gap-2.5 rounded-full border border-ak-hairline bg-ak-surface px-4 py-1.5 text-xs font-medium text-ak-text-2 shadow-sm"
                    style="animation-delay: 0s"
                >
                    <Icon name="sparkles" :size="14" :style="{ color: 'var(--ak-grad-2)' }" />
                    {{ hero.badge ?? 'Trusted by product teams worldwide' }}
                </div>

                <p v-if="hero.eyebrow" class="ak-eyebrow ak-fade-up mt-7" style="animation-delay: 0.06s">{{ hero.eyebrow }}</p>

                <h1
                    class="ak-fade-up mt-5 font-display text-[2.85rem] font-semibold leading-[1.02] tracking-tight text-ak-text sm:text-6xl lg:text-[4.4rem]"
                    style="animation-delay: 0.12s"
                >
                    <span>
                        <template v-for="(part, i) in line1Parts" :key="i">
                            <span v-if="part.accent" class="ak-gradient-text">{{ part.text }}</span>
                            <span v-else>{{ part.text }}</span>
                        </template>
                    </span>
                    <span class="block text-ak-text-2">{{ hero.headline_line2 }}</span>
                </h1>

                <p class="ak-fade-up mt-6 max-w-xl text-lg leading-relaxed text-ak-text-2" style="animation-delay: 0.22s">
                    {{ hero.subhead }}
                </p>

                <div class="ak-fade-up mt-9 flex flex-wrap items-center gap-3" style="animation-delay: 0.32s">
                    <AkButton :href="hero.primary_url ?? '/contact'" variant="gradient" size="lg">
                        {{ hero.primary_label ?? 'Start a project' }}
                        <Icon name="arrow-right" :size="18" />
                    </AkButton>
                    <AkButton :href="hero.secondary_url ?? '/work'" variant="ghost" size="lg">
                        {{ hero.secondary_label ?? 'View our work' }}
                    </AkButton>
                </div>

                <!-- trust micro-line -->
                <div class="ak-fade-up mt-6 flex items-center gap-2 text-xs text-ak-text-3" style="animation-delay: 0.38s">
                    <Icon name="check" :size="14" :style="{ color: 'var(--ak-grad-1)' }" />
                    <span>No-pressure discovery call · Fixed-scope proposals in 48h</span>
                </div>

                <!-- stat strip -->
                <div v-if="stats.length" class="ak-fade-up mt-12 grid max-w-xl grid-cols-2 gap-x-8 gap-y-7 sm:grid-cols-4" style="animation-delay: 0.46s">
                    <div v-for="(stat, i) in stats" :key="i" class="relative pl-4">
                        <span class="absolute left-0 top-1 h-9 w-[3px] rounded-full ak-gradient-bg" />
                        <div class="font-display text-2xl font-bold leading-none text-ak-text">
                            <span v-if="stat.prefix">{{ stat.prefix }}</span>{{ stat.value }}<span v-if="stat.suffix">{{ stat.suffix }}</span>
                        </div>
                        <div class="mt-1.5 text-[0.7rem] uppercase tracking-wide text-ak-text-3">{{ stat.label }}</div>
                    </div>
                </div>
            </div>

            <!-- ── Right: light product dashboard mock ──────────────────── -->
            <div class="ak-fade-up relative" style="animation-delay: 0.2s">
                <!-- glow halo behind the panel -->
                <div
                    class="pointer-events-none absolute -inset-6 -z-10 rounded-[2rem] opacity-70 blur-2xl"
                    :style="{
                        background:
                            'radial-gradient(60% 60% at 70% 20%, color-mix(in srgb, var(--ak-grad-3) 26%, transparent), transparent 70%), radial-gradient(50% 50% at 20% 90%, color-mix(in srgb, var(--ak-grad-1) 18%, transparent), transparent 70%)',
                    }"
                />

                <!-- Main dashboard surface -->
                <div class="ak-card overflow-hidden p-0 shadow-2xl">
                    <!-- window chrome -->
                    <div class="flex items-center gap-2 border-b border-ak-hairline bg-ak-surface-2 px-5 py-3.5">
                        <span class="h-3 w-3 rounded-full" :style="{ background: 'color-mix(in srgb, var(--ak-text-3) 45%, transparent)' }" />
                        <span class="h-3 w-3 rounded-full" :style="{ background: 'color-mix(in srgb, var(--ak-text-3) 30%, transparent)' }" />
                        <span class="h-3 w-3 rounded-full" :style="{ background: 'color-mix(in srgb, var(--ak-text-3) 20%, transparent)' }" />
                        <span class="ml-3 font-mono text-xs text-ak-text-3">app.akh.dev / overview</span>
                        <span class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-ak-surface px-2.5 py-1 text-[0.65rem] font-medium text-ak-text-2">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="ak-pulse-dot absolute inline-flex h-full w-full rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                            </span>
                            Live
                        </span>
                    </div>

                    <!-- body -->
                    <div class="space-y-5 p-5 sm:p-6">
                        <!-- top metric row -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-xl border border-ak-hairline bg-ak-surface-2 p-3.5">
                                <div class="flex items-center gap-1.5 text-[0.65rem] uppercase tracking-wide text-ak-text-3">
                                    <Icon name="zap" :size="12" :style="{ color: 'var(--ak-grad-2)' }" /> Uptime
                                </div>
                                <div class="mt-2 font-display text-xl font-bold text-ak-text">99.98%</div>
                            </div>
                            <div class="rounded-xl border border-ak-hairline bg-ak-surface-2 p-3.5">
                                <div class="text-[0.65rem] uppercase tracking-wide text-ak-text-3">Deploys</div>
                                <div class="mt-2 font-display text-xl font-bold text-ak-text">214</div>
                            </div>
                            <div class="rounded-xl border border-ak-hairline bg-ak-surface-2 p-3.5">
                                <div class="text-[0.65rem] uppercase tracking-wide text-ak-text-3">p95</div>
                                <div class="mt-2 font-display text-xl font-bold text-ak-text">42<span class="text-sm text-ak-text-3">ms</span></div>
                            </div>
                        </div>

                        <!-- featured chart card -->
                        <div class="rounded-2xl border border-ak-hairline bg-ak-surface p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-xs text-ak-text-3">Throughput this week</div>
                                    <div class="mt-1 font-display text-2xl font-bold text-ak-text">
                                        1.42<span class="text-base font-semibold text-ak-text-2">M req</span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full border border-ak-hairline bg-ak-surface-2 px-2.5 py-1 text-[0.7rem] font-semibold" :style="{ color: 'var(--ak-grad-1)' }">
                                    <Icon name="arrow-up-right" :size="13" /> +18.6%
                                </span>
                            </div>

                            <!-- div bar chart -->
                            <div class="mt-5 flex h-28 items-end gap-2.5">
                                <div v-for="(bar, i) in bars" :key="i" class="group flex flex-1 flex-col items-center gap-2">
                                    <div class="flex w-full flex-1 items-end">
                                        <div
                                            class="w-full rounded-md ak-gradient-bg transition-all duration-700 ease-out"
                                            :style="{
                                                height: revealed ? bar.h + '%' : '0%',
                                                transitionDelay: 80 * i + 'ms',
                                                opacity: i === 5 ? 1 : 0.78,
                                            }"
                                        />
                                    </div>
                                    <span class="font-mono text-[0.6rem] text-ak-text-3">{{ bar.label }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- bottom row: status + team -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl border border-ak-hairline bg-ak-surface p-4">
                                <div class="text-[0.65rem] uppercase tracking-wide text-ak-text-3">Pipeline</div>
                                <ul class="mt-3 space-y-2.5">
                                    <li class="flex items-center gap-2 text-sm text-ak-text">
                                        <Icon name="check" :size="14" :style="{ color: 'var(--ak-grad-1)' }" /> Build &amp; test
                                    </li>
                                    <li class="flex items-center gap-2 text-sm text-ak-text">
                                        <Icon name="check" :size="14" :style="{ color: 'var(--ak-grad-1)' }" /> Security scan
                                    </li>
                                    <li class="flex items-center gap-2 text-sm text-ak-text-2">
                                        <span class="h-3.5 w-3.5 shrink-0 rounded-full border-2" :style="{ borderColor: 'var(--ak-grad-2)' }" /> Rollout 70%
                                    </li>
                                </ul>
                            </div>

                            <div class="flex flex-col justify-between rounded-2xl border border-ak-hairline bg-ak-surface p-4">
                                <div>
                                    <div class="text-[0.65rem] uppercase tracking-wide text-ak-text-3">Your team</div>
                                    <div class="mt-3 flex -space-x-2.5">
                                        <span
                                            v-for="n in 4"
                                            :key="n"
                                            class="h-8 w-8 rounded-full border-2 ak-gradient-bg"
                                            :style="{ borderColor: 'var(--ak-surface)', opacity: 1 - (n - 1) * 0.14 }"
                                        />
                                        <span
                                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 bg-ak-surface-2 text-[0.65rem] font-semibold text-ak-text-2"
                                            :style="{ borderColor: 'var(--ak-surface)' }"
                                        >
                                            +6
                                        </span>
                                    </div>
                                </div>
                                <p class="mt-4 text-xs leading-relaxed text-ak-text-3">Experienced engineers, embedded &amp; on call.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- floating toast: deployed -->
                <div class="ak-float ak-glass absolute -bottom-5 -left-5 hidden items-center gap-3 px-4 py-3 shadow-xl sm:flex" style="animation-delay: 0.6s">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full ak-gradient-bg text-white">
                        <Icon name="check" :size="16" />
                    </span>
                    <div>
                        <div class="text-sm font-semibold text-ak-text">Deployed to production</div>
                        <div class="font-mono text-[0.7rem] text-ak-text-3">2m 14s · 0 downtime</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
