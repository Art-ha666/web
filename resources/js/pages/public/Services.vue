<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import { useSite } from '@/composables/useSite';

const props = defineProps<{
    content: Record<string, any>;
    services: Array<{
        title: string;
        slug: string;
        icon: string;
        tab_label?: string;
        short_blurb: string;
        value_metric?: string;
        benefit_bullets: string[];
        gradient: { from: string; via: string; to: string };
        tech_stack: string[];
    }>;
}>();

const { site } = useSite();
const c = computed(() => props.content ?? {});
const on = (key: string): boolean => c.value[key]?.enabled !== false;
</script>

<template>
    <div>
        <Head>
            <title>{{ c.seo?.title || `Services - ${site.name}` }}</title>
            <meta name="description" :content="c.seo?.description" />
        </Head>

        <!-- Header -->
        <section class="ak-container pt-36 pb-16 sm:pt-44 sm:pb-24">
            <ScrollReveal>
                <EyebrowLabel>{{ c.hero?.eyebrow }}</EyebrowLabel>
                <h1
                    class="mt-5 max-w-3xl font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ak-text sm:text-5xl lg:text-6xl"
                >
                    {{ c.hero?.title }} <span class="ak-gradient-text">{{ c.hero?.title_accent }}</span>.
                </h1>
                <p class="mt-6 max-w-2xl text-ak-text-2 sm:text-lg">
                    {{ c.hero?.body }}
                </p>
            </ScrollReveal>

            <!-- Service grid -->
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <ScrollReveal v-for="(svc, i) in services" :key="svc.slug" :delay="i * 80">
                    <Link
                        :href="'/services/' + svc.slug"
                        class="ak-card ak-card-hover ak-focusable group flex h-full flex-col p-6"
                    >
                        <!-- Icon tile -->
                        <div class="flex items-start justify-between">
                            <span
                                class="ak-gradient-bg inline-flex h-12 w-12 items-center justify-center rounded-xl text-white shadow-sm"
                            >
                                <Icon :name="svc.icon" :size="22" />
                            </span>
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-ak-glass-border text-ak-text-3 transition group-hover:border-ak-primary group-hover:text-ak-primary"
                            >
                                <Icon name="arrow-up-right" :size="16" />
                            </span>
                        </div>

                        <h2 class="mt-5 font-display text-xl font-semibold leading-snug text-ak-text">
                            {{ svc.title }}
                        </h2>
                        <p class="mt-2 text-sm leading-relaxed text-ak-text-2">{{ svc.short_blurb }}</p>

                        <p v-if="svc.value_metric" class="ak-eyebrow mt-4">{{ svc.value_metric }}</p>

                        <!-- Benefit bullets -->
                        <ul class="mt-5 flex flex-col gap-2.5">
                            <li
                                v-for="(bullet, b) in svc.benefit_bullets.slice(0, 3)"
                                :key="b"
                                class="flex items-start gap-2.5 text-sm leading-snug text-ak-text-2"
                            >
                                <span
                                    class="ak-gradient-text mt-0.5 inline-flex shrink-0 items-center justify-center"
                                >
                                    <Icon name="check" :size="16" />
                                </span>
                                <span>{{ bullet }}</span>
                            </li>
                        </ul>

                        <!-- Tech stack chips -->
                        <div v-if="svc.tech_stack?.length" class="mt-6 flex flex-wrap gap-2">
                            <span v-for="tech in svc.tech_stack" :key="tech" class="ak-chip">{{ tech }}</span>
                        </div>

                        <!-- Explore affordance -->
                        <div class="mt-auto flex items-center gap-1.5 pt-6 text-sm font-medium text-ak-text">
                            <span class="transition-colors group-hover:text-ak-primary">{{ c.grid?.explore_label || 'Explore service' }}</span>
                            <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                        </div>
                    </Link>
                </ScrollReveal>
            </div>
        </section>

        <!-- Contact CTA -->
        <section v-if="on('cta')" class="ak-container py-16 sm:py-24">
            <ScrollReveal>
                <div class="ak-card ak-grid-texture relative overflow-hidden p-10 text-center sm:p-16">
                    <EyebrowLabel class="justify-center">{{ c.cta?.eyebrow }}</EyebrowLabel>
                    <h2
                        class="mx-auto mt-5 max-w-2xl font-display text-3xl font-semibold leading-[1.08] tracking-tight text-ak-text sm:text-4xl"
                    >
                        {{ c.cta?.title }}
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
