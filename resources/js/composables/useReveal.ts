import type { Directive } from 'vue';

/**
 * v-reveal: fades an element up into place the first time it scrolls into
 * view. Pair it with the `sn-reveal` class; an optional value delays it (ms).
 */
export const vReveal: Directive<HTMLElement, number | undefined> = {
    mounted(element, binding) {
        element.classList.add('sn-reveal');

        if (binding.value) {
            element.style.transitionDelay = `${binding.value}ms`;
        }

        if (typeof IntersectionObserver === 'undefined') {
            element.classList.add('is-visible');

            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        element.classList.add('is-visible');
                        observer.disconnect();
                    }
                }
            },
            { rootMargin: '0px 0px -10% 0px' },
        );

        observer.observe(element);
    },
};
