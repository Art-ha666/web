<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ExternalLink, Plus, RotateCcw, Trash2 } from 'lucide-vue-next';

interface SubField {
    key: string;
    type: string;
    label: string;
}
interface Field {
    key: string;
    type: 'text' | 'textarea' | 'list' | 'repeater' | string;
    label: string;
    subfields: SubField[];
}
interface Section {
    key: string;
    label: string;
    toggle: boolean;
    fields: Field[];
}

const props = defineProps<{
    slug: string;
    label: string;
    route: string;
    sections: Section[];
    content: Record<string, any>;
    visible: boolean;
}>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Page content', href: '/admin/content' }] } });

// Deep clone the server content so the form is fully editable & reactive.
const form = useForm<{ content: Record<string, any>; visible: boolean }>({
    content: JSON.parse(JSON.stringify(props.content ?? {})),
    visible: props.visible !== false,
});

function emptyRow(field: Field): Record<string, string | string[]> {
    return Object.fromEntries(field.subfields.map((s) => [s.key, s.type === 'list' ? [] : '']));
}

function addRow(sectionKey: string, field: Field) {
    if (!Array.isArray(form.content[sectionKey][field.key])) {
form.content[sectionKey][field.key] = [];
}

    form.content[sectionKey][field.key].push(emptyRow(field));
}
function removeRow(sectionKey: string, fieldKey: string, i: number) {
    form.content[sectionKey][fieldKey].splice(i, 1);
}
function addItem(sectionKey: string, fieldKey: string) {
    if (!Array.isArray(form.content[sectionKey][fieldKey])) {
form.content[sectionKey][fieldKey] = [];
}

    form.content[sectionKey][fieldKey].push('');
}
function removeItem(sectionKey: string, fieldKey: string, i: number) {
    form.content[sectionKey][fieldKey].splice(i, 1);
}
function addSubItem(row: Record<string, any>, sfKey: string) {
    if (!Array.isArray(row[sfKey])) {
row[sfKey] = [];
}

    row[sfKey].push('');
}
function removeSubItem(row: Record<string, any>, sfKey: string, i: number) {
    row[sfKey].splice(i, 1);
}

