<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';
import StringList from '@/components/admin/StringList.vue';

const props = defineProps<{
    article: Record<string, any> | null;
    authors: Array<{ id: number; name: string }>;
}>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Articles', href: '/admin/articles' }, { title: 'Edit', href: '#' }] } });

const form = useForm({
    title: props.article?.title ?? '',
    slug: props.article?.slug ?? '',
    excerpt: props.article?.excerpt ?? '',
    body: props.article?.body ?? '',
    cover_image: props.article?.cover_image ?? '',
    author_id: props.article?.author_id ?? null,
    tags: (props.article?.tags ?? ['']) as string[],
    reading_time: props.article?.reading_time ?? 0,
    status: props.article?.status ?? 'draft',
    featured: Boolean(props.article?.featured),
    published_at: props.article?.published_at ? String(props.article.published_at).slice(0, 16) : '',
    seo_title: props.article?.seo_title ?? '',
    seo_description: props.article?.seo_description ?? '',
});

function save() {
    if (props.article) {
        form.put(`/admin/articles/${props.article.slug}`, { preserveScroll: true });
    } else {
        form.post('/admin/articles', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="article ? `Edit - ${article.title}` : 'New article'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/articles" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Articles</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ article ? article.title : 'New article' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Title" :error="form.errors.title" class="sm:col-span-2"><input v-model="form.title" :class="input" /></AdminField>
            <AdminField label="Slug" :error="form.errors.slug" hint="Leave blank to auto-generate" class="sm:col-span-2"><input v-model="form.slug" :class="input" /></AdminField>
            <AdminField label="Excerpt" :error="form.errors.excerpt" class="sm:col-span-2"><textarea v-model="form.excerpt" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
            <AdminField label="Body (HTML)" :error="form.errors.body" class="sm:col-span-2"><textarea v-model="form.body" rows="8" class="w-full rounded-lg border border-border bg-background p-3 font-mono text-xs" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Cover image" :error="form.errors.cover_image" class="sm:col-span-2"><input v-model="form.cover_image" :class="input" /></AdminField>
            <AdminField label="Author" :error="form.errors.author_id">
                <select v-model="form.author_id" :class="input">
                    <option :value="null">- None -</option>
                    <option v-for="author in authors" :key="author.id" :value="author.id">{{ author.name }}</option>
                </select>
            </AdminField>
            <AdminField label="Reading time (min)" :error="form.errors.reading_time"><input v-model.number="form.reading_time" type="number" :class="input" /></AdminField>
            <AdminField label="Tags" class="sm:col-span-2"><StringList v-model="form.tags" placeholder="Engineering" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-3">
            <AdminField label="Status" :error="form.errors.status">
                <select v-model="form.status" :class="input">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </AdminField>
            <AdminField label="Published at" :error="form.errors.published_at"><input v-model="form.published_at" type="datetime-local" :class="input" /></AdminField>
            <label class="flex items-end gap-3 pb-2 text-sm"><input v-model="form.featured" type="checkbox" class="size-4 accent-primary" /> Featured</label>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5">
            <AdminField label="SEO title" :error="form.errors.seo_title"><input v-model="form.seo_title" :class="input" /></AdminField>
            <AdminField label="SEO description" :error="form.errors.seo_description"><textarea v-model="form.seo_description" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save article' }}</button>
            <Link href="/admin/articles" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
