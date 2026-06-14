<script setup lang="ts">
withDefaults(defineProps<{ dot?: boolean }>(), { dot: true });
</script>

<template>
    <span class="ak-eyebrow inline-flex items-center gap-2">
        <span v-if="dot" class="h-1.5 w-6 rounded-full ak-gradient-bg" />
        <slot />
    </span>
</template>
