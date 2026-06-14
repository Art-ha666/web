<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, RotateCcw, Sparkles } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineOptions({ layout: { breadcrumbs: [{ title: 'Design', href: '/admin/design' }] } });

interface Theme {
    id: number;
    key: string;
    name: string;
    description?: string;
    tokens: Record<string, string>;
    hero_variant: string;
    layout_variant: string;
    uses_three: boolean;
    is_premium: boolean;
}

const props = defineProps<{
    themes: Theme[];
    activeThemeId: number | null;
    customTokens: Record<string, string>;
    customisable: string[];
}>();

const activeTheme = computed(() => props.themes.find((t) => t.id === props.activeThemeId) ?? props.themes[0]);

const labels: Record<string, string> = {
    primary: 'Primary',
    gradFrom: 'Gradient start',
    gradVia: 'Gradient mid',
    gradTo: 'Gradient end',
    bg: 'Background',
    surface: 'Surface',
    text: 'Text',
};

function initialTokens(): Record<string, string> {
    const out: Record<string, string> = {};

    for (const key of props.customisable) {
        out[key] = props.customTokens[key] ?? activeTheme.value?.tokens[key] ?? '#5778f8';
    }

    return out;
}

const palette = useForm<{ tokens: Record<string, string> }>({ tokens: initialTokens() });
const activating = ref<number | null>(null);

function activate(theme: Theme) {
    activating.value = theme.id;
    router.put(`/admin/design/${theme.id}/activate`, {}, {
        preserveScroll: true,
        onFinish: () => (activating.value = null),
    });
}

function savePalette() {
    palette.put('/admin/design/customize', { preserveScroll: true });
}

function resetPalette() {
    router.put('/admin/design/reset', {}, {
        preserveScroll: true,
        onSuccess: () => palette.defaults(initialTokens()).reset(),
    });
}

const previewStyle = computed(() => ({
    '--p-from': palette.tokens.gradFrom,
    '--p-via': palette.tokens.gradVia,
    '--p-to': palette.tokens.gradTo,
    '--p-bg': palette.tokens.bg,
    '--p-surface': palette.tokens.surface,
    '--p-text': palette.tokens.text,
    '--p-primary': palette.tokens.primary,
}));
</script>

<template>
    <Head title="Design" />

    <div class="flex flex-1 flex-col gap-8 p-4 sm:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Design</h1>
            <p class="text-sm text-muted-foreground">Pick a complete design for your site, then fine-tune its colours. Changes go live instantly.</p>
        </div>

        <!-- Theme grid -->
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div
                v-for="theme in themes"
                :key="theme.id"
                class="overflow-hidden rounded-xl border bg-card transition"
                :class="theme.id === activeThemeId ? 'border-primary ring-1 ring-primary' : 'border-border hover:border-primary/40'"
            >
                <!-- preview swatch -->
                <div class="relative h-28" :style="{ background: theme.tokens.bg }">
                    <div class="absolute inset-0" :style="{ background: `radial-gradient(120% 90% at 80% -10%, ${theme.tokens.gradTo}55, transparent 60%), linear-gradient(120deg, ${theme.tokens.gradFrom}33, transparent 50%)` }" />
                    <div class="absolute bottom-3 left-4 right-4">
                        <div class="h-2.5 w-2/3 rounded-full" :style="{ background: `linear-gradient(90deg, ${theme.tokens.gradFrom}, ${theme.tokens.gradVia}, ${theme.tokens.gradTo})` }" />
                        <div class="mt-2 h-2 w-1/2 rounded-full" :style="{ background: theme.tokens.text, opacity: 0.4 }" />
                    </div>
                    <span v-if="theme.id === activeThemeId" class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-primary px-2 py-1 text-xs font-medium text-primary-foreground">
                        <Check class="size-3" /> Live
                    </span>
                </div>

                <div class="p-4">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold">{{ theme.name }}</h3>
                        <span v-if="theme.uses_three" class="inline-flex items-center gap-1 rounded-full bg-violet-500/15 px-2 py-0.5 text-[10px] font-medium text-violet-400"><Sparkles class="size-3" /> 3D</span>
                        <span v-if="theme.is_premium" class="rounded-full bg-amber-500/15 px-2 py-0.5 text-[10px] font-medium text-amber-400">Premium</span>
                    </div>
                    <p class="mt-1.5 line-clamp-2 text-xs text-muted-foreground">{{ theme.description }}</p>
                    <button
                        class="mt-4 inline-flex h-9 w-full items-center justify-center rounded-lg text-sm font-medium transition disabled:opacity-60"
                        :class="theme.id === activeThemeId ? 'cursor-default bg-muted text-muted-foreground' : 'bg-primary text-primary-foreground hover:opacity-90'"
                        :disabled="theme.id === activeThemeId || activating === theme.id"
                        @click="activate(theme)"
                    >
                        {{ theme.id === activeThemeId ? 'Currently live' : activating === theme.id ? 'Activating…' : 'Make it live' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Colour customiser -->
        <div class="rounded-xl border border-border bg-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Colours - {{ activeTheme?.name }}</h2>
                    <p class="text-sm text-muted-foreground">Override any colour on your live design. Leave the rest as the design default.</p>
                </div>
                <button class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm transition hover:bg-muted" @click="resetPalette">
                    <RotateCcw class="size-4" /> Reset to default
                </button>
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-[1.4fr_1fr]">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <label v-for="key in customisable" :key="key" class="block">
                        <span class="mb-1.5 block text-xs font-medium text-muted-foreground">{{ labels[key] ?? key }}</span>
                        <div class="flex items-center gap-2 rounded-lg border border-border bg-background p-1.5">
                            <input v-model="palette.tokens[key]" type="color" class="size-8 cursor-pointer rounded border-0 bg-transparent p-0" />
                            <input v-model="palette.tokens[key]" type="text" class="w-full bg-transparent text-xs outline-none" />
                        </div>
                    </label>
                </div>

                <!-- live preview -->
                <div class="overflow-hidden rounded-xl border border-border" :style="previewStyle">
                    <div class="p-5" :style="{ background: 'var(--p-bg)', color: 'var(--p-text)' }">
                        <div class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px]" :style="{ border: '1px solid var(--p-surface)' }">
                            <span class="size-1.5 rounded-full" :style="{ background: 'var(--p-from)' }" /> Preview
                        </div>
                        <div class="mt-3 text-lg font-bold leading-tight" style="font-family: 'Space Grotesk', sans-serif">
                            Always
                            <span :style="{ background: 'linear-gradient(120deg, var(--p-from), var(--p-via), var(--p-to))', '-webkit-background-clip': 'text', backgroundClip: 'text', color: 'transparent' }">ahead</span>
                        </div>
                        <div class="mt-3 inline-flex rounded-lg px-3 py-1.5 text-xs font-semibold text-white" :style="{ background: 'linear-gradient(120deg, var(--p-from), var(--p-via), var(--p-to))' }">Book a call</div>
                        <div class="mt-3 rounded-lg p-3 text-xs" :style="{ background: 'var(--p-surface)' }">A card surface on your design.</div>
                    </div>
                </div>
            </div>

            <button
                class="mt-6 inline-flex h-10 items-center justify-center rounded-lg bg-primary px-5 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60"
                :disabled="palette.processing"
                @click="savePalette"
            >
                {{ palette.processing ? 'Saving…' : 'Save colours' }}
            </button>
        </div>
    </div>
</template>
