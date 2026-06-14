<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

defineOptions({ layout: { breadcrumbs: [{ title: 'Settings', href: '/admin/settings' }] } });

const props = defineProps<{ settings: Record<string, any> }>();

const form = useForm({
    site_name: props.settings.site_name ?? '',
    tagline: props.settings.tagline ?? '',
    primary_email: props.settings.primary_email ?? '',
    phone: props.settings.phone ?? '',
    whatsapp: props.settings.whatsapp ?? '',
    telegram: props.settings.telegram ?? '',
    address: props.settings.address ?? '',
    socials: {
        linkedin: props.settings.socials?.linkedin ?? '',
        github: props.settings.socials?.github ?? '',
        x: props.settings.socials?.x ?? '',
        youtube: props.settings.socials?.youtube ?? '',
        instagram: props.settings.socials?.instagram ?? '',
        facebook: props.settings.socials?.facebook ?? '',
    } as Record<string, string>,
    locationsText: (props.settings.locations ?? []).join(', '),
    nav_cta_label: props.settings.nav_cta_label ?? 'Book a call',
    nav_cta_url: props.settings.nav_cta_url ?? '/contact',
    footer_blurb: props.settings.footer_blurb ?? '',
    default_meta_title: props.settings.default_meta_title ?? '',
    default_meta_description: props.settings.default_meta_description ?? '',
    announcement_text: props.settings.announcement_text ?? '',
    announcement_active: Boolean(props.settings.announcement_active),
    og_image: props.settings.og_image ?? '',
    ga_measurement_id: props.settings.ga_measurement_id ?? '',
    head_scripts: props.settings.head_scripts ?? '',
    newsletter_heading: props.settings.newsletter_heading ?? '',
    newsletter_placeholder: props.settings.newsletter_placeholder ?? '',
    newsletter_success: props.settings.newsletter_success ?? '',
    cookie_banner_text: props.settings.cookie_banner_text ?? '',
    cookie_accept_label: props.settings.cookie_accept_label ?? '',
    cookie_decline_label: props.settings.cookie_decline_label ?? '',
});

function save() {
    form
        .transform(({ locationsText, ...data }) => ({
            ...data,
            locations: locationsText.split(',').map((part: string) => part.trim()).filter(Boolean),
        }))
        .put('/admin/settings', { preserveScroll: true });
}

const input = 'h-10 w-full rounded-lg border border-border bg-background px-3 text-sm';
</script>

<template>
    <Head title="Settings" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-6 p-4 sm:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Settings</h1>
            <p class="text-sm text-muted-foreground">Brand, contact details, SEO defaults and the site-wide announcement.</p>
        </div>

        <div class="space-y-6">
            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Brand</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Site name</span><input v-model="form.site_name" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Tagline</span><input v-model="form.tagline" :class="input" /></label>
                </div>
            </section>

            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Contact</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Email</span><input v-model="form.primary_email" type="email" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Phone</span><input v-model="form.phone" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">WhatsApp</span><input v-model="form.whatsapp" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Telegram</span><input v-model="form.telegram" :class="input" /></label>
                    <label class="block text-sm sm:col-span-2"><span class="mb-1.5 block text-muted-foreground">Address line</span><input v-model="form.address" :class="input" /></label>
                    <label class="block text-sm sm:col-span-2"><span class="mb-1.5 block text-muted-foreground">Locations (comma separated)</span><input v-model="form.locationsText" :class="input" /></label>
                </div>
            </section>

            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Social</h2>
                <p class="mt-1 text-sm text-muted-foreground">Leave a network empty to hide it everywhere on the site.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">LinkedIn</span><input v-model="form.socials.linkedin" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">GitHub</span><input v-model="form.socials.github" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">X</span><input v-model="form.socials.x" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">YouTube</span><input v-model="form.socials.youtube" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Instagram</span><input v-model="form.socials.instagram" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Facebook</span><input v-model="form.socials.facebook" :class="input" /></label>
                </div>
            </section>

            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Navigation &amp; footer</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Nav CTA label</span><input v-model="form.nav_cta_label" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Nav CTA URL</span><input v-model="form.nav_cta_url" :class="input" /></label>
                    <label class="block text-sm sm:col-span-2"><span class="mb-1.5 block text-muted-foreground">Footer blurb</span><textarea v-model="form.footer_blurb" rows="3" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Newsletter heading</span><input v-model="form.newsletter_heading" :class="input" placeholder="Engineering notes" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Newsletter email placeholder</span><input v-model="form.newsletter_placeholder" :class="input" placeholder="you@company.com" /></label>
                    <label class="block text-sm sm:col-span-2"><span class="mb-1.5 block text-muted-foreground">Newsletter success message</span><input v-model="form.newsletter_success" :class="input" /></label>
                </div>
            </section>

            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">SEO defaults</h2>
                <div class="mt-4 grid gap-4">
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Default meta title</span><input v-model="form.default_meta_title" :class="input" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Default meta description</span><textarea v-model="form.default_meta_description" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Social share image URL (og:image)</span><input v-model="form.og_image" :class="input" placeholder="/images/og.png or https://…" /></label>
                </div>
            </section>

            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Analytics &amp; scripts</h2>
                <p class="mt-1 text-sm text-muted-foreground">Google Analytics loads only after a visitor accepts cookies. Custom scripts are injected into every page's &lt;head&gt; - admin only, use with care.</p>
                <div class="mt-4 grid gap-4">
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Google Analytics measurement ID</span><input v-model="form.ga_measurement_id" :class="input" placeholder="G-XXXXXXXXXX" /><p v-if="form.errors.ga_measurement_id" class="mt-1 text-xs text-rose-400">{{ form.errors.ga_measurement_id }}</p></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Custom head scripts (HTML)</span><textarea v-model="form.head_scripts" rows="4" class="w-full rounded-lg border border-border bg-background p-3 font-mono text-xs" placeholder="&lt;script&gt;…&lt;/script&gt;" /></label>
                </div>
            </section>

            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Cookie banner</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm sm:col-span-2"><span class="mb-1.5 block text-muted-foreground">Banner text</span><textarea v-model="form.cookie_banner_text" rows="2" class="w-full rounded-lg border border-border bg-background p-3 text-sm" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Accept button label</span><input v-model="form.cookie_accept_label" :class="input" placeholder="Accept" /></label>
                    <label class="block text-sm"><span class="mb-1.5 block text-muted-foreground">Decline button label</span><input v-model="form.cookie_decline_label" :class="input" placeholder="Decline" /></label>
                </div>
            </section>

            <section class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-medium">Announcement bar</h2>
                <label class="mt-4 flex cursor-pointer items-center gap-3 text-sm">
                    <input v-model="form.announcement_active" type="checkbox" class="size-4 accent-primary" />
                    <span>Show the announcement bar at the top of the site</span>
                </label>
                <input v-model="form.announcement_text" :class="[input, 'mt-3']" placeholder="Announcement text" />
            </section>

            <div v-if="form.hasErrors" class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-400">
                Some fields could not be saved - please review: {{ Object.values(form.errors).join(' ') }}
            </div>
            <button class="h-11 rounded-lg bg-primary px-6 text-sm font-medium text-primary-foreground transition hover:opacity-90 disabled:opacity-60" :disabled="form.processing" @click="save">
                {{ form.processing ? 'Saving…' : 'Save settings' }}
            </button>
        </div>
    </div>
</template>
