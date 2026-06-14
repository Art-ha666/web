<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';
import AppNav from '@/components/site/AppNav.vue';
import CookieConsent from '@/components/site/CookieConsent.vue';
import Footer from '@/components/site/Footer.vue';
import Sonner from '@/components/ui/sonner/Sonner.vue';
import { useSite } from '@/composables/useSite';

const { theme } = useSite();
const page = usePage();

watch(
    () => page.props.flash,
    (flash) => {
        const f = flash as { success?: string; error?: string; message?: string } | undefined;

        if (!f) {
return;
}

        if (f.success) {
toast.success(f.success);
}

        if (f.error) {
toast.error(f.error);
}

        if (f.message) {
toast(f.message);
}
    },
    { immediate: true, deep: true },
);
</script>

<template>
    <div
        class="ak-site relative min-h-screen overflow-x-hidden font-body antialiased"
        :data-theme="theme.key"
        :data-hero="theme.hero"
        :data-layout="theme.layout"
        :data-scheme="theme.scheme"
        :style="{ background: 'var(--ak-bg)', color: 'var(--ak-text)' }"
    >
        <AppNav />
        <main>
            <slot />
        </main>
        <Footer />
        <CookieConsent />
        <Sonner />
    </div>
</template>
