<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InfaqContribution;
use App\Models\InfaqGallery;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredSlugs = ['kek-pisang-original', 'kaswi-pandan', 'popia-sira'];

        $products = Product::with('category')
            ->visible()
            ->whereIn('slug', $featuredSlugs)
            ->get()
            ->sortBy(fn ($product) => array_search($product->slug, $featuredSlugs))
            ->values();

        $categories = Category::with(['products' => function($query) {
            $query->visible()
                  ->where('stock_status', 'instock')
                  ->orderBy('created_at', 'DESC');
        }])->get();

        return view('index', compact('products', 'categories'));
    }

    public function infaq()
    {
        $currentTurn = (int) Setting::get('infaq_current_turn', 0);
        $collected   = (float) InfaqContribution::verified()->sum('amount');
        $gallery     = InfaqGallery::orderBy('masjid_index')->orderBy('id')->get()->groupBy('masjid_index');

        return view('infaq', compact('currentTurn', 'collected', 'gallery'));
    }

    /**
     * Accept an infaq contribution from a public visitor. The contribution
     * starts as `pending` — an admin verifies the receipt before it counts
     * toward the collected total, mirroring how order payments are confirmed.
     */
    public function infaqContribute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'nullable|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'amount'  => 'required|numeric|min:1|max:100000',
            'receipt' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $contribution = InfaqContribution::create([
            'user_id' => $request->user()?->id,
            'name'    => $validated['name'] ?? null,
            'email'   => $validated['email'] ?? null,
            'phone'   => $validated['phone'] ?? null,
            'amount'  => $validated['amount'],
            'status'  => InfaqContribution::STATUS_PENDING,
            'source'  => InfaqContribution::SOURCE_ONLINE,
        ]);

        $contribution->addMediaFromRequest('receipt')->toMediaCollection('receipt');

        return redirect()->route('home.infaq')
            ->with('success', 'Thank you! Your contribution has been received.');
    }
}
