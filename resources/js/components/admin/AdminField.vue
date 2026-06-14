<script setup lang="ts">
defineProps<{ label?: string; error?: string; hint?: string }>();
</script>

<template>
    <label class="block text-sm">
        <span v-if="label" class="mb-1.5 block font-medium text-muted-foreground">{{ label }}</span>
        <slot />
        <span v-if="hint" class="mt-1 block text-xs text-muted-foreground">{{ hint }}</span>
        <span v-if="error" class="mt-1 block text-xs text-rose-400">{{ error }}</span>
    </label>
</template>
