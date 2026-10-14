<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('stock_status', 'instock')
            ->orderBy('created_at', 'DESC')
            ->take(12)
            ->get();
        
        $categories = Category::with(['products' => function($query) {
            $query->where('stock_status', 'instock')
                  ->orderBy('created_at', 'DESC');
        }])->get();
            
        return view('user.index', compact('products', 'categories'));
    }
}
