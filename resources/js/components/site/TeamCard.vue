<script setup lang="ts">
import GenerativeAvatar from './GenerativeAvatar.vue';
import Icon from './Icon.vue';

defineProps<{
    member: {
        name: string;
        role?: string;
        specialty?: string;
        years_experience?: number;
        linkedin?: string;
        github?: string;
    };
}>();
</script>

<template>
    <div class="ak-card ak-card-hover group flex flex-col p-5">
        <div class="relative">
            <div class="h-44 overflow-hidden rounded-xl">
                <div class="h-full w-full transition-transform duration-500 group-hover:scale-105">
                    <GenerativeAvatar :name="member.name" />
                </div>
            </div>
            <span v-if="member.years_experience" class="ak-glass absolute bottom-2 right-2 rounded-full px-2.5 py-1 text-xs font-medium text-ak-text">
                {{ member.years_experience }}y exp
            </span>
        </div>
        <h3 class="mt-4 font-display text-lg font-semibold text-ak-text">{{ member.name }}</h3>
        <p class="text-sm font-medium" :style="{ color: 'var(--ak-primary)' }">{{ member.role }}</p>
        <p class="mt-1 flex-1 text-sm text-ak-text-3">{{ member.specialty }}</p>
        <div v-if="member.linkedin || member.github" class="mt-4 flex gap-2">
            <a
                v-if="member.linkedin"
                :href="member.linkedin"
                target="_blank"
                rel="noopener noreferrer"
                class="ak-focusable inline-flex h-8 w-8 items-center justify-center rounded-lg border border-ak-glass-border text-ak-text-3 transition hover:border-ak-primary hover:text-ak-text"
                aria-label="LinkedIn"
            >
                <Icon name="linkedin" :size="16" />
            </a>
            <a
                v-if="member.github"
                :href="member.github"
                target="_blank"
                rel="noopener noreferrer"
                class="ak-focusable inline-flex h-8 w-8 items-center justify-center rounded-lg border border-ak-glass-border text-ak-text-3 transition hover:border-ak-primary hover:text-ak-text"
                aria-label="GitHub"
            >
                <Icon name="github" :size="16" />
            </a>
        </div>
    </div>
</template>
