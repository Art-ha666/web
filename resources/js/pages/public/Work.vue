<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import CaseCard from '@/components/site/CaseCard.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import { useSite } from '@/composables/useSite';

interface Project {
    id: number | string;
    title: string;
    slug: string;
    client_type?: string;
    industry?: string;
    year?: string;
    category_tags?: string[];
    headline_result?: string;
    results?: Array<{ metric: string; label: string }>;
    tech_stack?: string[];
    featured?: boolean;
}

const props = defineProps<{
    content: Record<string, any>;
    projects: Project[];
}>();

const { site } = useSite();

const c = computed(() => props.content ?? {});
const on = (key: string): boolean => c.value[key]?.enabled !== false;

const ALL = c.value.filter?.all_label || 'All';
const selectedIndustry = ref<string>(ALL);

const industries = computed<string[]>(() => {
    const unique = new Set<string>();

    for (const project of props.projects) {
        if (project.industry) {
            unique.add(project.industry);
        }
    }

    return [ALL, ...Array.from(unique).sort((a, b) => a.localeCompare(b))];
});

const filteredProjects = computed<Project[]>(() => {
    if (selectedIndustry.value === ALL) {
        return props.projects;
    }

    return props.projects.filter((project) => project.industry === selectedIndustry.value);
});
</script>

<template>
    <div>
        <Head>
            <title>{{ c.seo?.title || `Work - ${site.name}` }}</title>
            <meta name="description" :content="c.seo?.description" />
        </Head>

        <!-- Header + filter -->
        <section class="ak-grid-texture relative overflow-hidden pt-36 pb-16 sm:pt-44 sm:pb-24">
            <div class="ak-container">
                <ScrollReveal>
                    <EyebrowLabel>{{ c.hero?.eyebrow }}</EyebrowLabel>
                    <h1 class="mt-5 max-w-3xl font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ak-text sm:text-5xl lg:text-6xl">
                        {{ c.hero?.title }} <span class="ak-gradient-text">{{ c.hero?.title_accent }}</span>{{ c.hero?.title_suffix }}
                    </h1>
                    <p class="mt-6 max-w-2xl text-ak-text-2 sm:text-lg">
                        {{ c.hero?.body }}
                    </p>
                </ScrollReveal>

                <ScrollReveal :delay="120">
                    <div class="mt-10 flex flex-wrap items-center gap-2.5" role="tablist" :aria-label="c.filter?.aria_label || 'Filter work by industry'">
                        <button
                            v-for="industry in industries"
                            :key="industry"
                            type="button"
                            role="tab"
                            :aria-selected="selectedIndustry === industry"
                            class="ak-focusable inline-flex items-center rounded-full px-4 py-2 font-mono text-xs font-medium uppercase tracking-wider transition"
                            :class="
                                selectedIndustry === industry
                                    ? 'text-ak-on-primary shadow-sm'
                                    : 'border border-ak-glass-border text-ak-text-2 hover:border-ak-primary hover:text-ak-text'
                            "
                            :style="selectedIndustry === industry ? { background: 'var(--ak-primary)' } : {}"
                            @click="selectedIndustry = industry"
                        >
                            {{ industry }}
                        </button>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Grid -->
        <section class="ak-container pb-16 sm:pb-24">
            <div v-if="filteredProjects.length" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <ScrollReveal
                    v-for="(project, i) in filteredProjects"
                    :key="project.id"
                    :delay="(i % 3) * 90 + Math.floor(i / 3) * 40"
                >
                    <CaseCard :project="project" />
                </ScrollReveal>
            </div>

            <ScrollReveal v-else>
                <div class="ak-card flex flex-col items-center justify-center px-8 py-20 text-center">
                    <p class="font-display text-xl font-semibold text-ak-text">{{ c.empty?.title }}</p>
                    <p class="mt-2 max-w-md text-sm text-ak-text-2">
                        {{ c.empty?.body }}
                    </p>
                    <button
                        type="button"
                        class="ak-focusable mt-6 inline-flex items-center rounded-full border border-ak-glass-border px-4 py-2 text-sm font-medium text-ak-text transition hover:border-ak-primary hover:text-ak-primary"
                        @click="selectedIndustry = ALL"
                    >
                        {{ c.empty?.button_label }}
                    </button>
                </div>
            </ScrollReveal>
        </section>

        <!-- Contact CTA -->
        <section v-if="on('cta')" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <div class="ak-card ak-glass relative overflow-hidden px-8 py-16 text-center sm:px-12 sm:py-20">
                    <EyebrowLabel class="justify-center">{{ c.cta?.eyebrow }}</EyebrowLabel>
                    <h2 class="mx-auto mt-5 max-w-2xl font-display text-3xl font-semibold leading-[1.1] tracking-tight text-ak-text sm:text-4xl">
                        {{ c.cta?.title }} <span class="ak-gradient-text">{{ c.cta?.title_accent }}</span>{{ c.cta?.title_suffix }}
                    </h2>
                    <p class="mx-auto mt-4 max-w-xl text-ak-text-2 sm:text-lg">
                        {{ c.cta?.body }}
                    </p>
                    <div class="mt-8 flex justify-center">
                        <AkButton href="/contact" variant="gradient" size="lg">{{ c.cta?.button_label }}</AkButton>
                    </div>
                </div>
            </ScrollReveal>
        </section>
    </div>
</template>
