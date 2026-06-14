<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Stats', href: '/admin/stats' }] } });

defineProps<{
    stats: Array<{ id: number; value: string; prefix?: string; suffix?: string; label: string; group: string; accent_color?: string; is_active: boolean; sort_order: number }>;
}>();

function remove(id: number, label: string) {
    if (confirm(`Delete the stat “${label}”?`)) {
        router.delete(`/admin/stats/${id}`);
    }
}
</script>

<template>
    <Head title="Stats" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Stats</h1>
                <p class="text-sm text-muted-foreground">The metrics shown in the hero and stat band.</p>
            </div>
            <Link href="/admin/stats/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"><Plus class="size-4" /> New stat</Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr><th class="px-4 py-3 font-medium">Value</th><th class="px-4 py-3 font-medium">Label</th><th class="hidden px-4 py-3 font-medium md:table-cell">Group</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="stat in stats" :key="stat.id" class="transition hover:bg-muted/50">
                        <td class="px-4 py-3 font-medium">{{ stat.prefix }}{{ stat.value }}{{ stat.suffix }}</td>
                        <td class="px-4 py-3">{{ stat.label }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ stat.group }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="stat.is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-muted text-muted-foreground'">{{ stat.is_active ? 'Active' : 'Hidden' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/stats/${stat.id}/edit`" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs transition hover:bg-muted"><Pencil class="size-3.5" /> Edit</Link>
                                <button class="rounded-lg border border-border p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(stat.id, stat.label)"><Trash2 class="size-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
