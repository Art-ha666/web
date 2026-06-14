<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useSite } from '@/composables/useSite';
import AkButton from './AkButton.vue';
import BrandGlyph from './BrandGlyph.vue';
import Icon from './Icon.vue';

const { site, nav } = useSite();
const page = usePage();

const scrolled = ref(false);
const mobileOpen = ref(false);
const servicesOpen = ref(false);
const annDismissed = ref(false);

const brandParts = computed<{ first: string; rest: string }>(() => {
    const name = site.value.name ?? 'AKH Solutions';
    const spaceIndex = name.indexOf(' ');

    return spaceIndex === -1 ? { first: name, rest: '' } : { first: name.slice(0, spaceIndex), rest: name.slice(spaceIndex + 1) };
});

function isActive(url: string): boolean {
    if (url === '/') {
return page.url === '/';
}

    return page.url.startsWith(url);
}

function onScroll() {
    scrolled.value = window.scrollY > 12;
}

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));

watch(
    () => page.url,
    () => {
        mobileOpen.value = false;
        servicesOpen.value = false;
    },
);
watch(mobileOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});
</script>

<template>
    <header class="fixed inset-x-0 top-0 z-50">
        <!-- Announcement strip -->
        <div v-if="site.announcement && !annDismissed" class="ak-gradient-bg text-white">
            <div class="ak-container flex items-center justify-center gap-2 py-1.5 text-center text-xs font-medium sm:text-[13px]">
                <Icon name="zap" :size="14" class="shrink-0" />
                <span>{{ site.announcement }}</span>
                <button class="ml-1 opacity-80 transition hover:opacity-100" aria-label="Dismiss announcement" @click="annDismissed = true">
                    <Icon name="x" :size="14" />
                </button>
            </div>
        </div>

        <div
            class="transition-all duration-300"
            :class="scrolled ? 'py-1.5' : 'py-3'"
        >
        <div
            class="border-b transition-colors duration-300"
            :class="scrolled ? 'border-ak-hairline' : 'border-transparent'"
            :style="{ background: scrolled ? 'var(--ak-nav-bg)' : 'transparent', backdropFilter: scrolled ? 'blur(14px) saturate(140%)' : 'none' }"
        >
            <nav class="ak-container flex items-center justify-between gap-6 py-1">
                <!-- Brand -->
                <Link href="/" class="ak-focusable group flex items-center gap-2.5 rounded-lg" :aria-label="`${site.name} home`">
                    <BrandGlyph :size="34" />
                    <span class="font-display text-lg tracking-tight text-ak-text">
                        <span class="font-bold">{{ brandParts.first }}</span><span v-if="brandParts.rest" class="font-normal text-ak-text-2"> {{ brandParts.rest }}</span>
                    </span>
                </Link>

                <!-- Desktop nav -->
                <div class="hidden items-center gap-1 lg:flex">
                    <div
                        v-for="link in nav.header"
                        :key="link.label"
                        class="relative"
                        @mouseenter="link.url === '/services' ? (servicesOpen = true) : null"
                        @mouseleave="link.url === '/services' ? (servicesOpen = false) : null"
                    >
                        <Link
                            :href="link.url"
                            class="ak-focusable inline-flex items-center gap-1 rounded-lg px-3.5 py-2 text-sm font-medium transition"
                            :class="isActive(link.url) ? 'text-ak-text' : 'text-ak-text-2 hover:text-ak-text'"
                        >
                            {{ link.label }}
                            <Icon v-if="link.url === '/services'" name="chevron-down" :size="15" class="transition-transform" :class="servicesOpen ? 'rotate-180' : ''" />
                        </Link>

                        <!-- Services mega dropdown -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 translate-y-1"
                            leave-active-class="transition duration-150 ease-in"
                            leave-to-class="opacity-0 translate-y-1"
                        >
                            <div
                                v-if="link.url === '/services' && servicesOpen && nav.services?.length"
                                class="absolute left-1/2 top-full z-50 w-[34rem] -translate-x-1/2 pt-3"
                            >
                                <div class="ak-glass grid grid-cols-1 gap-1 p-3 shadow-2xl sm:grid-cols-2">
                                    <Link
                                        v-for="svc in nav.services"
                                        :key="svc.url"
                                        :href="svc.url"
                                        class="ak-focusable group flex items-start gap-3 rounded-xl p-3 transition hover:bg-ak-surface-2"
                                    >
                                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ak-gradient-bg text-white">
                                            <Icon :name="svc.icon ?? 'sparkles'" :size="18" />
                                        </span>
                                        <span>
                                            <span class="block text-sm font-semibold text-ak-text">{{ svc.label }}</span>
                                            <span class="mt-0.5 block text-xs leading-snug text-ak-text-3">{{ svc.description }}</span>
                                        </span>
                                    </Link>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- CTA + mobile toggle -->
                <div class="flex items-center gap-2">
                    <AkButton :href="site.navCta?.url ?? '/contact'" variant="gradient" class="hidden sm:inline-flex">
                        {{ site.navCta?.label ?? 'Book a call' }}
                    </AkButton>
                    <button
                        class="ak-focusable inline-flex h-10 w-10 items-center justify-center rounded-lg text-ak-text lg:hidden"
                        :aria-expanded="mobileOpen"
                        aria-label="Toggle menu"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <Icon :name="mobileOpen ? 'x' : 'menu'" :size="24" />
                    </button>
                </div>
            </nav>
        </div>
        </div>

        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileOpen" class="fixed inset-0 top-0 z-40 lg:hidden" :style="{ background: 'var(--ak-bg)' }">
                <div class="ak-container flex h-full flex-col gap-2 pt-24">
                    <Link
                        v-for="link in nav.header"
                        :key="link.label"
                        :href="link.url"
                        class="ak-focusable border-b border-ak-hairline py-4 font-display text-2xl font-semibold text-ak-text"
                    >
                        {{ link.label }}
                    </Link>
                    <AkButton :href="site.navCta?.url ?? '/contact'" variant="gradient" size="lg" class="mt-6 w-full">
                        {{ site.navCta?.label ?? 'Book a call' }}
                    </AkButton>
                </div>
            </div>
        </Transition>
    </header>
</template>
