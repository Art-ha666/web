<script setup lang="ts">
import { useReveal } from '@/composables/useReveal';

const props = withDefaults(defineProps<{ delay?: number; as?: string; y?: number }>(), {
    delay: 0,
    as: 'div',
    y: 22,
});

const { el, visible } = useReveal();
</script>

<template>
    <component
        :is="as"
        ref="el"
        :style="{
            transition: 'opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1)',
            transitionDelay: `${props.delay}ms`,
            opacity: visible ? 1 : 0,
            transform: visible ? 'translateY(0)' : `translateY(${props.y}px)`,
        }"
    >
        <slot />
    </component>
</template>
