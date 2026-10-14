@extends('layouts.app')

{{-- ═══════════════════ PRELOADER (homepage only) ═══════════════════ --}}
@push('styles')
<style>
    /* Lock scroll + suppress content reveals while the preloader is on screen.
       These rules only bite when <html> carries .bk-preloading (added by the
       gate script below), so non-home pages and JS-off users are unaffected. */
    html.bk-preloading, html.bk-preloading body { overflow: hidden !important; height: 100%; }
    html.bk-preloading .bk-reveal { opacity: 0 !important; animation: none !important; }

    #bk-preloader { display: none; }
    html.bk-preloading #bk-preloader {
        display: flex;
        position: fixed;
        inset: 0;
        z-index: 99999;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #14110E;
        opacity: 1;
        transition: opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1), visibility 0.7s;
    }
    #bk-preloader.is-hiding { opacity: 0; visibility: hidden; }

    /* Animated bakery background with a slow zoom */
    .bk-pl__bg {
        position: absolute;
        inset: 0;
        background: url('{{ asset('assets/images/bakery/cta.webp') }}') center / cover no-repeat;
        transform: scale(1.05);
        opacity: 0;
        animation: bk-pl-zoom 4s ease-out forwards;
        will-change: transform, opacity;
    }
    .bk-pl__overlay {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 42%, rgba(20,17,14,0.55) 0%, rgba(20,17,14,0.86) 60%, rgba(14,11,8,0.96) 100%);
    }
    @keyframes bk-pl-zoom {
        0%   { opacity: 0; transform: scale(1.05); }
        18%  { opacity: 1; }
        100% { opacity: 1; transform: scale(1.2); }
    }

    .bk-pl__inner {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 24px;
    }

    /* Logo — fade-in → scale-up → glow → fade-out, driven by jQuery classes */
    /* The wrapper carries the scale + glow (overflow:hidden clips the shimmer,
       not the wrapper's own drop-shadow), so the 105% scale never gets cropped. */
    .bk-pl__logo-wrap {
        position: relative;
        display: inline-flex;
        overflow: hidden;
        border-radius: 12px;
        transform: scale(0.85);
        filter: drop-shadow(0 12px 32px rgba(0,0,0,0.5)) drop-shadow(0 0 0 rgba(196,172,94,0));
        transition:
            transform 1.1s cubic-bezier(0.22, 1, 0.36, 1),
            filter 0.9s ease;
    }
    .bk-pl__logo {
        max-width: min(320px, 74vw);
        height: auto;
        display: block;
        opacity: 0;
        transition: opacity 0.85s cubic-bezier(0.22, 1, 0.36, 1);
    }
    #bk-preloader.is-logo-in .bk-pl__logo { opacity: 1; }
    #bk-preloader.is-logo-in .bk-pl__logo-wrap { transform: scale(1); }
    #bk-preloader.is-logo-scaled .bk-pl__logo-wrap {
        transform: scale(1.05);
        filter: drop-shadow(0 12px 32px rgba(0,0,0,0.5)) drop-shadow(0 0 36px rgba(196,172,94,0.6));
    }

    /* Gold shimmer sweep across the logo */
    .bk-pl__shimmer {
        position: absolute;
        top: 0; bottom: 0;
        left: -60%;
        width: 60%;
        background: linear-gradient(100deg, transparent 0%, rgba(212,192,130,0.0) 20%, rgba(212,192,130,0.85) 50%, rgba(212,192,130,0.0) 80%, transparent 100%);
        transform: skewX(-18deg);
        opacity: 0;
        pointer-events: none;
    }
    #bk-preloader.is-shimmer .bk-pl__shimmer { animation: bk-pl-shimmer 1.05s ease forwards; }
    @keyframes bk-pl-shimmer {
        0%   { left: -60%; opacity: 0; }
        15%  { opacity: 1; }
        85%  { opacity: 1; }
        100% { left: 130%; opacity: 0; }
    }

    .bk-pl__tagline {
        margin: 1.5rem 0 0;
        font-family: 'Jost', sans-serif;
        font-size: 0.72rem;
        font-weight: 500;
        letter-spacing: 0.34em;
        text-transform: uppercase;
        color: #C4AC5E;
        opacity: 0;
        transform: translateY(8px);
        transition: opacity 0.8s ease 0.1s, transform 0.8s ease 0.1s;
    }
    #bk-preloader.is-logo-in .bk-pl__tagline { opacity: 0.92; transform: none; }

    /* Loading bar — fills to 100% over 2.5s */
    .bk-pl__bar {
        margin-top: 2rem;
        width: clamp(180px, 30vw, 260px);
        height: 2px;
        background: rgba(255,255,255,0.14);
        border-radius: 2px;
        overflow: hidden;
    }
    .bk-pl__bar-fill {
        display: block;
        width: 0;
        height: 100%;
        background: linear-gradient(90deg, #A08A3C, #C4AC5E);
        border-radius: 2px;
    }
    #bk-preloader.is-loading .bk-pl__bar-fill { width: 100%; transition: width 2.5s cubic-bezier(0.45, 0, 0.15, 1); }

    /* Staggered fade-up of the hero once loading completes.
       Scoped to .bkr-hero — the section was renamed from .bakery-hero, which
       left this stagger dead and the hero falling back to the generic reveal. */
    @keyframes bk-pl-fade-up { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: none; } }
    html.bk-loaded .bkr-hero .bk-reveal { animation: bk-pl-fade-up 0.9s cubic-bezier(0.22, 1, 0.36, 1) both; }
    html.bk-loaded .bkr-hero .bk-reveal[data-delay="1"] { animation-delay: 0.18s; }
    html.bk-loaded .bkr-hero .bk-reveal[data-delay="2"] { animation-delay: 0.36s; }


    @media (prefers-reduced-motion: reduce) {
        html.bk-preloading #bk-preloader { transition: none; }
        .bk-pl__bg, .bk-pl__logo, .bk-pl__shimmer, .bk-pl__tagline, .bk-pl__bar-fill { animation: none !important; transition: none !important; }
    }
