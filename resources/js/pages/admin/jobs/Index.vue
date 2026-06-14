<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Job postings', href: '/admin/jobs' }] } });

defineProps<{
    jobPostings: Array<{ id: number; title: string; slug: string; department?: string; is_open: boolean; sort_order: number }>;
}>();

function remove(slug: string, title: string) {
    if (confirm(`Delete the job posting “${title}”?`)) {
        router.delete(`/admin/jobs/${slug}`);
    }
}
</script>

<template>
    <Head title="Job postings" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Job postings</h1>
                <p class="text-sm text-muted-foreground">Open roles shown on the careers page.</p>
            </div>
            <Link href="/admin/jobs/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"><Plus class="size-4" /> New posting</Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr><th class="px-4 py-3 font-medium">Title</th><th class="hidden px-4 py-3 font-medium md:table-cell">Department</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="job in jobPostings" :key="job.id" class="transition hover:bg-muted/50">
                        <td class="px-4 py-3 font-medium">{{ job.title }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ job.department || '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="job.is_open ? 'bg-emerald-500/15 text-emerald-400' : 'bg-muted text-muted-foreground'">{{ job.is_open ? 'Open' : 'Closed' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/jobs/${job.slug}/edit`" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs transition hover:bg-muted"><Pencil class="size-3.5" /> Edit</Link>
                                <button class="rounded-lg border border-border p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(job.slug, job.title)"><Trash2 class="size-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
