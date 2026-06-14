<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Lock, Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Pages', href: '/admin/pages' }] } });

defineProps<{
    pages: Array<{
        id: number;
        title: string;
        slug: string;
        parent_id: number | null;
        public_path: string;
        status: string;
        is_system: boolean;
        show_in_nav: boolean;
        updated_at: string;
    }>;
}>();

function remove(slug: string, title: string) {
    if (confirm(`Delete the page “${title}”?`)) {
        router.delete(`/admin/pages/${slug}`);
    }
}
</script>

<template>
    <Head title="Pages" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Pages</h1>
                <p class="text-sm text-muted-foreground">Create pages and edit the wording across your site.</p>
            </div>
            <Link href="/admin/pages/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90">
                <Plus class="size-4" /> New page
            </Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="hidden px-4 py-3 font-medium sm:table-cell">URL</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="page in pages" :key="page.id" class="transition hover:bg-muted/50">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-2 font-medium">
                                <Lock v-if="page.is_system" class="size-3.5 text-muted-foreground" />
                                {{ page.title }}
                            </span>
                        </td>
                        <td class="hidden px-4 py-3 font-mono text-xs text-muted-foreground sm:table-cell">{{ page.public_path }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium capitalize" :class="page.status === 'published' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-muted text-muted-foreground'">{{ page.status }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/pages/${page.slug}/edit`" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs transition hover:bg-muted"><Pencil class="size-3.5" /> Edit</Link>
                                <button v-if="!page.is_system" class="inline-flex items-center rounded-lg border border-border p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(page.slug, page.title)"><Trash2 class="size-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
