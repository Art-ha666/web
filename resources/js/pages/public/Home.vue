<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import CaseCard from '@/components/site/CaseCard.vue';
import ClosingCta from '@/components/site/ClosingCta.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import HeroRenderer from '@/components/site/HeroRenderer.vue';
import LogoMarquee from '@/components/site/LogoMarquee.vue';
import ManifestoBand from '@/components/site/ManifestoBand.vue';
import ProcessTimeline from '@/components/site/ProcessTimeline.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import ServiceTabs from '@/components/site/ServiceTabs.vue';
import StatCounter from '@/components/site/StatCounter.vue';
import TeamCard from '@/components/site/TeamCard.vue';
import TestimonialCard from '@/components/site/TestimonialCard.vue';
import { useSite } from '@/composables/useSite';

const props = defineProps<{
    home: Record<string, any>;
    services: any[];
    stats: { band: any[]; hero: any[] };
    process: any[];
    projects: any[];
    team: any[];
    testimonials: any[];
    logos: any[];
    cta: any;
    manifesto: any;
}>();

const { site } = useSite();

const hero = computed(() => props.home?.hero ?? {});
const sections = computed(() => props.home?.sections ?? {});
const partners = computed(() => props.home?.partners ?? {});

// A section renders unless the admin explicitly disabled it.
const on = (key: string): boolean => sections.value?.[key]?.enabled !== false;

const featuredProjects = computed(() => props.projects.slice(0, 3));
const featuredTeam = computed(() => props.team.slice(0, 4));
const featuredTestimonials = computed(() => props.testimonials.slice(0, 2));
</script>

<template>
    <div>
        <Head>
            <title>{{ site.metaTitle || site.name }}</title>
            <meta name="description" :content="site.metaDescription" />
        </Head>

        <HeroRenderer :hero="hero" :stats="stats.hero" />

        <!-- Partners (formerly "trusted by") -->
        <section v-if="partners.enabled !== false && logos.length" class="ak-container py-12">
            <p class="ak-eyebrow mb-6">{{ partners.eyebrow ?? 'Technologies we build with' }}</p>
            <LogoMarquee :logos="logos" />
        </section>

        <!-- Services -->
        <section v-if="on('services')" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader
                    :eyebrow="sections.services?.eyebrow"
                    :title="sections.services?.title"
                    :subtitle="sections.services?.subtitle"
                    :view-all-label="sections.services?.view_all_label ?? 'All services'"
                    view-all-href="/services"
                />
            </ScrollReveal>
            <div class="mt-10">
                <ServiceTabs :services="services" :explore-label="sections.services?.explore_label" />
            </div>
        </section>

        <!-- Stats band -->
        <section v-if="on('stats')" class="relative border-y border-ak-hairline py-16" :style="{ background: 'var(--ak-bg-2)' }">
            <div class="ak-container">
                <EyebrowLabel>{{ sections.stats?.eyebrow ?? 'Numbers that speak' }}</EyebrowLabel>
                <div class="mt-8 grid grid-cols-2 gap-8 lg:grid-cols-4">
                    <div v-for="(stat, i) in stats.band" :key="i" class="border-l border-ak-hairline pl-6">
                        <StatCounter :value="stat.value" :prefix="stat.prefix" :suffix="stat.suffix" :label="stat.label" gradient />
                    </div>
                </div>
            </div>
        </section>

        <!-- Process -->
        <section v-if="on('process')" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader :eyebrow="sections.process?.eyebrow" :title="sections.process?.title" />
            </ScrollReveal>
            <div class="mt-12">
                <ProcessTimeline :steps="process" />
            </div>
        </section>

        <!-- Work -->
        <section v-if="on('work')" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader
                    :eyebrow="sections.work?.eyebrow"
                    :title="sections.work?.title"
                    :view-all-label="sections.work?.view_all_label ?? 'All work'"
                    view-all-href="/work"
                />
            </ScrollReveal>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                <ScrollReveal v-for="(project, i) in featuredProjects" :key="project.id" :delay="i * 90">
                    <CaseCard :project="project" />
                </ScrollReveal>
            </div>
        </section>

        <ManifestoBand v-if="on('manifesto')" :cta="manifesto" />

        <!-- Team -->
        <section v-if="on('team')" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader
                    :eyebrow="sections.team?.eyebrow"
                    :title="sections.team?.title"
                    :view-all-label="sections.team?.view_all_label ?? 'Meet the team'"
                    view-all-href="/team"
                />
            </ScrollReveal>
            <div class="mt-10 grid grid-cols-2 gap-6 lg:grid-cols-4">
                <ScrollReveal v-for="(member, i) in featuredTeam" :key="member.id" :delay="i * 80">
                    <TeamCard :member="member" />
                </ScrollReveal>
            </div>
        </section>

        <!-- Testimonials -->
        <section v-if="on('testimonials')" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <SectionHeader :eyebrow="sections.testimonials?.eyebrow" :title="sections.testimonials?.title" />
            </ScrollReveal>
            <div class="mt-10 grid gap-6 md:grid-cols-2">
                <ScrollReveal v-for="(t, i) in featuredTestimonials" :key="t.id" :delay="i * 90">
                    <TestimonialCard :testimonial="t" />
                </ScrollReveal>
            </div>
        </section>

        <ClosingCta v-if="on('closing')" :cta="cta" />
    </div>
</template>
