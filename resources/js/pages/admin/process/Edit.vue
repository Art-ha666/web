<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';

const props = defineProps<{ processStep: Record<string, any> | null }>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Process', href: '/admin/process-steps' }, { title: 'Edit', href: '#' }] } });

const form = useForm({
    number: props.processStep?.number ?? '',
    title: props.processStep?.title ?? '',
    description: props.processStep?.description ?? '',
    deliverable_tag: props.processStep?.deliverable_tag ?? '',
    icon: props.processStep?.icon ?? 'compass',
    is_active: props.processStep ? Boolean(props.processStep.is_active) : true,
    sort_order: props.processStep?.sort_order ?? 0,
});

function save() {
    if (props.processStep) {
        form.put(`/admin/process-steps/${props.processStep.id}`, { preserveScroll: true });
    } else {
        form.post('/admin/process-steps', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="processStep ? `Edit - ${processStep.title}` : 'New process step'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/process-steps" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Process</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ processStep ? processStep.title : 'New process step' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Number" hint="e.g. 01"><input v-model="form.number" :class="input" /></AdminField>
            <AdminField label="Title" :error="form.errors.title"><input v-model="form.title" :class="input" /></AdminField>
            <AdminField label="Deliverable tag"><input v-model="form.deliverable_tag" :class="input" /></AdminField>
            <AdminField label="Icon" hint="lucide name e.g. compass, search, rocket"><input v-model="form.icon" :class="input" /></AdminField>
            <AdminField label="Description" class="sm:col-span-2"><textarea v-model="form.description" rows="3" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <label class="flex items-center gap-3 text-sm"><input v-model="form.is_active" type="checkbox" class="size-4 accent-primary" /> Active</label>
            <AdminField label="Sort order"><input v-model.number="form.sort_order" type="number" :class="input" /></AdminField>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save step' }}</button>
            <Link href="/admin/process-steps" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
