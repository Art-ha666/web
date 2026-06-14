<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ExternalLink, PencilRuler } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Page content', href: '/admin/content' }] } });

defineProps<{
    pages: Array<{ slug: string; label: string; route: string }>;
}>();
</script>

<template>
    <Head title="Page content" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Page content</h1>
            <p class="text-sm text-muted-foreground">Edit every section and every word on every page. Toggle sections on or off. The homepage has its own editor under <span class="font-medium">Homepage</span>.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div v-for="page in pages" :key="page.slug" class="flex items-center justify-between gap-3 rounded-xl border border-border bg-card p-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <PencilRuler class="size-4 text-muted-foreground" />
                        <span class="font-medium">{{ page.label }}</span>
                    </div>
                    <a :href="page.route" target="_blank" rel="noopener" class="mt-1 inline-flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground">
                        {{ page.route }} <ExternalLink class="size-3" />
                    </a>
                </div>
                <Link :href="`/admin/content/${page.slug}`" class="shrink-0 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90">Edit</Link>
            </div>
        </div>
    </div>
</template>
