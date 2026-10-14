<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Customers only ever see products flagged as visible.
        $productsQuery = Product::visible();

        $categorySlug = $request->query('category');
        if ($categorySlug) {
            $productsQuery->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $products = $productsQuery
            ->orderBy('created_at', 'DESC')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('shop', compact('products', 'categories', 'categorySlug'));
    }

    public function product_details($product_slug)
    {
        // A hidden product must not be reachable by its direct URL either.
        $product = Product::visible()->where('slug', $product_slug)->first();

        if (! $product) {
            abort(404);
        }

        $rproducts = Product::visible()
            ->where('slug', '<>', $product_slug)
            ->take(8)
            ->get();

        return view('details', compact('product', 'rproducts'));
    }

    /**
     * Admin: toggle whether a product is displayed to customers.
     */
    public function toggle_visibility(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->is_visible = ! $product->is_visible;
        $product->save();

        // AJAX toggle from the products table — respond without a page reload.
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['is_visible' => $product->is_visible]);
        }

        $message = $product->is_visible
            ? "\"{$product->name}\" is now visible to customers."
            : "\"{$product->name}\" is now hidden from customers.";

        return back()->with('status', $message);
    }
}
