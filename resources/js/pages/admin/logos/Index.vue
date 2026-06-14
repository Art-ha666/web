<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Client logos', href: '/admin/client-logos' }] } });

defineProps<{
    clientLogos: Array<{ id: number; name: string; logo_path?: string; url?: string; type: string; is_active: boolean; sort_order: number }>;
}>();

function remove(id: number, name: string) {
    if (confirm(`Delete the logo “${name}”?`)) {
        router.delete(`/admin/client-logos/${id}`);
    }
}
</script>

<template>
    <Head title="Client logos" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Client logos</h1>
                <p class="text-sm text-muted-foreground">Client and tech-partner logos shown across the site.</p>
            </div>
            <Link href="/admin/client-logos/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"><Plus class="size-4" /> New logo</Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr><th class="px-4 py-3 font-medium">Name</th><th class="px-4 py-3 font-medium">Type</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="item in clientLogos" :key="item.id" class="transition hover:bg-muted/50">
                        <td class="px-4 py-3 font-medium">{{ item.name }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ item.type === 'tech_partner' ? 'Tech partner' : 'Client' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/client-logos/${item.id}/edit`" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs transition hover:bg-muted"><Pencil class="size-3.5" /> Edit</Link>
                                <button class="rounded-lg border border-border p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(item.id, item.name)"><Trash2 class="size-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
