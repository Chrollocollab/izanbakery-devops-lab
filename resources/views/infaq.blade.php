@extends('layouts.app')

@push('styles')
<style>
    .payment-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1050;
        align-items: center;
        justify-content: center;
    }
    .payment-modal-backdrop.active {
        display: flex;
    }
    .payment-modal {
        background: #fff;
        border-radius: 16px;
        padding: 2rem;
        max-width: 400px;
        width: 90%;
        position: relative;
        text-align: center;
        box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    }
    #qr-modal .payment-modal {
        max-width: 460px;
    }
    .payment-modal__close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: none;
        border: none;
        font-size: 1.4rem;
        cursor: pointer;
        color: #888;
        line-height: 1;
    }
    .payment-modal h6 {
        color: var(--bk-primary);
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .payment-modal .account-detail {
        background: #fdf6ee;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 0.5rem;
        text-align: left;
    }
    .payment-modal .account-detail dt {
        font-size: 0.78rem;
        font-weight: 600;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.15rem;
    }
    .payment-modal .account-detail dd {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--bk-text-dark, #2c2c2c);
        margin: 0;
    }
    .qr-placeholder {
        width: 260px;
        height: 260px;
        border: 2px dashed #e0d4c3;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        background: #fdf6ee;
        color: #bbb;
        font-size: 0.85rem;
    }
    .qr-placeholder img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 6px;
    }
    .btn-payment {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        border: 2px solid var(--bk-primary);
        background: #fff;
        color: var(--bk-primary);
        transition: all 0.2s;
    }
    .btn-payment:hover {
        background: var(--bk-primary);
        color: #fff;
    }
    .btn-payment.filled {
        background: var(--bk-primary);
        color: #fff;
    }
    .btn-payment.filled:hover {
        background: var(--bk-primary-dark);
        border-color: var(--bk-primary-dark);
    }
</style>
@endpush

