<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AkButton from '@/components/site/AkButton.vue';
import EyebrowLabel from '@/components/site/EyebrowLabel.vue';
import { useSite } from '@/composables/useSite';

interface Block {
    type?: string;
    data?: { html?: string; text?: string; level?: number };
}

const props = defineProps<{
    page: {
        title: string;
        slug: string;
        blocks?: Block[] | Record<string, unknown> | null;
        seo_title?: string;
        seo_description?: string;
    };
    breadcrumbs?: Array<{ title: string; url: string }>;
}>();

const { site } = useSite();

// Legal/CMS pages store blocks as an ordered array; ignore non-array (home-style) shapes.
const blocks = computed<Block[]>(() => (Array.isArray(props.page.blocks) ? props.page.blocks : []));
</script>

<template>
    <Head>
        <title>{{ page.seo_title || page.title }}</title>
        <meta v-if="page.seo_description" name="description" :content="page.seo_description" />
    </Head>

    <article class="ak-container max-w-3xl pb-24 pt-36 sm:pt-44">
        <nav v-if="breadcrumbs?.length" aria-label="Breadcrumb" class="mb-4 text-sm text-ak-text-3">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li v-for="crumb in breadcrumbs" :key="crumb.url" class="flex items-center gap-1.5">
                    <Link :href="crumb.url" class="ak-focusable hover:text-ak-text">{{ crumb.title }}</Link>
                    <span aria-hidden="true">/</span>
                </li>
                <li aria-current="page" class="text-ak-text-2">{{ page.title }}</li>
            </ol>
        </nav>
        <EyebrowLabel v-else>{{ site.name }}</EyebrowLabel>
        <h1 class="mt-5 font-display text-4xl font-semibold leading-tight tracking-tight text-ak-text sm:text-5xl">
            {{ page.title }}
        </h1>

        <div class="mt-10 space-y-8">
            <template v-for="(block, i) in blocks" :key="i">
                <div v-if="block.data?.html" class="ak-prose" v-html="block.data.html" />
                <h2 v-else-if="block.type === 'heading' && block.data?.text" class="font-display text-2xl font-semibold text-ak-text">
                    {{ block.data.text }}
                </h2>
                <p v-else-if="block.data?.text" class="text-ak-text-2">{{ block.data.text }}</p>
            </template>

            <p v-if="!blocks.length" class="text-ak-text-2">This page has no content yet.</p>
        </div>
    </article>

    <section class="ak-container pb-24">
        <div class="ak-card flex flex-col items-center gap-4 p-10 text-center">
            <h2 class="font-display text-2xl font-semibold text-ak-text">Questions? Talk to an engineer.</h2>
            <AkButton href="/contact" variant="gradient" size="lg">Book a discovery call</AkButton>
        </div>
    </section>
</template>
