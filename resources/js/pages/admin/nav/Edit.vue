<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';

const props = defineProps<{
    navItem: Record<string, any> | null;
    parentOptions?: Array<{ id: number; label: string }>;
}>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Navigation', href: '/admin/nav-items' }, { title: 'Edit', href: '#' }] } });

const form = useForm({
    label: props.navItem?.label ?? '',
    url: props.navItem?.url ?? '',
    location: props.navItem?.location ?? 'header',
    mega_group: props.navItem?.mega_group ?? '',
    description: props.navItem?.description ?? '',
    icon: props.navItem?.icon ?? '',
    parent_id: props.navItem?.parent_id ?? null,
    is_cta: Boolean(props.navItem?.is_cta),
    is_active: props.navItem ? Boolean(props.navItem.is_active) : true,
    sort_order: props.navItem?.sort_order ?? 0,
});

function save() {
    if (props.navItem) {
        form.put(`/admin/nav-items/${props.navItem.id}`, { preserveScroll: true });
    } else {
        form.post('/admin/nav-items', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="navItem ? `Edit - ${navItem.label}` : 'New nav item'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/nav-items" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Navigation</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ navItem ? navItem.label : 'New nav item' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Label" :error="form.errors.label"><input v-model="form.label" :class="input" /></AdminField>
            <AdminField label="URL" :error="form.errors.url" hint="e.g. /services or https://…"><input v-model="form.url" :class="input" /></AdminField>
            <AdminField label="Location" :error="form.errors.location">
                <select v-model="form.location" :class="input">
                    <option value="header">header</option>
                    <option value="footer">footer</option>
                </select>
            </AdminField>
            <AdminField label="Parent" :error="form.errors.parent_id" hint="Optional grouping under another item">
                <select v-model="form.parent_id" :class="input">
                    <option :value="null">- None -</option>
                    <option v-for="opt in parentOptions ?? []" :key="opt.id" :value="opt.id">{{ opt.label }}</option>
                </select>
            </AdminField>
            <AdminField label="Mega group" :error="form.errors.mega_group"><input v-model="form.mega_group" :class="input" /></AdminField>
            <AdminField label="Icon" :error="form.errors.icon" hint="lucide name e.g. code-2, server"><input v-model="form.icon" :class="input" /></AdminField>
            <AdminField label="Description" :error="form.errors.description" class="sm:col-span-2"><textarea v-model="form.description" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-3">
            <label class="flex items-center gap-3 text-sm"><input v-model="form.is_cta" type="checkbox" class="size-4 accent-primary" /> CTA</label>
            <label class="flex items-center gap-3 text-sm"><input v-model="form.is_active" type="checkbox" class="size-4 accent-primary" /> Active</label>
            <AdminField label="Sort order" :error="form.errors.sort_order"><input v-model.number="form.sort_order" type="number" :class="input" /></AdminField>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save nav item' }}</button>
            <Link href="/admin/nav-items" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
