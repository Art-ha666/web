<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useSite } from '@/composables/useSite';
import Icon from './Icon.vue';

const { site, nav } = useSite();

const socialIcons: Record<string, string> = {
    linkedin: 'linkedin',
    github: 'github',
    x: 'twitter',
    twitter: 'twitter',
};

const brandParts = computed<{ first: string; rest: string }>(() => {
    const name = site.value.name ?? 'AKH Solutions';
    const spaceIndex = name.indexOf(' ');

    return spaceIndex === -1 ? { first: name, rest: '' } : { first: name.slice(0, spaceIndex), rest: name.slice(spaceIndex + 1) };
});
</script>

<template>
    <footer class="relative overflow-hidden border-t border-ak-hairline" :style="{ background: 'var(--ak-footer-bg)' }">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px ak-gradient-bg opacity-60" />

        <div class="ak-container py-16">
            <div class="grid gap-12 lg:grid-cols-[1.4fr_2fr]">
                <!-- Brand + newsletter -->
                <div>
                    <Link href="/" class="font-display text-2xl font-bold tracking-tight text-ak-text">
                        {{ brandParts.first }}<span v-if="brandParts.rest" class="font-normal text-ak-text-2"> {{ brandParts.rest }}</span>
                    </Link>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-ak-text-2">{{ site.footerBlurb }}</p>

                    <div class="mt-6">
                        <p class="ak-eyebrow">{{ site.newsletter?.heading ?? 'Engineering notes' }}</p>
                        <Form
                            action="/newsletter"
                            method="post"
                            reset-on-success
                            class="mt-3 flex max-w-sm flex-wrap items-center gap-2"
                            #default="{ processing, wasSuccessful, errors }"
                        >
                            <input type="text" name="company" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />
                            <input
                                type="email"
                                name="email"
                                required
                                :placeholder="site.newsletter?.placeholder ?? 'you@company.com'"
                                class="ak-focusable h-11 w-full rounded-xl border border-ak-glass-border bg-[var(--ak-surface)] px-4 text-sm text-ak-text placeholder:text-ak-text-3"
                            />
                            <button
                                type="submit"
                                :disabled="processing"
                                class="ak-focusable inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ak-gradient-bg text-white transition hover:opacity-90"
                                aria-label="Subscribe"
                            >
                                <Icon name="send" :size="18" />
                            </button>
                            <span v-if="wasSuccessful" role="status" class="w-full text-sm text-ak-text-3">{{ site.newsletter?.success ?? 'Subscribed.' }}</span>
                            <span v-if="errors.email" role="alert" class="w-full text-sm text-rose-400">{{ errors.email }}</span>
                        </Form>
                    </div>
                </div>

                <!-- Link columns -->
                <div class="grid grid-cols-2 gap-8 sm:grid-cols-4">
                    <div v-for="(links, group) in nav.footer" :key="group">
                        <p class="ak-eyebrow">{{ group }}</p>
                        <ul class="mt-4 space-y-2.5">
                            <li v-for="link in links" :key="link.url">
                                <Link :href="link.url" class="ak-focusable text-sm text-ak-text-2 transition hover:text-ak-text">{{ link.label }}</Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contact + socials -->
            <div class="mt-12 flex flex-col gap-6 border-t border-ak-hairline pt-8 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-ak-text-2">
                    <a v-if="site.email" :href="`mailto:${site.email}`" class="ak-focusable inline-flex items-center gap-2 hover:text-ak-text">
                        <Icon name="mail" :size="16" /> {{ site.email }}
                    </a>
                    <a v-if="site.phone" :href="`tel:${site.phone}`" class="ak-focusable inline-flex items-center gap-2 hover:text-ak-text">
                        <Icon name="phone" :size="16" /> {{ site.phone }}
                    </a>
                    <span v-if="site.locations?.length" class="inline-flex items-center gap-2">
                        <Icon name="map-pin" :size="16" /> {{ site.locations.join(' · ') }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        v-for="(url, key) in site.socials"
                        :key="key"
                        :href="url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="ak-focusable inline-flex h-10 w-10 items-center justify-center rounded-lg border border-ak-glass-border text-ak-text-2 transition hover:border-ak-primary hover:text-ak-text"
                        :aria-label="String(key)"
                    >
                        <Icon :name="socialIcons[String(key)] ?? 'arrow-up-right'" :size="18" />
                    </a>
                </div>
            </div>

            <!-- Giant wordmark -->
            <div class="mt-12 select-none">
                <div class="ak-gradient-text font-display text-[18vw] font-bold leading-none tracking-tighter opacity-[0.13] lg:text-[14rem]">{{ brandParts.first.toUpperCase() }}</div>
            </div>

            <div class="mt-6 flex flex-col gap-3 text-sm text-ak-text-3 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ site.year }} {{ site.name }} - {{ site.tagline }}.</p>
                <div v-if="site.legalPages?.length" class="flex gap-5">
                    <Link v-for="legal in site.legalPages" :key="legal.slug" :href="legal.url" class="ak-focusable hover:text-ak-text">{{ legal.title }}</Link>
                </div>
            </div>
        </div>
    </footer>
</template>
