<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import { useSite } from '@/composables/useSite';

interface Job {
    title: string;
    slug: string;
    department?: string;
    location?: string;
    employment_type?: string;
    seniority?: string;
    summary?: string;
    description?: string;
    responsibilities?: string[];
    requirements?: string[];
    tech_stack?: string[];
    salary_range?: string;
    apply_url?: string;
}

const props = defineProps<{
    job: Job;
    content?: Record<string, any>;
}>();

const { site } = useSite();

const c = computed(() => props.content ?? {});

const pills = computed(() =>
    [props.job.department, props.job.location, props.job.employment_type, props.job.seniority].filter(
        (value): value is string => Boolean(value),
    ),
);

const applyHref = computed(
    () => props.job.apply_url || (site.value.email ? `mailto:${site.value.email}` : '/contact'),
);

const metaDescription = computed(
    () =>
        props.job.summary ||
        `${props.job.title} role at ${site.value.name}${props.job.location ? ` - ${props.job.location}` : ''}. Apply to join our team.`,
);
</script>

<template>
    <div>
        <Head>
            <title>{{ `${job.title} - Careers at ${site.name}` }}</title>
            <meta name="description" :content="metaDescription" />
        </Head>

        <!-- Header -->
        <section class="ak-grid-texture relative overflow-hidden pt-36 sm:pt-44">
            <div class="ak-container py-16 sm:py-24">
                <ScrollReveal>
                    <Link
                        href="/careers"
                        class="ak-focusable inline-flex items-center gap-2 text-sm font-medium text-ak-text-3 transition hover:text-ak-text"
                    >
                        <span aria-hidden="true">←</span> {{ c.job?.back_label }}
                    </Link>
                </ScrollReveal>

                <ScrollReveal :delay="80" class="mt-8 max-w-3xl">
                    <EyebrowLabel v-if="job.department">{{ job.department }}</EyebrowLabel>
                    <h1 class="mt-5 font-display text-4xl font-semibold leading-tight tracking-tight text-ak-text sm:text-5xl">
                        {{ job.title }}
                    </h1>

                    <div v-if="pills.length" class="mt-6 flex flex-wrap gap-2.5">
                        <span v-for="pill in pills" :key="pill" class="ak-chip">{{ pill }}</span>
                    </div>

                    <p v-if="job.summary" class="mt-6 text-lg text-ak-text-2">{{ job.summary }}</p>
                </ScrollReveal>

                <ScrollReveal :delay="160" class="mt-10">
                    <AkButton :href="applyHref" variant="gradient" size="lg" :external="true">
                        {{ c.job?.apply_label }}
                        <Icon name="arrow-up-right" :size="18" />
                    </AkButton>
                </ScrollReveal>
            </div>
        </section>

        <!-- Description -->
        <section v-if="job.description" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <div class="ak-prose max-w-3xl" v-html="job.description" />
            </ScrollReveal>
        </section>

        <!-- What you'll do / What you bring -->
        <section
            v-if="job.responsibilities?.length || job.requirements?.length"
            class="ak-container grid gap-8 py-16 sm:py-24 lg:grid-cols-2"
        >
            <ScrollReveal v-if="job.responsibilities?.length">
                <div class="ak-card h-full p-8 sm:p-10">
                    <h2 class="font-display text-2xl font-semibold text-ak-text">{{ c.job?.responsibilities_title }}</h2>
                    <ul class="mt-6 space-y-4">
                        <li v-for="item in job.responsibilities" :key="item" class="flex items-start gap-3 text-ak-text-2">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full ak-gradient-bg text-white">
                                <Icon name="check" :size="14" />
                            </span>
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>
            </ScrollReveal>

            <ScrollReveal v-if="job.requirements?.length" :delay="120">
                <div class="ak-card h-full p-8 sm:p-10">
                    <h2 class="font-display text-2xl font-semibold text-ak-text">{{ c.job?.requirements_title }}</h2>
                    <ul class="mt-6 space-y-4">
                        <li v-for="item in job.requirements" :key="item" class="flex items-start gap-3 text-ak-text-2">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full ak-gradient-bg text-white">
                                <Icon name="check" :size="14" />
                            </span>
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>
            </ScrollReveal>
        </section>

        <!-- Tech stack + salary -->
        <section v-if="job.tech_stack?.length || job.salary_range" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <div class="ak-glass max-w-3xl p-8 sm:p-10">
                    <div v-if="job.tech_stack?.length">
                        <h3 class="ak-eyebrow">{{ c.job?.tech_title }}</h3>
                        <div class="mt-5 flex flex-wrap gap-2.5">
                            <span v-for="tech in job.tech_stack" :key="tech" class="ak-chip font-mono">{{ tech }}</span>
                        </div>
                    </div>

                    <div
                        v-if="job.salary_range"
                        class="flex items-center gap-3 text-ak-text-2"
                        :class="job.tech_stack?.length ? 'mt-8 border-t border-ak-hairline pt-8' : ''"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ak-gradient-bg text-white">
                            <Icon name="zap" :size="18" />
                        </span>
                        <span>
                            <span class="block text-xs text-ak-text-3">{{ c.job?.salary_label }}</span>
                            <span class="font-medium text-ak-text">{{ job.salary_range }}</span>
                        </span>
                    </div>
                </div>
            </ScrollReveal>
        </section>

        <!-- Apply CTA -->
        <section class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <div class="ak-glass relative overflow-hidden px-8 py-16 text-center sm:px-12 sm:py-20">
                    <h2 class="mx-auto max-w-2xl font-display text-3xl font-semibold leading-tight tracking-tight text-ak-text sm:text-4xl">
                        {{ c.job?.cta_title }} <span class="ak-gradient-text">{{ c.job?.cta_title_accent }}</span>
                    </h2>
                    <p class="mx-auto mt-5 max-w-md text-lg text-ak-text-2">
                        {{ c.job?.cta_body }}
                    </p>
                    <div class="mt-9 flex flex-wrap justify-center gap-4">
                        <AkButton :href="applyHref" variant="gradient" size="lg" :external="true">
                            {{ c.job?.apply_label }}
                            <Icon name="arrow-up-right" :size="18" />
                        </AkButton>
                        <AkButton href="/careers" variant="ghost" size="lg">{{ c.job?.view_all_label }}</AkButton>
                    </div>
                </div>
            </ScrollReveal>
        </section>
    </div>
</template>
