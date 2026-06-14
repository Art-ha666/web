<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Testimonials', href: '/admin/testimonials' }] } });

defineProps<{
    testimonials: Array<{ id: number; author_name: string; author_role?: string; company_name?: string; rating?: number; featured: boolean; is_active: boolean; sort_order: number }>;
}>();

function remove(id: number, authorName: string) {
    if (confirm(`Delete the testimonial by “${authorName}”?`)) {
        router.delete(`/admin/testimonials/${id}`);
    }
}
</script>

<template>
    <Head title="Testimonials" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Testimonials</h1>
                <p class="text-sm text-muted-foreground">Client quotes shown across the marketing pages.</p>
            </div>
            <Link href="/admin/testimonials/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"><Plus class="size-4" /> New testimonial</Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr><th class="px-4 py-3 font-medium">Author</th><th class="hidden px-4 py-3 font-medium md:table-cell">Company</th><th class="px-4 py-3 font-medium">Rating</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="item in testimonials" :key="item.id" class="transition hover:bg-muted/50">
                        <td class="px-4 py-3 font-medium">{{ item.author_name }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ item.company_name || '-' }}</td>
                        <td class="px-4 py-3 text-muted-foreground">{{ item.rating ? `${item.rating}/5` : '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="item.is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-muted text-muted-foreground'">{{ item.is_active ? 'Active' : 'Hidden' }}</span>
                            <span v-if="item.featured" class="ml-1 rounded-full bg-violet-500/15 px-2 py-0.5 text-xs text-violet-400">Featured</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/testimonials/${item.id}/edit`" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs transition hover:bg-muted"><Pencil class="size-3.5" /> Edit</Link>
                                <button class="rounded-lg border border-border p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(item.id, item.author_name)"><Trash2 class="size-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
