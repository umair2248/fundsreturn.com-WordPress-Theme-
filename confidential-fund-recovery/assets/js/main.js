(() => {
  'use strict';
  document.documentElement.classList.add('js');
  const siteHeader = document.querySelector('.site-header-stack') || document.querySelector('.site-header');
  if (siteHeader) {
    const updateHeaderOffset = () => {
      const top = parseFloat(getComputedStyle(siteHeader).top) || 0;
      document.documentElement.style.setProperty(
        '--cfr-header-offset', (siteHeader.getBoundingClientRect().height + top + 20) + 'px'
      );
    };
    updateHeaderOffset();
    if ('ResizeObserver' in window) new ResizeObserver(updateHeaderOffset).observe(siteHeader);
    window.addEventListener('resize', updateHeaderOffset, { passive: true });
    let scrollFrame = 0;
    const compactHeader = () => {
      scrollFrame = 0;
      if (window.scrollY > 120) siteHeader.classList.add('is-compact');
      else if (window.scrollY <= 8) siteHeader.classList.remove('is-compact');
      updateHeaderOffset();
    };
    compactHeader();
    window.addEventListener('scroll', () => {
      if (!scrollFrame) scrollFrame = requestAnimationFrame(compactHeader);
    }, { passive: true });
  }
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.getElementById('site-navigation');
  if (toggle && nav) {
    const close = (focus = false) => {
      nav.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      if (focus) toggle.focus();
    };
    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') !== 'true';
      nav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && nav.classList.contains('is-open')) close(true);
    });
    document.addEventListener('click', event => {
      if (!nav.contains(event.target) && !toggle.contains(event.target)) close();
    });
    const media = window.matchMedia('(max-width: 1024px)');
    if (media.addEventListener) media.addEventListener('change', () => close());
    else media.addListener(() => close());
  }
  // Keep an existing Home section fragment when switching translated pages.
  const syncLanguageFragments = () => {
    document.querySelectorAll('.language-switcher a').forEach(link => {
      const url = new URL(link.href);
      url.hash = window.location.hash;
      link.href = url.href;
    });
  };
  syncLanguageFragments();
  window.addEventListener('hashchange', syncLanguageFragments);
  const message = document.querySelector('.form-message');
  if (message) message.focus();
  // Entry-only motion: no offscreen content is hidden and layout stays intact.
  const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const animations = new Set();
  const seen = new WeakSet();
  let observer;
  const clearEntrances = () => {
    if (observer) observer.disconnect();
    animations.forEach(animation => animation.cancel());
    animations.clear();
  };
  if (!motion.matches && 'IntersectionObserver' in window && Element.prototype.animate) {
    const selectors = [
      '.site-branding', '.header-cta', '.hero-copy > *', '.evidence-graphic > *',
      '.page-banner .eyebrow', '.page-banner h1',
      'main .section-index', 'main h2', 'main h3', 'main p', 'main .site-image',
      'main .check-list > li', 'main .analysis-list > article',
      'main .evidence-panels > article', 'main .process-list > li',
      'main .faq-list > details', 'main .assessment-intro', 'main .case-form',
      'main .button', '.footer-masthead > *', '.footer-grid > div',
      '.important-notice', '.footer-utilities', '.footer-bottom'
    ];
    const candidates = [...document.querySelectorAll(selectors.join(','))];
    const selected = new Set(candidates);
    const elements = candidates.filter(element => {
      for (let parent = element.parentElement; parent; parent = parent.parentElement) {
        if (selected.has(parent)) return false;
      }
      return true;
    });
    const reveal = element => {
      if (seen.has(element) || motion.matches) return;
      seen.add(element);
      if (observer) observer.unobserve(element);
      if (element.contains(document.activeElement)) return;
      const picture = element.matches('.site-image');
      const heading = element.matches('h1,h2,h3');
      const siblings = [...element.parentElement.children];
      const stagger = element.matches('li,article,details') ?
        (siblings.indexOf(element) % 3) * 90 : 0;
      const animation = element.animate([
        { opacity: 0, transform: picture ? 'translateY(36px) scale(.97)' :
          'translateY(' + (heading ? 30 : 42) + 'px)' },
        { opacity: 1, transform: 'translateY(0) scale(1)' }
      ], {
        duration: picture ? 1050 : 820, delay: stagger,
        easing: 'cubic-bezier(.16,1,.3,1)', fill: 'backwards'
      });
      animations.add(animation);
      animation.finished.then(() => animations.delete(animation), () => animations.delete(animation));
    };
    try {
      observer = new IntersectionObserver(entries => {
        entries.forEach(entry => { if (entry.isIntersecting) reveal(entry.target); });
      }, { threshold: 0.06, rootMargin: '0px 0px -16px 0px' });
      elements.forEach(element => {
        const bounds = element.getBoundingClientRect();
        if (bounds.bottom <= 0) { seen.add(element); return; }
        if (bounds.top < innerHeight && bounds.bottom > 0) reveal(element);
        else observer.observe(element);
      });
    } catch (_) { clearEntrances(); }
    document.addEventListener('focusin', event => {
      elements.forEach(element => {
        if (!element.contains(event.target)) return;
        seen.add(element);
        observer.unobserve(element);
        element.getAnimations().forEach(animation => animation.cancel());
      });
    });
    if (motion.addEventListener) motion.addEventListener('change', clearEntrances);
    window.addEventListener('beforeprint', clearEntrances);
    window.addEventListener('pagehide', clearEntrances);
  }
})();
