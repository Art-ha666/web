import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface NavLink {
    label: string;
    url: string;
}

interface NavData {
    header: NavLink[];
    footer: Record<string, NavLink[]>;
    services?: Array<NavLink & { icon?: string | null; description?: string | null }>;
}

interface SiteData {
    name: string;
    tagline?: string;
    email?: string;
    phone?: string;
    whatsapp?: string;
    telegram?: string;
    address?: string;
    socials?: Record<string, string>;
    locations?: string[];
    navCta?: { label: string; url: string };
    footerBlurb?: string;
    metaTitle?: string;
    metaDescription?: string;
    announcement?: string | null;
    newsletter?: { heading: string; placeholder: string; success: string };
    cookieConsent?: { text: string; acceptLabel: string; declineLabel: string; gaId?: string | null };
    legalPages?: Array<{ slug: string; title: string; url: string }>;
    year?: number;
}

interface ThemeData {
    key: string;
    name: string;
    hero: string;
    layout: string;
    usesThree: boolean;
    scheme: string;
    tokens: Record<string, string>;
}

/**
 * Typed access to the globally-shared site, theme and nav props.
 */
export function useSite() {
    const page = usePage();

    const site = computed<SiteData>(() => (page.props.site as SiteData) ?? ({ name: 'AKH Solutions' } as SiteData));
    const theme = computed<ThemeData>(() => (page.props.theme as ThemeData) ?? ({ key: 'aurora', hero: 'aurora', layout: 'standard', usesThree: false, scheme: 'dark', tokens: {} } as ThemeData));
    const nav = computed<NavData>(() => (page.props.nav as NavData) ?? { header: [], footer: {} });

    return { site, theme, nav };
}

export type { NavData, NavLink, SiteData, ThemeData };
