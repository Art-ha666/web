<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useSite } from '@/composables/useSite';

const STORAGE_KEY = 'akh-cookie-consent';

const { site } = useSite();
const visible = ref(false);

declare global {
    interface Window {
        dataLayer?: unknown[];
        gtag?: (...args: unknown[]) => void;
    }
}

function loadAnalytics() {
    const gaId = site.value.cookieConsent?.gaId;

    if (!gaId || document.getElementById('akh-ga-script')) {
        return;
    }

    const script = document.createElement('script');
    script.id = 'akh-ga-script';
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(gaId)}`;
    document.head.appendChild(script);

    window.dataLayer = window.dataLayer ?? [];
    window.gtag = function gtag(...args: unknown[]) {
        window.dataLayer!.push(args);
    };
    window.gtag('js', new Date());
    window.gtag('config', gaId, { anonymize_ip: true });
}

function choose(consent: 'accepted' | 'declined') {
    localStorage.setItem(STORAGE_KEY, consent);
    visible.value = false;

    if (consent === 'accepted') {
        loadAnalytics();
    }
}

onMounted(() => {
    const stored = localStorage.getItem(STORAGE_KEY);

    if (stored === 'accepted') {
        loadAnalytics();
    } else if (stored !== 'declined') {
        visible.value = true;
    }
});
</script>

<template>
    <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-6 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-6 opacity-0"
    >
        <aside
            v-if="visible"
            role="region"
            aria-label="Cookie consent"
            class="fixed inset-x-4 bottom-4 z-[60] mx-auto max-w-xl rounded-2xl border border-ak-glass-border bg-[var(--ak-surface)]/95 p-5 shadow-2xl backdrop-blur sm:inset-x-auto sm:left-6 sm:right-auto"
        >
            <p class="text-sm leading-relaxed text-ak-text-2">
                {{ site.cookieConsent?.text }}
                <Link href="/cookies" class="ak-focusable underline underline-offset-2 hover:text-ak-text">Cookie Policy</Link>
            </p>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button type="button" class="ak-btn ak-btn-gradient ak-focusable px-5 py-2 text-sm" @click="choose('accepted')">
                    {{ site.cookieConsent?.acceptLabel }}
                </button>
                <button
                    type="button"
                    class="ak-focusable rounded-xl border border-ak-glass-border px-5 py-2 text-sm text-ak-text-2 transition hover:text-ak-text"
                    @click="choose('declined')"
                >
                    {{ site.cookieConsent?.declineLabel }}
                </button>
            </div>
        </aside>
    </transition>
</template>
