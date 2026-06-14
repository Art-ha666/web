<script setup lang="ts">
import Icon from './Icon.vue';
import ScrollReveal from './ScrollReveal.vue';

defineProps<{
    steps: Array<{ number?: string; title: string; description?: string; deliverable_tag?: string; icon?: string }>;
}>();
</script>

<template>
    <div class="relative">
        <!-- connector line -->
        <div class="pointer-events-none absolute left-0 right-0 top-12 hidden h-px ak-gradient-bg opacity-40 lg:block" />

        <div class="grid gap-6 lg:grid-cols-4">
            <ScrollReveal v-for="(step, i) in steps" :key="i" :delay="i * 100">
                <div class="ak-card h-full p-6">
                    <div class="flex items-center justify-between">
                        <span class="ak-gradient-text font-display text-4xl font-bold leading-none">{{ step.number }}</span>
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-ak-glass-border text-ak-text-2" :style="{ background: 'var(--ak-surface-2)' }">
                            <Icon :name="step.icon ?? 'sparkles'" :size="20" />
                        </span>
                    </div>
                    <h3 class="mt-5 font-display text-lg font-semibold text-ak-text">{{ step.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ak-text-2">{{ step.description }}</p>
                    <span v-if="step.deliverable_tag" class="ak-chip mt-5 inline-flex">
                        <Icon name="check" :size="12" :style="{ color: 'var(--ak-primary)' }" />
                        {{ step.deliverable_tag }}
                    </span>
                </div>
            </ScrollReveal>
        </div>
    </div>
</template>
