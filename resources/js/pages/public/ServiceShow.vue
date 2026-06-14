<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import CaseCard from '@/components/site/CaseCard.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { useSite } from '@/composables/useSite';

interface ServiceProject {
    title: string;
    slug: string;
    client_type?: string;
    industry?: string;
    year?: string;
    category_tags?: string[];
    headline_result?: string;
    results?: Array<{ metric: string; label: string }>;
}

const props = defineProps<{
    content?: Record<string, any>;
    service: {
        title: string;
        slug: string;
        icon: string;
        tab_label: string;
        short_blurb: string;
        intro: string;
        value_metric: string;
        benefit_bullets: string[];
        detail_body: string;
        gradient?: { from?: string; via?: string; to?: string } | null;
        tech_stack: string[];
        projects: ServiceProject[];
    };
    related: Array<{ title: string; slug: string; icon: string; short_blurb: string }>;
}>();

const { site } = useSite();
const d = computed(() => props.content?.detail ?? {});

const gradientStyle = computed(() => {
    const gradient = props.service.gradient ?? {};

    return {
        backgroundImage: `linear-gradient(135deg, ${gradient.from ?? 'var(--ak-grad-1)'}, ${gradient.via ?? 'var(--ak-grad-2)'}, ${gradient.to ?? 'var(--ak-grad-3)'})`,
    };
});

const metaDescription = computed(() => props.service.short_blurb ?? props.service.intro);
</script>

