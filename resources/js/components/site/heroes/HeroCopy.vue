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
    trust_items?: string[];
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

// Trust micro-row is fully editable from the admin Homepage editor (hero.trust_items).
const trustIcons = ['zap', 'check', 'sparkles'];
const trustItems = computed(() =>
    (props.hero.trust_items?.length ? props.hero.trust_items : ['One team, no hand-offs', 'Working demos weekly', 'You own the code and IP'])
        .slice(0, 3)
        .map((label, i) => ({ label, icon: trustIcons[i] ?? 'check' })),
);
</script>

<template>
    <div class="ak-container relative pb-24 pt-36 sm:pt-44 lg:pb-32">
        <div class="mx-auto max-w-3xl text-center">
            <div
                v-if="hero.badge"
                class="ak-fade-up inline-flex items-center gap-2.5 rounded-full border border-ak-glass-border bg-[var(--ak-glass)] px-4 py-1.5 text-xs font-medium text-ak-text-2 backdrop-blur"
                style="animation-delay: 0s"
            >
                <span class="relative flex h-2 w-2">
                    <span class="ak-pulse-dot absolute inline-flex h-full w-full rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                    <span class="relative inline-flex h-2 w-2 rounded-full" :style="{ background: 'var(--ak-grad-1)' }" />
                </span>
                {{ hero.badge }}
            </div>

            <p v-if="hero.eyebrow" class="ak-fade-up ak-eyebrow mx-auto mt-7 justify-center" style="animation-delay: 0.06s">
                {{ hero.eyebrow }}
            </p>

            <h1
                class="ak-fade-up mt-6 font-display text-[2.7rem] font-semibold leading-[1.03] tracking-tight text-ak-text sm:text-6xl lg:text-[4.4rem]"
                style="animation-delay: 0.1s"
            >
                <span>
                    <template v-for="(part, i) in line1Parts" :key="i">
                        <span v-if="part.accent" class="ak-gradient-text">{{ part.text }}</span>
                        <span v-else>{{ part.text }}</span>
                    </template>
                </span>
                <span v-if="hero.headline_line2" class="block text-ak-text-2">{{ hero.headline_line2 }}</span>
            </h1>

            <p v-if="hero.subhead" class="ak-fade-up mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-ak-text-2" style="animation-delay: 0.2s">
                {{ hero.subhead }}
            </p>

            <div class="ak-fade-up mt-9 flex flex-wrap items-center justify-center gap-3" style="animation-delay: 0.3s">
                <AkButton :href="hero.primary_url ?? '/contact'" variant="gradient" size="lg">
                    {{ hero.primary_label ?? 'Start a project' }}
                    <Icon name="arrow-right" :size="18" />
                </AkButton>
                <AkButton v-if="hero.secondary_label" :href="hero.secondary_url ?? '/work'" variant="ghost" size="lg">
                    <Icon name="sparkles" :size="18" />
                    {{ hero.secondary_label }}
                </AkButton>
            </div>

            <!-- trust micro-row (editable) -->
            <div
                v-if="trustItems.length"
                class="ak-fade-up mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-ak-text-3"
                style="animation-delay: 0.38s"
            >
                <span v-for="(item, i) in trustItems" :key="i" class="inline-flex items-center gap-1.5">
                    <Icon
                        :name="item.icon"
                        :size="14"
                        :class="item.icon === 'check' ? 'text-emerald-400' : ''"
                        :style="item.icon !== 'check' ? { color: i === 0 ? 'var(--ak-grad-1)' : 'var(--ak-grad-3)' } : undefined"
                    />
                    {{ item.label }}
                </span>
            </div>
        </div>

        <!-- hero stat strip -->
        <div
            v-if="stats.length"
            class="ak-fade-up ak-glass mx-auto mt-14 grid max-w-3xl grid-cols-2 gap-px overflow-hidden rounded-2xl sm:grid-cols-4"
            style="animation-delay: 0.46s"
        >
            <div v-for="(stat, i) in stats" :key="i" class="bg-[var(--ak-glass)] px-5 py-6 text-center">
                <div class="font-display text-2xl font-bold text-ak-text sm:text-3xl">
                    <span v-if="stat.prefix" class="ak-gradient-text">{{ stat.prefix }}</span
                    ><span>{{ stat.value }}</span
                    ><span v-if="stat.suffix" class="ak-gradient-text">{{ stat.suffix }}</span>
                </div>
                <div class="mt-1 text-xs text-ak-text-3">{{ stat.label }}</div>
            </div>
        </div>
    </div>
</template>
