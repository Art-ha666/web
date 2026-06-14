<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    page: Record<string, any> | null;
    parentOptions?: Array<{ id: number; title: string; path: string }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pages', href: '/admin/pages' },
            { title: 'Edit', href: '#' },
        ],
    },
});

const isHome = computed(() => props.page?.slug === 'home');
const isSystem = computed(() => Boolean(props.page?.is_system));

const defaultHomeBlocks = {
    hero: { badge: '', eyebrow: '', headline_line1: '', headline_line2: '', accent_word: '', subhead: '', primary_label: '', primary_url: '', secondary_label: '', secondary_url: '' },
    sections: {} as Record<string, { eyebrow?: string; title?: string; subtitle?: string }>,
};

function initialBlocks(): any {
    if (isHome.value) {
        return { ...defaultHomeBlocks, ...(props.page?.blocks ?? {}), hero: { ...defaultHomeBlocks.hero, ...(props.page?.blocks?.hero ?? {}) } };
    }

    return Array.isArray(props.page?.blocks) ? props.page.blocks : [];
}

const form = useForm({
    title: props.page?.title ?? '',
    slug: props.page?.slug ?? '',
    parent_id: (props.page?.parent_id ?? null) as number | null,
    status: props.page?.status ?? 'draft',
    show_in_nav: Boolean(props.page?.show_in_nav),
    seo_title: props.page?.seo_title ?? '',
    seo_description: props.page?.seo_description ?? '',
    blocks: initialBlocks(),
});

function addBlock() {
    form.blocks.push({ type: 'richtext', data: { html: '' } });
}
function removeBlock(i: number) {
    form.blocks.splice(i, 1);
}

function save() {
    if (props.page) {
        form.put(`/admin/pages/${props.page.slug}`, { preserveScroll: true });
    } else {
        form.post('/admin/pages', { preserveScroll: true });
    }
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
const sectionKeys = ['services', 'stats', 'process', 'work', 'team', 'testimonials'];
</script>

<template>
    <Head :title="page ? `Edit - ${page.title}` : 'New page'" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <Link href="/admin/pages" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ArrowLeft class="size-4" /> Pages</Link>
            <a v-if="page && page.status === 'published'" :href="page.public_path ?? (isHome ? '/' : `/page/${page.slug}`)" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"><ExternalLink class="size-4" /> View</a>
        </div>

        <h1 class="text-2xl font-semibold tracking-tight">{{ page ? page.title : 'New page' }}</h1>

        <!-- Core fields -->
        <section class="grid gap-4 rounded-xl border border-border bg-card p-5 sm:grid-cols-2">
            <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Title</span><input v-model="form.title" :class="input" /><p v-if="form.errors.title" class="mt-1 text-xs text-rose-400">{{ form.errors.title }}</p></label>
            <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Slug</span><input v-model="form.slug" :class="input" :disabled="isSystem" placeholder="auto from title" /><p v-if="form.errors.slug" class="mt-1 text-xs text-rose-400">{{ form.errors.slug }}</p></label>
            <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Status</span>
                <select v-model="form.status" :class="input"><option value="draft">Draft</option><option value="published">Published</option></select>
            </label>
            <label v-if="!isSystem" class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Parent page</span>
                <select v-model="form.parent_id" :class="input">
                    <option :value="null">- None (top level)</option>
                    <option v-for="option in (parentOptions ?? []).filter((o) => o.id !== page?.id)" :key="option.id" :value="option.id">{{ option.title }} ({{ option.path }})</option>
                </select>
                <p v-if="form.errors.parent_id" class="mt-1 text-xs text-rose-400">{{ form.errors.parent_id }}</p>
            </label>
            <label class="flex items-center gap-3 self-end pb-2 text-sm"><input v-model="form.show_in_nav" type="checkbox" class="size-4 accent-primary" /> Show in navigation</label>
        </section>

        <!-- Home hero + section editor -->
        <template v-if="isHome">
            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Hero</h2>
                <p class="text-sm text-muted-foreground">The headline, badge and call-to-action at the top of your homepage.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm sm:col-span-2"><span class="mb-1.5 block text-muted-foreground">Badge</span><input v-model="form.blocks.hero.badge" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Headline line 1</span><input v-model="form.blocks.hero.headline_line1" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Headline line 2</span><input v-model="form.blocks.hero.headline_line2" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Accent word (gets the gradient)</span><input v-model="form.blocks.hero.accent_word" :class="input" /></label>
                    <label class="block text-sm sm:col-span-2"><span class="mb-1.5 block text-muted-foreground">Subhead</span><textarea v-model="form.blocks.hero.subhead" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Primary button label</span><input v-model="form.blocks.hero.primary_label" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Primary button URL</span><input v-model="form.blocks.hero.primary_url" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Secondary button label</span><input v-model="form.blocks.hero.secondary_label" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Secondary button URL</span><input v-model="form.blocks.hero.secondary_url" :class="input" /></label>
                </div>
            </section>

            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Section headings</h2>
                <p class="text-sm text-muted-foreground">The eyebrow and title above each homepage section.</p>
                <div class="mt-4 space-y-4">
                    <div v-for="key in sectionKeys" :key="key" class="rounded-lg border border-border p-3">
                        <div class="mb-2 text-xs font-medium uppercase text-muted-foreground">{{ key }}</div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <input v-if="form.blocks.sections[key]" v-model="form.blocks.sections[key].eyebrow" :class="input" placeholder="Eyebrow" />
                            <input v-if="form.blocks.sections[key]" v-model="form.blocks.sections[key].title" :class="input" placeholder="Title" />
                        </div>
                    </div>
                </div>
            </section>
        </template>

        <!-- Generic content blocks editor -->
        <section v-else class="rounded-xl border border-border bg-card p-5">
            <div class="flex items-center justify-between">
                <div><h2 class="font-medium">Content</h2><p class="text-sm text-muted-foreground">Rich-text blocks rendered in order.</p></div>
                <button class="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-sm transition hover:bg-muted" @click="addBlock"><Plus class="size-4" /> Add block</button>
            </div>
            <div class="mt-4 space-y-3">
                <div v-for="(block, i) in form.blocks" :key="i" class="rounded-lg border border-border p-3">
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs font-medium uppercase text-muted-foreground">Rich text</span>
                        <button class="rounded p-1 text-rose-400 transition hover:bg-rose-500/10" @click="removeBlock(i)"><Trash2 class="size-3.5" /></button>
                    </div>
                    <textarea v-model="block.data.html" rows="5" class="w-full rounded-lg border border-border bg-background p-3 font-mono text-xs" placeholder="<p>Your content… HTML allowed.</p>" />
                </div>
                <p v-if="!form.blocks.length" class="py-4 text-center text-sm text-muted-foreground">No content yet. Add a block to start.</p>
            </div>
        </section>

        <!-- SEO -->
        <section class="rounded-xl border border-border bg-card p-5">
            <h2 class="font-medium">SEO</h2>
            <div class="mt-4 grid gap-4">
                <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Meta title</span><input v-model="form.seo_title" :class="input" /></label>
                <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Meta description</span><textarea v-model="form.seo_description" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></label>
            </div>
        </section>

        <div class="flex gap-3">
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">
                {{ form.processing ? 'Saving…' : 'Save page' }}
            </button>
            <Link href="/admin/pages" class="inline-flex h-11 items-center rounded-lg border border-border px-6 text-sm transition hover:bg-muted">Cancel</Link>
        </div>
    </div>
</template>