<template>
    <div>
        <Head>
            <title>{{ `${service.title} - ${site.name}` }}</title>
            <meta name="description" :content="metaDescription" />
        </Head>

        <!-- Header -->
        <section class="ak-container pt-36 pb-12 sm:pt-44 sm:pb-16">
            <ScrollReveal>
                <EyebrowLabel>{{ service.tab_label }}</EyebrowLabel>
                <h1 class="mt-5 max-w-3xl font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ak-text sm:text-6xl">
                    {{ service.title }}
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-ak-text-2 sm:text-xl">
                    {{ service.short_blurb }}
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-4">
                    <AkButton href="/contact" variant="gradient" size="lg">
                        {{ d.cta_label || 'Book a discovery call' }}
                        <Icon name="arrow-right" :size="18" />
                    </AkButton>
                    <AkButton href="/services" variant="ghost" size="lg">{{ d.back_label || 'All services' }}</AkButton>
                </div>
            </ScrollReveal>
        </section>

        <!-- Gradient backplate strip -->
        <section class="ak-container pb-16 sm:pb-24">
            <ScrollReveal :delay="80">
                <div
                    class="ak-grid-texture relative flex flex-col gap-8 overflow-hidden rounded-3xl p-8 sm:flex-row sm:items-center sm:justify-between sm:p-12"
                    :style="gradientStyle"
                >
                    <div class="flex items-center gap-5">
                        <span
                            class="ak-glass inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-white sm:h-20 sm:w-20"
                        >
                            <Icon :name="service.icon" :size="34" />
                        </span>
                        <p class="max-w-md font-display text-lg font-medium leading-snug text-white sm:text-xl">
                            {{ service.intro }}
                        </p>
                    </div>
                    <div v-if="service.value_metric" class="shrink-0 text-white sm:text-right">
                        <div class="font-display text-4xl font-bold leading-none sm:text-5xl">{{ service.value_metric }}</div>
                        <div class="mt-2 text-sm font-medium uppercase tracking-wide text-white/75">{{ d.metric_caption || 'What to expect' }}</div>
                    </div>
                </div>
            </ScrollReveal>
        </section>

        <!-- Benefit bullets -->
        <section v-if="service.benefit_bullets?.length" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader :eyebrow="d.benefits_eyebrow || 'Why teams choose this'" :title="d.benefits_title || 'What you get'" />
            </ScrollReveal>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <ScrollReveal v-for="(bullet, i) in service.benefit_bullets" :key="i" :delay="i * 70">
                    <div class="ak-card ak-card-hover flex h-full flex-col gap-4 p-6">
                        <span
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-ak-glass-border text-ak-primary"
                        >
                            <Icon name="check" :size="18" />
                        </span>
                        <p class="text-sm leading-relaxed text-ak-text-2">{{ bullet }}</p>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Detail body + tech stack -->
        <section class="relative border-y border-ak-hairline py-16 sm:py-24" :style="{ background: 'var(--ak-bg-2)' }">
            <div class="ak-container">
                <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <ScrollReveal>
                        <div v-if="service.detail_body" class="ak-prose max-w-3xl" v-html="service.detail_body" />
                    </ScrollReveal>

                    <ScrollReveal v-if="service.tech_stack?.length" :delay="100">
                        <div class="ak-card lg:sticky lg:top-28 p-6">
                            <EyebrowLabel>{{ d.tech_title || 'Tech stack' }}</EyebrowLabel>
                            <div class="mt-5 flex flex-wrap gap-2.5">
                                <span v-for="tech in service.tech_stack" :key="tech" class="ak-chip font-mono">{{ tech }}</span>
                            </div>
                            <div class="mt-8 border-t border-ak-hairline pt-6">
                                <p class="text-sm leading-relaxed text-ak-text-3">
                                    {{ d.tech_note || 'Pragmatic tooling, chosen for longevity - not hype.' }}
                                </p>
                            </div>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Related work -->
        <section v-if="service.projects?.length" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader
                    :eyebrow="d.related_eyebrow || 'Proof of work'"
                    :title="d.related_title || 'Related work'"
                    :view-all-label="d.related_view_all || 'All work'"
                    view-all-href="/work"
                />
            </ScrollReveal>
            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <ScrollReveal v-for="(project, i) in service.projects" :key="project.slug" :delay="i * 80">
                    <CaseCard :project="project" />
                </ScrollReveal>
            </div>
        </section>

        <!-- More services -->
        <section v-if="related?.length" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader
                    :eyebrow="d.more_eyebrow || 'Keep exploring'"
                    :title="d.more_title || 'More services'"
                    :view-all-label="d.back_label || 'All services'"
                    view-all-href="/services"
                />
            </ScrollReveal>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <ScrollReveal v-for="(item, i) in related" :key="item.slug" :delay="i * 70">
                    <Link
                        :href="`/services/${item.slug}`"
                        class="ak-card ak-card-hover ak-focusable group flex h-full flex-col p-6"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-ak-glass-border text-ak-text-2 transition group-hover:border-ak-primary group-hover:text-ak-primary"
                            >
                                <Icon :name="item.icon" :size="20" />
                            </span>
                            <Icon
                                name="arrow-up-right"
                                :size="18"
                                class="text-ak-text-3 transition group-hover:text-ak-primary"
                            />
                        </div>
                        <h3 class="mt-5 font-display text-lg font-semibold leading-snug text-ak-text">{{ item.title }}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-ak-text-2">{{ item.short_blurb }}</p>
                    </Link>
                </ScrollReveal>
            </div>
        </section>

        <!-- Closing CTA -->
        <section class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <div class="ak-glass flex flex-col items-center gap-6 rounded-3xl px-6 py-16 text-center sm:py-20">
                    <EyebrowLabel>{{ d.closing_eyebrow || "Let's build it together" }}</EyebrowLabel>
                    <h2 class="max-w-2xl font-display text-3xl font-semibold leading-[1.1] tracking-tight text-ak-text sm:text-4xl">
                        {{ d.closing_title_prefix || 'Ready to put' }} <span class="ak-gradient-text">{{ service.title.toLowerCase() }}</span> {{ d.closing_title_suffix || 'to work?' }}
                    </h2>
                    <p class="max-w-xl text-ak-text-2 sm:text-lg">
                        {{ d.closing_body || "Tell us where you're headed. We'll map the fastest credible path to get there." }}
                    </p>
                    <AkButton href="/contact" variant="gradient" size="lg">
                        {{ d.cta_label || 'Book a discovery call' }}
                        <Icon name="arrow-right" :size="18" />
                    </AkButton>
                </div>
            </ScrollReveal>
        </section>
    </div>
</template>
