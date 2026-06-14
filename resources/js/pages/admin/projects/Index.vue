<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Projects', href: '/admin/projects' }] } });

defineProps<{
    projects: Array<{ id: number; title: string; slug: string; industry?: string; year?: string; featured: boolean; is_published: boolean; sort_order: number }>;
}>();

function remove(slug: string, title: string) {
    if (confirm(`Delete the project “${title}”?`)) {
        router.delete(`/admin/projects/${slug}`);
    }
}
</script>

<template>
    <Head title="Projects" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Projects</h1>
                <p class="text-sm text-muted-foreground">Case studies and portfolio entries.</p>
            </div>
            <Link href="/admin/projects/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"><Plus class="size-4" /> New project</Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr><th class="px-4 py-3 font-medium">Title</th><th class="hidden px-4 py-3 font-medium md:table-cell">Industry</th><th class="hidden px-4 py-3 font-medium md:table-cell">Year</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="project in projects" :key="project.id" class="transition hover:bg-muted/50">
                        <td class="px-4 py-3 font-medium">{{ project.title }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ project.industry || '-' }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ project.year || '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="project.is_published ? 'bg-emerald-500/15 text-emerald-400' : 'bg-muted text-muted-foreground'">{{ project.is_published ? 'Published' : 'Draft' }}</span>
                            <span v-if="project.featured" class="ml-1 rounded-full bg-violet-500/15 px-2 py-0.5 text-xs text-violet-400">Featured</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/projects/${project.slug}/edit`" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs transition hover:bg-muted"><Pencil class="size-3.5" /> Edit</Link>
                                <button class="rounded-lg border border-border p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(project.slug, project.title)"><Trash2 class="size-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
