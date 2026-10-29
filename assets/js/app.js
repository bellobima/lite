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
    });

    document.querySelectorAll('[data-whatsapp-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const text = message?.value.trim() || 'Hello, I need help with the FCWM Student Support Portal.';
            const url = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(text)}`;
            window.open(url, '_blank', 'noopener,noreferrer');
        });
    });

    document.querySelectorAll('[data-hero-slider]').forEach((slider) => {
        const slides = [...slider.querySelectorAll('.hero-slide')];
        const dots = [...slider.querySelectorAll('.hero-dot')];
        if (slides.length < 2) return;
        let current = 0;
        let timer;
        const activate = (index) => {
            current = index;
            slides.forEach((slide, position) => slide.classList.toggle('is-active', position === index));
            dots.forEach((dot, position) => {
                dot.classList.toggle('is-active', position === index);
                dot.setAttribute('aria-pressed', String(position === index));
            });
        };
        const start = () => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            window.clearInterval(timer);
            timer = window.setInterval(() => activate((current + 1) % slides.length), 6500);
        };
        dots.forEach((dot, index) => dot.addEventListener('click', () => {
            activate(index);
            start();
        }));
        slider.addEventListener('mouseenter', () => window.clearInterval(timer));
        slider.addEventListener('mouseleave', start);
        slider.addEventListener('focusin', () => window.clearInterval(timer));
        slider.addEventListener('focusout', start);
        start();
    });

    const animated = document.querySelectorAll('.panel-card, .stat-card, .page-intro');
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
