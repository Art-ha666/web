<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Bot,
    Briefcase,
    ExternalLink,
    FileText,
    FolderGit2,
    Home,
    Inbox,
    LayoutGrid,
    ListChecks,
    Megaphone,
    Navigation,
    Newspaper,
    Palette,
    PencilRuler,
    Quote,
    Settings2,
    Sparkles,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';

const { isCurrentUrl } = useCurrentUrl();

const groups = [
    {
        label: 'Overview',
        items: [
            { title: 'Dashboard', href: '/admin', icon: LayoutGrid },
            { title: 'Leads', href: '/admin/leads', icon: Inbox },
        ],
    },
    {
        label: 'Appearance',
        items: [
            { title: 'Homepage', href: '/admin/home', icon: Home },
            { title: 'Page content', href: '/admin/content', icon: PencilRuler },
            { title: 'Design', href: '/admin/design', icon: Palette },
            { title: 'Pages', href: '/admin/pages', icon: FileText },
            { title: 'Settings', href: '/admin/settings', icon: Settings2 },
        ],
    },
    {
        label: 'Content',
        items: [
            { title: 'Services', href: '/admin/services', icon: Briefcase },
            { title: 'Work', href: '/admin/projects', icon: FolderGit2 },
            { title: 'Team', href: '/admin/team', icon: Users },
            { title: 'Testimonials', href: '/admin/testimonials', icon: Quote },
            { title: 'Insights', href: '/admin/articles', icon: Newspaper },
            { title: 'AI Writer', href: '/admin/ai-writer', icon: Bot },
            { title: 'Careers', href: '/admin/jobs', icon: Sparkles },
        ],
    },
    {
        label: 'Sections',
        items: [
            { title: 'Stats', href: '/admin/stats', icon: BarChart3 },
            { title: 'Process', href: '/admin/process-steps', icon: ListChecks },
            { title: 'Logos', href: '/admin/client-logos', icon: Sparkles },
            { title: 'CTAs', href: '/admin/cta-sections', icon: Megaphone },
            { title: 'Navigation', href: '/admin/nav-items', icon: Navigation },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/admin">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup v-for="group in groups" :key="group.label" class="px-2 py-0">
                <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in group.items" :key="item.title">
                        <SidebarMenuButton as-child :is-active="isCurrentUrl(item.href)" :tooltip="item.title">
                            <Link :href="item.href">
                                <component :is="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child tooltip="View live site">
                        <a href="/" target="_blank" rel="noopener">
                            <ExternalLink />
                            <span>View live site</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
