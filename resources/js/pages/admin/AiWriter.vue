<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Bot, Check, Plus, Sparkles, Star, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'AI Writer', href: '/admin/ai-writer' }] } });

interface Provider {
    id: number;
    name: string;
    provider: string;
    model: string;
    is_active: boolean;
    has_own_key: boolean;
    usable: boolean;
}

const props = defineProps<{
    settings: { ai_blog_enabled: boolean; ai_blog_frequency: string; ai_blog_per_run: number; ai_blog_topics: string; ai_blog_autopublish: boolean };
    keyStatus: { openai: boolean; gemini: boolean };
    modelOptions: { openai: string[]; gemini: string[] };
    aiProviders: Provider[];
    recent: Array<{ id: number; title: string; slug: string; status: string; generated_by: string; created_at: string }>;
}>();

const form = useForm({ ...props.settings });
const generating = ref(false);

const newProvider = useForm({ name: '', provider: 'openai', model: 'gpt-5.4-mini', api_key: '' });
const modelChoices = computed(() => props.modelOptions[newProvider.provider as 'openai' | 'gemini'] ?? []);

function pickProviderDefaults() {
    newProvider.model = modelChoices.value[0] ?? '';

    if (!newProvider.name) {
newProvider.name = newProvider.provider === 'gemini' ? 'Gemini' : 'OpenAI';
}
}

