(() => {
    const whatsappNumber = '2348079406633';
    const backdrop = document.querySelector('[data-contact-backdrop]');
    const message = document.querySelector('#contact-message');
    let previousFocus = null;

    const openContact = () => {
        if (!backdrop) return;
        previousFocus = document.activeElement;
        backdrop.hidden = false;
        document.body.classList.add('contact-is-open');
        backdrop.querySelector('[data-contact-close]')?.focus();
    };

    const closeContact = () => {
        if (!backdrop) return;
        backdrop.hidden = true;
        document.body.classList.remove('contact-is-open');
        previousFocus?.focus();
    };

    document.querySelectorAll('[data-contact-open]').forEach((button) => {
        button.addEventListener('click', openContact);
    });
    document.querySelectorAll('[data-contact-close]').forEach((button) => {
        button.addEventListener('click', closeContact);
    });
    backdrop?.addEventListener('click', (event) => {
        if (event.target === backdrop) closeContact();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && backdrop && !backdrop.hidden) closeContact();
        if (event.key === 'Tab' && backdrop && !backdrop.hidden) {
            const focusable = [...backdrop.querySelectorAll('button, a[href], textarea, input, select, [tabindex]:not([tabindex="-1"])')]
                .filter((element) => !element.disabled && element.getClientRects().length);
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    document.querySelectorAll('[data-whatsapp-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const text = message?.value.trim() || 'Hello, I need help with the FCWM Student Support Portal.';
            const url = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(text)}`;
            window.open(url, '_blank', 'noopener,noreferrer');
        });
    });

    document.querySelectorAll('form[data-loading-label]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (!button) return;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.textContent = form.dataset.loadingLabel || 'Please wait…';
        });
    });

    const animated = document.querySelectorAll('.panel-card, .stat-card, .page-intro, .step-card, .support-card');
    if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    currentObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });
        animated.forEach((element) => {
            element.classList.add('reveal-on-scroll');
            observer.observe(element);
        });
    }
})();