</style>
<script>
    /* Gate: show once per browser session, on the homepage only, with motion respected.
       Runs in <head> before <body> paints, so content never flashes underneath.
       (To replay on every load while testing: remove the sessionStorage guard,
        or run sessionStorage.removeItem('bkPreloaderSeen') in the console.) */
    (function () {
        try {
            var seen = sessionStorage.getItem('bkPreloaderSeen');
            var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!seen && !reduce) {
                document.documentElement.classList.add('bk-preloading');
                sessionStorage.setItem('bkPreloaderSeen', '1');
                /* Safety net: never trap content if the jQuery timeline fails to run */
                window.setTimeout(function () {
                    document.documentElement.classList.remove('bk-preloading');
                }, 5000);
            } else if (!seen) {
                sessionStorage.setItem('bkPreloaderSeen', '1');
            }
        } catch (e) { /* sessionStorage unavailable — skip preloader gracefully */ }
    })();
</script>
@endpush

{{-- The hero opens on a dark photograph, so the header sits over it transparently. --}}
@section('header_class', 'is-overlay')

@section('content')

<div id="bk-preloader" role="status" aria-label="Loading Izan Bakery">
    <div class="bk-pl__bg"></div>
    <div class="bk-pl__overlay"></div>
    <div class="bk-pl__inner">
        <div class="bk-pl__logo-wrap">
            <img class="bk-pl__logo" src="{{ asset('assets/images/logo.png') }}" alt="Izan Bakery">
            <span class="bk-pl__shimmer"></span>
        </div>
        <p class="bk-pl__tagline">Izan Bakery · Freshly Baked Every Day</p>
        <div class="bk-pl__bar"><span class="bk-pl__bar-fill"></span></div>
    </div>
</div>

