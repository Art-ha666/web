<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import ClosingCta from '@/components/site/ClosingCta.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import TestimonialCard from '@/components/site/TestimonialCard.vue';
import { useSite } from '@/composables/useSite';

interface ResultStat {
    metric: string;
    label: string;
}

interface Testimonial {
    quote: string;
    author_name: string;
    author_role?: string;
    company_name?: string;
    rating?: number;
}

interface RelatedProject {
    title: string;
    slug: string;
    industry?: string;
    year?: string;
    headline_result?: string;
}

const props = defineProps<{
    content: Record<string, any>;
    closingCta?: {
        eyebrow?: string;
        headline: string;
        body?: string;
        primary_cta_label?: string;
        primary_cta_url?: string;
        secondary_cta_label?: string;
        secondary_cta_url?: string;
        microcopy?: string;
    } | null;
    project: {
        title: string;
        slug: string;
        client_name?: string;
        client_type?: string;
        industry?: string;
        year?: string;
        category_tags?: string[];
        headline_result?: string;
        summary?: string;
        challenge?: string;
        approach?: string;
        architecture_notes?: string;
        results?: ResultStat[];
        tech_stack?: string[];
        video_url?: string;
        service?: { title: string; slug: string } | null;
        testimonials?: Testimonial[];
    };
    related: RelatedProject[];
}>();

const { site } = useSite();

const c = computed(() => props.content ?? {});

const metaLine = computed(() =>
    [props.project.client_type, props.project.industry, props.project.year].filter(Boolean).join(' · '),
);

const metaTitle = computed(() => `${props.project.title} - Work - ${site.value.name}`);

const metaDescription = computed(() => props.project.summary || props.project.headline_result || metaLine.value);

const sections = computed(() =>
    [
        { key: 'challenge', title: c.value.case?.challenge_title, html: props.project.challenge },
        { key: 'approach', title: c.value.case?.approach_title, html: props.project.approach },
        { key: 'architecture', title: c.value.case?.architecture_title, html: props.project.architecture_notes },
    ].filter((section) => Boolean(section.html)),
);
</script>

