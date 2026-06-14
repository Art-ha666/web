<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';

const props = defineProps<{ clientLogo: Record<string, any> | null }>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Client logos', href: '/admin/client-logos' }, { title: 'Edit', href: '#' }] } });

const form = useForm({
    name: props.clientLogo?.name ?? '',
    logo_path: props.clientLogo?.logo_path ?? '',
    logo_svg: props.clientLogo?.logo_svg ?? '',
    url: props.clientLogo?.url ?? '',
    type: props.clientLogo?.type ?? 'client',
    is_active: props.clientLogo ? Boolean(props.clientLogo.is_active) : true,
    sort_order: props.clientLogo?.sort_order ?? 0,
});

function save() {
    if (props.clientLogo) {
        form.put(`/admin/client-logos/${props.clientLogo.id}`, { preserveScroll: true });
    } else {
        form.post('/admin/client-logos', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="clientLogo ? `Edit - ${clientLogo.name}` : 'New logo'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/client-logos" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Client logos</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ clientLogo ? clientLogo.name : 'New logo' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Name" :error="form.errors.name"><input v-model="form.name" :class="input" /></AdminField>
            <AdminField label="Type" :error="form.errors.type">
                <select v-model="form.type" :class="input">
                    <option value="client">Client</option>
                    <option value="tech_partner">Tech partner</option>
                </select>
            </AdminField>
            <AdminField label="Logo path" :error="form.errors.logo_path" hint="Path to an uploaded image"><input v-model="form.logo_path" :class="input" /></AdminField>
            <AdminField label="URL" :error="form.errors.url"><input v-model="form.url" :class="input" /></AdminField>
            <AdminField label="Logo SVG" :error="form.errors.logo_svg" class="sm:col-span-2" hint="Inline SVG markup"><textarea v-model="form.logo_svg" rows="4" class="w-full rounded-lg border border-border bg-background p-3 font-mono text-xs" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <label class="flex items-center gap-3 text-sm"><input v-model="form.is_active" type="checkbox" class="size-4 accent-primary" /> Active</label>
            <AdminField label="Sort order" :error="form.errors.sort_order"><input v-model.number="form.sort_order" type="number" :class="input" /></AdminField>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save logo' }}</button>
            <Link href="/admin/client-logos" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
