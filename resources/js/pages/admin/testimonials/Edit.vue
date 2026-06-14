<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import AdminField from '@/components/admin/AdminField.vue';

const props = defineProps<{
    testimonial: Record<string, any> | null;
    projects: Array<{ id: number; title: string }>;
}>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Testimonials', href: '/admin/testimonials' }, { title: 'Edit', href: '#' }] } });

const form = useForm({
    quote: props.testimonial?.quote ?? '',
    author_name: props.testimonial?.author_name ?? '',
    author_role: props.testimonial?.author_role ?? '',
    company_name: props.testimonial?.company_name ?? '',
    company_logo: props.testimonial?.company_logo ?? '',
    avatar: props.testimonial?.avatar ?? '',
    project_id: props.testimonial?.project_id ?? null,
    rating: props.testimonial?.rating ?? null,
    featured: Boolean(props.testimonial?.featured),
    is_active: props.testimonial ? Boolean(props.testimonial.is_active) : true,
    sort_order: props.testimonial?.sort_order ?? 0,
});

function save() {
    if (props.testimonial) {
        form.put(`/admin/testimonials/${props.testimonial.id}`, { preserveScroll: true });
    } else {
        form.post('/admin/testimonials', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head :title="testimonial ? `Edit - ${testimonial.author_name}` : 'New testimonial'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <Link href="/admin/testimonials" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Testimonials</Link>
        <h1 class="text-2xl font-semibold tracking-tight">{{ testimonial ? testimonial.author_name : 'New testimonial' }}</h1>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5">
            <AdminField label="Quote" :error="form.errors.quote"><textarea v-model="form.quote" rows="4" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <AdminField label="Author name" :error="form.errors.author_name"><input v-model="form.author_name" :class="input" /></AdminField>
            <AdminField label="Author role" :error="form.errors.author_role"><input v-model="form.author_role" :class="input" /></AdminField>
            <AdminField label="Company name" :error="form.errors.company_name"><input v-model="form.company_name" :class="input" /></AdminField>
            <AdminField label="Company logo" :error="form.errors.company_logo" hint="Image URL or path"><input v-model="form.company_logo" :class="input" /></AdminField>
            <AdminField label="Avatar" :error="form.errors.avatar" hint="Image URL or path"><input v-model="form.avatar" :class="input" /></AdminField>
            <AdminField label="Project" :error="form.errors.project_id">
                <select v-model="form.project_id" :class="input">
                    <option :value="null">- None -</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.title }}</option>
                </select>
            </AdminField>
        </section>

        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-3">
            <AdminField label="Rating" :error="form.errors.rating">
                <select v-model.number="form.rating" :class="input">
                    <option :value="null">-</option>
                    <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
                </select>
            </AdminField>
            <AdminField label="Sort order" :error="form.errors.sort_order"><input v-model.number="form.sort_order" type="number" :class="input" /></AdminField>
            <div class="flex flex-col justify-end gap-3 pb-1">
                <label class="flex items-center gap-3 text-sm"><input v-model="form.featured" type="checkbox" class="size-4 accent-primary" /> Featured</label>
                <label class="flex items-center gap-3 text-sm"><input v-model="form.is_active" type="checkbox" class="size-4 accent-primary" /> Active</label>
            </div>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">{{ form.processing ? 'Saving…' : 'Save testimonial' }}</button>
            <Link href="/admin/testimonials" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
