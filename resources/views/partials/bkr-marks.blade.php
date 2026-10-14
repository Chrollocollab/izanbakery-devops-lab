{{-- Editorial-layer SVG symbols: wheat stalk mark + menu rosette.
     Included once in layouts/app.blade.php; referenced via <use href="#bkr_wheat"> etc.
     Both inherit currentColor so they sit on light or dark grounds. --}}
<svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">

    {{-- Wheat stalk — brand mark, used in the header and footer --}}
    <symbol id="bkr_wheat" viewBox="0 0 48 72">
        <g fill="currentColor">
            {{-- central stem --}}
            <rect x="23.1" y="26" width="1.8" height="46" rx="0.9"/>
            {{-- grain pairs, tapering as they climb toward the tip --}}
            <g>
                <ellipse cx="16.4" cy="46.5" rx="5.4" ry="9.1" transform="rotate(24 16.4 46.5)"/>
                <ellipse cx="31.6" cy="46.5" rx="5.4" ry="9.1" transform="rotate(-24 31.6 46.5)"/>
            </g>
            <g>
                <ellipse cx="17.3" cy="34.2" rx="5.0" ry="8.6" transform="rotate(22 17.3 34.2)"/>
                <ellipse cx="30.7" cy="34.2" rx="5.0" ry="8.6" transform="rotate(-22 30.7 34.2)"/>
            </g>
            <g>
                <ellipse cx="18.4" cy="22.4" rx="4.5" ry="7.9" transform="rotate(20 18.4 22.4)"/>
                <ellipse cx="29.6" cy="22.4" rx="4.5" ry="7.9" transform="rotate(-20 29.6 22.4)"/>
            </g>
            {{-- crowning grain --}}
            <ellipse cx="24" cy="10.4" rx="4.6" ry="9.4"/>
        </g>
    </symbol>

    {{-- Scalloped rosette ring framing the "Menu" badge --}}
    <symbol id="bkr_rosette" viewBox="0 0 200 200">
        <g fill="none" stroke="currentColor" stroke-width="1.3">
            <circle cx="100" cy="100" r="72"/>
            {{-- 24 petals stepped 15° apart, forming the scalloped outer edge --}}
            @for ($i = 0; $i < 24; $i++)
            <circle cx="100" cy="18" r="13" transform="rotate({{ $i * 15 }} 100 100)"/>
            @endfor
        </g>
    </symbol>

</svg>
