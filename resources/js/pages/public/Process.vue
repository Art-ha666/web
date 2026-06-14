<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import ClosingCta from '@/components/site/ClosingCta.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import ProcessTimeline from '@/components/site/ProcessTimeline.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { useSite } from '@/composables/useSite';

const props = defineProps<{
    content: Record<string, any>;
    page: {
        title?: string;
        blocks?: Array<{ type?: string; data?: Record<string, any> }>;
        seo_title?: string;
        seo_description?: string;
    } | null;
    steps: Array<{ number?: string; title: string; description?: string; deliverable_tag?: string; icon?: string }>;
    cta: {
        eyebrow?: string;
        headline: string;
        body?: string;
        primary_cta_label?: string;
        primary_cta_url?: string;
        secondary_cta_label?: string;
        secondary_cta_url?: string;
        microcopy?: string;
    } | null;
}>();

const { site } = useSite();

const c = computed(() => props.content ?? {});
const on = (key: string): boolean => c.value[key]?.enabled !== false;

const engagementModels = computed<Array<{ icon: string; title: string; description: string; points: string[] }>>(
    () => c.value.engagement?.items ?? [],
);
const principles = computed<Array<{ icon: string; title: string; description: string }>>(
    () => c.value.principles?.items ?? [],
);

const richBlocks = computed(() =>
    Array.isArray(props.page?.blocks)
        ? props.page!.blocks!.filter((b) => b.type === 'richtext' && b.data?.html)
        : [],
);
const hasRichText = computed(() => richBlocks.value.length > 0);
</script>

<template>
    <div>
        <Head>
            <title>{{ c.seo?.title || `Process - ${site?.name || 'AKH Solutions'}` }}</title>
            <meta name="description" :content="c.seo?.description" />
        </Head>

        <!-- Header -->
        <section class="ak-container pt-36 pb-16 sm:pt-44 sm:pb-24">
            <ScrollReveal>
                <p class="ak-eyebrow">{{ c.hero?.eyebrow }}</p>
                <h1 class="mt-5 max-w-3xl font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ak-text sm:text-5xl lg:text-6xl">
                    {{ c.hero?.title }} <span class="ak-gradient-text">{{ c.hero?.title_accent }}</span>.
                </h1>
                <p class="mt-6 max-w-2xl text-lg text-ak-text-2">
                    {{ c.hero?.body }}
                </p>
            </ScrollReveal>

            <ScrollReveal :delay="120">
                <div v-if="hasRichText" class="ak-prose mt-10 max-w-3xl">
                    <div v-for="(block, i) in richBlocks" :key="i" v-html="block.data!.html" />
                </div>
                <p v-else class="mt-10 max-w-3xl text-ak-text-2">
                    {{ c.hero?.fallback }}
                </p>
            </ScrollReveal>
        </section>

        <!-- Process timeline -->
        <section v-if="on('timeline')" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader
                    :eyebrow="c.timeline?.eyebrow"
                    :title="c.timeline?.title"
                    :subtitle="c.timeline?.subtitle"
                />
            </ScrollReveal>
            <div class="mt-12">
                <ProcessTimeline :steps="steps" />
            </div>
        </section>

        <!-- Engagement models -->
        <section v-if="on('engagement') && engagementModels.length" class="relative border-y border-ak-hairline py-16 sm:py-24" :style="{ background: 'var(--ak-bg-2)' }">
            <div class="ak-container">
                <ScrollReveal>
                    <SectionHeader
                        :eyebrow="c.engagement?.eyebrow"
                        :title="c.engagement?.title"
                        :subtitle="c.engagement?.subtitle"
                    />
                </ScrollReveal>
                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    <ScrollReveal v-for="(model, i) in engagementModels" :key="model.title" :delay="i * 90">
                        <div class="ak-card ak-card-hover flex h-full flex-col p-7">
                            <span
                                class="flex h-12 w-12 items-center justify-center rounded-xl border border-ak-glass-border text-ak-text-2"
                                :style="{ background: 'var(--ak-surface-2)' }"
                            >
                                <Icon :name="model.icon" :size="22" />
                            </span>
                            <h3 class="mt-6 font-display text-xl font-semibold text-ak-text">{{ model.title }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-ak-text-2">{{ model.description }}</p>
                            <ul class="mt-6 space-y-3 border-t border-ak-hairline pt-6">
                                <li
                                    v-for="point in model.points"
                                    :key="point"
                                    class="flex items-start gap-2.5 text-sm text-ak-text-2"
                                >
                                    <Icon
                                        name="check"
                                        :size="14"
                                        class="mt-0.5 shrink-0"
                                        :style="{ color: 'var(--ak-primary)' }"
                                    />
                                    <span>{{ point }}</span>
                                </li>
                            </ul>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Why it works -->
        <section v-if="on('principles') && principles.length" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader
                    :eyebrow="c.principles?.eyebrow"
                    :title="c.principles?.title"
                    :subtitle="c.principles?.subtitle"
                />
            </ScrollReveal>
            <div class="mt-12 grid gap-6 md:grid-cols-3">
                <ScrollReveal v-for="(principle, i) in principles" :key="principle.title" :delay="i * 90">
                    <div class="ak-card flex h-full flex-col p-7">
                        <span class="ak-gradient-text">
                            <Icon :name="principle.icon" :size="26" />
                        </span>
                        <h3 class="mt-5 font-display text-lg font-semibold text-ak-text">{{ principle.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ak-text-2">{{ principle.description }}</p>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Closing CTA -->
        <ClosingCta v-if="cta" :cta="cta" />
        <section v-else class="ak-container py-20 sm:py-28">
            <ScrollReveal>
                <div class="relative overflow-hidden rounded-[2rem] border border-ak-glass-border p-10 text-center sm:p-16">
                    <div class="ak-gradient-pan pointer-events-none absolute inset-0 opacity-25 ak-gradient-bg" />
                    <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-30" />
                    <div class="relative">
                        <EyebrowLabel class="justify-center">{{ c.closing?.eyebrow }}</EyebrowLabel>
                        <h2 class="mx-auto mt-5 max-w-3xl font-display text-4xl font-semibold leading-tight tracking-tight text-ak-text sm:text-5xl">
                            {{ c.closing?.title }}
                        </h2>
                        <p class="mx-auto mt-5 max-w-2xl text-lg text-ak-text-2">
                            {{ c.closing?.subtitle }}
                        </p>
                        <div class="mt-9 flex justify-center">
                            <AkButton href="/contact" variant="gradient" size="lg">
                                {{ c.closing?.button_label }}
                                <Icon name="arrow-right" :size="18" />
                            </AkButton>
                        </div>
                    </div>
                </div>
            </ScrollReveal>
        </section>
    </div>
</template>
