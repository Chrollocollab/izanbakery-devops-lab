@extends('layouts.admin')
@section('content')
<div class="main-content-inner">
    <div class="main-content-wrap">
        <div class="flex items-center flex-wrap justify-between gap20 mb-27">
            <h3>Tetapan Infaq</h3>
            <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                <li><a href="{{ route('admin.index') }}"><div class="text-tiny">Dashboard</div></a></li>
                <li><i class="icon-chevron-right"></i></li>
                <li><div class="text-tiny">Infaq</div></li>
            </ul>
        </div>

        @if (session('success'))
        <div class="mb-20" style="padding: 12px 16px; border-radius: 8px; background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7;">
            {{ session('success') }}
        </div>
        @endif

        @php
            $masjids = [
                'Masjid Al-Syakirin', 'Masjid Al Ehsan', 'Masjid Al-Khairiah',
                'Masjid Al-Hidayah, Taman Melawati', 'Masjid Al-Ridhuan, Hulu Klang',
                'Masjid Jamek Pekan Sungai Buloh', 'Masjid Al-Ansar', 'Masjid Jamek FRIM',
                'Masjid Sungai Chinchin', 'Masjid Ar-Rahimah', 'Masjid Al Ikhlas',
                'Masjid Al-Muttaqin, Selayang Pandang', 'Masjid Kampung Gombak',
                'Masjid At-Taqwa, Selayang Baru', 'Masjid Nurul Yaqin',
                'Masjid Al-Faizin, Kepong', 'Masjid As-Sobirin, Lembah Keramat',
                'Masjid Darul Ehsan, Ampang', 'Masjid Al-Hidayah',
                'Masjid Al-Sharif, Simpang Tiga Gombak', 'Masjid Zakaria',
                'Masjid Al-Islahiah, Kuang', 'Masjid Nurul Iman', 'Masjid Jamiatus Solahiah',
                'Masjid Al-Ehsaniah, Batu 16 Rawang', 'Masjid As-Siraj Batu 23 Sungai Nibong',
                'Masjid Bukit Indah', 'Masjid Cahaya Iman, Rawang',
                'Masjid Nurul Iman, Pekan Rawang', 'Masjid Jamek Kg. Nakhoda',
                'Masjid Kg. Klang Gate Baru', 'Masjid Al-Khairiah, AU3',
                'Masjid Jamek Al-Hikmah', 'Masjid Ar-Rahmah, RTB', 'Masjid Ahmadiah',
                'Masjid Jamek Al-Amaniah', 'Masjid Jamek Kg. Gombak Kuang',
                'Masjid At-Taufiqiah, Batu 14, Sungai Burong', 'Masjid Jamek Batu Arang',
                'Masjid Bandar Tasik Puteri', 'Masjid Al-Ittifiqiyah', 'Masjid Lama Batu 6',
                'Masjid At-Taiyyibin', 'Masjid Jamek Sg. Serai Kuang', 'Masjid An-Nur',
                'Masjid Al-Mardhiah', 'Masjid Bukit Antarabangsa',
            ];
            $target  = 125;
            $percent = $target > 0 ? min(100, round(($collected / $target) * 100)) : 0;
        @endphp

        {{-- Status & Giliran --}}
        <div class="wg-box mb-20">
            <h5 class="mb-20">Kemaskini Status Infaq</h5>
            <form action="{{ route('admin.infaq.update') }}" method="POST">
                @csrf

                <div class="mb-20">
                    <label class="body-title mb-10 d-block">Jumlah Terkumpul (RM)</label>
                    <p class="mb-0" style="font-size: 20px; font-weight: 700;">RM {{ number_format($collected, 2) }} <span class="text-tiny text-secondary" style="font-weight:400;">/ RM 125.00</span></p>
                    <p class="text-tiny text-secondary mb-0">Dikira secara automatik daripada sumbangan yang disahkan di bawah.</p>
                    <div class="mt-10" style="max-width: 400px;">
                        <div style="height: 8px; border-radius: 8px; background: #e8ddd1;">
                            <div style="height:8px; border-radius:8px; background:#c8a882; width:{{ $percent }}%;"></div>
                        </div>
                        <p class="text-tiny mt-5 text-secondary">{{ $percent }}% daripada sasaran</p>
                    </div>
                </div>

                <div class="mb-20">
                    <label class="body-title mb-10 d-block">Giliran Masjid Sekarang</label>
                    <p class="text-tiny text-secondary mb-10">Pilih 0 untuk paparkan "Belum Ditentukan".</p>
                    <select name="current_turn"
                            style="padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:14px; max-width:400px; width:100%; background:white;">
                        <option value="0" {{ $currentTurn === 0 ? 'selected' : '' }}>0 — Belum Ditentukan</option>
                        @foreach ($masjids as $i => $masjid)
                        <option value="{{ $i + 1 }}" {{ $currentTurn === ($i + 1) ? 'selected' : '' }}>
                            {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} — {{ $masjid }}
                        </option>
                        @endforeach
                    </select>
                    @error('current_turn')
                        <p class="text-tiny mt-5" style="color:#ef4444;">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="tf-button style-1">Simpan Perubahan</button>
            </form>
        </div>

        {{-- Sumbangan Infaq --}}
        <div class="wg-box mb-20">
            <h5 class="mb-20">Sumbangan Infaq</h5>

            @if ($contributions->isEmpty())
                <p class="text-tiny text-secondary">Belum ada sumbangan direkodkan.</p>
            @else
                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; min-width:720px;">
                        <thead>
                            <tr class="text-tiny text-secondary" style="text-align:left;">
                                <th style="padding:8px;">Tarikh</th>
                                <th style="padding:8px;">Penyumbang</th>
                                <th style="padding:8px;">Jumlah (RM)</th>
                                <th style="padding:8px;">Sumber</th>
                                <th style="padding:8px;">Resit</th>
                                <th style="padding:8px;">Status</th>
                                <th style="padding:8px;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contributions as $contribution)
                            <tr style="border-top:1px solid #f0f0f0;">
                                <td style="padding:8px;" class="text-tiny">{{ $contribution->created_at->format('d M Y') }}</td>
                                <td style="padding:8px;" class="text-tiny">
                                    {{ $contribution->name ?: 'Hamba Allah' }}
                                    @if ($contribution->email)<br><span class="text-secondary">{{ $contribution->email }}</span>@endif
                                </td>
                                <td style="padding:8px;" class="text-tiny fw-semibold">{{ number_format($contribution->amount, 2) }}</td>
                                <td style="padding:8px;" class="text-tiny">{{ $contribution->source === 'manual' ? 'Manual' : 'Dalam Talian' }}</td>
                                <td style="padding:8px;" class="text-tiny">
                                    @if ($contribution->hasReceipt())
                                        <a href="{{ $contribution->receipt_url }}" target="_blank">Lihat</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="padding:8px;">
                                    <span class="badge {{ $contribution->status_badge }}" style="padding:4px 10px; border-radius:6px; font-size:11px; color:#fff;">
                                        {{ ucfirst($contribution->status) }}
                                    </span>
                                </td>
                                <td style="padding:8px;">
                                    @if ($contribution->status === 'pending')
                                        <form action="{{ route('admin.infaq.contribution.verify', $contribution) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="text-tiny" style="border:none;background:none;color:#16a34a;font-weight:600;">Sahkan</button>
                                        </form>
                                        <form action="{{ route('admin.infaq.contribution.reject', $contribution) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Tolak sumbangan ini?')">
                                            @csrf @method('PUT')
                                            <button type="submit" class="text-tiny" style="border:none;background:none;color:#ef4444;font-weight:600;">Tolak</button>
                                        </form>
                                    @else
                                        <span class="text-tiny text-secondary">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <hr style="border-color:#f0f0f0; margin:20px 0;">

            <h6 class="mb-15">Tambah Sumbangan Manual (tunai / luar talian)</h6>
            <form action="{{ route('admin.infaq.contribution.manual') }}" method="POST" class="flex items-end flex-wrap gap10">
                @csrf
                <div>
                    <label class="text-tiny text-secondary mb-5 d-block">Nama (pilihan)</label>
                    <input type="text" name="name" style="padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:14px;">
                </div>
                <div>
                    <label class="text-tiny text-secondary mb-5 d-block">Jumlah (RM)</label>
                    <input type="number" name="amount" step="0.01" min="1" required
                           style="padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:14px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label class="text-tiny text-secondary mb-5 d-block">Nota (pilihan)</label>
                    <input type="text" name="notes" placeholder="cth: Tunai diserah di kedai"
                           style="width:100%; padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:14px;">
                </div>
                <button type="submit" class="tf-button style-1">Tambah</button>
            </form>
            @error('amount')
                <p class="text-tiny mt-5" style="color:#ef4444;">{{ $message }}</p>
            @enderror
        </div>

        {{-- Upload Gambar --}}
        <div class="wg-box mb-20">
            <h5 class="mb-20">Muat Naik Gambar Infaq</h5>
            <form action="{{ route('admin.infaq.photo.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-15">
                    <label class="body-title mb-10 d-block">Masjid</label>
                    <select name="masjid_index"
                            style="padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:14px; max-width:400px; width:100%; background:white;">
                        @foreach ($masjids as $i => $masjid)
                        <option value="{{ $i + 1 }}">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} — {{ $masjid }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-15">
                    <label class="body-title mb-10 d-block">Caption (pilihan)</label>
                    <input type="text" name="caption" placeholder="cth: Pengiriman ke Masjid Al-Syakirin — 15 Jan 2025"
                           style="padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:14px; max-width:500px; width:100%;">
                </div>
                <div class="mb-20">
                    <label class="body-title mb-10 d-block">Gambar (boleh pilih berbilang)</label>
                    <input type="file" name="photos[]" multiple accept="image/*"
                           style="padding:10px 14px; border:1px solid #e0e0e0; border-radius:8px; font-size:14px; max-width:400px; width:100%; background:white;">
                    @error('photos.*')
                        <p class="text-tiny mt-5" style="color:#ef4444;">{{ $message }}</p>
                    @enderror
                    <p class="text-tiny mt-5 text-secondary">Format: JPG, PNG, WEBP. Maks 5MB setiap gambar.</p>
                </div>
                <button type="submit" class="tf-button style-1">Muat Naik</button>
            </form>
        </div>

        {{-- Gallery semua --}}
        @if ($gallery->isNotEmpty())
        <div class="wg-box">
            <h5 class="mb-20">Galeri Semasa</h5>
            @foreach ($gallery as $masjidIndex => $photos)
            @php $masjidLabel = $masjids[$masjidIndex - 1] ?? "Masjid #$masjidIndex"; @endphp
            <div class="mb-20">
                <p class="body-title mb-10">
                    <span style="background:#c8a882; color:#fff; padding:2px 8px; border-radius:4px; font-size:12px; margin-right:8px;">
                        {{ str_pad($masjidIndex, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    {{ $masjidLabel }}
                </p>
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap:10px;">
                    @foreach ($photos as $photo)
                    <div style="position:relative; border-radius:8px; overflow:hidden;">
                        <img src="{{ asset($photo->image_path) }}" alt=""
                             style="width:100%; height:110px; object-fit:cover; display:block;">
                        <form action="{{ route('admin.infaq.photo.delete', $photo->id) }}" method="POST"
                              style="position:absolute; top:5px; right:5px;"
                              onsubmit="return confirm('Padam gambar ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="background:rgba(0,0,0,0.6); color:#fff; border:none; border-radius:50%; width:26px; height:26px; font-size:13px; cursor:pointer; line-height:1;"
                                    title="Padam">✕</button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @if (!$loop->last)<hr style="border-color:#f0f0f0; margin:16px 0;">@endif
            @endforeach
        </div>
        @endif

        <div class="mt-20">
            <a href="{{ route('home.infaq') }}" target="_blank" class="text-tiny" style="color:#c8a882;">
                <i class="icon-eye"></i> Lihat halaman infaq awam →
            </a>
        </div>
    </div>
</div>
@endsection
