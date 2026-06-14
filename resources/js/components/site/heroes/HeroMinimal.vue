<script setup lang="ts">
import { computed } from 'vue';
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

// Split headline_line1 around the accent word so only that word gets the gradient.
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
</script>

<template>
    <section class="relative overflow-hidden bg-[var(--ak-bg)]">
        <!-- faint structural grid -->
        <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-40" />

        <!-- single restrained glow, dead center, very soft -->
        <div
            class="pointer-events-none absolute inset-0"
            :style="{
                background:
                    'radial-gradient(680px 420px at 50% 18%, color-mix(in srgb, var(--ak-grad-2) 14%, transparent), transparent 70%)',
            }"
        />

        <!-- hairline frame: two whisper-thin guide lines that pull the eye to center -->
        <div class="pointer-events-none absolute inset-y-0 left-1/2 hidden w-px -translate-x-1/2 md:block" :style="{ background: 'linear-gradient(to bottom, transparent, var(--ak-hairline) 28%, var(--ak-hairline) 72%, transparent)', opacity: 0.5 }" />

        <!-- bottom fade into page -->
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-32" :style="{ background: 'linear-gradient(to top, var(--ak-bg), transparent)' }" />

        <div class="ak-container relative pb-24 pt-36 sm:pt-44 lg:pb-32">
            <div class="mx-auto flex max-w-3xl flex-col items-center text-center">
                <!-- eyebrow -->
                <div
                    class="ak-fade-up inline-flex items-center gap-2.5 rounded-full border border-ak-hairline bg-[var(--ak-glass)] px-4 py-1.5 text-[0.7rem] font-medium uppercase tracking-[0.22em] text-ak-text-3 backdrop-blur"
                    style="animation-delay: 0s"
                >
                    <Icon name="sparkles" :size="13" class="text-ak-text-2" />
                    {{ hero.eyebrow ?? hero.badge ?? 'Engineering agency' }}
                </div>

                <!-- headline -->
                <h1
                    class="ak-fade-up mt-8 font-display text-[2.9rem] font-semibold leading-[1.02] tracking-[-0.03em] text-ak-text sm:text-6xl lg:text-[5rem]"
                    style="animation-delay: 0.08s"
                >
                    <span class="block">
                        <template v-for="(part, i) in line1Parts" :key="i">
                            <span v-if="part.accent" class="ak-gradient-text">{{ part.text }}</span>
                            <span v-else>{{ part.text }}</span>
                        </template>
                    </span>
                    <span v-if="hero.headline_line2" class="mt-1 block text-ak-text-3">{{ hero.headline_line2 }}</span>
                </h1>

                <!-- one-line subhead -->
                <p
                    class="ak-fade-up mt-7 max-w-xl text-balance text-base leading-relaxed text-ak-text-2 sm:text-lg"
                    style="animation-delay: 0.16s"
                >
                    {{ hero.subhead }}
                </p>

                <!-- CTAs: one primary, one quiet text link -->
                <div class="ak-fade-up mt-10 flex flex-col items-center gap-4 sm:flex-row sm:gap-6" style="animation-delay: 0.24s">
                    <AkButton :href="hero.primary_url ?? '/contact'" variant="gradient" size="lg">
                        {{ hero.primary_label ?? 'Start a project' }}
                        <Icon name="arrow-right" :size="18" />
                    </AkButton>
                    <AkButton :href="hero.secondary_url ?? '/work'" variant="ghost" size="lg">
                        <span class="text-ak-text-2">{{ hero.secondary_label ?? 'View our work' }}</span>
                        <Icon name="arrow-up-right" :size="16" class="text-ak-text-3" />
                    </AkButton>
                </div>
            </div>

            <!-- stats strip: minimal, centered, separated by hairlines -->
            <div
                v-if="stats.length"
                class="ak-fade-up mx-auto mt-20 flex max-w-2xl flex-wrap items-center justify-center divide-x divide-ak-hairline border-y border-ak-hairline"
                style="animation-delay: 0.34s"
            >
                <div v-for="(stat, i) in stats" :key="i" class="px-7 py-5 text-center sm:px-9">
                    <div class="font-display text-2xl font-semibold tracking-tight text-ak-text sm:text-3xl">
                        <span v-if="stat.prefix" class="text-ak-text-3">{{ stat.prefix }}</span
                        >{{ stat.value }}<span v-if="stat.suffix" class="ak-gradient-text">{{ stat.suffix }}</span>
                    </div>
                    <div class="mt-1.5 text-[0.7rem] font-medium uppercase tracking-[0.16em] text-ak-text-3">{{ stat.label }}</div>
                </div>
            </div>
        </div>
    </section>
</template>
