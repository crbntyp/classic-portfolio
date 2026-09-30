/*
 * crbntyp.com — motion.
 *
 * The bar is gsap.com/showcase: type that rises out of a mask, images that are
 * uncovered rather than faded in, a hero that holds while you scroll into the
 * work, smooth scrolling, and filtering that moves things instead of swapping
 * them. All of it in service of the work — every effect finishes and gets out
 * of the way, nothing loops, and nothing hides content from anyone who has not
 * got the motion.
 *
 * Loaded deferred, after the page's own script, from self-hosted copies in
 * vendor/ (GSAP 3.15 — every plugin is free now — and Lenis 1.3). The page
 * talks to this through window.crbntypMotion and treats it as optional: if it
 * is missing, slow, or reduced motion is on, the site is exactly as it was.
 *
 * Contract with the page (index.html):
 *   leave()        before a route swaps the view — reverts everything the last
 *                  page set up, pins and splits included
 *   enter(route)   after the view has its content and the scroll is reset
 *   toTop()        the scroll reset, through Lenis when it is running
 *   flip(fn)       wraps a tag filter: records where the cards are, lets fn
 *                  re-render, then moves them to where they now sit
 */
(function () {
  'use strict';

  if (!window.gsap || !window.ScrollTrigger || !window.SplitText || !window.Lenis) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var gsap = window.gsap;
  var ScrollTrigger = window.ScrollTrigger;
  var SplitText = window.SplitText;
  var Flip = window.Flip;
  gsap.registerPlugin(ScrollTrigger, SplitText, window.ScrambleTextPlugin, Flip);

  var EASE = 'expo.out';
  var MONO = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  var view = document.getElementById('view');
  if (!view) return;

  /* ------------------------------------------------------------ scrolling */

  /*
   * Lenis eases the real scroll position rather than transforming a wrapper,
   * so the sticky nav and the sticky case-study column keep working, and the
   * page is still a page to the browser. Touch is left to the OS.
   */
  var lenis = new window.Lenis({ autoRaf: false, lerp: 0.1 });
  lenis.on('scroll', ScrollTrigger.update);
  gsap.ticker.add(function (time) { lenis.raf(time * 1000); });
  gsap.ticker.lagSmoothing(0);

  // Covers and case-study images load after the triggers are measured and push
  // everything below them down. Re-measure whenever the page changes height.
  var settle;
  new ResizeObserver(function () {
    clearTimeout(settle);
    settle = setTimeout(function () { ScrollTrigger.refresh(); }, 150);
  }).observe(document.body);

  /* ----------------------------------------------------------- utilities */

  var page = null;      // the current route's gsap.context
  var enteredAt = 0;    // when the current route's entrance began

  var all = function (sel, root) { return Array.prototype.slice.call((root || view).querySelectorAll(sel)); };
  var one = function (sel, root) { return (root || view).querySelector(sel); };
  var navHeight = function () { var n = document.querySelector('.nav'); return n ? n.offsetHeight : 0; };
  // Anything already on screen when a page arrives waits for the heading to
  // start landing, rather than every block on the page going off at once.
  var entranceDelay = function (base) {
    var since = (performance.now() - enteredAt) / 1000;
    return Math.max(0, base - since);
  };

  /*
   * The fonts decide where the lines break, so splitting before Archivo has
   * arrived splits at the fallback's line breaks and the reveal runs on lines
   * that are about to reflow. The view is held invisible — never longer than
   * the timeout — until they are in.
   */
  function whenFontsReady(fn) {
    if (!document.fonts || document.fonts.status === 'loaded') { fn(); return; }
    gsap.set(view, { autoAlpha: 0 });
    var done = false;
    var go = function () {
      if (done) return;
      done = true;
      gsap.set(view, { autoAlpha: 1 });
      fn();
    };
    document.fonts.ready.then(go);
    setTimeout(go, 700);
  }

  /* ---------------------------------------------------- the reveal kit */

  // A label in the mono face resolves out of noise, like the readout it is.
  function scramble(tl, el, at) {
    if (!el || !window.ScrambleTextPlugin) return;
    tl.to(el, {
      duration: 0.8,
      ease: 'none',
      scrambleText: { text: el.textContent, chars: MONO, revealDelay: 0.2, speed: 0.6 },
    }, at);
  }

  // Words rising out of their own line, the line acting as the mask.
  function riseWords(tl, el, at) {
    if (!el) return;
    var split = SplitText.create(el, { type: 'words,lines', mask: 'lines' });
    tl.from(split.words, { yPercent: 115, duration: 1.2, ease: EASE, stagger: 0.035 }, at);
    tl.call(function () { split.revert(); }, null, '>');
  }

  // Lines rising out of a mask, for copy that is read rather than scanned.
  function riseLines(tl, el, at) {
    if (!el || !el.textContent.trim()) return;
    var split = SplitText.create(el, { type: 'lines', mask: 'lines' });
    tl.from(split.lines, { yPercent: 105, duration: 1.1, ease: EASE, stagger: 0.07 }, at);
    tl.call(function () { split.revert(); }, null, '>');
  }

  function liftIn(tl, els, at, stagger) {
    if (!els || (Array.isArray(els) && !els.length)) return;
    tl.from(els, { autoAlpha: 0, y: 14, duration: 0.9, ease: EASE, stagger: stagger || 0.04 }, at);
  }

  // A page heading, whichever page it is on.
  function heading(tl, head, at) {
    if (!head) return;
    scramble(tl, one('.stencil', head), at);
    riseWords(tl, one('h1', head), at + 0.05);
    riseLines(tl, one('.lede', head), at + 0.3);
    liftIn(tl, all('.btn-row > *', head), at + 0.45, 0.06);
  }

  /*
   * An image uncovered from the bottom edge while the picture inside it
   * settles from a slight zoom. clip-path, not opacity: the work arrives at
   * full strength, it is just not all there yet.
   */
  function uncover(box, img, delay) {
    gsap.fromTo(box,
      { clipPath: 'inset(100% 0% 0% 0%)' },
      { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.3, ease: EASE, delay: delay, clearProps: 'clipPath' });
    if (img) gsap.fromTo(img, { scale: 1.22 }, { scale: 1, duration: 1.8, ease: EASE, delay: delay, clearProps: 'scale' });
  }

  /*
   * Everything below the heading reveals as it is scrolled to. What is already
   * on screen when the page lands goes too, just after the heading.
   *
   * Text blocks are split into lines only when they are reached — a long post
   * is dozens of paragraphs, and splitting all of them up front would be work
   * for lines nobody has scrolled to yet.
   */
  function onScroll(els, reveal, opts) {
    opts = opts || {};
    els.forEach(function (el) {
      if (opts.hide !== false) gsap.set(el, opts.hideAs || { autoAlpha: 0 });
      ScrollTrigger.create({
        trigger: el,
        start: 'top 92%',
        once: true,
        onEnter: function () { reveal(el, entranceDelay(opts.after == null ? 0.45 : opts.after)); },
      });
    });
  }

  function textIn(el, delay) {
    gsap.set(el, { autoAlpha: 1 });
    // Blocks of prose split into lines; anything with media in it just rises.
    if (el.querySelector('img, video, iframe, figure') || !el.textContent.trim()) {
      gsap.from(el, { y: 30, autoAlpha: 0, duration: 1.1, ease: EASE, delay: delay });
      return;
    }
    var split = SplitText.create(el, { type: 'lines', mask: 'lines' });
    gsap.from(split.lines, {
      yPercent: 105, duration: 1.1, ease: EASE, stagger: 0.05, delay: delay,
      onComplete: function () { split.revert(); },
    });
  }

  function blockIn(el, delay) {
    gsap.fromTo(el, { autoAlpha: 0, y: 30 }, { autoAlpha: 1, y: 0, duration: 1.1, ease: EASE, delay: delay, clearProps: 'transform' });
  }

  function figureIn(el, delay) {
    uncover(el, el.querySelector('img, video'), delay);
  }

  // Rows of a list, in a stagger, a screenful at a time.
  function batch(els, stagger) {
    if (!els.length) return;
    gsap.set(els, { autoAlpha: 0, y: 24 });
    ScrollTrigger.batch(els, {
      start: 'top 94%',
      once: true,
      onEnter: function (group) {
        gsap.to(group, {
          autoAlpha: 1, y: 0, duration: 1, ease: EASE, stagger: stagger || 0.06,
          delay: entranceDelay(0.4), clearProps: 'transform',
        });
      },
    });
  }

  /* -------------------------------------------------------------- pages */

  function workGrid(cards) {
    cards.forEach(function (card) {
      var cover = one('.cover', card);
      var meta = one('.meta', card);
      if (cover) gsap.set(cover, { clipPath: 'inset(100% 0% 0% 0%)' });
      if (meta) gsap.set(meta, { autoAlpha: 0, y: 16 });
    });
    ScrollTrigger.batch(cards, {
      start: 'top 94%',
      once: true,
      onEnter: function (group) {
        var d = entranceDelay(0.35);
        group.forEach(function (card, i) {
          var cover = one('.cover', card);
          var meta = one('.meta', card);
          if (cover) uncover(cover, one('img', cover), d + i * 0.09);
          if (meta) gsap.to(meta, { autoAlpha: 1, y: 0, duration: 1, ease: EASE, delay: d + i * 0.09 + 0.25, clearProps: 'transform' });
        });
      },
    });
  }

  /*
   * The project hero: uncovered on arrival, then pinned as you begin to scroll.
   *
   * It holds while the case study slides up over it on its own sheet (.sheet
   * in index.html), the cover pushing in and dimming and the title lifting
   * away underneath — the page turning from "what this is" to "here is the
   * work". Once the sheet has covered it, it lets go.
   *
   * A curtain rather than a pin with spacing: the banner is half a screen, not
   * a full one, so pin spacing would open a blank band under it before anyone
   * had scrolled at all.
   *
   * Only where there is room. On a phone the banner is already short and
   * pinning it makes the page feel stuck, so there it parallaxes out instead.
   */
  function hero(tl, h) {
    var media = one('.hero-media', h);
    var img = one('img', h);
    var body = one('.hero-body', h);
    var back = one('.hero-back', h);

    if (media) tl.fromTo(media, { clipPath: 'inset(100% 0% 0% 0%)' },
      { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.4, ease: 'expo.inOut', clearProps: 'clipPath' }, 0);
    if (img) tl.from(img, { scale: 1.35, duration: 2.2, ease: EASE, clearProps: 'scale' }, 0);
    if (back) liftIn(tl, back, 0.7);

    var mm = gsap.matchMedia();
    mm.add('(min-width: 861px)', function () {
      var hold = gsap.timeline({ defaults: { ease: 'none' } });
      if (media) hold.to(media, { scale: 1.14, filter: 'brightness(0.45)' }, 0);
      if (body) hold.to(body, { yPercent: -40, autoAlpha: 0 }, 0);
      if (back) hold.to(back, { autoAlpha: 0 }, 0);
      ScrollTrigger.create({
        trigger: h,
        start: function () { return 'top ' + navHeight() + 'px'; },
        end: function () { return '+=' + h.offsetHeight; },
        pin: true,
        pinSpacing: false,
        scrub: 0.4,
        animation: hold,
        invalidateOnRefresh: true,
      });
    });
    mm.add('(max-width: 860px)', function () {
      if (!media) return;
      gsap.to(media, {
        yPercent: 18, ease: 'none',
        scrollTrigger: { trigger: h, start: 'top top', end: 'bottom top', scrub: true },
      });
    });
    return mm;
  }

  var ROUTES = {
    work: function (tl) {
      heading(tl, one('.page-head'), 0);
      liftIn(tl, all('.tag-bar > *'), 0.35, 0.025);
      workGrid(all('.card'));
    },

    project: function (tl) {
      var h = one('.hero');
      if (h) hero(tl, h);
      else liftIn(tl, one('.back'), 0);
      heading(tl, one('.project-head'), h ? 0.35 : 0.05);
      batch(all('.fact'), 0.08);
      onScroll(all('.modules > .module-text'), textIn);
      onScroll(all('.modules > figure'), figureIn, { hideAs: { clipPath: 'inset(100% 0% 0% 0%)' } });
      onScroll(all('.modules > video, .empty'), blockIn);
      batch(all('.features, .feature-list li'), 0.05);
      batch(all('.pager a'), 0.1);
    },

    'undo-stack': function (tl) {
      heading(tl, one('.page-head'), 0);
      liftIn(tl, all('.tag-bar > *'), 0.35, 0.025);
      batch(all('.post-row'), 0.08);
    },

    post: function (tl) {
      liftIn(tl, one('.back'), 0);
      heading(tl, one('.page-head'), 0.05);
      liftIn(tl, one('.post-meta'), 0.5);
      onScroll(all('.prose > *'), textIn, { after: 0.6 });
      batch(all('.more-posts .stencil, .more-list li'), 0.05);
      batch(all('.pager a'), 0.1);
    },

    about: function (tl) {
      heading(tl, one('.page-head'), 0);
      onScroll(all('.about-copy > *'), textIn, { after: 0.55 });
      batch(all('.about-block, .socials > *'), 0.05);
      batch(all('.worked > .stencil, .history li'), 0.018);
    },
  };

  function leave() {
    if (page) page.revert();
    page = null;
  }

  function enter(route) {
    leave();
    var run = ROUTES[route];
    if (!run) return;
    whenFontsReady(function () {
      enteredAt = performance.now();
      page = gsap.context(function () {
        var tl = gsap.timeline();
        run(tl);
      }, view);
      ScrollTrigger.refresh();
    });
  }

  function toTop() {
    lenis.scrollTo(0, { immediate: true, force: true });
  }

  /*
   * Filtering by tag re-renders the grid. Instead of the cards blinking into a
   * new arrangement, each one that survives the filter travels to its new
   * place and anything newly let in grows into its gap. Cards are matched
   * across the re-render by data-flip-id, since the elements themselves are
   * brand new.
   */
  function flip(fn) {
    var state = Flip ? Flip.getState(all('[data-flip-id]')) : null;
    leave();
    return Promise.resolve(fn()).then(function () {
      page = gsap.context(function () {
        var now = all('[data-flip-id]');
        if (state && now.length) {
          Flip.from(state, {
            targets: now,
            duration: 0.9,
            ease: 'expo.inOut',
            absolute: true,
            stagger: 0.02,
            onEnter: function (els) {
              return gsap.fromTo(els, { autoAlpha: 0, scale: 0.94 }, { autoAlpha: 1, scale: 1, duration: 0.7, ease: EASE, delay: 0.25 });
            },
          });
        }
      }, view);
      ScrollTrigger.refresh();
    });
  }

  /* ------------------------------------------------------------ footer */

  // Once per visit: the footer is outside the view and never re-rendered.
  var footer = document.querySelector('footer .footer-row');
  if (footer) {
    gsap.from(footer.children, {
      autoAlpha: 0, y: 16, duration: 1, ease: EASE, stagger: 0.1,
      scrollTrigger: { trigger: footer, start: 'top 98%', once: true },
    });
  }

  window.crbntypMotion = { leave: leave, enter: enter, toTop: toTop, flip: flip };
})();
