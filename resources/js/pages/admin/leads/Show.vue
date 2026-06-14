<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Mail, Trash2 } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Leads', href: '/admin/leads' },
            { title: 'Lead', href: '#' },
        ],
    },
});

const props = defineProps<{
    lead: Record<string, any>;
    statuses: string[];
}>();

const form = useForm({
    status: props.lead.status,
    admin_notes: props.lead.admin_notes ?? '',
});

function save() {
    form.put(`/admin/leads/${props.lead.id}`, { preserveScroll: true });
}

function remove() {
    if (confirm('Delete this lead permanently?')) {
        router.delete(`/admin/leads/${props.lead.id}`);
    }
}

const fields = [
    ['Email', props.lead.business_email],
    ['Company', props.lead.company],
    ['Phone', props.lead.phone],
    ['Budget', props.lead.budget_range],
    ['Interested in', props.lead.service_interest],
    ['Source', props.lead.source_page],
];
</script>

<template>
    <Head :title="`Lead - ${lead.name}`" />

    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-5 p-4 sm:p-6">
        <Link href="/admin/leads" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Back to leads</Link>

        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">{{ lead.name }}</h1>
                <p class="text-sm text-muted-foreground">Received {{ new Date(lead.created_at).toLocaleString() }}</p>
            </div>
            <div class="flex gap-2">
                <a :href="`mailto:${lead.business_email}`" class="inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"><Mail class="size-4" /> Reply</a>
                <button class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm text-rose-400 transition hover:bg-rose-500/10" @click="remove"><Trash2 class="size-4" /> Delete</button>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-[1.6fr_1fr]">
            <div class="space-y-4">
                <div class="rounded-xl border border-border bg-card p-5">
                    <h2 class="text-sm font-medium text-muted-foreground">Message</h2>
                    <p class="mt-2 whitespace-pre-line">{{ lead.message || '-' }}</p>
                </div>
                <div class="grid gap-3 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
                    <div v-for="[label, value] in fields" :key="label">
                        <div class="text-xs uppercase text-muted-foreground">{{ label }}</div>
                        <div class="mt-0.5 text-sm">{{ value || '-' }}</div>
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card p-5 text-xs text-muted-foreground">
                    Consent - marketing: {{ lead.consent_marketing ? 'yes' : 'no' }} · data processing: {{ lead.consent_data_processing ? 'yes' : 'no' }}<br />
                    IP: {{ lead.ip_address || '-' }}
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Manage</h2>
                <label class="mt-4 block text-sm">
                    <span class="mb-1.5 block text-muted-foreground">Status</span>
                    <select v-model="form.status" class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm capitalize">
                        <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                    </select>
                </label>
                <label class="mt-4 block text-sm">
                    <span class="mb-1.5 block text-muted-foreground">Internal notes</span>
                    <textarea v-model="form.admin_notes" rows="5" class="w-full rounded-lg border border-border bg-background p-3 text-sm" placeholder="Notes only your team sees…" />
                </label>
                <button class="mt-4 h-10 w-full rounded-lg bg-primary text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">
                    {{ form.processing ? 'Saving…' : 'Save' }}
                </button>
            </div>
        </div>
    </div>
</template>
