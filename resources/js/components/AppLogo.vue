<script setup lang="ts">
import BrandGlyph from '@/components/site/BrandGlyph.vue';
</script>

<template>
    <BrandGlyph :size="30" :animated="false" />
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate font-semibold leading-tight">AKH Solutions</span>
        <span class="truncate text-xs text-muted-foreground">Studio CMS</span>
    </div>
</template>
