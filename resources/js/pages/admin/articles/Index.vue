<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Articles', href: '/admin/articles' }] } });

defineProps<{
    articles: Array<{ id: number; title: string; slug: string; status: string; featured: boolean; published_at?: string | null }>;
}>();

function remove(slug: string, title: string) {
    if (confirm(`Delete the article “${title}”?`)) {
        router.delete(`/admin/articles/${slug}`);
    }
}
</script>

<template>
    <Head title="Articles" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Articles</h1>
                <p class="text-sm text-muted-foreground">Blog posts and editorial content.</p>
            </div>
            <Link href="/admin/articles/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90"><Plus class="size-4" /> New article</Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr><th class="px-4 py-3 font-medium">Title</th><th class="px-4 py-3 font-medium">Status</th><th class="hidden px-4 py-3 font-medium md:table-cell">Published</th><th class="px-4 py-3 text-right font-medium">Actions</th></tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="article in articles" :key="article.id" class="transition hover:bg-muted/50">
                        <td class="px-4 py-3 font-medium">{{ article.title }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="article.status === 'published' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-muted text-muted-foreground'">{{ article.status === 'published' ? 'Published' : 'Draft' }}</span>
                            <span v-if="article.featured" class="ml-1 rounded-full bg-violet-500/15 px-2 py-0.5 text-xs text-violet-400">Featured</span>
                        </td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ article.published_at || '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="`/admin/articles/${article.slug}/edit`" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1.5 text-xs transition hover:bg-muted"><Pencil class="size-3.5" /> Edit</Link>
                                <button class="rounded-lg border border-border p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="remove(article.slug, article.title)"><Trash2 class="size-3.5" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
