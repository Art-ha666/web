<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import ClosingCta from '@/components/site/ClosingCta.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import ManifestoBand from '@/components/site/ManifestoBand.vue';

interface CtaData {
    headline: string;
    eyebrow?: string;
    body?: string;
    primary_cta_label?: string;
    primary_cta_url?: string;
    secondary_cta_label?: string;
    secondary_cta_url?: string;
    microcopy?: string;
}
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import StatCounter from '@/components/site/StatCounter.vue';
import TeamCard from '@/components/site/TeamCard.vue';
import { useSite } from '@/composables/useSite';

interface TeamMember {
    name: string;
    role?: string;
    specialty?: string;
    years_experience?: number;
    linkedin?: string;
    github?: string;
}

interface Stat {
    value: string;
    prefix?: string | null;
    suffix?: string | null;
    label: string;
}

const props = defineProps<{
    content: Record<string, any>;
    team: TeamMember[];
    stats: Stat[];
    manifesto: CtaData | null;
    cta: CtaData | null;
}>();

const { site } = useSite();

const c = computed(() => props.content ?? {});
const teamPreview = computed(() => (props.team ?? []).slice(0, 4));
const principles = computed<Array<{ icon: string; title: string; body: string }>>(() => c.value.principles?.items ?? []);
const on = (key: string): boolean => c.value[key]?.enabled !== false;
</script>

<template>
    <div>
        <Head>
            <title>{{ c.seo?.title || `About - ${site?.name || 'AKH Solutions'}` }}</title>
            <meta name="description" :content="c.seo?.description" />
        </Head>

        <!-- Header -->
        <section class="relative overflow-hidden pt-36 sm:pt-44">
            <div class="ak-grid-texture pointer-events-none absolute inset-0 opacity-30" />
            <div class="ak-container relative">
                <ScrollReveal class="max-w-3xl">
                    <EyebrowLabel>{{ c.hero?.eyebrow }}</EyebrowLabel>
                    <h1 class="mt-6 font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ak-text sm:text-5xl lg:text-6xl">
                        {{ c.hero?.title }} <span class="ak-gradient-text">{{ c.hero?.title_accent }}</span>
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-relaxed text-ak-text-2">
                        {{ c.hero?.body }}
                    </p>
                </ScrollReveal>
            </div>
        </section>

        <!-- Body / intro -->
        <section v-if="on('intro') && c.intro?.body" class="py-16 sm:py-24">
            <div class="ak-container">
                <ScrollReveal>
                    <div class="ak-prose max-w-3xl" v-html="c.intro.body" />
                </ScrollReveal>
            </div>
        </section>

        <!-- Stats band -->
        <section v-if="on('stats') && stats && stats.length" class="py-16 sm:py-24">
            <div class="ak-container">
                <ScrollReveal>
                    <div class="ak-card grid grid-cols-2 gap-8 p-8 sm:p-12 lg:grid-cols-4">
                        <ScrollReveal v-for="(stat, i) in stats" :key="stat.label" :delay="i * 80" class="text-center sm:text-left">
                            <StatCounter :value="stat.value" :prefix="stat.prefix" :suffix="stat.suffix" :label="stat.label" gradient />
                        </ScrollReveal>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Engineering principles -->
        <section v-if="on('principles') && principles.length" class="py-16 sm:py-24">
            <div class="ak-container">
                <ScrollReveal class="max-w-2xl">
                    <EyebrowLabel>{{ c.principles?.eyebrow }}</EyebrowLabel>
                    <h2 class="mt-6 font-display text-3xl font-semibold leading-[1.1] tracking-tight text-ak-text sm:text-4xl">
                        {{ c.principles?.title }}
                    </h2>
                    <p class="mt-5 text-lg leading-relaxed text-ak-text-2">
                        {{ c.principles?.subtitle }}
                    </p>
                </ScrollReveal>

                <div class="mt-12 grid gap-6 sm:grid-cols-2">
                    <ScrollReveal v-for="(principle, i) in principles" :key="i" :delay="i * 90">
                        <div class="ak-card ak-card-hover h-full p-7">
                            <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl ak-gradient-bg text-white">
                                <Icon :name="principle.icon || 'check'" :size="20" />
                            </div>
                            <h3 class="mt-5 font-display text-xl font-semibold text-ak-text">{{ principle.title }}</h3>
                            <p class="mt-3 text-base leading-relaxed text-ak-text-3">{{ principle.body }}</p>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Manifesto -->
        <ManifestoBand v-if="manifesto" :cta="manifesto" />

        <!-- Team preview -->
        <section v-if="on('team_preview') && teamPreview.length" class="py-16 sm:py-24">
            <div class="ak-container">
                <ScrollReveal class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <EyebrowLabel>{{ c.team_preview?.eyebrow }}</EyebrowLabel>
                        <h2 class="mt-6 font-display text-3xl font-semibold leading-[1.1] tracking-tight text-ak-text sm:text-4xl">
                            {{ c.team_preview?.title }}
                        </h2>
                        <p class="mt-5 text-lg leading-relaxed text-ak-text-2">
                            {{ c.team_preview?.subtitle }}
                        </p>
                    </div>
                    <Link href="/team" class="ak-focusable group inline-flex shrink-0 items-center gap-2 text-base font-semibold text-ak-text">
                        <span class="ak-gradient-text">{{ c.team_preview?.link_label }}</span>
                        <Icon name="arrow-right" :size="18" class="transition-transform group-hover:translate-x-1" :style="{ color: 'var(--ak-primary)' }" />
                    </Link>
                </ScrollReveal>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <ScrollReveal v-for="(member, i) in teamPreview" :key="member.name" :delay="i * 80">
                        <TeamCard :member="member" />
                    </ScrollReveal>
                </div>
            </div>
        </section>

        <!-- Closing CTA -->
        <ClosingCta v-if="cta" :cta="cta" />
        <section v-else class="py-16 sm:py-24">
            <div class="ak-container text-center">
                <ScrollReveal>
                    <h2 class="mx-auto max-w-3xl font-display text-3xl font-semibold leading-[1.1] tracking-tight text-ak-text sm:text-4xl">
                        {{ c.closing?.title }}
                    </h2>
                    <div class="mt-8">
                        <AkButton href="/contact" variant="gradient" size="lg">{{ c.closing?.button_label }}</AkButton>
                    </div>
                </ScrollReveal>
            </div>
        </section>
    </div>
</template>
