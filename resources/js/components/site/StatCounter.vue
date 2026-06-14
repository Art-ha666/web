<script setup lang="ts">
import { computed } from 'vue';
import { useCountUp } from '@/composables/useCountUp';
import { useReveal } from '@/composables/useReveal';

const props = defineProps<{
    value: string;
    prefix?: string | null;
    suffix?: string | null;
    label: string;
    gradient?: boolean;
}>();

const { el, visible } = useReveal();

const numeric = computed(() => parseFloat(props.value));
const isNumeric = computed(() => !Number.isNaN(numeric.value) && /^[\d.]+$/.test(props.value));
const decimals = computed(() => (props.value.includes('.') ? props.value.split('.')[1].length : 0));

const animated = useCountUp(isNumeric.value ? numeric.value : 0, visible);
const display = computed(() => (isNumeric.value ? animated.value.toFixed(decimals.value) : props.value));
</script>

<template>
    <div ref="el">
        <div
            class="font-display text-4xl font-bold leading-none tracking-tight sm:text-5xl"
            :class="gradient ? 'ak-gradient-text' : 'text-ak-text'"
        >
            <span v-if="prefix">{{ prefix }}</span>{{ display }}<span v-if="suffix">{{ suffix }}</span>
        </div>
        <p class="mt-3 text-sm text-ak-text-2">{{ label }}</p>
    </div>
</template>