<template>
    <div>
        <Head>
            <title>{{ metaTitle }}</title>
            <meta name="description" :content="metaDescription" />
        </Head>

        <!-- Header -->
        <section class="ak-grid-texture relative overflow-hidden border-b border-ak-hairline">
            <div class="ak-container pb-16 pt-36 sm:pb-24 sm:pt-44">
                <ScrollReveal>
                    <Link
                        href="/work"
                        class="ak-focusable group inline-flex items-center gap-2 text-sm font-medium text-ak-text-3 transition hover:text-ak-text"
                    >
                        <Icon name="arrow-right" :size="16" class="rotate-180 transition group-hover:-translate-x-0.5" />
                        {{ c.case?.back_label }}
                    </Link>

                    <div v-if="project.category_tags?.length" class="mt-8 flex flex-wrap gap-2">
                        <span v-for="tag in project.category_tags" :key="tag" class="ak-chip">{{ tag }}</span>
                    </div>

                    <h1 class="mt-6 max-w-4xl font-display text-4xl font-bold leading-[1.05] tracking-tight text-ak-text sm:text-6xl">
                        {{ project.title }}
                    </h1>

                    <p v-if="metaLine" class="mt-5 font-mono text-sm uppercase tracking-widest text-ak-text-3">
                        {{ metaLine }}
                    </p>

                    <p v-if="c.case?.disclosure" class="mt-3 max-w-2xl text-xs leading-relaxed text-ak-text-3">
                        {{ c.case?.disclosure }}
                    </p>

                    <p
                        v-if="project.headline_result"
                        class="ak-gradient-text mt-8 max-w-3xl font-display text-2xl font-semibold leading-snug sm:text-4xl"
                    >
                        {{ project.headline_result }}
                    </p>

                    <p v-if="project.summary" class="mt-6 max-w-2xl text-lg leading-relaxed text-ak-text-2">
                        {{ project.summary }}
                    </p>

                    <div v-if="project.service || project.client_name" class="mt-8 flex flex-wrap items-center gap-3">
                        <span v-if="project.client_name" class="text-sm text-ak-text-3">
                            {{ c.case?.client_label }} <span class="text-ak-text-2">{{ project.client_name }}</span>
                        </span>
                        <Link
                            v-if="project.service"
                            :href="`/services/${project.service.slug}`"
                            class="ak-focusable inline-flex items-center gap-1.5 text-sm font-medium text-ak-text-2 transition hover:text-ak-primary"
                        >
                            <Icon name="compass" :size="15" />
                            {{ project.service.title }}
                        </Link>
                    </div>

                    <div v-if="project.video_url" class="mt-12">
                        <AkButton :href="project.video_url" variant="ghost" external>
                            <Icon name="rocket" :size="18" />
                            {{ c.case?.video_label }}
                        </AkButton>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Results -->
        <section v-if="project.results?.length" class="border-b border-ak-hairline" :style="{ background: 'var(--ak-bg-2)' }">
            <div class="ak-container py-16 sm:py-24">
                <ScrollReveal>
                    <EyebrowLabel>{{ c.case?.results_eyebrow }}</EyebrowLabel>
                </ScrollReveal>
                <div class="mt-10 grid grid-cols-2 gap-x-8 gap-y-12 lg:grid-cols-4">
                    <ScrollReveal
                        v-for="(result, i) in project.results"
                        :key="`${result.label}-${i}`"
                        :delay="i * 80"
                        class="border-l border-ak-hairline pl-6"
                    >
                        <div class="ak-gradient-text font-display text-4xl font-bold leading-none sm:text-5xl">
                            {{ result.metric }}
                        </div>
                        <div class="mt-3 text-sm leading-snug text-ak-text-3">{{ result.label }}</div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Narrative sections -->
        <section v-if="sections.length" class="ak-container py-16 sm:py-24">
            <div class="space-y-16 sm:space-y-24">
                <ScrollReveal v-for="section in sections" :key="section.key" :as="'article'">
                    <h2 class="font-display text-2xl font-bold tracking-tight text-ak-text sm:text-3xl">
                        {{ section.title }}
                    </h2>
                    <div class="ak-prose mt-6 max-w-3xl" v-html="section.html" />
                </ScrollReveal>
            </div>
        </section>

        <!-- Tech stack -->
        <section
            v-if="project.tech_stack?.length"
            class="border-y border-ak-hairline"
            :style="{ background: 'var(--ak-bg-2)' }"
        >
            <div class="ak-container py-16 sm:py-24">
                <ScrollReveal>
                    <EyebrowLabel>{{ c.case?.tech_eyebrow }}</EyebrowLabel>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <span v-for="tech in project.tech_stack" :key="tech" class="ak-chip font-mono text-xs">
                            {{ tech }}
                        </span>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Testimonials -->
        <section v-if="project.testimonials?.length" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <EyebrowLabel>{{ c.case?.testimonials_eyebrow }}</EyebrowLabel>
            </ScrollReveal>
            <div class="mt-10 grid gap-6 md:grid-cols-2">
                <ScrollReveal
                    v-for="(testimonial, i) in project.testimonials"
                    :key="`${testimonial.author_name}-${i}`"
                    :delay="i * 90"
                >
                    <TestimonialCard :testimonial="testimonial" />
                </ScrollReveal>
            </div>
        </section>

        <!-- Related work -->
        <section v-if="related?.length" class="border-t border-ak-hairline py-16 sm:py-24" :style="{ background: 'var(--ak-bg-2)' }">
            <div class="ak-container">
                <ScrollReveal>
                    <EyebrowLabel>{{ c.case?.related_eyebrow }}</EyebrowLabel>
                </ScrollReveal>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <ScrollReveal v-for="(item, i) in related" :key="item.slug" :delay="i * 80">
                        <Link
                            :href="`/work/${item.slug}`"
                            class="ak-card ak-card-hover ak-focusable group flex h-full flex-col p-6"
                        >
                            <div class="flex items-center gap-2 text-xs text-ak-text-3">
                                <span v-if="item.industry">{{ item.industry }}</span>
                                <span v-if="item.industry && item.year">·</span>
                                <span v-if="item.year">{{ item.year }}</span>
                            </div>
                            <h3 class="mt-4 font-display text-lg font-semibold leading-snug text-ak-text">
                                {{ item.title }}
                            </h3>
                            <p v-if="item.headline_result" class="mt-2 flex-1 text-sm leading-relaxed text-ak-text-2">
                                {{ item.headline_result }}
                            </p>
                            <span
                                class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-ak-text-2 transition group-hover:text-ak-primary"
                            >
                                {{ c.case?.related_cta }}
                                <Icon name="arrow-up-right" :size="16" class="transition group-hover:translate-x-0.5" />
                            </span>
                        </Link>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Contact CTA -->
        <ClosingCta v-if="closingCta" :cta="closingCta" />
    </div>
</template>
