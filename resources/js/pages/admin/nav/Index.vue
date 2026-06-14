<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Navigation', href: '/admin/nav-items' }] } });

defineProps<{
    navItems: Array<{ id: number; label: string; location: string; url?: string; is_cta: boolean; is_active: boolean; sort_order: number }>;
}>();

function remove(id: number, label: string) {
    if (confirm(`Delete the nav item “${label}”?`)) {
        router.delete(`/admin/nav-items/${id}`);
    }
}
</script>

<template>
    <Head title="Navigation" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Navigation</h1>
                <p class="text-sm text-muted-foreground">The links shown in the site header and footer.</p>
            </div>
            <Link href="/admin/nav-items/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"><Plus class="size-4" /> New nav item</Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr><th class="px-4 py-3 font-medium">Label</th><th class="px-4 py-3 font-medium">Location</th><th class="hidden px-4 py-3 font-medium md:table-cell">URL</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="item in navItems" :key="item.id" class="transition hover:bg-muted/50">
                        <td class="px-4 py-3 font-medium">
                            {{ item.label }}
                            <span v-if="item.is_cta" class="ml-1 rounded-full bg-violet-500/15 px-2 py-0.5 text-xs text-violet-400">CTA</span>
                            <span v-if="!item.is_active" class="ml-1 rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">Hidden</span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ item.location }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ item.url || '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/nav-items/${item.id}/edit`" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs transition hover:bg-muted"><Pencil class="size-3.5" /> Edit</Link>
                                <button class="rounded-lg border border-border p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(item.id, item.label)"><Trash2 class="size-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
