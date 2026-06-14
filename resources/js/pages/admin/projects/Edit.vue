<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, X } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';
import StringList from '@/components/admin/StringList.vue';

const props = defineProps<{
    project: Record<string, any> | null;
    services: Array<{ id: number; title: string }>;
}>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Projects', href: '/admin/projects' }, { title: 'Edit', href: '#' }] } });

const form = useForm({
    title: props.project?.title ?? '',
    slug: props.project?.slug ?? '',
    client_name: props.project?.client_name ?? '',
    client_type: props.project?.client_type ?? '',
    industry: props.project?.industry ?? '',
    year: props.project?.year ?? '',
    headline_result: props.project?.headline_result ?? '',
    summary: props.project?.summary ?? '',
    challenge: props.project?.challenge ?? '',
    approach: props.project?.approach ?? '',
    architecture_notes: props.project?.architecture_notes ?? '',
    video_url: props.project?.video_url ?? '',
    category_tags: (props.project?.category_tags ?? ['']) as string[],
    tech_stack: (props.project?.tech_stack ?? ['']) as string[],
    gallery: (props.project?.gallery ?? ['']) as string[],
    results: (props.project?.results ?? [{ metric: '', label: '' }]) as Array<{ metric: string; label: string }>,
    related_service_id: props.project?.related_service_id ?? null,
    featured: Boolean(props.project?.featured),
    is_published: props.project ? Boolean(props.project.is_published) : true,
    sort_order: props.project?.sort_order ?? 0,
    seo_title: props.project?.seo_title ?? '',
    seo_description: props.project?.seo_description ?? '',
});

function addResult() {
    form.results.push({ metric: '', label: '' });
}
function removeResult(index: number) {
    form.results.splice(index, 1);
}

function save() {
    if (props.project) {
        form.put(`/admin/projects/${props.project.slug}`, { preserveScroll: true });
    } else {
        form.post('/admin/projects', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="project ? `Edit - ${project.title}` : 'New project'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/projects" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Projects</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ project ? project.title : 'New project' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Title" :error="form.errors.title"><input v-model="form.title" :class="input" /></AdminField>
            <AdminField label="Slug" :error="form.errors.slug" hint="Leave blank to auto-generate"><input v-model="form.slug" :class="input" /></AdminField>
            <AdminField label="Client name" :error="form.errors.client_name"><input v-model="form.client_name" :class="input" /></AdminField>
            <AdminField label="Client type" :error="form.errors.client_type"><input v-model="form.client_type" :class="input" /></AdminField>
            <AdminField label="Industry" :error="form.errors.industry"><input v-model="form.industry" :class="input" /></AdminField>
            <AdminField label="Year" :error="form.errors.year"><input v-model="form.year" :class="input" /></AdminField>
            <AdminField label="Headline result" class="sm:col-span-2" :error="form.errors.headline_result"><input v-model="form.headline_result" :class="input" /></AdminField>
            <AdminField label="Video URL" class="sm:col-span-2" :error="form.errors.video_url"><input v-model="form.video_url" :class="input" /></AdminField>
            <AdminField label="Summary" class="sm:col-span-2" :error="form.errors.summary"><textarea v-model="form.summary" rows="3" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5">
            <AdminField label="Challenge (HTML)" :error="form.errors.challenge"><textarea v-model="form.challenge" rows="4" class="w-full rounded-lg border border-border bg-background p-3 font-mono text-xs" /></AdminField>
            <AdminField label="Approach (HTML)" :error="form.errors.approach"><textarea v-model="form.approach" rows="4" class="w-full rounded-lg border border-border bg-background p-3 font-mono text-xs" /></AdminField>
            <AdminField label="Architecture notes (HTML)" :error="form.errors.architecture_notes"><textarea v-model="form.architecture_notes" rows="4" class="w-full rounded-lg border border-border bg-background p-3 font-mono text-xs" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Category tags"><StringList v-model="form.category_tags" placeholder="Fintech" /></AdminField>
            <AdminField label="Tech stack"><StringList v-model="form.tech_stack" placeholder="Laravel" /></AdminField>
            <AdminField label="Gallery" class="sm:col-span-2"><StringList v-model="form.gallery" placeholder="/images/gallery/shot.png" /></AdminField>
        </section>

        <section class="rounded-xl border border-border bg-card p-5">
            <h2 class="font-medium">Results</h2>
            <div class="mt-4 space-y-2">
                <div v-for="(row, i) in form.results" :key="i" class="flex items-center gap-2">
                    <input v-model="row.metric" placeholder="Metric e.g. 3x" class="h-9 w-full rounded-lg border border-border bg-background px-3 text-sm" />
                    <input v-model="row.label" placeholder="Label e.g. faster checkout" class="h-9 w-full rounded-lg border border-border bg-background px-3 text-sm" />
                    <button type="button" class="rounded p-1.5 text-rose-400 transition hover:bg-rose-500/10" @click="removeResult(i)"><X class="size-4" /></button>
                </div>
                <button type="button" class="inline-flex items-center gap-1.5 text-xs text-muted-foreground transition hover:text-foreground" @click="addResult"><Plus class="size-3.5" /> Add result</button>
            </div>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-3">
            <AdminField label="Related service" class="sm:col-span-3" :error="form.errors.related_service_id">
                <select v-model="form.related_service_id" :class="input">
                    <option :value="null">- None -</option>
                    <option v-for="service in services" :key="service.id" :value="service.id">{{ service.title }}</option>
                </select>
            </AdminField>
            <label class="flex items-center gap-3 text-sm"><input v-model="form.featured" type="checkbox" class="size-4 accent-primary" /> Featured</label>
            <label class="flex items-center gap-3 text-sm"><input v-model="form.is_published" type="checkbox" class="size-4 accent-primary" /> Published</label>
            <AdminField label="Sort order"><input v-model.number="form.sort_order" type="number" :class="input" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5">
            <AdminField label="SEO title" :error="form.errors.seo_title"><input v-model="form.seo_title" :class="input" /></AdminField>
            <AdminField label="SEO description" :error="form.errors.seo_description"><textarea v-model="form.seo_description" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save project' }}</button>
            <Link href="/admin/projects" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
