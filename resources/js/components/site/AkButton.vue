<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        href?: string;
        variant?: 'primary' | 'gradient' | 'ghost';
        size?: 'md' | 'lg';
        type?: 'button' | 'submit';
        external?: boolean;
        disabled?: boolean;
    }>(),
    { variant: 'primary', size: 'md', type: 'button', external: false, disabled: false },
);

const classes = computed(() => [
    'ak-btn ak-focusable',
    {
        primary: 'ak-btn-primary',
        gradient: 'ak-btn-gradient',
        ghost: 'ak-btn-ghost',
    }[props.variant],
    props.size === 'lg' ? 'text-base px-7 py-3.5' : '',
    props.disabled ? 'pointer-events-none opacity-60' : '',
]);
</script>

<template>
    <Link v-if="href && !external" :href="href" :class="classes">
        <slot />
    </Link>
    <a v-else-if="href && external" :href="href" target="_blank" rel="noopener noreferrer" :class="classes">
        <slot />
    </a>
    <button v-else :type="type" :class="classes" :disabled="disabled">
        <slot />
    </button>
</template>
