<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from './Icon.vue';

const props = defineProps<{
    project: {
        title: string;
        slug: string;
        client_type?: string;
        industry?: string;
        year?: string;
        category_tags?: string[];
        headline_result?: string;
        results?: Array<{ metric: string; label: string }>;
    };
}>();

const primaryResult = computed(() => props.project.results?.[0]);
</script>

<template>
    <Link
        :href="`/work/${project.slug}`"
        class="ak-card ak-card-hover ak-focusable group flex flex-col p-6"
    >
        <div class="flex items-center gap-2">
            <span class="ak-chip" :style="{ borderColor: 'color-mix(in srgb, var(--ak-primary) 40%, transparent)' }">
                {{ project.industry }}
            </span>
            <span class="text-xs text-ak-text-3">{{ project.client_type }} · {{ project.year }}</span>
        </div>

        <h3 class="mt-5 font-display text-xl font-semibold leading-snug text-ak-text">{{ project.title }}</h3>
        <p class="mt-2 flex-1 text-sm leading-relaxed text-ak-text-2">{{ project.headline_result }}</p>

        <div v-if="primaryResult" class="mt-6 flex items-end justify-between border-t border-ak-hairline pt-5">
            <div>
                <div class="ak-gradient-text font-display text-3xl font-bold leading-none">{{ primaryResult.metric }}</div>
                <div class="mt-1 text-xs text-ak-text-3">{{ primaryResult.label }}</div>
            </div>
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-ak-glass-border text-ak-text-2 transition group-hover:border-ak-primary group-hover:text-ak-primary">
                <Icon name="arrow-up-right" :size="18" />
            </span>
        </div>
    </Link>
</template>