function save() {
    form.put(`/admin/content/${props.slug}`, { preserveScroll: true });
}
function resetToDefault() {
    if (confirm('Reset this page to its original wording? Your edits will be replaced.')) {
        router.put(`/admin/content/${props.slug}/reset`, {}, { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
const area = 'w-full rounded-lg border border-border bg-background p-3 text-sm';
</script>

<template>
    <Head :title="`${label} content`" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">{{ label }}</h1>
                <p class="text-sm text-muted-foreground">Edit every section and word on this page. Toggle sections off to hide them.</p>
            </div>
            <div class="flex items-center gap-2">
                <button class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm transition hover:bg-muted" @click="resetToDefault">
                    <RotateCcw class="size-4" /> Reset
                </button>
                <a :href="route" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm transition hover:bg-muted">
                    <ExternalLink class="size-4" /> View page
                </a>
            </div>
        </div>

        <div class="space-y-5">
            <!-- Page visibility -->
            <section class="flex flex-wrap items-center justify-between gap-3 rounded-xl border bg-card p-5" :class="form.visible ? 'border-border' : 'border-amber-500/40'">
                <div>
                    <h2 class="font-medium">Page visibility</h2>
                    <p class="text-sm text-muted-foreground">{{ form.visible ? 'This page is live and visible to visitors.' : 'This page is hidden - visitors get a 404 until you publish it.' }}</p>
                </div>
                <label class="inline-flex cursor-pointer items-center gap-2 text-sm">
                    <input v-model="form.visible" type="checkbox" class="size-4 accent-primary" />
                    <span class="font-medium">{{ form.visible ? 'Live' : 'Hidden (draft)' }}</span>
                </label>
            </section>

            <section v-for="section in sections" :key="section.key" class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-medium">{{ section.label }}</h2>
                    <label v-if="section.toggle" class="inline-flex cursor-pointer items-center gap-2 text-sm">
                        <input v-model="form.content[section.key].enabled" type="checkbox" class="size-4 accent-primary" />
                        <span class="text-muted-foreground">{{ form.content[section.key].enabled ? 'Shown' : 'Hidden' }}</span>
                    </label>
                </div>

                <div class="mt-4 space-y-4" :class="section.toggle && !form.content[section.key].enabled ? 'pointer-events-none opacity-50' : ''">
                    <div v-for="field in section.fields" :key="field.key">
                        <span class="mb-1.5 block text-sm text-muted-foreground">{{ field.label }}</span>

                        <!-- text -->
                        <input v-if="field.type === 'text'" v-model="form.content[section.key][field.key]" :class="input" />

                        <!-- textarea -->
                        <textarea v-else-if="field.type === 'textarea'" v-model="form.content[section.key][field.key]" rows="3" :class="area" />

                        <!-- list of strings -->
                        <div v-else-if="field.type === 'list'" class="space-y-2">
                            <div v-for="(_, i) in form.content[section.key][field.key]" :key="i" class="flex items-center gap-2">
                                <input v-model="form.content[section.key][field.key][i]" :class="input" />
                                <button class="shrink-0 rounded-lg border border-border p-2 text-muted-foreground transition hover:bg-muted" @click="removeItem(section.key, field.key, i)"><Trash2 class="size-4" /></button>
                            </div>
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-border px-3 py-1.5 text-sm text-muted-foreground transition hover:bg-muted" @click="addItem(section.key, field.key)"><Plus class="size-4" /> Add item</button>
                        </div>

                        <!-- repeater of objects -->
                        <div v-else-if="field.type === 'repeater'" class="space-y-3">
                            <div v-for="(row, i) in form.content[section.key][field.key]" :key="i" class="rounded-lg border border-border p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-muted-foreground">#{{ i + 1 }}</span>
                                    <button class="rounded-lg border border-border p-1.5 text-muted-foreground transition hover:bg-muted" @click="removeRow(section.key, field.key, i)"><Trash2 class="size-4" /></button>
                                </div>
                                <div class="mt-2 grid gap-2">
                                    <label v-for="sf in field.subfields" :key="sf.key" class="block text-sm">
                                        <span class="mb-1 block text-xs text-muted-foreground">{{ sf.label }}</span>
                                        <textarea v-if="sf.type === 'textarea'" v-model="row[sf.key]" rows="2" :class="area" />
                                        <div v-else-if="sf.type === 'list'" class="space-y-2">
                                            <div v-for="(_, j) in row[sf.key]" :key="j" class="flex items-center gap-2">
                                                <input v-model="row[sf.key][j]" :class="input" />
                                                <button class="shrink-0 rounded-lg border border-border p-2 text-muted-foreground transition hover:bg-muted" @click.prevent="removeSubItem(row, sf.key, j)"><Trash2 class="size-4" /></button>
                                            </div>
                                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-border px-3 py-1.5 text-sm text-muted-foreground transition hover:bg-muted" @click.prevent="addSubItem(row, sf.key)"><Plus class="size-4" /> Add item</button>
                                        </div>
                                        <input v-else v-model="row[sf.key]" :class="input" />
                                    </label>
                                </div>
                            </div>
                            <button class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-border px-3 py-1.5 text-sm text-muted-foreground transition hover:bg-muted" @click="addRow(section.key, field)"><Plus class="size-4" /> Add</button>
                        </div>
                    </div>
                </div>
            </section>

            <div v-if="form.hasErrors" class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-400">
                Some fields could not be saved - please review: {{ Object.values(form.errors).join(' ') }}
            </div>
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">
                {{ form.processing ? 'Saving…' : `Save ${label}` }}
            </button>
        </div>
    </div>
</template>
