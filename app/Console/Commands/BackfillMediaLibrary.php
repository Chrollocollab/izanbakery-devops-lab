<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class BackfillMediaLibrary extends Command
{
    protected $signature = 'media:backfill {--dry-run} {--only=}';

    protected $description = 'Import existing public/uploads/* files into the media library, non-destructively.';

    private array $missing = [];

    private array $imported = [];

    private array $skipped = [];

    public function handle(): int
    {
        $only = $this->option('only');
        $tasks = [
            'brands' => fn () => $this->backfillSingleFile(Brand::query(), 'image', 'brands', 'brand'),
            'categories' => fn () => $this->backfillSingleFile(Category::query(), 'image', 'categories', 'category'),
            'products' => fn () => $this->backfillSingleFile(Product::query(), 'image', 'products', 'product'),
            'gallery' => fn () => $this->backfillProductGallery(),
            'receipts' => fn () => $this->backfillSingleFile(Payment::query(), 'receipt_path', 'receipts', 'receipt'),
        ];

        foreach ($tasks as $name => $task) {
            if ($only && $only !== $name) {
                continue;
            }

            $this->info("Backfilling {$name}...");
            $task();
        }

        $this->newLine();
        $this->info('Imported: '.count($this->imported));
        $this->info('Skipped (already backfilled): '.count($this->skipped));

        if ($this->missing) {
            $this->warn('Missing physical files: '.count($this->missing));
            foreach ($this->missing as $row) {
                $this->line("  - {$row}");
            }
        }

        return self::SUCCESS;
    }

    private function backfillSingleFile($query, string $column, string $uploadsDir, string $collection): void
    {
        $query->whereNotNull($column)->where($column, '!=', '')
            ->chunkById(50, function ($models) use ($column, $uploadsDir, $collection) {
                foreach ($models as $model) {
                    $this->importOne($model, $model->{$column}, $uploadsDir, $collection);
                }
            });
    }

    private function backfillProductGallery(): void
    {
        Product::query()->whereNotNull('images')->where('images', '!=', '')
            ->chunkById(50, function ($products) {
                foreach ($products as $product) {
                    $filenames = array_filter(array_map('trim', explode(',', (string) $product->images)));

                    foreach ($filenames as $filename) {
                        $this->importOne($product, $filename, 'products', 'gallery', matchByFileName: true);
                    }
                }
            });
    }

    private function importOne(Model $model, string $filename, string $uploadsDir, string $collection, bool $matchByFileName = false): void
    {
        $label = class_basename($model)." #{$model->getKey()} ({$filename})";

        $alreadyImported = $matchByFileName
            ? $model->getMedia($collection)->contains('file_name', $filename)
            : $model->getMedia($collection)->isNotEmpty();

        if ($alreadyImported) {
            $this->skipped[] = $label;

            return;
        }

        $path = public_path("uploads/{$uploadsDir}/{$filename}");

        if (! File::exists($path)) {
            $this->missing[] = $label;

            return;
        }

        if ($this->option('dry-run')) {
            $this->imported[] = $label;

            return;
        }

        try {
            $model->addMedia($path)->preservingOriginal()->toMediaCollection($collection);
            $this->imported[] = $label;
        } catch (\Throwable $e) {
            $this->missing[] = "{$label} — error: {$e->getMessage()}";
        }
    }
}
