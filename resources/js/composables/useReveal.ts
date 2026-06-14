import { onBeforeUnmount, onMounted, ref } from 'vue';

interface RevealOptions {
    threshold?: number;
    once?: boolean;
}

/**
 * Reveal an element when it scrolls into view. Returns a template ref to bind
 * and a reactive `visible` flag. Honours prefers-reduced-motion by revealing
 * immediately.
 */
export function useReveal(options: RevealOptions = {}) {
    const el = ref<HTMLElement | null>(null);
    const visible = ref(false);
    let observer: IntersectionObserver | null = null;

    onMounted(() => {
        const prefersReduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

        if (prefersReduced || typeof IntersectionObserver === 'undefined' || !el.value) {
            visible.value = true;

            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        visible.value = true;

                        if (options.once !== false) {
                            observer?.unobserve(entry.target);
                        }
                    }
                });
            },
            { threshold: options.threshold ?? 0.15, rootMargin: '0px 0px -8% 0px' },
        );

        observer.observe(el.value);
    });

    onBeforeUnmount(() => observer?.disconnect());

    return { el, visible };
}
