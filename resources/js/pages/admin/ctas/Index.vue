<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'CTA sections', href: '/admin/cta-sections' }] } });

defineProps<{
    ctaSections: Array<{ id: number; key: string; headline: string; is_active: boolean }>;
}>();

function remove(id: number, key: string) {
    if (confirm(`Delete the CTA section “${key}”?`)) {
        router.delete(`/admin/cta-sections/${id}`);
    }
}
</script>

<template>
    <Head title="CTA sections" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">CTA sections</h1>
                <p class="text-sm text-muted-foreground">The reusable call-to-action blocks rendered across the marketing pages.</p>
            </div>
            <Link href="/admin/cta-sections/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"><Plus class="size-4" /> New CTA section</Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr><th class="px-4 py-3 font-medium">Key</th><th class="px-4 py-3 font-medium">Headline</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="cta in ctaSections" :key="cta.id" class="transition hover:bg-muted/50">
                        <td class="px-4 py-3 font-mono text-xs">{{ cta.key }}</td>
                        <td class="px-4 py-3 font-medium">{{ cta.headline }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="cta.is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-muted text-muted-foreground'">{{ cta.is_active ? 'Active' : 'Hidden' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/cta-sections/${cta.id}/edit`" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs transition hover:bg-muted"><Pencil class="size-3.5" /> Edit</Link>
                                <button class="rounded-lg border border-border p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(cta.id, cta.key)"><Trash2 class="size-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
