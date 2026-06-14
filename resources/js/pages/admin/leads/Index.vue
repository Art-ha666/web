<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({ layout: { breadcrumbs: [{ title: 'Leads', href: '/admin/leads' }] } });

defineProps<{
    leads: Array<{ id: number; name: string; business_email: string; company?: string; budget_range?: string; service_interest?: string; status: string; created_at: string }>;
    statuses: string[];
    filter: string;
    counts: { all: number; new: number };
}>();

function setFilter(status: string) {
    router.get('/admin/leads', status ? { status } : {}, { preserveScroll: true, preserveState: true });
}

function statusColor(status: string): string {
    return (
        {
            new: 'bg-blue-500/15 text-blue-400',
            contacted: 'bg-amber-500/15 text-amber-400',
            qualified: 'bg-violet-500/15 text-violet-400',
            won: 'bg-emerald-500/15 text-emerald-400',
            lost: 'bg-rose-500/15 text-rose-400',
        }[status] ?? 'bg-muted text-muted-foreground'
    );
}

function fmt(date: string): string {
    return new Date(date).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}
</script>

<template>
    <Head title="Leads" />

    <div class="flex flex-1 flex-col gap-5 p-4 sm:p-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Leads</h1>
                <p class="text-sm text-muted-foreground">Discovery-call requests from the contact form.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    class="rounded-full border px-3 py-1.5 text-sm transition"
                    :class="!filter ? 'border-primary bg-primary/10 text-foreground' : 'border-border text-muted-foreground hover:text-foreground'"
                    @click="setFilter('')"
                >
                    All ({{ counts.all }})
                </button>
                <button
                    v-for="status in statuses"
                    :key="status"
                    class="rounded-full border px-3 py-1.5 text-sm capitalize transition"
                    :class="filter === status ? 'border-primary bg-primary/10 text-foreground' : 'border-border text-muted-foreground hover:text-foreground'"
                    @click="setFilter(status)"
                >
                    {{ status }}
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <table v-if="leads.length" class="w-full text-sm">
                <thead class="border-b border-border text-left text-xs uppercase text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="hidden px-4 py-3 font-medium md:table-cell">Email</th>
                        <th class="hidden px-4 py-3 font-medium lg:table-cell">Interested in</th>
                        <th class="hidden px-4 py-3 font-medium lg:table-cell">Budget</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="hidden px-4 py-3 font-medium sm:table-cell">Received</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr
                        v-for="lead in leads"
                        :key="lead.id"
                        class="cursor-pointer transition hover:bg-muted/50"
                        @click="router.visit(`/admin/leads/${lead.id}`)"
                    >
                        <td class="px-4 py-3">
                            <Link :href="`/admin/leads/${lead.id}`" class="font-medium hover:underline">{{ lead.name }}</Link>
                            <div v-if="lead.company" class="text-xs text-muted-foreground">{{ lead.company }}</div>
                        </td>
                        <td class="hidden px-4 py-3 text-muted-foreground md:table-cell">{{ lead.business_email }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground lg:table-cell">{{ lead.service_interest || '-' }}</td>
                        <td class="hidden px-4 py-3 text-muted-foreground lg:table-cell">{{ lead.budget_range || '-' }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-medium capitalize" :class="statusColor(lead.status)">{{ lead.status }}</span></td>
                        <td class="hidden px-4 py-3 text-muted-foreground sm:table-cell">{{ fmt(lead.created_at) }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="p-10 text-center text-sm text-muted-foreground">No leads {{ filter ? `with status “${filter}”` : 'yet' }}.</p>
        </div>
    </div>
</template>
