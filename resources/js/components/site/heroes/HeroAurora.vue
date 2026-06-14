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

const parallax = ref({ x: 0, y: 0 });

function onMove(e: MouseEvent) {
    const x = (e.clientX / window.innerWidth - 0.5) * 2;
    const y = (e.clientY / window.innerHeight - 0.5) * 2;
    parallax.value = { x, y };
}

onMounted(() => {
    if (!window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
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

function cardStyle(depth: number, delay: number) {
    return {
        transform: `translate(${parallax.value.x * depth}px, ${parallax.value.y * depth}px)`,
        animationDelay: `${delay}s`,
    };
}
</script>

<template>
    <section class="relative overflow-hidden">
        <!-- gradient wash -->
        <div
            class="pointer-events-none absolute inset-0"
            :style="{
                background:
                    'radial-gradient(1100px 560px at 65% -8%, color-mix(in srgb, var(--ak-grad-3) 32%, transparent), transparent 60%), radial-gradient(900px 500px at 12% 8%, color-mix(in srgb, var(--ak-grad-1) 20%, transparent), transparent 55%)',
            }"
        />
        <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-60" />
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-40" :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }" />

        <div class="ak-container relative grid items-center gap-12 pb-20 pt-36 lg:grid-cols-[1.05fr_0.95fr] lg:pb-28 lg:pt-44">
            <!-- Copy -->
            <div>
                <div class="ak-fade-up inline-flex items-center gap-2.5 rounded-full border border-ak-glass-border bg-[var(--ak-glass)] px-4 py-1.5 text-xs font-medium text-ak-text-2 backdrop-blur" style="animation-delay: 0s">
                    <span class="relative flex h-2 w-2">
                        <span class="ak-pulse-dot absolute inline-flex h-full w-full rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                        <span class="relative inline-flex h-2 w-2 rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                    </span>
                    {{ hero.badge ?? 'Software · AI · IT engineering' }}
                </div>

                <h1 class="ak-fade-up mt-6 font-display text-[2.7rem] font-semibold leading-[1.03] tracking-tight text-ak-text sm:text-6xl lg:text-[4.2rem]" style="animation-delay: 0.1s">
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
                        {{ hero.primary_label ?? 'Book a discovery call' }}
                        <Icon name="arrow-right" :size="18" />
                    </AkButton>
                    <AkButton :href="hero.secondary_url ?? '/work'" variant="ghost" size="lg">
                        {{ hero.secondary_label ?? 'See our work' }}
                    </AkButton>
                </div>

                <!-- hero stat strip -->
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

            <!-- Floating glass cards -->
            <div class="relative hidden h-[460px] lg:block">
                <div class="ak-glass ak-float absolute right-2 top-2 w-72 p-5 shadow-2xl" :style="cardStyle(-14, 0)">
                    <div class="flex items-center justify-between">
                        <span class="ak-eyebrow" :dot="false">Deploy</span>
                        <span class="inline-flex items-center gap-1.5 text-xs text-ak-text-2"><span class="h-2 w-2 rounded-full bg-emerald-400" /> Production</span>
                    </div>
                    <p class="mt-3 font-display text-sm font-semibold text-ak-text">akh/settlement-core</p>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-[var(--ak-surface-2)]">
                        <div class="h-full w-[88%] rounded-full ak-gradient-bg" />
                    </div>
                    <p class="mt-2 text-xs text-ak-text-3">Deployed in 2m 14s · 0 downtime</p>
                </div>

                <div class="ak-glass ak-float absolute left-0 top-40 w-64 p-5 shadow-2xl" :style="cardStyle(18, 1.4)">
                    <div class="flex items-center gap-2">
                        <Icon name="check" :size="16" class="text-emerald-400" />
                        <span class="text-sm font-semibold text-ak-text">PR #482 approved</span>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-ak-text-3">"Clean migration path, instant rollback. Release it." - Staff review</p>
                    <div class="mt-3 flex -space-x-2">
                        <span v-for="n in 3" :key="n" class="h-7 w-7 rounded-full border-2 border-[var(--ak-surface)] ak-gradient-bg" />
                    </div>
                </div>

                <div class="ak-glass ak-float absolute bottom-2 right-8 w-72 p-5 shadow-2xl" :style="cardStyle(-22, 0.7)">
                    <span class="ak-eyebrow" :dot="false">This sprint</span>
                    <ul class="mt-3 space-y-2.5">
                        <li class="flex items-center gap-2 text-sm text-ak-text"><Icon name="check" :size="15" class="text-emerald-400" /> Realtime settlement</li>
                        <li class="flex items-center gap-2 text-sm text-ak-text"><Icon name="check" :size="15" class="text-emerald-400" /> Observability wired</li>
                        <li class="flex items-center gap-2 text-sm text-ak-text-2"><span class="h-3.5 w-3.5 rounded-full border-2" :style="{ borderColor: 'var(--ak-grad-2)' }" /> Load test at 10×</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
