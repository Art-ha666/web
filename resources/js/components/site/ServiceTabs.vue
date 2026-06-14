<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Icon from './Icon.vue';

interface Service {
    title: string;
    slug: string;
    icon?: string;
    tab_label?: string;
    short_blurb?: string;
    value_metric?: string;
    benefit_bullets?: string[];
    tech_stack?: string[];
    gradient?: { from?: string; via?: string; to?: string };
}

const props = withDefaults(defineProps<{ services: Service[]; exploreLabel?: string }>(), {
    exploreLabel: 'Explore service',
});
const active = ref(0);
const current = computed(() => props.services[active.value]);

const backplate = computed(() => {
    const g = current.value?.gradient ?? {};
    const from = g.from ?? 'var(--ak-grad-1)';
    const via = g.via ?? 'var(--ak-grad-2)';
    const to = g.to ?? 'var(--ak-grad-3)';

    return `linear-gradient(135deg, ${from}, ${via} 50%, ${to})`;
});
</script>

<template>
    <div>
        <!-- Tabs -->
        <div class="ak-mask-fade-x -mx-2 flex gap-2 overflow-x-auto px-2 pb-2">
            <button
                v-for="(svc, i) in services"
                :key="svc.slug"
                class="ak-focusable inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2.5 text-sm font-medium transition"
                :class="active === i ? 'border-transparent text-ak-on-primary' : 'border-ak-glass-border text-ak-text-2 hover:text-ak-text'"
                :style="active === i ? { background: 'var(--ak-primary)' } : {}"
                @click="active = i"
            >
                <Icon :name="svc.icon ?? 'sparkles'" :size="16" />
                {{ svc.tab_label ?? svc.title }}
            </button>
        </div>

        <!-- Panel -->
        <Transition mode="out-in" enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 translate-y-2" leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0">
            <div :key="active" class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="ak-card flex flex-col justify-center p-8">
                    <p class="ak-eyebrow">{{ current.value_metric }}</p>
                    <h3 class="mt-4 font-display text-2xl font-semibold text-ak-text sm:text-3xl">{{ current.title }}</h3>
                    <p class="mt-3 text-ak-text-2">{{ current.short_blurb }}</p>
                    <ul class="mt-6 space-y-3">
                        <li v-for="bullet in current.benefit_bullets" :key="bullet" class="flex items-start gap-3 text-sm text-ak-text">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full ak-gradient-bg text-white">
                                <Icon name="check" :size="12" />
                            </span>
                            {{ bullet }}
                        </li>
                    </ul>
                    <div class="mt-7 flex flex-wrap items-center gap-2">
                        <span v-for="tech in current.tech_stack" :key="tech" class="ak-chip">{{ tech }}</span>
                    </div>
                    <Link :href="`/services/${current.slug}`" class="ak-focusable group mt-7 inline-flex items-center gap-2 text-sm font-semibold" :style="{ color: 'var(--ak-primary)' }">
                        {{ exploreLabel }}
                        <Icon name="arrow-right" :size="16" class="transition-transform group-hover:translate-x-1" />
                    </Link>
                </div>

                <!-- gradient backplate showcase -->
                <div class="relative min-h-[20rem] overflow-hidden rounded-[var(--ak-radius,16px)]">
                    <div class="ak-gradient-pan absolute inset-0" :style="{ background: backplate }" />
                    <div class="ak-grid-texture absolute inset-0 opacity-30" />
                    <div class="absolute inset-0 flex items-center justify-center p-8">
                        <div class="ak-glass w-full max-w-sm p-6">
                            <div class="flex items-center justify-between">
                                <Icon :name="current.icon ?? 'sparkles'" :size="22" class="text-white" />
                                <span class="rounded-full bg-white/15 px-2.5 py-1 text-xs font-medium text-white">{{ current.tab_label ?? current.title }}</span>
                            </div>
                            <div class="mt-6 space-y-2.5">
                                <div class="h-2 w-3/4 rounded-full bg-white/30" />
                                <div class="h-2 w-full rounded-full bg-white/20" />
                                <div class="h-2 w-5/6 rounded-full bg-white/20" />
                            </div>
                            <div class="mt-6 font-display text-3xl font-bold text-white">{{ current.value_metric }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
