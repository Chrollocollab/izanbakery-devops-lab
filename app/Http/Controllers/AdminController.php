<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InfaqContribution;
use App\Models\InfaqGallery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class AdminController extends Controller
{
    public function index(Request $request)
    {
        $recentOrders = Order::with(['items', 'user'])
            ->orderBy('created_at', 'DESC')
            ->paginate(15);
        
        // Quick stats for today
        $todayStats = [
            'orders' => Order::whereDate('created_at', today())->count(),
            'revenue' => Order::whereDate('created_at', today())->completed()->sum('total'),
            'pending' => Order::active()->count(),
        ];
        
        $statuses = Order::getStatuses();
        
        return view('admin.index', compact('recentOrders', 'todayStats', 'statuses'));
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));http://localhost:8000/
    }

    public function brand_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);  
        
        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $brand->save();

        if ($request->hasFile('image')) {
            $brand->addMediaFromRequest('image')->toMediaCollection('brand');
        }

        return redirect()->route('admin.brands')->with('status', 'Brand has been update succesfully');
    }

    public function brand_store(Request $request){
        $request->validate([
            'name' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $brand->save();

        if ($request->hasFile('image')) {
            $brand->addMediaFromRequest('image')->toMediaCollection('brand');
        }

        return redirect()->route('admin.brands')->with('status', 'Brand has been added succesfully');
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        $brand->delete();
        return redirect()->route('admin.brands')->with('status',"Brand has been deleted succesfully");
    }

    public function categories()
    {
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view('admin.categories',compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->save();

        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')->toMediaCollection('category');
        }

        return redirect()->route('admin.categories')->with('status', 'Categories has been added succesfully');
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit',compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);  
        
        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->save();

        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')->toMediaCollection('category');
        }

        return redirect()->route('admin.categories')->with('status', 'Category has been update succesfully');
    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        $category->delete();
        return redirect()->route('admin.categories')->with('status','Category has been deleted successfully!');
    }

    public function products()
    {
        $products = Product::orderBy('created_at','DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }

    public function product_add()
    {
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-add',compact('categories','brands'));
    }

    public function product_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:products',
            'short_description' => 'required|string',
            'description' => 'required|string',
            'regular_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'stock_status' => 'required|in:instock,outofstock',
            'is_visible' => 'nullable|in:0,1',
            'quantity' => 'required|integer',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required|integer|exists:categories,id',
            'brand_id' => 'required|integer|exists:brands,id'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->stock_status = $request->stock_status;
        $product->is_visible = $request->input('is_visible', 1);
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->save();

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('product');
        }

        if ($request->hasFile('images')) {
            $allowedFileExtension = ['jpg','png','jpeg'];
            foreach ($request->file('images') as $file) {
                if (in_array($file->getClientOriginalExtension(), $allowedFileExtension)) {
                    $product->addMedia($file)->toMediaCollection('gallery');
                }
            }
        }

        return redirect()->route('admin.products')->with('status','Product has been added successfully');
    }

    public function product_edit($id)
    {
        $product = Product::find($id);
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view ('admin.product-edit',compact('product','categories','brands'));    
    }

    public function product_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,'.$request->id,
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'stock_status' => 'required',
            'is_visible' => 'nullable|in:0,1',
            'quantity' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
            'category_id' => 'required',
            'brand_id' => 'required'
        ]);

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->slug);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->stock_status = $request->stock_status;
        $product->is_visible = $request->input('is_visible', 1);
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->save();

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('product');
        }

        if ($request->hasFile('images')) {
            $product->clearMediaCollection('gallery');

            $allowedFileExtension = ['jpg','png','jpeg'];
            foreach ($request->file('images') as $file) {
                if (in_array($file->getClientOriginalExtension(), $allowedFileExtension)) {
                    $product->addMedia($file)->toMediaCollection('gallery');
                }
            }
        }

        return redirect()->route('admin.products')->with('status', 'Product has been updated successfully!');
    }

    public function product_delete($id)
    {
        $product = Product::find($id);
        $product->delete();
        return redirect()->route('admin.products')->with('status','Product has been deleted successfully!');
    }

    /**
     * AJAX search for admin Select2
     */
    public function search(Request $request)
    {
        $term = $request->get('term', '');
        $results = [];
        $addedOrderIds = [];

        if (strlen($term) >= 1) {
            // Search Orders by customer name (case-insensitive with ILIKE for PostgreSQL)
            $ordersByCustomer = Order::where(function($query) use ($term) {
                    $query->where('name', 'ILIKE', "{$term}%")
                          ->orWhere('name', 'ILIKE', "% {$term}%");
                })
                ->with('items')
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get();
            
            foreach ($ordersByCustomer as $order) {
                $addedOrderIds[] = $order->id;
                $productNames = $order->items->pluck('product_name')->take(3)->implode(', ');
                if ($order->items->count() > 3) {
                    $productNames .= '...';
                }
                
                $results[] = [
                    'id' => 'customer-' . $order->id,
                    'text' => $order->name,
                    'subtext' => $productNames ?: 'No items',
                    'type' => 'Customer',
                    'url' => route('admin.order.show', $order->id),
                ];
            }

            // Search Orders by product name ordered (case-insensitive with ILIKE)
            $ordersByProduct = Order::whereHas('items', function($q) use ($term) {
                    $q->where('product_name', 'ILIKE', "{$term}%")
                      ->orWhere('product_name', 'ILIKE', "% {$term}%");
                })
                ->whereNotIn('id', $addedOrderIds)
                ->with('items')
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get();
            
            foreach ($ordersByProduct as $order) {
                $matchingProducts = $order->items->filter(function($item) use ($term) {
                    return stripos($item->product_name, $term) !== false;
                })->pluck('product_name')->implode(', ');
                
                $results[] = [
                    'id' => 'product-order-' . $order->id,
                    'text' => $order->name,
                    'subtext' => $matchingProducts,
                    'type' => 'Product',
                    'url' => route('admin.order.show', $order->id),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    public function infaq()
    {
        $currentTurn   = (int) Setting::get('infaq_current_turn', 0);
        $collected     = (float) InfaqContribution::verified()->sum('amount');
        $gallery       = InfaqGallery::orderBy('masjid_index')->orderBy('id')->get()->groupBy('masjid_index');
        $contributions = InfaqContribution::orderByRaw("status = 'pending' desc")->latest()->get();

        return view('admin.infaq', compact('currentTurn', 'collected', 'gallery', 'contributions'));
    }

    public function infaq_update(Request $request)
    {
        $request->validate([
            'current_turn' => 'required|integer|min:0|max:47',
        ]);

        Setting::set('infaq_current_turn', $request->current_turn);

        return redirect()->route('admin.infaq')->with('success', 'Tetapan infaq berjaya dikemaskini.');
    }

    /**
     * Log an offline/cash contribution directly as verified — it never went
     * through the public form so there's no receipt to check.
     */
    public function infaq_contribution_manual(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:1|max:100000',
            'notes'  => 'nullable|string|max:1000',
        ]);

        InfaqContribution::create([
            'name'        => $validated['name'] ?? null,
            'amount'      => $validated['amount'],
            'notes'       => $validated['notes'] ?? null,
            'status'      => InfaqContribution::STATUS_VERIFIED,
            'source'      => InfaqContribution::SOURCE_MANUAL,
            'verified_at' => now(),
        ]);

        return redirect()->route('admin.infaq')->with('success', 'Sumbangan manual berjaya ditambah.');
    }

    public function infaq_contribution_verify(InfaqContribution $contribution)
    {
        $contribution->update([
            'status'      => InfaqContribution::STATUS_VERIFIED,
            'verified_at' => now(),
        ]);

        return redirect()->route('admin.infaq')->with('success', 'Sumbangan disahkan.');
    }

    public function infaq_contribution_reject(InfaqContribution $contribution)
    {
        $contribution->update([
            'status'      => InfaqContribution::STATUS_REJECTED,
            'verified_at' => null,
        ]);

        return redirect()->route('admin.infaq')->with('success', 'Sumbangan ditolak.');
    }

    public function infaq_photo_upload(Request $request)
    {
        $request->validate([
            'masjid_index' => 'required|integer|min:1|max:47',
            'caption'      => 'nullable|string|max:255',
            'photos'       => 'required|array|min:1',
            'photos.*'     => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $dir = public_path('assets/images/infaq');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($request->file('photos') as $file) {
            $filename = 'masjid-' . $request->masjid_index . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);

            InfaqGallery::create([
                'masjid_index' => $request->masjid_index,
                'image_path'   => 'assets/images/infaq/' . $filename,
                'caption'      => $request->caption,
            ]);
        }

        return redirect()->route('admin.infaq')->with('success', 'Gambar berjaya dimuat naik.');
    }

    public function infaq_photo_delete(InfaqGallery $photo)
    {
        $fullPath = public_path($photo->image_path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        $photo->delete();

        return redirect()->route('admin.infaq')->with('success', 'Gambar berjaya dipadam.');
    }
}
