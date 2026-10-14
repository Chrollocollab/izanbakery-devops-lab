<?php

namespace Database\Seeders;

use App\Models\InfaqGallery;
use Illuminate\Database\Seeder;

class InfaqGallerySeeder extends Seeder
{
    // Mosques that have already received deliveries (with sample dates)
    private array $delivered = [
        1 => ['name' => 'Masjid Al-Syakirin',        'date' => '15 Januari 2025',  'photos' => 3],
        2 => ['name' => 'Masjid Al Ehsan',             'date' => '22 Januari 2025',  'photos' => 3],
        3 => ['name' => 'Masjid Al-Khairiah',          'date' => '5 Februari 2025',  'photos' => 2],
    ];

    // Warm bakery palette — one accent colour per mosque
    private array $colors = [
        1 => ['bg' => [212, 163, 115], 'text' => [255, 255, 255]],
        2 => ['bg' => [180, 120,  80], 'text' => [255, 255, 255]],
        3 => ['bg' => [140,  90,  60], 'text' => [255, 255, 255]],
    ];

    // Real photos (uploaded to assets/images) used for the first mosque instead of a generated placeholder.
    private array $realPhotos = [
        'seederInfaq1.jpeg',
        'seederinfaq.jpeg',
        'infaqSeeder2.jpg',
    ];

    public function run(): void
    {
        $dir = public_path('assets/images/infaq');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($this->delivered as $index => $info) {
            if ($index === 1) {
                $this->seedFromRealPhotos($dir, $index, $info);
                continue;
            }

            for ($p = 1; $p <= $info['photos']; $p++) {
                $filename = "masjid-{$index}-{$p}.jpg";
                $path     = "{$dir}/{$filename}";

                if (!file_exists($path)) {
                    $this->generateImage($path, $index, $info['name'], $info['date'], $p);
                }

                InfaqGallery::firstOrCreate(
                    ['masjid_index' => $index, 'image_path' => "assets/images/infaq/{$filename}"],
                    ['caption' => "Pengiriman ke {$info['name']} — {$info['date']}"]
                );
            }
        }
    }

    /**
     * Copy the real uploaded photos into the infaq gallery folder for a mosque,
     * replacing any stale placeholder files left over from a previous seed.
     */
    private function seedFromRealPhotos(string $dir, int $index, array $info): void
    {
        foreach (glob("{$dir}/masjid-{$index}-*") ?: [] as $stale) {
            unlink($stale);
        }
        InfaqGallery::where('masjid_index', $index)->delete();

        foreach ($this->realPhotos as $i => $sourceName) {
            $sourcePath = public_path("assets/images/{$sourceName}");
            if (!file_exists($sourcePath)) {
                continue;
            }

            $photoNum  = $i + 1;
            $extension = pathinfo($sourceName, PATHINFO_EXTENSION);
            $filename  = "masjid-{$index}-{$photoNum}.{$extension}";

            copy($sourcePath, "{$dir}/{$filename}");

            InfaqGallery::create([
                'masjid_index' => $index,
                'image_path'   => "assets/images/infaq/{$filename}",
                'caption'      => "Pengiriman ke {$info['name']} — {$info['date']}",
            ]);
        }
    }

    private function generateImage(string $path, int $index, string $masjidName, string $date, int $photoNum): void
    {
        $w = 800;
        $h = 600;
        $im = imagecreatetruecolor($w, $h);

        [$r, $g, $b]   = $this->colors[$index]['bg'];
        [$tr, $tg, $tb] = $this->colors[$index]['text'];

        // Slightly vary shade per photo number
        $shade = ($photoNum - 1) * 12;
        $bg    = imagecolorallocate($im, min(255, $r + $shade), min(255, $g + $shade), min(255, $b + $shade));
        $fg    = imagecolorallocate($im, $tr, $tg, $tb);
        $accent = imagecolorallocate($im, 255, 255, 255);

        imagefill($im, 0, 0, $bg);

        // Decorative border
        imagerectangle($im, 20, 20, $w - 20, $h - 20, imagecolorallocatealpha($im, 255, 255, 255, 80));
        imagerectangle($im, 24, 24, $w - 24, $h - 24, imagecolorallocatealpha($im, 255, 255, 255, 100));

        // Cake emoji area (simple circle placeholder)
        $cx = $w / 2;
        $cy = $h / 2 - 60;
        imagefilledellipse($im, (int)$cx, (int)$cy, 140, 140, imagecolorallocate($im, 255, 255, 255));
        imagefilledellipse($im, (int)$cx, (int)$cy, 130, 130, $bg);

        // Label text using built-in font (size 5)
        $label1 = 'PENGIRIMAN KEK PISANG';
        $label2 = strtoupper($masjidName);
        $label3 = $date;
        $label4 = "Foto " . $photoNum;

        $font = 5; // largest built-in font
        $charW = imagefontwidth($font);

        imagestring($im, $font, (int)(($w - strlen($label1) * $charW) / 2), 260, $label1, $accent);
        imagestring($im, $font, (int)(($w - strlen($label2) * $charW) / 2), 295, $label2, $fg);

        $font2 = 3;
        $charW2 = imagefontwidth($font2);
        imagestring($im, $font2, (int)(($w - strlen($label3) * $charW2) / 2), 335, $label3, $accent);
        imagestring($im, $font2, (int)(($w - strlen($label4) * $charW2) / 2), 360, $label4, $accent);

        // Izan's Bakery watermark
        $watermark = "Izan's Bakery";
        imagestring($im, 2, (int)(($w - strlen($watermark) * imagefontwidth(2)) / 2), $h - 50, $watermark, $accent);

        imagejpeg($im, $path, 85);
        imagedestroy($im);
    }
}
