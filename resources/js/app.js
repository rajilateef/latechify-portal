import './bootstrap';
import { createIcons, icons } from 'lucide';

window.renderIcons = () => {
    try {
        createIcons({ icons });
    } catch (e) {
        // ignore icons that fail to resolve
    }
};

/* ── Scroll reveal + count-up ── */
function initMotion() {
    const revealEls = document.querySelectorAll('.reveal, .reveal-scale, .reveal-left, .reveal-right, .reveal-fade');
    if (revealEls.length) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                io.unobserve(el);

                // Promote to its own layer ONLY for the duration of the reveal,
                // then release it — a permanent `will-change` on dozens of
                // elements starves the compositor and janks scrolling.
                el.style.willChange = 'opacity, transform';
                el.classList.add('reveal-visible');
                const release = () => { el.style.willChange = 'auto'; };
                el.addEventListener('transitionend', release, { once: true });
                // Safety net in case the element is offscreen when it flips.
                setTimeout(release, 1100);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

        revealEls.forEach((el) => {
            const delay = el.getAttribute('data-reveal-delay');
            if (delay) el.style.transitionDelay = `${delay}ms`;
            io.observe(el);
        });
    }

    const countEls = document.querySelectorAll('[data-count]');
    if (countEls.length) {
        const co = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                co.unobserve(el);

                const target = parseFloat(el.getAttribute('data-count')) || 0;
                const prefix = el.getAttribute('data-prefix') || '';
                const suffix = el.getAttribute('data-suffix') || '';
                const duration = 1500;
                const start = performance.now();

                const tick = (now) => {
                    const p = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - p, 3);
                    const value = Math.round(target * eased);
                    el.textContent = prefix + value.toLocaleString() + suffix;
                    if (p < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            });
        }, { threshold: 0.4 });

        countEls.forEach((el) => co.observe(el));
    }
}

/* ── Sleek top scroll-progress bar (composited: transform only) ── */
function initScrollProgress() {
    let bar = document.getElementById('scroll-progress');
    if (!bar) return;
    let ticking = false;
    const update = () => {
        ticking = false;
        const doc = document.documentElement;
        const max = doc.scrollHeight - doc.clientHeight;
        const p = max > 0 ? Math.min(doc.scrollTop / max, 1) : 0;
        bar.style.transform = `scaleX(${p})`;
    };
    const onScroll = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(update);
    };
    // Passive listener + rAF coalescing keeps the main thread free while scrolling.
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
}

/* ── Pause decorative animations (aurora blobs, marquees) while off-screen ── */
function initAnimationPausing() {
    const animated = document.querySelectorAll('.aurora-blob, .marquee-track');
    if (!animated.length || !('IntersectionObserver' in window)) return;

    const po = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            entry.target.classList.toggle('anim-paused', !entry.isIntersecting);
        });
    }, { rootMargin: '100px' });

    animated.forEach((el) => po.observe(el));
}

function boot() {
    window.renderIcons();
    initMotion();
    initScrollProgress();
    initAnimationPausing();
}

document.addEventListener('DOMContentLoaded', boot);
document.addEventListener('livewire:navigated', boot);
document.addEventListener('livewire:init', () => {
    if (window.Livewire) {
        window.Livewire.hook('morph.updated', () => window.renderIcons());
    }
});
