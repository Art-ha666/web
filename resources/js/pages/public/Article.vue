<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ClosingCta from '@/components/site/ClosingCta.vue';
import Icon from '@/components/site/Icon.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import { useSite } from '@/composables/useSite';

interface Author {
    name: string;
    slug: string;
    role: string;
    specialty: string;
}

interface RelatedArticle {
    title: string;
    slug: string;
    excerpt: string;
    reading_time: number;
    published_at: string;
}

interface ClosingCtaData {
    eyebrow?: string;
    headline: string;
    body?: string;
    primary_cta_label?: string;
    primary_cta_url?: string;
    secondary_cta_label?: string;
    secondary_cta_url?: string;
    microcopy?: string;
}

const props = defineProps<{
    content: Record<string, any>;
    article: {
        title: string;
        slug: string;
        excerpt: string;
        body: string;
        cover_image: string | null;
        tags: string[];
        reading_time: number;
        published_at: string;
        author: Author | null;
    };
    related: RelatedArticle[];
    closingCta: ClosingCtaData | null;
}>();

const { site } = useSite();

const c = computed(() => props.content ?? {});

function formatDate(value: string): string {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

const authorInitials = computed(() => {
    if (!props.article.author?.name) {
        return '';
    }

    return props.article.author.name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
});
</script>

<template>
    <div>
        <Head>
            <title>{{ `${article.title} - Insights · ${site.name}` }}</title>
            <meta name="description" :content="article.excerpt" />
        </Head>

        <!-- Article header + body -->
        <article class="ak-container pt-36 sm:pt-44">
            <div class="mx-auto max-w-3xl">
                <ScrollReveal>
                    <Link
                        href="/insights"
                        class="ak-focusable group inline-flex items-center gap-2 text-sm font-medium text-ak-text-3 transition hover:text-ak-text"
                    >
                        <span class="transition-transform group-hover:-translate-x-0.5">&larr;</span>
                        {{ c.article?.back_label || 'Insights' }}
                    </Link>

                    <div v-if="article.tags?.length" class="mt-8 flex flex-wrap gap-2">
                        <span v-for="tag in article.tags" :key="tag" class="ak-chip">{{ tag }}</span>
                    </div>

                    <h1
                        class="mt-6 font-display text-4xl font-semibold leading-[1.08] tracking-tight text-ak-text sm:text-5xl"
                    >
                        {{ article.title }}
                    </h1>

                    <p v-if="article.excerpt" class="mt-6 text-lg leading-relaxed text-ak-text-2">
                        {{ article.excerpt }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-3 text-sm text-ak-text-3">
                        <span v-if="article.author" class="font-medium text-ak-text-2">
                            {{ article.author.name }}
                            <span class="text-ak-text-3">· {{ article.author.role }}</span>
                        </span>
                        <span v-if="article.author" class="hidden h-1 w-1 rounded-full bg-ak-hairline sm:inline-block" />
                        <span v-if="article.published_at" class="inline-flex items-center gap-1.5">
                            <Icon name="calendar" :size="15" />
                            {{ formatDate(article.published_at) }}
                        </span>
                        <span class="hidden h-1 w-1 rounded-full bg-ak-hairline sm:inline-block" />
                        <span class="inline-flex items-center gap-1.5">
                            <Icon name="clock" :size="15" />
                            {{ article.reading_time }} {{ c.article?.min_read_suffix || 'min read' }}
                        </span>
                    </div>
                </ScrollReveal>

                <ScrollReveal :delay="80" :y="28">
                    <div
                        v-if="article.cover_image"
                        class="mt-10 overflow-hidden rounded-2xl border border-ak-hairline bg-ak-bg-2"
                    >
                        <img
                            :src="article.cover_image"
                            :alt="article.title"
                            class="aspect-[16/9] w-full object-cover"
                            loading="eager"
                        />
                    </div>
                </ScrollReveal>

                <ScrollReveal :delay="120">
                    <div class="ak-prose max-w-3xl pt-12 sm:pt-16" v-html="article.body" />
                </ScrollReveal>

                <!-- Author card -->
                <ScrollReveal v-if="article.author" :delay="80">
                    <div class="ak-card mt-16 flex flex-col gap-5 p-7 sm:flex-row sm:items-center sm:gap-6 sm:p-8">
                        <span
                            class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl ak-gradient-bg font-display text-xl font-semibold text-white"
                        >
                            {{ authorInitials }}
                        </span>
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-ak-text-3">{{ c.article?.author_label || 'Written by' }}</p>
                            <p class="mt-1.5 font-display text-xl font-semibold text-ak-text">
                                {{ article.author.name }}
                            </p>
                            <p class="mt-1 text-sm text-ak-text-2">
                                {{ article.author.role }}
                                <span v-if="article.author.specialty" class="text-ak-text-3">
                                    · {{ article.author.specialty }}</span
                                >
                            </p>
                        </div>
                    </div>
                </ScrollReveal>
            </div>
        </article>

        <!-- Related reading -->
        <section v-if="related?.length" class="ak-container py-16 sm:py-24">
            <div class="mx-auto max-w-5xl">
                <ScrollReveal>
                    <p class="ak-eyebrow inline-flex items-center gap-2">
                        <span class="h-1.5 w-6 rounded-full ak-gradient-bg" />
                        {{ c.article?.related_eyebrow || 'Keep reading' }}
                    </p>
                    <h2 class="mt-4 font-display text-3xl font-semibold tracking-tight text-ak-text sm:text-4xl">
                        {{ c.article?.related_title || 'Related reading' }}
                    </h2>
                </ScrollReveal>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <ScrollReveal v-for="(item, index) in related" :key="item.slug" :delay="index * 70" as="div">
                        <Link
                            :href="`/insights/${item.slug}`"
                            class="ak-card ak-card-hover group flex h-full flex-col p-6"
                        >
                            <div class="flex items-center gap-3 text-xs text-ak-text-3">
                                <span class="inline-flex items-center gap-1.5">
                                    <Icon name="calendar" :size="13" />
                                    {{ formatDate(item.published_at) }}
                                </span>
                                <span class="h-1 w-1 rounded-full bg-ak-hairline" />
                                <span class="inline-flex items-center gap-1.5">
                                    <Icon name="clock" :size="13" />
                                    {{ item.reading_time }} min
                                </span>
                            </div>
                            <h3
                                class="mt-4 font-display text-lg font-semibold leading-snug text-ak-text transition-colors group-hover:text-ak-primary"
                            >
                                {{ item.title }}
                            </h3>
                            <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-ak-text-2">
                                {{ item.excerpt }}
                            </p>
                            <span
                                class="mt-5 inline-flex items-center gap-1.5 text-sm font-medium text-ak-text-2 transition-colors group-hover:text-ak-primary"
                            >
                                {{ c.article?.related_cta || 'Read article' }}
                                <Icon
                                    name="arrow-right"
                                    :size="15"
                                    class="transition-transform group-hover:translate-x-0.5"
                                />
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
