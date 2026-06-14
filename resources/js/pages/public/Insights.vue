<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import Icon from '@/components/site/Icon.vue';
import ScrollReveal from '@/components/site/ScrollReveal.vue';
import { useSite } from '@/composables/useSite';

interface Author {
    name: string;
    role: string;
}

interface Article {
    id: number | string;
    title: string;
    slug: string;
    excerpt: string;
    tags: string[];
    reading_time: number;
    published_at: string;
    author: Author | null;
    featured?: boolean;
}

const props = defineProps<{
    content: Record<string, any>;
    articles: Article[];
}>();

const { site } = useSite();

const c = computed(() => props.content ?? {});
const on = (key: string): boolean => c.value[key]?.enabled !== false;

const featured = computed<Article | null>(
    () => props.articles.find((a) => a.featured) ?? props.articles[0] ?? null,
);

const rest = computed<Article[]>(() =>
    featured.value ? props.articles.filter((a) => a.id !== featured.value!.id) : props.articles,
);

function formatDate(value: string): string {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
}
</script>

<template>
    <div>
        <Head>
            <title>{{ c.seo?.title || `Insights - Engineering notes from ${site.name}` }}</title>
            <meta name="description" :content="c.seo?.description" />
        </Head>

        <!-- Header -->
        <section class="ak-container pt-36 pb-12 sm:pt-44 sm:pb-16">
            <ScrollReveal>
                <EyebrowLabel>{{ c.hero?.eyebrow }}</EyebrowLabel>
                <h1
                    class="mt-5 max-w-3xl font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ak-text sm:text-6xl"
                >
                    {{ c.hero?.title }} <span class="ak-gradient-text">{{ c.hero?.title_accent }}</span>
                </h1>
                <p class="mt-6 max-w-2xl text-lg text-ak-text-2">
                    {{ c.hero?.body }}
                </p>
            </ScrollReveal>
        </section>

        <!-- Featured -->
        <section v-if="on('featured') && featured" class="ak-container pb-12 sm:pb-16">
            <ScrollReveal>
                <Link
                    :href="'/insights/' + featured.slug"
                    class="ak-card ak-card-hover ak-focusable group grid gap-8 p-7 sm:p-10 lg:grid-cols-[1.4fr_1fr] lg:items-center"
                >
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="ak-eyebrow text-ak-text-3">{{ c.featured?.label }}</span>
                            <span
                                v-for="tag in featured.tags?.slice(0, 3)"
                                :key="tag"
                                class="ak-chip"
                                :style="{ borderColor: 'color-mix(in srgb, var(--ak-primary) 40%, transparent)' }"
                            >
                                {{ tag }}
                            </span>
                        </div>
                        <h2
                            class="mt-5 font-display text-3xl font-semibold leading-[1.1] tracking-tight text-ak-text transition group-hover:text-ak-primary sm:text-4xl"
                        >
                            {{ featured.title }}
                        </h2>
                        <p class="mt-5 max-w-xl text-lg leading-relaxed text-ak-text-2">{{ featured.excerpt }}</p>
                    </div>

                    <div class="flex flex-col gap-6 border-ak-hairline lg:border-l lg:pl-10">
                        <div v-if="featured.author" class="flex items-center gap-3">
                            <span
                                class="flex h-11 w-11 items-center justify-center rounded-full ak-gradient-bg font-display text-sm font-bold text-white"
                            >
                                {{ featured.author.name?.charAt(0) }}
                            </span>
                            <span>
                                <span class="block font-medium text-ak-text">{{ featured.author.name }}</span>
                                <span class="block text-xs text-ak-text-3">{{ featured.author.role }}</span>
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-ak-text-3">
                            <span class="inline-flex items-center gap-1.5">
                                <Icon name="clock" :size="15" /> {{ featured.reading_time }} {{ c.article?.min_read_suffix || 'min read' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <Icon name="calendar" :size="15" /> {{ formatDate(featured.published_at) }}
                            </span>
                        </div>

                        <span
                            class="inline-flex items-center gap-2 self-start rounded-full border border-ak-glass-border px-5 py-2.5 text-sm font-medium text-ak-text transition group-hover:border-ak-primary group-hover:text-ak-primary"
                        >
                            {{ c.featured?.cta_label }}
                            <span class="transition-transform group-hover:translate-x-0.5">
                                <Icon name="arrow-up-right" :size="16" />
                            </span>
                        </span>
                    </div>
                </Link>
            </ScrollReveal>
        </section>

        <!-- Article grid -->
        <section v-if="rest.length" class="ak-container py-16 sm:py-24">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <ScrollReveal
                    v-for="(article, index) in rest"
                    :key="article.id"
                    :delay="(index % 3) * 80"
                    as="div"
                    class="h-full"
                >
                    <Link
                        :href="'/insights/' + article.slug"
                        class="ak-card ak-card-hover ak-focusable group flex h-full flex-col p-6"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                v-for="tag in article.tags?.slice(0, 2)"
                                :key="tag"
                                class="ak-chip"
                                :style="{ borderColor: 'color-mix(in srgb, var(--ak-primary) 40%, transparent)' }"
                            >
                                {{ tag }}
                            </span>
                        </div>

                        <h3
                            class="mt-5 font-display text-xl font-semibold leading-snug text-ak-text transition group-hover:text-ak-primary"
                        >
                            {{ article.title }}
                        </h3>
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-ak-text-2">{{ article.excerpt }}</p>

                        <div
                            class="mt-6 flex items-center justify-between border-t border-ak-hairline pt-5 text-xs text-ak-text-3"
                        >
                            <span class="inline-flex items-center gap-1.5">
                                <Icon name="clock" :size="14" /> {{ article.reading_time }} min
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <Icon name="calendar" :size="14" /> {{ formatDate(article.published_at) }}
                            </span>
                        </div>
                    </Link>
                </ScrollReveal>
            </div>
        </section>

        <!-- Newsletter CTA -->
        <section v-if="on('newsletter')" class="ak-container pb-24 sm:pb-32">
            <ScrollReveal>
                <div class="ak-glass ak-grid-texture relative overflow-hidden p-10 text-center sm:p-16">
                    <span
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ak-gradient-bg text-white"
                    >
                        <Icon :name="c.newsletter?.icon || 'mail'" :size="24" />
                    </span>
                    <h2
                        class="mx-auto mt-7 max-w-2xl font-display text-3xl font-semibold leading-tight tracking-tight text-ak-text sm:text-4xl"
                    >
                        {{ c.newsletter?.title }} <span class="ak-gradient-text">{{ c.newsletter?.title_accent }}</span>
                    </h2>
                    <p class="mx-auto mt-5 max-w-xl text-lg text-ak-text-2">
                        {{ c.newsletter?.body }}
                    </p>
                    <div class="mt-9 flex justify-center">
                        <AkButton href="/contact" variant="gradient" size="lg">
                            {{ c.newsletter?.button_label }}
                            <Icon name="arrow-right" :size="18" />
                        </AkButton>
                    </div>
                </div>
            </ScrollReveal>
        </section>
    </div>
</template>
