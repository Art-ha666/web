<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import StatCounter from '@/components/site/StatCounter.vue';
import TeamCard from '@/components/site/TeamCard.vue';
import { useSite } from '@/composables/useSite';

interface Member {
    id: number | string;
    name: string;
    slug?: string;
    role?: string;
    specialty?: string;
    bio?: string;
    years_experience?: number;
    linkedin?: string;
    github?: string;
}

const props = defineProps<{
    content: Record<string, any>;
    team: Member[];
}>();

const { site } = useSite();

const c = computed(() => props.content ?? {});
const on = (key: string): boolean => c.value[key]?.enabled !== false;

const teamCount = computed(() => props.team.length);

const avgYears = computed<number | null>(() => {
    const withYears = props.team.filter((m) => typeof m.years_experience === 'number');

    if (!withYears.length) {
        return null;
    }

    const total = withYears.reduce((sum, m) => sum + (m.years_experience ?? 0), 0);

    return Math.round(total / withYears.length);
});
</script>

<template>
    <div>
        <Head>
            <title>{{ c.seo?.title || `The team - ${site?.name || 'AKH Solutions'}` }}</title>
            <meta name="description" :content="c.seo?.description" />
        </Head>

        <!-- Header -->
        <section class="ak-grid-texture pt-36 sm:pt-44">
            <div class="ak-container">
                <ScrollReveal class="max-w-3xl">
                    <EyebrowLabel>{{ c.hero?.eyebrow }}</EyebrowLabel>
                    <h1 class="mt-5 font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ak-text sm:text-6xl">
                        {{ c.hero?.title }} <span class="ak-gradient-text">{{ c.hero?.title_accent }}</span>
                    </h1>
                    <p class="mt-6 text-lg text-ak-text-2 sm:text-xl">
                        {{ c.hero?.body }}
                    </p>
                </ScrollReveal>

                <ScrollReveal v-if="on('hero_stats')" :delay="120">
                    <div class="mt-12 grid grid-cols-2 gap-8 border-t border-ak-hairline pt-10 sm:grid-cols-3">
                        <div v-if="avgYears !== null">
                            <StatCounter :value="String(avgYears)" :suffix="c.hero_stats?.years_suffix" :label="c.hero_stats?.years_label" gradient />
                        </div>
                        <div>
                            <StatCounter :value="String(teamCount)" :label="c.hero_stats?.engineers_label" gradient />
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <StatCounter :value="c.hero_stats?.handoffs_value ?? '0'" :label="c.hero_stats?.handoffs_label" gradient />
                        </div>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Roster -->
        <section class="ak-container py-16 sm:py-24">
            <div class="grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                <ScrollReveal v-for="(member, i) in team" :key="member.id" :delay="(i % 3) * 90" class="flex flex-col">
                    <TeamCard :member="member" />
                    <p v-if="member.bio" class="ak-text-3 mt-4 text-sm leading-relaxed text-ak-text-3">
                        {{ member.bio }}
                    </p>
                </ScrollReveal>
            </div>

            <ScrollReveal v-if="!team.length" class="ak-card flex flex-col items-center gap-4 p-12 text-center">
                <Icon name="users" :size="32" class="text-ak-text-3" />
                <p class="text-ak-text-2">{{ c.roster?.empty }}</p>
            </ScrollReveal>
        </section>

        <!-- Contact CTA -->
        <section v-if="on('cta')" class="ak-container pb-24 sm:pb-32">
            <ScrollReveal>
                <div class="ak-card ak-glass relative overflow-hidden p-10 text-center sm:p-16">
                    <div class="ak-gradient-bg pointer-events-none absolute inset-x-0 top-0 h-px opacity-60" />
                    <EyebrowLabel class="justify-center">{{ c.cta?.eyebrow }}</EyebrowLabel>
                    <h2 class="mx-auto mt-5 max-w-2xl font-display text-3xl font-semibold leading-tight tracking-tight text-ak-text sm:text-4xl">
                        {{ c.cta?.title }}
                    </h2>
                    <p class="mx-auto mt-5 max-w-xl text-ak-text-2 sm:text-lg">
                        {{ c.cta?.body }}
                    </p>
                    <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
                        <AkButton href="/careers" variant="gradient" size="lg">
                            {{ c.cta?.primary_label }}
                            <Icon name="arrow-right" :size="18" />
                        </AkButton>
                        <AkButton href="/contact" variant="ghost" size="lg">{{ c.cta?.secondary_label }}</AkButton>
                    </div>
                    <p v-if="site?.email" class="mt-6 text-sm text-ak-text-3">
                        {{ c.cta?.email_prefix }}
                        <a :href="`mailto:${site.email}`" class="text-ak-text transition hover:text-ak-primary">{{ site.email }}</a>
                    </p>
                </div>
            </ScrollReveal>
        </section>
    </div>
</template>
