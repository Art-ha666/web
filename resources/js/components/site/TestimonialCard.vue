<script setup lang="ts">
import { computed } from 'vue';
import Icon from './Icon.vue';

const props = defineProps<{
    testimonial: {
        quote: string;
        author_name: string;
        author_role?: string;
        company_name?: string;
        rating?: number;
    };
}>();

const initials = computed(() =>
    props.testimonial.author_name
        .split(' ')
        .map((p) => p[0])
        .slice(0, 2)
        .join(''),
);
</script>

<template>
    <figure class="ak-glass flex h-full flex-col p-7">
        <Icon name="quote" :size="30" class="opacity-30" :style="{ color: 'var(--ak-primary)' }" />
        <div v-if="testimonial.rating" class="mt-3 flex gap-1">
            <Icon v-for="n in testimonial.rating" :key="n" name="star" :size="15" class="fill-current" :style="{ color: 'var(--ak-grad-2)' }" />
        </div>
        <blockquote class="mt-4 flex-1 font-display text-lg leading-relaxed text-ak-text">"{{ testimonial.quote }}"</blockquote>
        <figcaption class="mt-6 flex items-center gap-3 border-t border-ak-hairline pt-5">
            <span class="flex h-11 w-11 items-center justify-center rounded-full ak-gradient-bg text-sm font-bold text-white">{{ initials }}</span>
            <span>
                <span class="block text-sm font-semibold text-ak-text">{{ testimonial.author_name }}</span>
                <span class="block text-xs text-ak-text-3">{{ testimonial.author_role }}<template v-if="testimonial.company_name"> · {{ testimonial.company_name }}</template></span>
            </span>
        </figcaption>
    </figure>
</template>
