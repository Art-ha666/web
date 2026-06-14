<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';

const props = defineProps<{ teamMember: Record<string, any> | null }>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Team', href: '/admin/team' }, { title: 'Edit', href: '#' }] } });

const form = useForm({
    name: props.teamMember?.name ?? '',
    slug: props.teamMember?.slug ?? '',
    role: props.teamMember?.role ?? '',
    specialty: props.teamMember?.specialty ?? '',
    bio: props.teamMember?.bio ?? '',
    photo: props.teamMember?.photo ?? '',
    years_experience: props.teamMember?.years_experience ?? 0,
    linkedin: props.teamMember?.linkedin ?? '',
    github: props.teamMember?.github ?? '',
    twitter: props.teamMember?.twitter ?? '',
    featured: Boolean(props.teamMember?.featured),
    is_active: props.teamMember ? Boolean(props.teamMember.is_active) : true,
    sort_order: props.teamMember?.sort_order ?? 0,
});

function save() {
    if (props.teamMember) {
        form.put(`/admin/team/${props.teamMember.id}`, { preserveScroll: true });
    } else {
        form.post('/admin/team', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="teamMember ? `Edit - ${teamMember.name}` : 'New member'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/team" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Team</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ teamMember ? teamMember.name : 'New member' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Name" :error="form.errors.name"><input v-model="form.name" :class="input" /></AdminField>
            <AdminField label="Slug" :error="form.errors.slug" hint="Leave blank to auto-generate"><input v-model="form.slug" :class="input" /></AdminField>
            <AdminField label="Role" :error="form.errors.role"><input v-model="form.role" :class="input" /></AdminField>
            <AdminField label="Specialty" :error="form.errors.specialty"><input v-model="form.specialty" :class="input" /></AdminField>
            <AdminField label="Photo" :error="form.errors.photo" hint="Image path or URL" class="sm:col-span-2"><input v-model="form.photo" :class="input" /></AdminField>
            <AdminField label="Bio" :error="form.errors.bio" class="sm:col-span-2"><textarea v-model="form.bio" rows="4" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Years experience" :error="form.errors.years_experience"><input v-model.number="form.years_experience" type="number" :class="input" /></AdminField>
            <AdminField label="LinkedIn" :error="form.errors.linkedin"><input v-model="form.linkedin" :class="input" /></AdminField>
            <AdminField label="GitHub" :error="form.errors.github"><input v-model="form.github" :class="input" /></AdminField>
            <AdminField label="Twitter" :error="form.errors.twitter"><input v-model="form.twitter" :class="input" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-3">
            <label class="flex items-center gap-3 text-sm"><input v-model="form.featured" type="checkbox" class="size-4 accent-primary" /> Featured</label>
            <label class="flex items-center gap-3 text-sm"><input v-model="form.is_active" type="checkbox" class="size-4 accent-primary" /> Active</label>
            <AdminField label="Sort order" :error="form.errors.sort_order"><input v-model.number="form.sort_order" type="number" :class="input" /></AdminField>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save member' }}</button>
            <Link href="/admin/team" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
