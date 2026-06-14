import { ref, watch  } from 'vue';
import type {Ref} from 'vue';

/**
 * Animate a number from 0 to `target` once `active` becomes true.
 * Honours prefers-reduced-motion by jumping straight to the target.
 */
export function useCountUp(target: number, active: Ref<boolean>, duration = 1600) {
    const value = ref(0);
    let started = false;

    watch(
        active,
        (isActive) => {
            if (!isActive || started) {
                return;
            }

            started = true;

            const prefersReduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

            if (prefersReduced || typeof requestAnimationFrame === 'undefined') {
                value.value = target;

                return;
            }

            const start = performance.now();
            const step = (now: number) => {
                const progress = Math.min(1, (now - start) / duration);
                const eased = 1 - Math.pow(1 - progress, 3);
                value.value = target * eased;

                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    value.value = target;
                }
            };
            requestAnimationFrame(step);
        },
        { immediate: true },
    );

    return value;
}
