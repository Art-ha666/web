<script setup lang="ts">
import { Plus, X } from 'lucide-vue-next';

const model = defineModel<string[]>({ default: () => [] });
defineProps<{ placeholder?: string }>();

function add() {
    model.value.push('');
}
function remove(index: number) {
    model.value.splice(index, 1);
}
</script>

<template>
    <div class="space-y-2">
        <div v-for="(item, i) in model" :key="i" class="flex items-center gap-2">
            <input v-model="model[i]" :placeholder="placeholder" class="h-9 w-full rounded-lg border border-border bg-background px-3 text-sm" />
            <button type="button" class="rounded p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(i)"><X class="size-4" /></button>
        </div>
        <button type="button" class="inline-flex items-center gap-1.5 text-xs text-muted-foreground transition hover:text-foreground" @click="add">
            <Plus class="size-3.5" /> Add item
        </button>
    </div>
</template>
