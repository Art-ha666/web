<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import { useSite } from '@/composables/useSite';

interface Job {
    id: number | string;
    title: string;
    slug: string;
    department: string;
    location: string;
    employment_type: string;
    seniority: string;
    summary: string;
    tech_stack: string[];
}

const props = defineProps<{
    content: Record<string, any>;
    jobs: Job[];
}>();

const { site } = useSite();

const c = computed(() => props.content ?? {});
const on = (key: string): boolean => c.value[key]?.enabled !== false;
</script>

<template>
    <div>
        <Head>
            <title>{{ c.seo?.title || `Careers - ${site.name}` }}</title>
            <meta name="description" :content="c.seo?.description" />
        </Head>

        <!-- Hero -->
        <section class="ak-container pb-12 pt-36 sm:pb-16 sm:pt-44">
            <ScrollReveal>
                <EyebrowLabel>{{ c.hero?.eyebrow }}</EyebrowLabel>
                <h1 class="mt-5 max-w-3xl font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ak-text sm:text-5xl lg:text-6xl">
                    {{ c.hero?.title }} <span class="ak-gradient-text">{{ c.hero?.title_accent }}</span>
                </h1>
                <p class="mt-6 max-w-2xl text-lg text-ak-text-2">
                    {{ c.hero?.body }}
                </p>
            </ScrollReveal>
        </section>

        <!-- Roles -->
        <section class="ak-container pb-16 sm:pb-24">
            <!-- Empty state -->
            <ScrollReveal v-if="!jobs || jobs.length === 0">
                <div class="ak-glass relative overflow-hidden p-10 text-center sm:p-16">
                    <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-30" />
                    <div class="relative mx-auto max-w-xl">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ak-gradient-bg text-white">
                            <Icon :name="c.empty_state?.icon || 'users'" :size="26" />
                        </span>
                        <h2 class="mt-6 font-display text-2xl font-semibold tracking-tight text-ak-text sm:text-3xl">
                            {{ c.empty_state?.title }}
                        </h2>
                        <p class="mt-4 text-ak-text-2">
                            {{ c.empty_state?.body }}
                        </p>
                        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                            <AkButton href="/contact" variant="gradient" size="lg">
                                {{ c.empty_state?.button_label }}
                                <Icon name="arrow-right" :size="18" />
                            </AkButton>
                            <AkButton v-if="site.email" :href="`mailto:${site.email}`" variant="ghost" size="lg" :external="true">
                                <Icon name="mail" :size="18" />
                                {{ site.email }}
                            </AkButton>
                        </div>
                    </div>
                </div>
            </ScrollReveal>

            <!-- Roles list -->
            <div v-else class="space-y-4 sm:space-y-5">
                <ScrollReveal v-for="(job, i) in jobs" :key="job.id" :delay="i * 70">
                    <Link
                        :href="'/careers/' + job.slug"
                        class="ak-card ak-card-hover ak-focusable group block p-6 sm:p-8"
                    >
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <h2 class="font-display text-xl font-semibold tracking-tight text-ak-text transition-colors group-hover:text-ak-primary sm:text-2xl">
                                    {{ job.title }}
                                </h2>

                                <!-- Meta pills -->
                                <div class="mt-4 flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="job.department"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-ak-glass-border bg-ak-bg-2 px-3 py-1 text-xs font-medium text-ak-text-2"
                                    >
                                        <Icon name="compass" :size="13" />
                                        {{ job.department }}
                                    </span>
                                    <span
                                        v-if="job.location"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-ak-glass-border bg-ak-bg-2 px-3 py-1 text-xs font-medium text-ak-text-2"
                                    >
                                        <Icon name="map-pin" :size="13" />
                                        {{ job.location }}
                                    </span>
                                    <span
                                        v-if="job.employment_type"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-ak-glass-border bg-ak-bg-2 px-3 py-1 text-xs font-medium text-ak-text-2"
                                    >
                                        <Icon name="clock" :size="13" />
                                        {{ job.employment_type }}
                                    </span>
                                    <span
                                        v-if="job.seniority"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-ak-glass-border bg-ak-bg-2 px-3 py-1 text-xs font-medium text-ak-text-2"
                                    >
                                        <Icon name="star" :size="13" />
                                        {{ job.seniority }}
                                    </span>
                                </div>

                                <p v-if="job.summary" class="mt-5 max-w-2xl text-ak-text-2">
                                    {{ job.summary }}
                                </p>

                                <!-- Tech stack -->
                                <div v-if="job.tech_stack?.length" class="mt-5 flex flex-wrap gap-2">
                                    <span v-for="tech in job.tech_stack" :key="tech" class="ak-chip">{{ tech }}</span>
                                </div>
                            </div>

                            <!-- Affordance -->
                            <div class="flex shrink-0 items-center gap-2 self-start text-sm font-medium text-ak-text transition-colors group-hover:text-ak-primary lg:self-center">
                                {{ c.roles?.view_label }}
                                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-ak-glass-border transition-all group-hover:border-ak-primary group-hover:bg-ak-bg-2">
                                    <Icon name="arrow-right" :size="16" class="transition-transform group-hover:translate-x-0.5" />
                                </span>
                            </div>
                        </div>
                    </Link>
                </ScrollReveal>
            </div>
        </section>

        <!-- Closing CTA -->
        <section v-if="on('closing')" class="ak-container pb-20 pt-4 sm:pb-28">
            <ScrollReveal>
                <div class="relative overflow-hidden rounded-[2rem] border border-ak-glass-border p-10 text-center sm:p-16">
                    <div class="ak-gradient-pan pointer-events-none absolute inset-0 opacity-25 ak-gradient-bg" />
                    <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-30" />
                    <div class="relative">
                        <p class="ak-eyebrow justify-center">{{ c.closing?.eyebrow }}</p>
                        <h2 class="mx-auto mt-5 max-w-2xl font-display text-3xl font-semibold leading-tight tracking-tight text-ak-text sm:text-4xl lg:text-5xl">
                            {{ c.closing?.title }}
                        </h2>
                        <p class="mx-auto mt-5 max-w-xl text-lg text-ak-text-2">
                            {{ c.closing?.body }}
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