@section('content')
<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            <h1 class="mb-4 fw-bold" style="font-family: 'Playfair Display', serif;">{{ __('messages.infaq.page_title') }}</h1>

            @if (session('success'))
            <div class="alert" role="alert" style="background-color:#f9f5f0; border:1px solid #c8a882; color:#6b5637; border-radius: 10px;">
                {{ session('success') }}
            </div>
            @endif

            {{-- Definisi --}}
            <div class="mb-5">
                <h4 class="fw-semibold mb-3">{{ __('messages.infaq.definition_title') }}</h4>
                <p class="text-secondary" style="line-height: 1.9;">
                    {!! __('messages.infaq.definition_text') !!}
                </p>
            </div>

            {{-- Objektif --}}
            <div class="mb-5">
                <h4 class="fw-semibold mb-3">{{ __('messages.infaq.objective_title') }}</h4>
                <p class="text-secondary" style="line-height: 1.9;">
                    {!! __('messages.infaq.objective_text_1') !!}
                </p>
                <p class="text-secondary" style="line-height: 1.9;">
                    {{ __('messages.infaq.objective_text_2') }}
                </p>
            </div>

            @php
                $target   = 125;
                $percent  = $target > 0 ? min(100, round(($collected / $target) * 100)) : 0;

                $masjids = [
                    'Masjid Al-Syakirin',
                    'Masjid Al Ehsan',
                    'Masjid Al-Khairiah',
                    'Masjid Al-Hidayah, Taman Melawati',
                    'Masjid Al-Ridhuan, Hulu Klang',
                    'Masjid Jamek Pekan Sungai Buloh',
                    'Masjid Al-Ansar',
                    'Masjid Jamek FRIM',
                    'Masjid Sungai Chinchin',
                    'Masjid Ar-Rahimah',
                    'Masjid Al Ikhlas',
                    'Masjid Al-Muttaqin, Selayang Pandang',
                    'Masjid Kampung Gombak',
                    'Masjid At-Taqwa, Selayang Baru',
                    'Masjid Nurul Yaqin',
                    'Masjid Al-Faizin, Kepong',
                    'Masjid As-Sobirin, Lembah Keramat',
                    'Masjid Darul Ehsan, Ampang',
                    'Masjid Al-Hidayah',
                    'Masjid Al-Sharif, Simpang Tiga Gombak',
                    'Masjid Zakaria',
                    'Masjid Al-Islahiah, Kuang',
                    'Masjid Nurul Iman',
                    'Masjid Jamiatus Solahiah',
                    'Masjid Al-Ehsaniah, Batu 16 Rawang',
                    'Masjid As-Siraj Batu 23 Sungai Nibong',
                    'Masjid Bukit Indah',
                    'Masjid Cahaya Iman, Rawang',
                    'Masjid Nurul Iman, Pekan Rawang',
                    'Masjid Jamek Kg. Nakhoda',
                    'Masjid Kg. Klang Gate Baru',
                    'Masjid Al-Khairiah, AU3',
                    'Masjid Jamek Al-Hikmah',
                    'Masjid Ar-Rahmah, RTB',
                    'Masjid Ahmadiah',
                    'Masjid Jamek Al-Amaniah',
                    'Masjid Jamek Kg. Gombak Kuang',
                    'Masjid At-Taufiqiah, Batu 14, Sungai Burong',
                    'Masjid Jamek Batu Arang',
                    'Masjid Bandar Tasik Puteri',
                    'Masjid Al-Ittifiqiyah',
                    'Masjid Lama Batu 6',
                    'Masjid At-Taiyyibin',
                    'Masjid Jamek Sg. Serai Kuang',
                    'Masjid An-Nur',
                    'Masjid Al-Mardhiah',
                    'Masjid Bukit Antarabangsa',
                ];

                $currentMasjid = ($currentTurn > 0 && isset($masjids[$currentTurn - 1]))
                    ? $masjids[$currentTurn - 1]
                    : null;
            @endphp

            {{-- Progress Bar --}}
            <div class="p-4 rounded-3 mb-5" style="background-color: #f9f5f0; border-left: 4px solid #c8a882;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="mb-0 fw-semibold">{{ __('messages.infaq.collection_status') }}</p>
                    <span class="text-secondary small">{{ $percent }}%</span>
                </div>
                <div class="progress mb-3" style="height: 12px; border-radius: 8px; background-color: #e8ddd1;">
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ $percent }}%; background-color: #c8a882; border-radius: 8px;"
                         aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <div>
                        <span class="fs-4 fw-bold">RM {{ number_format($collected, 2) }}</span>
                        <span class="text-secondary ms-1">/ RM {{ number_format($target, 2) }}</span>
                    </div>
                    <span class="text-secondary small">{{ __('messages.infaq.boxes_of_cake') }}</span>
                </div>
            </div>

            {{-- Contribute --}}
            <div class="mb-5">
                <h4 class="fw-semibold mb-3">{{ __('messages.infaq.contribute_title') }}</h4>
                <p class="text-secondary small mb-3">{{ __('messages.infaq.contribute_desc') }}</p>

                {{-- QR & Account buttons --}}
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button type="button" class="btn-payment filled" id="btn-show-qr">
                        <i class="bi bi-qr-code"></i>
                        {{ __('messages.infaq.show_qr') }}
                    </button>
                    <button type="button" class="btn-payment" id="btn-show-account">
                        <i class="bi bi-bank2"></i>
                        {{ __('messages.infaq.show_account') }}
                    </button>
                </div>

                <form action="{{ route('home.infaq.contribute') }}" method="POST" enctype="multipart/form-data"
                      class="p-4 rounded-3" style="background-color: #faf8f5; border: 1px solid #e8ddd1;">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ __('messages.infaq.your_name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control"
                                   placeholder="{{ __('messages.infaq.name_placeholder') }}">
                            @error('name')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ __('messages.infaq.amount_rm') }}</label>
                            <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="1"
                                   class="form-control" placeholder="0.00" required>
                            @error('amount')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ __('messages.infaq.email_optional') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                            @error('email')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ __('messages.infaq.phone_optional') }}</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                            @error('phone')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">{{ __('messages.infaq.upload_receipt') }}</label>
                            <input type="file" name="receipt" accept="image/*,.pdf" class="form-control" required>
                            <p class="text-secondary mt-1" style="font-size:0.72rem;">{{ __('messages.infaq.receipt_hint') }}</p>
                            @error('receipt')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn mt-4" style="background-color:#c8a882;color:#fff;padding:10px 26px;border-radius:8px;font-weight:600;">
                        {{ __('messages.infaq.submit_contribution') }}
                    </button>
                </form>
            </div>

            {{-- Giliran Masjid Sekarang --}}
            <div class="mb-5">
                <h4 class="fw-semibold mb-3">{{ __('messages.infaq.current_turn_title') }}</h4>
                @if ($currentMasjid)
                <div class="p-4 rounded-3 d-flex align-items-center gap-3"
                     style="background-color: #f9f5f0; border: 1px solid #c8a882;">
                    <div style="font-size: 2rem;">🕌</div>
                    <div>
                        <p class="mb-0 text-secondary small">{{ __('messages.infaq.no_label') }} {{ str_pad($currentTurn, 2, '0', STR_PAD_LEFT) }}</p>
                        <p class="mb-0 fw-semibold" style="font-size: 1.05rem;">{{ $currentMasjid }}</p>
                    </div>
                </div>
                @else
                <div class="p-4 rounded-3 d-flex align-items-center gap-3"
                     style="background-color: #f0f0f0; border: 1px dashed #ccc; opacity: 0.6; pointer-events: none; user-select: none;">
                    <div style="font-size: 2rem;">🕌</div>
                    <div>
                        <p class="mb-0 fw-semibold text-secondary">{{ __('messages.infaq.not_determined') }}</p>
                        <p class="mb-0 text-secondary small">{{ __('messages.infaq.not_determined_desc') }}</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Senarai Masjid Terlibat --}}
            <div class="mb-5">
                <h4 class="fw-semibold mb-3">{{ __('messages.infaq.list_title') }}</h4>
                <p class="text-secondary small mb-3">{{ __('messages.infaq.list_desc') }}</p>
                <div class="row row-cols-1 row-cols-md-2 g-2">
                    @foreach ($masjids as $i => $masjid)
                    @php $isActive = ($currentTurn > 0 && ($i + 1) === $currentTurn); @endphp
                    <div class="col">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-2"
                             style="{{ $isActive ? 'background-color: #f9f5f0; border: 1px solid #c8a882;' : 'background-color: #faf8f5;' }}">
                            <span class="{{ $isActive ? 'fw-semibold' : 'text-secondary' }} small" style="min-width: 28px;">
                                {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}.
                            </span>
                            <span class="small {{ $isActive ? 'fw-semibold' : '' }}">{{ $masjid }}</span>
                            @if ($isActive)
                                <span class="ms-auto badge" style="background-color: #c8a882; font-size: 0.7rem;">{{ __('messages.infaq.now_badge') }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Gallery Bukti Infaq --}}
            <div class="mb-5">
                <h4 class="fw-semibold mb-1">{{ __('messages.infaq.gallery_title') }}</h4>
                <p class="text-secondary small mb-4">{{ __('messages.infaq.gallery_desc') }}</p>

                @if ($gallery->isEmpty())
                <div class="p-4 rounded-3 text-center" style="background-color: #f9f5f0; border: 1px dashed #c8a882; color: #8a7860;">
                    {{ __('messages.infaq.gallery_empty') }}
                </div>
                @else
                <style>
                    .infaq-accordion { border: 1px solid #e8ddd1; border-radius: 10px; overflow: hidden; }
                    .infaq-accordion details + details { border-top: 1px solid #e8ddd1; }
                    .infaq-accordion summary {
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        padding: 14px 18px;
                        cursor: pointer;
                        list-style: none;
                        background: #faf8f5;
                        user-select: none;
                        transition: background 0.15s;
                    }
                    .infaq-accordion summary::-webkit-details-marker { display: none; }
                    .infaq-accordion summary:hover { background: #f5efe6; }
                    .infaq-accordion details[open] summary { background: #f5efe6; }
                    .infaq-accordion .accordion-arrow {
                        font-size: 11px;
                        color: #c8a882;
                        transition: transform 0.2s ease;
                        display: inline-block;
                        line-height: 1;
                    }
                    .infaq-accordion details[open] .accordion-arrow { transform: rotate(0deg); }
                    .infaq-accordion details:not([open]) .accordion-arrow { transform: rotate(-90deg); }
                    .infaq-accordion .accordion-body { padding: 16px 18px 20px; background: #fff; }
                    .gallery-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                        gap: 10px;
                    }
                    .gallery-grid .gallery-thumb {
                        display: block;
                        border-radius: 8px;
                        overflow: hidden;
                        aspect-ratio: 4/3;
                        cursor: zoom-in;
                    }
                    .gallery-grid img {
                        width: 100%; height: 100%;
                        object-fit: cover;
                        transition: transform 0.3s ease;
                    }
                    .gallery-grid .gallery-thumb:hover img { transform: scale(1.05); }

                    /* Lightbox */
                    #infaq-lightbox {
                        display: none;
                        position: fixed;
                        inset: 0;
                        z-index: 9999;
                        background: rgba(0,0,0,0.85);
                        align-items: center;
                        justify-content: center;
                        padding: 20px;
                    }
                    #infaq-lightbox.active { display: flex; }
                    #infaq-lightbox img {
                        max-width: 90vw;
                        max-height: 85vh;
                        object-fit: contain;
                        border-radius: 8px;
                        box-shadow: 0 8px 40px rgba(0,0,0,0.5);
                    }
                    #infaq-lightbox-close {
                        position: absolute;
                        top: 18px; right: 22px;
                        color: #fff;
                        font-size: 28px;
                        cursor: pointer;
                        line-height: 1;
                        opacity: 0.8;
                        background: none; border: none;
                    }
                    #infaq-lightbox-close:hover { opacity: 1; }
                    #infaq-lightbox-caption {
                        position: absolute;
                        bottom: 20px;
                        left: 50%; transform: translateX(-50%);
                        color: rgba(255,255,255,0.8);
                        font-size: 0.82rem;
                        text-align: center;
                        max-width: 80%;
                    }
                    #lightbox-prev, #lightbox-next {
                        position: absolute;
                        top: 50%; transform: translateY(-50%);
                        background: rgba(255,255,255,0.15);
                        border: none;
                        color: #fff;
                        font-size: 22px;
                        width: 44px; height: 44px;
                        border-radius: 50%;
                        cursor: pointer;
                        display: flex; align-items: center; justify-content: center;
                        transition: background 0.2s;
                    }
                    #lightbox-prev:hover, #lightbox-next:hover { background: rgba(255,255,255,0.3); }
                    #lightbox-prev { left: 18px; }
                    #lightbox-next { right: 18px; }
                </style>

                {{-- Lightbox overlay --}}
                <div id="infaq-lightbox">
                    <button id="infaq-lightbox-close">✕</button>
                    <button id="lightbox-prev">&#8249;</button>
                    <img id="lightbox-img" src="" alt="">
                    <button id="lightbox-next">&#8250;</button>
                    <div id="infaq-lightbox-caption"></div>
                </div>

                <div class="infaq-accordion">
                    @foreach ($gallery as $masjidIndex => $photos)
                    @php $masjidLabel = $masjids[$masjidIndex - 1] ?? __('messages.infaq.mosque_fallback', ['index' => $masjidIndex]); @endphp
                    <details open>
                        <summary>
                            <span class="accordion-arrow">▼</span>
                            <span style="width:1px; height:18px; background:#c8a882; display:inline-block; flex-shrink:0;"></span>
                            <span class="small fw-semibold" style="color:#c8a882; min-width:22px;">
                                {{ str_pad($masjidIndex, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="fw-semibold" style="font-size: 0.9rem;">{{ $masjidLabel }}</span>
                            <span class="text-secondary small ms-auto">{{ $photos->count() }} {{ __('messages.infaq.photos_count') }}</span>
                        </summary>
                        <div class="accordion-body">
                            <div class="gallery-grid">
                                @foreach ($photos as $photo)
                                <div>
                                    <span class="gallery-thumb"
                                          data-src="{{ asset($photo->image_path) }}"
                                          data-caption="{{ $photo->caption ?? $masjidLabel }}">
                                        <img src="{{ asset($photo->image_path) }}"
                                             alt="{{ $photo->caption ?? $masjidLabel }}">
                                    </span>
                                    @if ($photo->caption)
                                    <p class="text-secondary mt-1 mb-0" style="font-size: 0.72rem;">{{ $photo->caption }}</p>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </details>
                    @endforeach
                </div>

                <script>
                    (function () {
                        const lightbox  = document.getElementById('infaq-lightbox');
                        const lbImg     = document.getElementById('lightbox-img');
                        const lbCaption = document.getElementById('infaq-lightbox-caption');
                        const btnClose  = document.getElementById('infaq-lightbox-close');
                        const btnPrev   = document.getElementById('lightbox-prev');
                        const btnNext   = document.getElementById('lightbox-next');

                        let allThumbs = [];
                        let current   = 0;

                        function open(index) {
                            current = index;
                            const thumb = allThumbs[current];
                            lbImg.src        = thumb.dataset.src;
                            lbImg.alt        = thumb.dataset.caption;
                            lbCaption.textContent = thumb.dataset.caption;
                            lightbox.classList.add('active');
                            document.body.style.overflow = 'hidden';
                        }

                        function close() {
                            lightbox.classList.remove('active');
                            document.body.style.overflow = '';
                        }

                        function prev() { open((current - 1 + allThumbs.length) % allThumbs.length); }
                        function next() { open((current + 1) % allThumbs.length); }

                        document.addEventListener('click', function (e) {
                            const thumb = e.target.closest('.gallery-thumb');
                            if (thumb) {
                                allThumbs = Array.from(document.querySelectorAll('.gallery-thumb'));
                                open(allThumbs.indexOf(thumb));
                            }
                        });

                        btnClose.addEventListener('click', close);
                        btnPrev.addEventListener('click', prev);
                        btnNext.addEventListener('click', next);

                        lightbox.addEventListener('click', function (e) {
                            if (e.target === lightbox) close();
                        });

                        document.addEventListener('keydown', function (e) {
                            if (!lightbox.classList.contains('active')) return;
                            if (e.key === 'Escape')      close();
                            if (e.key === 'ArrowLeft')   prev();
                            if (e.key === 'ArrowRight')  next();
                        });
                    })();
                </script>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- QR Code Modal --}}
<div class="payment-modal-backdrop" id="qr-modal">
    <div class="payment-modal">
        <button class="payment-modal__close" onclick="closeInfaqModal('qr-modal')">&times;</button>
        <h6>{{ __('messages.infaq.scan_to_pay') }}</h6>
        <div class="qr-placeholder">
            <img src="{{ asset('assets/images/qr.png') }}" alt="{{ __('messages.infaq.qr_alt') }}">
        </div>
        <p style="font-size:0.85rem;color:#888;">
            {{ __('messages.infaq.scan_instruction') }}
        </p>
    </div>
</div>

{{-- Account Number Modal --}}
<div class="payment-modal-backdrop" id="account-modal">
    <div class="payment-modal">
        <button class="payment-modal__close" onclick="closeInfaqModal('account-modal')">&times;</button>
        <h6>{{ __('messages.infaq.bank_transfer_details') }}</h6>
        <dl class="account-detail">
            <dt>{{ __('messages.infaq.bank_label') }}</dt>
            <dd>Maybank</dd>
        </dl>
        <dl class="account-detail">
            <dt>{{ __('messages.infaq.account_name_label') }}</dt>
            <dd>Rohanizah Binti Abdullah</dd>
        </dl>
        <dl class="account-detail">
            <dt>{{ __('messages.infaq.account_number_label') }}</dt>
            <dd>1625 0826 7241</dd>
        </dl>
        <p style="font-size:0.82rem;color:#aaa;margin-top:0.75rem;">
            {{ __('messages.infaq.after_transfer_note') }}
        </p>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        function openInfaqModal(id) {
            document.getElementById(id).classList.add('active');
        }
        window.closeInfaqModal = function (id) {
            document.getElementById(id).classList.remove('active');
        };

        var btnQr = document.getElementById('btn-show-qr');
        var btnAccount = document.getElementById('btn-show-account');
        if (btnQr) btnQr.addEventListener('click', function () { openInfaqModal('qr-modal'); });
        if (btnAccount) btnAccount.addEventListener('click', function () { openInfaqModal('account-modal'); });

        document.querySelectorAll('.payment-modal-backdrop').forEach(function (backdrop) {
            backdrop.addEventListener('click', function (e) {
                if (e.target === this) closeInfaqModal(this.id);
            });
        });
    })();
</script>
@endpush
@endsection
