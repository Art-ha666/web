<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';

defineOptions({ layout: { breadcrumbs: [{ title: 'Homepage', href: '/admin/home' }] } });

interface SectionMeta {
    label: string;
    fields: string[];
}

const props = defineProps<{
    hero: Record<string, any>;
    partners: { enabled?: boolean; eyebrow?: string };
    sections: Record<string, any>;
    sectionMeta: Record<string, SectionMeta>;
}>();

const trust: string[] = Array.isArray(props.hero?.trust_items) ? props.hero.trust_items : [];

const sectionsInit: Record<
    string,
    { enabled: boolean; eyebrow: string; title: string; subtitle: string; view_all_label: string; explore_label: string }
> = {};

for (const key of Object.keys(props.sectionMeta)) {
    const s = props.sections?.[key] ?? {};
    sectionsInit[key] = {
        enabled: s.enabled !== false,
        eyebrow: s.eyebrow ?? '',
        title: s.title ?? '',
        subtitle: s.subtitle ?? '',
        view_all_label: s.view_all_label ?? '',
        explore_label: s.explore_label ?? '',
    };
}

const form = useForm({
    hero: {
        badge: props.hero?.badge ?? '',
        eyebrow: props.hero?.eyebrow ?? '',
        headline_line1: props.hero?.headline_line1 ?? '',
        headline_line2: props.hero?.headline_line2 ?? '',
        accent_word: props.hero?.accent_word ?? '',
        subhead: props.hero?.subhead ?? '',
        primary_label: props.hero?.primary_label ?? '',
        primary_url: props.hero?.primary_url ?? '',
        secondary_label: props.hero?.secondary_label ?? '',
        secondary_url: props.hero?.secondary_url ?? '',
        trust_items: [trust[0] ?? '', trust[1] ?? '', trust[2] ?? ''],
    },
    partners: {
        enabled: props.partners?.enabled !== false,
        eyebrow: props.partners?.eyebrow ?? '',
    },
    sections: sectionsInit,
});

function save() {
    form.put('/admin/home', { preserveScroll: true });
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
const area = 'w-full rounded-lg border border-border bg-background p-3 text-sm';
</script>

<template>
    <Head title="Homepage" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Homepage</h1>
                <p class="text-sm text-muted-foreground">Edit every word on the homepage and turn any section on or off. Changes go live on save.</p>
            </div>
            <a href="/" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm transition hover:bg-muted">
                <ExternalLink class="size-4" /> View live homepage
            </a>
        </div>

        <div class="space-y-6">
            <!-- Hero -->
            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Hero</h2>
                <p class="mt-1 text-xs text-muted-foreground">The headline shows line 1 then line 2. The accent word (must appear in line 1) is rendered in the gradient.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm sm:col-span-2"><span class="mb-1.5 block text-muted-foreground">Badge</span><input v-model="form.hero.badge" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Eyebrow</span><input v-model="form.hero.eyebrow" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Accent word</span><input v-model="form.hero.accent_word" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Headline line 1</span><input v-model="form.hero.headline_line1" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Headline line 2</span><input v-model="form.hero.headline_line2" :class="input" /></label>
                    <label class="block text-sm sm:col-span-2"><span class="mb-1.5 block text-muted-foreground">Subheading</span><textarea v-model="form.hero.subhead" rows="3" :class="area" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Primary button label</span><input v-model="form.hero.primary_label" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Primary button URL</span><input v-model="form.hero.primary_url" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Secondary button label</span><input v-model="form.hero.secondary_label" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Secondary button URL</span><input v-model="form.hero.secondary_url" :class="input" /></label>
                </div>
                <div class="mt-4">
                    <span class="mb-1.5 block text-sm text-muted-foreground">Trust badges (up to 3, leave blank to hide)</span>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <input v-for="(_, i) in form.hero.trust_items" :key="i" v-model="form.hero.trust_items[i]" :class="input" :placeholder="`Badge ${i + 1}`" />
                    </div>
                </div>
            </section>

            <!-- Partners -->
            <section class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-medium">Partners strip</h2>
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm">
                        <input v-model="form.partners.enabled" type="checkbox" class="size-4 accent-primary" />
                        <span class="text-muted-foreground">{{ form.partners.enabled ? 'Shown' : 'Hidden' }}</span>
                    </label>
                </div>
                <label class="mt-4 block text-sm"><span class="mb-1.5 block text-muted-foreground">Heading</span><input v-model="form.partners.eyebrow" :class="input" /></label>
                <p class="mt-2 text-xs text-muted-foreground">The logos themselves live under <span class="font-medium">Sections → Logos</span>.</p>
            </section>

            <!-- Section toggles + wording -->
            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Sections</h2>
                <p class="mt-1 text-xs text-muted-foreground">Toggle any section off to remove it from the homepage, or reword its heading.</p>
                <div class="mt-4 space-y-4">
                    <div v-for="(meta, key) in sectionMeta" :key="key" class="rounded-lg border border-border p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium">{{ meta.label }}</span>
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm">
                                <input v-model="form.sections[key].enabled" type="checkbox" class="size-4 accent-primary" />
                                <span class="text-muted-foreground">{{ form.sections[key].enabled ? 'Shown' : 'Hidden' }}</span>
                            </label>
                        </div>
                        <div v-if="meta.fields.length" class="mt-3 grid gap-3" :class="form.sections[key].enabled ? '' : 'pointer-events-none opacity-50'">
                            <label v-if="meta.fields.includes('eyebrow')" class="block text-sm"><span class="mb-1 block text-xs text-muted-foreground">Eyebrow</span><input v-model="form.sections[key].eyebrow" :class="input" /></label>
                            <label v-if="meta.fields.includes('title')" class="block text-sm"><span class="mb-1 block text-xs text-muted-foreground">Title</span><input v-model="form.sections[key].title" :class="input" /></label>
                            <label v-if="meta.fields.includes('subtitle')" class="block text-sm"><span class="mb-1 block text-xs text-muted-foreground">Subtitle</span><input v-model="form.sections[key].subtitle" :class="input" /></label>
                            <label v-if="meta.fields.includes('view_all_label')" class="block text-sm"><span class="mb-1 block text-xs text-muted-foreground">View-all button label (blank for default)</span><input v-model="form.sections[key].view_all_label" :class="input" /></label>
                            <label v-if="meta.fields.includes('explore_label')" class="block text-sm"><span class="mb-1 block text-xs text-muted-foreground">Explore link label (blank for default)</span><input v-model="form.sections[key].explore_label" :class="input" /></label>
                        </div>
                        <p v-else class="mt-2 text-xs text-muted-foreground">Content for this section is managed under <span class="font-medium">Sections → CTAs</span>.</p>
                    </div>
                </div>
            </section>

            <div v-if="form.hasErrors" class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-400">
                Some fields could not be saved - please review: {{ Object.values(form.errors).join(' ') }}
            </div>
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">
                {{ form.processing ? 'Saving…' : 'Save homepage' }}
            </button>
        </div>
    </div>
</template>
