<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Inbox, Palette } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Dashboard', href: '/admin' }] } });

defineProps<{
    stats: Record<string, number>;
    activeTheme: { key: string; name: string; tokens: Record<string, string> };
    recentLeads: Array<{ id: number; name: string; business_email: string; company?: string; service_interest?: string; status: string; created_at: string }>;
}>();

const cards = [
    { key: 'leads', label: 'Total leads', to: '/admin/leads' },
    { key: 'services', label: 'Services', to: '/admin/services' },
    { key: 'projects', label: 'Case studies', to: '/admin/projects' },
    { key: 'team', label: 'Team members', to: '/admin/team' },
    { key: 'articles', label: 'Articles', to: '/admin/articles' },
    { key: 'subscribers', label: 'Subscribers', to: '/admin/settings' },
];

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
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 sm:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Welcome back</h1>
            <p class="text-sm text-muted-foreground">Here's what's happening across the AKH Solutions site.</p>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3 xl:grid-cols-6">
            <Link
                v-for="card in cards"
                :key="card.key"
                :href="card.to"
                class="rounded-xl border border-border bg-card p-4 transition hover:border-primary/50 hover:shadow-sm"
            >
                <div class="text-2xl font-bold tabular-nums">{{ stats[card.key] ?? 0 }}</div>
                <div class="mt-1 text-xs text-muted-foreground">{{ card.label }}</div>
            </Link>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Active design -->
            <Link href="/admin/design" class="group rounded-xl border border-border bg-card p-5 transition hover:border-primary/50">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-sm font-medium"><Palette class="size-4" /> Active design</span>
                    <ArrowRight class="size-4 text-muted-foreground transition group-hover:translate-x-0.5" />
                </div>
                <div class="mt-4 text-xl font-semibold">{{ activeTheme.name }}</div>
                <div class="mt-3 flex gap-1.5">
                    <span
                        v-for="key in ['gradFrom', 'gradVia', 'gradTo', 'primary']"
                        :key="key"
                        class="size-6 rounded-full border border-border"
                        :style="{ background: activeTheme.tokens[key] }"
                    />
                </div>
                <p class="mt-3 text-xs text-muted-foreground">Switch designs and tune colours →</p>
            </Link>

            <!-- Recent leads -->
            <div class="rounded-xl border border-border bg-card p-5 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-sm font-medium"><Inbox class="size-4" /> Recent leads</span>
                    <Link href="/admin/leads" class="text-xs text-muted-foreground hover:text-foreground">View all</Link>
                </div>
                <div v-if="recentLeads.length" class="mt-4 divide-y divide-border">
                    <Link
                        v-for="lead in recentLeads"
                        :key="lead.id"
                        :href="`/admin/leads/${lead.id}`"
                        class="flex items-center justify-between gap-4 py-3 transition hover:opacity-80"
                    >
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium">{{ lead.name }}<span v-if="lead.company" class="text-muted-foreground"> · {{ lead.company }}</span></div>
                            <div class="truncate text-xs text-muted-foreground">{{ lead.business_email }}<span v-if="lead.service_interest"> · {{ lead.service_interest }}</span></div>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium capitalize" :class="statusColor(lead.status)">{{ lead.status }}</span>
                    </Link>
                </div>
                <p v-else class="mt-6 text-sm text-muted-foreground">No leads yet. They'll appear here when the contact form is used.</p>
            </div>
        </div>
    </div>
</template>
