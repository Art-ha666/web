<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';

const props = defineProps<{ ctaSection: Record<string, any> | null }>();

defineOptions({ layout: { breadcrumbs: [{ title: 'CTA sections', href: '/admin/cta-sections' }, { title: 'Edit', href: '#' }] } });

const gradientVariants = ['default', 'aurora', 'sunset', 'ocean', 'violet'];

const form = useForm({
    key: props.ctaSection?.key ?? '',
    eyebrow: props.ctaSection?.eyebrow ?? '',
    headline: props.ctaSection?.headline ?? '',
    body: props.ctaSection?.body ?? '',
    primary_cta_label: props.ctaSection?.primary_cta_label ?? '',
    primary_cta_url: props.ctaSection?.primary_cta_url ?? '',
    secondary_cta_label: props.ctaSection?.secondary_cta_label ?? '',
    secondary_cta_url: props.ctaSection?.secondary_cta_url ?? '',
    microcopy: props.ctaSection?.microcopy ?? '',
    gradient_variant: props.ctaSection?.gradient_variant ?? 'default',
    is_active: props.ctaSection ? Boolean(props.ctaSection.is_active) : true,
});

function save() {
    if (props.ctaSection) {
        form.put(`/admin/cta-sections/${props.ctaSection.id}`, { preserveScroll: true });
    } else {
        form.post('/admin/cta-sections', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="ctaSection ? `Edit - ${ctaSection.key}` : 'New CTA section'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/cta-sections" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> CTA sections</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ ctaSection ? ctaSection.key : 'New CTA section' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Key" :error="form.errors.key" hint="Unique identifier used to fetch this block"><input v-model="form.key" :class="input" /></AdminField>
            <AdminField label="Gradient variant" :error="form.errors.gradient_variant">
                <select v-model="form.gradient_variant" :class="input">
                    <option v-for="variant in gradientVariants" :key="variant" :value="variant">{{ variant }}</option>
                </select>
            </AdminField>
            <AdminField label="Eyebrow" :error="form.errors.eyebrow" class="sm:col-span-2"><input v-model="form.eyebrow" :class="input" /></AdminField>
            <AdminField label="Headline" :error="form.errors.headline" class="sm:col-span-2"><input v-model="form.headline" :class="input" /></AdminField>
            <AdminField label="Body" :error="form.errors.body" class="sm:col-span-2"><textarea v-model="form.body" rows="3" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
            <AdminField label="Microcopy" :error="form.errors.microcopy" class="sm:col-span-2"><input v-model="form.microcopy" :class="input" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Primary CTA label" :error="form.errors.primary_cta_label"><input v-model="form.primary_cta_label" :class="input" /></AdminField>
            <AdminField label="Primary CTA URL" :error="form.errors.primary_cta_url"><input v-model="form.primary_cta_url" :class="input" /></AdminField>
            <AdminField label="Secondary CTA label" :error="form.errors.secondary_cta_label"><input v-model="form.secondary_cta_label" :class="input" /></AdminField>
            <AdminField label="Secondary CTA URL" :error="form.errors.secondary_cta_url"><input v-model="form.secondary_cta_url" :class="input" /></AdminField>
        </section>

        <section class="rounded-xl border border-border bg-card p-5">
            <label class="flex items-center gap-3 text-sm"><input v-model="form.is_active" type="checkbox" class="size-4 accent-primary" /> Active</label>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save CTA section' }}</button>
            <Link href="/admin/cta-sections" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
