<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';
import StringList from '@/components/admin/StringList.vue';

const props = defineProps<{ jobPosting: Record<string, any> | null; employmentTypes: string[] }>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Job postings', href: '/admin/jobs' }, { title: 'Edit', href: '#' }] } });

function toLocal(value: string | null | undefined): string {
    if (!value) {
        return '';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const pad = (n: number) => String(n).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

const form = useForm({
    title: props.jobPosting?.title ?? '',
    slug: props.jobPosting?.slug ?? '',
    department: props.jobPosting?.department ?? '',
    location: props.jobPosting?.location ?? '',
    employment_type: props.jobPosting?.employment_type ?? '',
    seniority: props.jobPosting?.seniority ?? '',
    summary: props.jobPosting?.summary ?? '',
    description: props.jobPosting?.description ?? '',
    responsibilities: (props.jobPosting?.responsibilities ?? ['']) as string[],
    requirements: (props.jobPosting?.requirements ?? ['']) as string[],
    tech_stack: (props.jobPosting?.tech_stack ?? ['']) as string[],
    salary_range: props.jobPosting?.salary_range ?? '',
    is_open: props.jobPosting ? Boolean(props.jobPosting.is_open) : true,
    apply_url: props.jobPosting?.apply_url ?? '',
    posted_at: toLocal(props.jobPosting?.posted_at),
    sort_order: props.jobPosting?.sort_order ?? 0,
});

function save() {
    if (props.jobPosting) {
        form.put(`/admin/jobs/${props.jobPosting.slug}`, { preserveScroll: true });
    } else {
        form.post('/admin/jobs', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="jobPosting ? `Edit - ${jobPosting.title}` : 'New job posting'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/jobs" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Job postings</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ jobPosting ? jobPosting.title : 'New job posting' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Title" :error="form.errors.title"><input v-model="form.title" :class="input" /></AdminField>
            <AdminField label="Slug" :error="form.errors.slug" hint="Leave blank to auto-generate"><input v-model="form.slug" :class="input" /></AdminField>
            <AdminField label="Department" :error="form.errors.department"><input v-model="form.department" :class="input" /></AdminField>
            <AdminField label="Location" :error="form.errors.location"><input v-model="form.location" :class="input" /></AdminField>
            <AdminField label="Employment type" :error="form.errors.employment_type">
                <select v-model="form.employment_type" :class="input">
                    <option value="">-</option>
                    <option v-for="type in employmentTypes" :key="type" :value="type">{{ type }}</option>
                </select>
            </AdminField>
            <AdminField label="Seniority" :error="form.errors.seniority"><input v-model="form.seniority" :class="input" /></AdminField>
            <AdminField label="Summary" class="sm:col-span-2" :error="form.errors.summary"><textarea v-model="form.summary" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Responsibilities" :error="form.errors.responsibilities"><StringList v-model="form.responsibilities" placeholder="Lead the architecture…" /></AdminField>
            <AdminField label="Requirements" :error="form.errors.requirements"><StringList v-model="form.requirements" placeholder="5+ years experience…" /></AdminField>
            <AdminField label="Tech stack" :error="form.errors.tech_stack"><StringList v-model="form.tech_stack" placeholder="Laravel" /></AdminField>
            <AdminField label="Description (HTML)" class="sm:col-span-2" :error="form.errors.description"><textarea v-model="form.description" rows="6" class="w-full rounded-lg border border-border bg-background p-3 font-mono text-xs" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Salary range" :error="form.errors.salary_range"><input v-model="form.salary_range" :class="input" /></AdminField>
            <AdminField label="Apply URL" :error="form.errors.apply_url"><input v-model="form.apply_url" :class="input" /></AdminField>
            <AdminField label="Posted at" :error="form.errors.posted_at"><input v-model="form.posted_at" type="datetime-local" :class="input" /></AdminField>
            <AdminField label="Sort order" :error="form.errors.sort_order"><input v-model.number="form.sort_order" type="number" :class="input" /></AdminField>
            <label class="flex items-center gap-3 text-sm sm:col-span-2"><input v-model="form.is_open" type="checkbox" class="size-4 accent-primary" /> Open for applications</label>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save posting' }}</button>
            <Link href="/admin/jobs" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
