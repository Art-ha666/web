<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';
import StringList from '@/components/admin/StringList.vue';

const props = defineProps<{ service: Record<string, any> | null }>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Services', href: '/admin/services' }, { title: 'Edit', href: '#' }] } });

const form = useForm({
    title: props.service?.title ?? '',
    slug: props.service?.slug ?? '',
    icon: props.service?.icon ?? 'sparkles',
    tab_label: props.service?.tab_label ?? '',
    short_blurb: props.service?.short_blurb ?? '',
    intro: props.service?.intro ?? '',
    value_metric: props.service?.value_metric ?? '',
    benefit_bullets: (props.service?.benefit_bullets ?? ['']) as string[],
    detail_body: props.service?.detail_body ?? '',
    gradient: {
        from: props.service?.gradient?.from ?? '#28baf3',
        via: props.service?.gradient?.via ?? '#5778f8',
        to: props.service?.gradient?.to ?? '#7e2cfd',
    } as Record<string, string>,
    tech_stack: (props.service?.tech_stack ?? ['']) as string[],
    featured: Boolean(props.service?.featured),
    is_active: props.service ? Boolean(props.service.is_active) : true,
    sort_order: props.service?.sort_order ?? 0,
    seo_title: props.service?.seo_title ?? '',
    seo_description: props.service?.seo_description ?? '',
});

function save() {
    if (props.service) {
        form.put(`/admin/services/${props.service.slug}`, { preserveScroll: true });
    } else {
        form.post('/admin/services', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="service ? `Edit - ${service.title}` : 'New service'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/services" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Services</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ service ? service.title : 'New service' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Title" :error="form.errors.title"><input v-model="form.title" :class="input" /></AdminField>
            <AdminField label="Slug" :error="form.errors.slug" hint="Leave blank to auto-generate"><input v-model="form.slug" :class="input" /></AdminField>
            <AdminField label="Icon" hint="lucide name e.g. code-2, sparkles, server"><input v-model="form.icon" :class="input" /></AdminField>
            <AdminField label="Tab label"><input v-model="form.tab_label" :class="input" /></AdminField>
            <AdminField label="Short blurb" class="sm:col-span-2"><input v-model="form.short_blurb" :class="input" /></AdminField>
            <AdminField label="Value metric" class="sm:col-span-2"><input v-model="form.value_metric" :class="input" /></AdminField>
            <AdminField label="Intro" class="sm:col-span-2"><textarea v-model="form.intro" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Benefit bullets"><StringList v-model="form.benefit_bullets" placeholder="Architect for scale…" /></AdminField>
            <AdminField label="Tech stack"><StringList v-model="form.tech_stack" placeholder="Laravel" /></AdminField>
            <AdminField label="Detail body (HTML)" class="sm:col-span-2"><textarea v-model="form.detail_body" rows="4" class="w-full rounded-lg border border-border bg-background p-3 font-mono text-xs" /></AdminField>
        </section>

        <section class="rounded-xl border border-border bg-card p-5">
            <h2 class="font-medium">Gradient backplate</h2>
            <div class="mt-4 grid grid-cols-3 gap-4">
                <AdminField v-for="stop in ['from', 'via', 'to']" :key="stop" :label="stop">
                    <div class="flex items-center gap-2 rounded-lg border border-border bg-background p-1.5">
                        <input v-model="form.gradient[stop]" type="color" class="size-8 cursor-pointer rounded border-0 bg-transparent p-0" />
                        <input v-model="form.gradient[stop]" type="text" class="w-full bg-transparent text-xs outline-none" />
                    </div>
                </AdminField>
            </div>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-3">
            <label class="flex items-center gap-3 text-sm"><input v-model="form.featured" type="checkbox" class="size-4 accent-primary" /> Featured</label>
            <label class="flex items-center gap-3 text-sm"><input v-model="form.is_active" type="checkbox" class="size-4 accent-primary" /> Active</label>
            <AdminField label="Sort order"><input v-model.number="form.sort_order" type="number" :class="input" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5">
            <AdminField label="SEO title"><input v-model="form.seo_title" :class="input" /></AdminField>
            <AdminField label="SEO description"><textarea v-model="form.seo_description" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save service' }}</button>
            <Link href="/admin/services" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
