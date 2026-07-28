function preferReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function revealAllImmediately() {
    document.querySelectorAll('.reveal:not(.is-visible)').forEach((el) => {
        el.classList.add('is-visible');
    });
}

function initScrollReveals() {
    const reveals = document.querySelectorAll('.reveal:not(.is-visible)');

    if (!reveals.length) {
        return;
    }

    if (preferReducedMotion()) {
        reveals.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -4% 0px' },
    );

    reveals.forEach((el) => {
        const rect = el.getBoundingClientRect();
        const alreadyOnScreen = rect.top < window.innerHeight * 0.92 && rect.bottom > 0;

        if (alreadyOnScreen) {
            el.classList.add('is-visible');
            return;
        }

        observer.observe(el);
    });
}

document.addEventListener('DOMContentLoaded', initScrollReveals);

document.addEventListener('livewire:navigated', initScrollReveals);

// After Livewire morph (e.g. file upload), .reveal nodes lose is-visible and stay opacity:0.
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', () => {
        revealAllImmediately();
    });
});
