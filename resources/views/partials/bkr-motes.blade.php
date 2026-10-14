{{-- Floating flour — decorative motes for dark, full-bleed sections.

     Usage:  @include('partials.bkr-motes')
             @include('partials.bkr-motes', ['count' => 20])

     Per-mote variation is emitted as inline custom properties rather than a
     wall of :nth-child rules, so the density is one number instead of one CSS
     block per speck. The animation itself stays in custom.css, which also
     halves the count on phones and drops the layer entirely under
     prefers-reduced-motion. --}}

@php
    $count = $count ?? 40;

    // Golden-ratio stepping distributes each parameter evenly across its range
    // without the clumping a random source produces at this density — and it is
    // deterministic, so the markup is byte-identical on every render.
    $phi = 0.6180339887;
@endphp

<div class="bkr-motes" aria-hidden="true">
    @for ($i = 0; $i < $count; $i++)
        @php
            // Stepped from $i + 0.5, not $i: at i = 0 every term would other-
            // wise land on zero, giving one degenerate mote pinned to the left
            // edge at minimum size that fades in on load while the rest are
            // already mid-flight.
            $n = $i + 0.5;
            $a = fmod($n * $phi, 1);          // position jitter + duration
            $b = fmod($n * $phi * 3, 1);      // size + peak opacity
            $c = fmod($n * $phi * 7, 1);      // sway + start offset

            // One mote per evenly-spaced lane, nudged within its own lane so the
            // spacing reads as scattered rather than as a ruler.
            $left     = round(($i + $a) / $count * 100, 2);
            $size     = 3 + (int) round($b * 4);              // 3–7px
            $sway     = (int) round(12 + $c * 20) * ($i % 2 ? -1 : 1);
            $peak     = round(0.24 + $b * 0.22, 2);           // 0.24–0.46
            $duration = round(16 + $a * 13, 1);               // 16–29s
            // Negative delay starts each mote mid-flight, so a fresh page load
            // shows a settled drift instead of 40 specks launching in unison.
            $delay    = -round($c * $duration, 1);
        @endphp
        <span style="left:{{ $left }}%;width:{{ $size }}px;height:{{ $size }}px;--bk-mote-sway:{{ $sway }}px;--bk-mote-peak:{{ $peak }};animation-duration:{{ $duration }}s;animation-delay:{{ $delay }}s"></span>
    @endfor
</div>
