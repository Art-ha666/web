<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';

const props = defineProps<{ stat: Record<string, any> | null }>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Stats', href: '/admin/stats' }, { title: 'Edit', href: '#' }] } });

const form = useForm({
    value: props.stat?.value ?? '',
    prefix: props.stat?.prefix ?? '',
    suffix: props.stat?.suffix ?? '',
    label: props.stat?.label ?? '',
    group: props.stat?.group ?? 'band',
    accent_color: props.stat?.accent_color ?? '',
    is_active: props.stat ? Boolean(props.stat.is_active) : true,
    sort_order: props.stat?.sort_order ?? 0,
});

function save() {
    if (props.stat) {
        form.put(`/admin/stats/${props.stat.id}`, { preserveScroll: true });
    } else {
        form.post('/admin/stats', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="stat ? `Edit - ${stat.label}` : 'New stat'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/stats" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Stats</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ stat ? stat.label : 'New stat' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Value" :error="form.errors.value"><input v-model="form.value" :class="input" /></AdminField>
            <AdminField label="Label" :error="form.errors.label"><input v-model="form.label" :class="input" /></AdminField>
            <AdminField label="Prefix" :error="form.errors.prefix" hint="e.g. $"><input v-model="form.prefix" :class="input" /></AdminField>
            <AdminField label="Suffix" :error="form.errors.suffix" hint="e.g. % or +"><input v-model="form.suffix" :class="input" /></AdminField>
            <AdminField label="Group" :error="form.errors.group">
                <select v-model="form.group" :class="input">
                    <option value="band">band</option>
                    <option value="hero">hero</option>
                </select>
            </AdminField>
            <AdminField label="Accent color" :error="form.errors.accent_color" hint="hex or token">
                <div class="flex items-center gap-2 rounded-lg border border-border bg-background p-1.5">
                    <input v-model="form.accent_color" type="color" class="size-8 cursor-pointer rounded border-0 bg-transparent p-0" />
                    <input v-model="form.accent_color" type="text" class="w-full bg-transparent text-xs outline-none" />
                </div>
            </AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-3">
            <label class="flex items-center gap-3 text-sm"><input v-model="form.is_active" type="checkbox" class="size-4 accent-primary" /> Active</label>
            <AdminField label="Sort order" :error="form.errors.sort_order"><input v-model.number="form.sort_order" type="number" :class="input" /></AdminField>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save stat' }}</button>
            <Link href="/admin/stats" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