function save() {
    form.put('/admin/ai-writer', { preserveScroll: true });
}
function generateNow() {
    generating.value = true;
    router.post('/admin/ai-writer/generate', {}, { preserveScroll: true, onFinish: () => (generating.value = false) });
}
function addProvider() {
    newProvider.post('/admin/ai-writer/providers', { preserveScroll: true, onSuccess: () => newProvider.reset('name', 'api_key') });
}
function activate(p: Provider) {
    router.put(`/admin/ai-writer/providers/${p.id}/activate`, {}, { preserveScroll: true });
}
function remove(p: Provider) {
    if (confirm(`Remove “${p.name}”?`)) {
router.delete(`/admin/ai-writer/providers/${p.id}`, { preserveScroll: true });
}
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head title="AI Writer" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-semibold tracking-tight"><Bot class="size-6" /> AI Writer</h1>
                <p class="text-sm text-muted-foreground">Auto-generate blog posts from live industry trends. Add the AIs you want to use, pick the active one, and control the schedule - all from here.</p>
            </div>
            <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="generating" @click="generateNow">
                <Sparkles class="size-4" /> {{ generating ? 'Generating…' : 'Generate now' }}
            </button>
        </div>

        <!-- AI providers manager -->
        <section class="rounded-xl border border-border bg-card p-5">
            <h2 class="font-medium">Your AIs</h2>
            <p class="mt-1 text-xs text-muted-foreground">Add OpenAI or Gemini configurations to use later. Keys are encrypted; leave the key blank to use the server's environment key. The starred AI is the one used for generation.</p>

            <div v-if="aiProviders.length" class="mt-4 divide-y divide-border">
                <div v-for="p in aiProviders" :key="p.id" class="flex items-center justify-between gap-3 py-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <button :title="p.is_active ? 'Active' : 'Set active'" @click="activate(p)">
                            <Star class="size-5" :class="p.is_active ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground hover:text-amber-400'" />
                        </button>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ p.name }} <span class="text-xs font-normal text-muted-foreground">· {{ p.provider }} · {{ p.model }}</span></p>
                            <p class="text-xs" :class="p.usable ? 'text-emerald-500' : 'text-rose-500'">
                                {{ p.usable ? (p.has_own_key ? 'Own key ✓' : 'Using env key ✓') : 'No key available' }}
                            </p>
                        </div>
                    </div>
                    <button class="rounded-lg border border-border p-2 text-muted-foreground transition hover:bg-muted" @click="remove(p)"><Trash2 class="size-4" /></button>
                </div>
            </div>
            <p v-else class="mt-3 text-sm text-muted-foreground">No AIs added yet - add one below.</p>

            <!-- add provider -->
            <div class="mt-5 grid gap-3 rounded-lg border border-dashed border-border p-4 sm:grid-cols-2">
                <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Name</span><input v-model="newProvider.name" :class="input" placeholder="e.g. OpenAI (work account)" /></label>
                <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Provider</span>
                    <select v-model="newProvider.provider" :class="input" @change="pickProviderDefaults">
                        <option value="openai">OpenAI</option>
                        <option value="gemini">Gemini</option>
                    </select>
                </label>
                <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Model</span>
                    <select v-model="newProvider.model" :class="input">
                        <option v-for="m in modelChoices" :key="m" :value="m">{{ m }}</option>
                    </select>
                </label>
                <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">API key (optional - blank uses env key)</span><input v-model="newProvider.api_key" type="password" autocomplete="off" :class="input" placeholder="sk-… / AIza…" /></label>
                <div class="sm:col-span-2">
                    <button class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="newProvider.processing" @click="addProvider"><Plus class="size-4" /> Add AI</button>
                    <span v-if="newProvider.errors.name || newProvider.errors.model" class="ml-3 text-xs text-rose-500">Name and model are required.</span>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3 text-xs text-muted-foreground">
                <span class="inline-flex items-center gap-1.5">OpenAI env key:
                    <component :is="keyStatus.openai ? Check : X" class="size-3.5" :class="keyStatus.openai ? 'text-emerald-500' : 'text-rose-500'" />
                </span>
                <span class="inline-flex items-center gap-1.5">Gemini env key:
                    <component :is="keyStatus.gemini ? Check : X" class="size-3.5" :class="keyStatus.gemini ? 'text-emerald-500' : 'text-rose-500'" />
                </span>
            </div>
        </section>

        <!-- Schedule / behaviour -->
        <section class="rounded-xl border border-border bg-card p-5">
            <label class="flex cursor-pointer items-center gap-3 text-sm">
                <input v-model="form.ai_blog_enabled" type="checkbox" class="size-4 accent-primary" />
                <span class="font-medium">Auto-generate posts on a schedule (admin can turn this off any time)</span>
            </label>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <label class="block text-sm">
                    <span class="mb-1.5 block text-muted-foreground">Frequency</span>
                    <select v-model="form.ai_blog_frequency" :class="input">
                        <option value="daily">Daily</option>
                        <option value="twice_weekly">Twice a week (Mon &amp; Thu)</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </label>
                <label class="block text-sm">
                    <span class="mb-1.5 block text-muted-foreground">Posts per run</span>
                    <input v-model.number="form.ai_blog_per_run" type="number" min="1" max="5" :class="input" />
                </label>
                <label class="flex items-center gap-3 pt-7 text-sm">
                    <input v-model="form.ai_blog_autopublish" type="checkbox" class="size-4 accent-primary" />
                    <span>Publish immediately (fully autonomous - skip draft review)</span>
                </label>
                <label class="block text-sm sm:col-span-2">
                    <span class="mb-1.5 block text-muted-foreground">Seed topics (optional, used when no live trends are found)</span>
                    <textarea v-model="form.ai_blog_topics" rows="3" class="w-full rounded-lg border border-border bg-background p-3 text-sm" placeholder="Platform engineering, AI agents in production, zero-downtime migrations" />
                </label>
            </div>

            <div v-if="form.hasErrors" class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-400">
                Some fields could not be saved - please review: {{ Object.values(form.errors).join(' ') }}
            </div>
            <button class="mt-5 h-10 rounded-lg bg-primary px-5 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">
                {{ form.processing ? 'Saving…' : 'Save settings' }}
            </button>
        </section>

        <!-- Recent drafts -->
        <section class="rounded-xl border border-border bg-card p-5">
            <h2 class="font-medium">Recently generated</h2>
            <div v-if="recent.length" class="mt-4 divide-y divide-border">
                <div v-for="post in recent" :key="post.id" class="flex items-center justify-between gap-3 py-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ post.title }}</p>
                        <p class="text-xs text-muted-foreground">{{ post.generated_by }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="post.status === 'published' ? 'bg-emerald-500/15 text-emerald-500' : 'bg-amber-500/15 text-amber-500'">{{ post.status }}</span>
                        <Link :href="`/admin/articles/${post.slug}/edit`" class="text-sm text-primary hover:underline">Edit</Link>
                    </div>
                </div>
            </div>
            <p v-else class="mt-3 text-sm text-muted-foreground">No AI posts yet. Hit “Generate now” to create your first one.</p>
        </section>
    </div>
</template>