{{-- ═══════════════════ HERO ═══════════════════ --}}
<section class="bkr-hero">
    <div class="bkr-hero__bg">
        <img src="{{ asset('assets/images/bakery/cta.webp') }}" alt="" fetchpriority="high">
    </div>

    {{-- Flour catching the light. Pass ['count' => n] to change the density. --}}
    @include('partials.bkr-motes')

    <div class="bkr-hero__inner">
        <span class="bkr-eyebrow bkr-hero__eyebrow bk-reveal">{{ __('messages.home.eyebrow') }}</span>
        @php
            // Both locales author the heading as two lines joined by <br>.
            // Splitting it here keeps the reveal structure in the view and
            // leaves translators one familiar string rather than markup.
            $headingLines = preg_split('/<br\s*\/?>/i', __('messages.home.hero_heading'));
        @endphp
        {{-- bkr-reveal is only an observer hook here: CSS neutralises its own
             fade so the motion belongs to the masked lines below. --}}
        <h1 class="bkr-display on-dark bkr-hero__title bkr-reveal">
            @foreach ($headingLines as $i => $line)
                <span class="bkr-hero__line"><span class="bkr-hero__line-in" style="--bkr-line-i: {{ $i }}">{!! $line !!}</span></span>
            @endforeach
        </h1>
        <a href="{{ route('shop.index') }}" class="bkr-btn on-dark bk-reveal" data-delay="2">
            {{ __('messages.nav.shop') }}
        </a>
    </div>

    
    {{-- @if (file_exists(public_path('assets/images/bakery/loaf-cutout.png')))
        <div class="bkr-hero__cutout">
            <div class="bkr-steam" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
            <img src="{{ asset('assets/images/bakery/loaf-cutout.png') }}" alt="" loading="lazy">
        </div>
    @endif --}}
</section>

{{-- Welcome bar for logged-in users --}}
@auth
<div class="container pt-5">
    <div class="bakery-welcome-bar bkr-reveal bkr-reveal--soft">
        <p class="user-greeting mb-0">
            {{ __('messages.home.welcome_back') }} <span class="user-name">{{ Auth::user()->name }}</span>!
            {{ __('messages.home.welcome_message') }}
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('user.feedback') }}" class="btn btn-sm order-now-btn">{{ __('messages.home.my_feedback') }}</a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('messages.home.sign_out') }}</button>
            </form>
        </div>
    </div>
</div>
@endauth

{{-- ═══════════════════ FEATURED SPLIT ROWS ═══════════════════ --}}
{{-- Alternating image ↔ copy rows, one per featured product. The cutout above
     overhangs this section, so the first row needs extra headroom. --}}
<section class="container" id="featured-products"
         style="padding-top: clamp(140px, 20vw, 260px); padding-bottom: var(--bkr-gap);">
    @foreach ($products as $product)
        <article class="bkr-split bkr-reveal {{ $loop->odd ? 'is-reversed' : '' }}">
            <div class="bkr-split__media">
                <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
                    @if ($product->getFirstMediaUrl('product', 'display'))
                        <img src="{{ $product->getFirstMediaUrl('product', 'display') }}"
                             alt="{{ $product->name }}" loading="lazy">
                    @else
                        <div class="bkr-img-placeholder bkr-img-placeholder--lg" style="height: clamp(340px, 44vw, 560px);">
                            <i class="bi bi-cake2" aria-hidden="true"></i>
                        </div>
                    @endif
                </a>
            </div>

            <div class="bkr-split__body">
                <span class="bkr-eyebrow">{{ $product->category->name ?? __('messages.home.customer_favourites') }}</span>
                <h2 class="bkr-display bkr-split__title">{{ $product->name }}</h2>

                @if ($product->short_description)
                    <p class="bkr-split__text">{{ $product->short_description }}</p>
                @endif

                <span class="bkr-split__price">
                    @if ($product->sale_price)
                        <del>RM{{ number_format($product->regular_price, 2) }}</del>
                        RM{{ number_format($product->sale_price, 2) }}
                    @else
                        RM{{ number_format($product->regular_price, 2) }}
                    @endif
                </span>

                <div class="bkr-split__cta">
                    <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}"
                       class="bkr-btn">{{ __('messages.home.buy') }}</a>
                    <span class="bkr-split__rule" aria-hidden="true"></span>
                </div>
            </div>
        </article>
    @endforeach
</section>

