<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

// Surface session flashes (success/error) from admin controllers as toasts,
// mirroring the public SiteLayout - without this, admin saves are silent.
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
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
        <Toaster />
    </AppShell>
</template>