{{-- ═══════════════════ A WORD FROM THE BAKER ═══════════════════ --}}
<section class="bkr-baker bkr-reveal" id="our-story">
    <svg class="bkr-baker__watermark" viewBox="0 0 200 200" aria-hidden="true"><use href="#bkr_rosette"/></svg>

    <h2 class="bkr-display bkr-baker__title">{{ __('messages.home.baker_title') }}</h2>
    <p class="bkr-baker__lead">{{ __('messages.home.baker_lead') }}</p>
    <p class="bkr-baker__text">{{ __('messages.home.baker_text') }}</p>

    <img class="bkr-baker__portrait" src="{{ asset('assets/images/bakery/baker.jpg') }}"
         alt="{{ __('messages.home.baker_name') }}" loading="lazy">
    <div class="bkr-baker__stem" aria-hidden="true"></div>
    <p class="bkr-baker__name">{{ __('messages.home.baker_name') }}</p>
</section>

{{-- ═══════════════════ MENU ═══════════════════ --}}
{{-- White panel floating over a full-bleed dark photograph. --}}
<section class="bkr-menu">
    <div class="bkr-menu__bg">
        <img src="{{ asset('assets/images/bakery/cta.webp') }}" alt="" loading="lazy">
    </div>

    <div class="bkr-menu__panel bkr-reveal">
        <div class="bkr-rosette">
            <svg viewBox="0 0 200 200" aria-hidden="true"><use href="#bkr_rosette"/></svg>
            <span class="bkr-rosette__label">
                <span class="bkr-rosette__word">{{ __('messages.footer.our_menu') }}</span>
                <span class="bkr-rosette__year">{{ date('Y') }}</span>
            </span>
        </div>

        @php
            // Flatten every category's products into one list for the two-column menu.
            $menuItems = $categories->flatMap->products->take(8);
        @endphp

        <div class="bkr-menu__grid" data-reveal-stagger>
            @foreach ($menuItems as $item)
                <div class="bkr-menu__item bkr-reveal bkr-reveal--soft">
                    <div class="bkr-menu__head">
                        <h3 class="bkr-menu__name">{{ $item->name }}</h3>
                        <span class="bkr-menu__dots" aria-hidden="true"></span>
                        <span class="bkr-menu__price">{{ number_format($item->sale_price ?: $item->regular_price, 2) }}</span>
                    </div>
                    @if ($item->short_description)
                        <p class="bkr-menu__desc">{{ $item->short_description }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <a href="{{ route('shop.index') }}" class="bkr-btn">{{ __('messages.home.view_menu') }}</a>
    </div>
</section>

@endsection

@push('scripts')
<script>
    /* Premium preloader timeline — jQuery drives the timing, CSS does the easing. */
    jQuery(function ($) {
        var $pl = $('#bk-preloader');
        if (!$pl.length || !document.documentElement.classList.contains('bk-preloading')) {
            return; // not showing this load (repeat visit, reduced motion, or non-home)
        }

        // 0.0s — background fades in (CSS) + loading bar starts filling
        setTimeout(function () { $pl.addClass('is-loading'); }, 60);
        // 0.5s — Izan Bakery logo appears
        setTimeout(function () { $pl.addClass('is-logo-in'); }, 500);
        // 1.0s — logo gently scales to 105% (+ warm glow)
        setTimeout(function () { $pl.addClass('is-logo-scaled'); }, 1000);
        // 2.0s — gold shimmer passes over the logo
        setTimeout(function () { $pl.addClass('is-shimmer'); }, 2000);
        // 2.5s — loading bar reaches 100% (handled by the 2.5s CSS transition)

        // 3.0s — preloader fades out
        setTimeout(function () { $pl.addClass('is-hiding'); }, 3000);

        // 3.2s — release scroll lock + trigger the staggered hero fade-up
        setTimeout(function () {
            document.documentElement.classList.remove('bk-preloading');
            document.documentElement.classList.add('bk-loaded');
        }, 3200);

        // Cleanup once the fade-out transition has finished
        setTimeout(function () { $pl.remove(); }, 3900);
    });
</script>
@endpush
