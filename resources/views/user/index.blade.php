@extends('layouts.app')
@section('content')
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
      <div class="row">
        <div class="col-lg-3">
            @include('user.account-nav')
        </div>
        <div class="col-lg-9">
          <div class="page-content my-account__dashboard">
            <p>Hello <strong>{{ Auth::user()->name }}</strong></p>
            
            <!-- Featured Products Carousel -->
            <div class="products-carousel mt-5">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-uppercase" style="color: #d4a574;">Featured Products</h4>
                <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary btn-sm">View All</a>
              </div>
              
              <div class="carousel-wrapper position-relative">
                <!-- Prev Button -->
                <button class="products-carousel__prev btn position-absolute" style="width: 40px; height: 40px; padding: 0; left: -15px; top: 50%; transform: translateY(-50%); z-index: 10; background: white; border: 2px solid #d4a574; color: #d4a574; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 8px;">
                  <svg width="8" height="14" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.5 1L1 5.5L5.5 10" stroke="currentColor" stroke-width="2" fill="none"/>
                  </svg>
                </button>
                
                <div class="swiper-container js-swiper-slider products-slider"
                data-settings='{
                  "autoplay": {
                    "delay": 3000,
                    "disableOnInteraction": false
                  },
                  "slidesPerView": 1,
                  "slidesPerGroup": 1,
                  "effect": "none",
                  "loop": true,
                  "pagination": {
                    "el": ".products-pagination",
                    "type": "bullets",
                    "clickable": true
                  },
                  "navigation": {
                    "nextEl": ".products-carousel__next",
                    "prevEl": ".products-carousel__prev"
                  },
                  "breakpoints": {
                    "576": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 2,
                      "spaceBetween": 15
                    },
                    "768": {
                      "slidesPerView": 2,
                      "slidesPerGroup": 2,
                      "spaceBetween": 20
                    },
                    "992": {
                      "slidesPerView": 3,
                      "slidesPerGroup": 1,
                      "spaceBetween": 25
                    }
                  }
                }'>
                <div class="swiper-wrapper">
                  @foreach ($products as $product)
                  <div class="swiper-slide" style="height: auto;">
                    <div class="product-card text-center h-100" style="background: linear-gradient(145deg, #fff9f0, #fff5e6); border-radius: 15px; padding: 15px; box-shadow: 0 4px 15px rgba(212, 165, 116, 0.15); transition: all 0.3s ease; display: flex; flex-direction: column;">
                      <div class="pc__img-wrapper mb-3" style="position: relative; overflow: hidden; border-radius: 12px; flex-shrink: 0;">
                        <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
                          @if ($product->getFirstMediaUrl('product', 'display'))
                            <img loading="lazy"
                                 src="{{ $product->getFirstMediaUrl('product', 'display') }}"
                                 alt="{{ $product->name }}"
                                 class="pc__img"
                                 style="width: 100%; height: 180px; object-fit: cover; border-radius: 12px; transition: transform 0.3s ease; display: block;">
                          @else
                            <div class="bkr-img-placeholder bkr-img-placeholder--md" style="width: 100%; height: 180px; border-radius: 12px;">
                              <i class="bi bi-cake2" aria-hidden="true"></i>
                            </div>
                          @endif
                        </a>
                        @if($product->sale_price)
                          <span class="badge position-absolute" style="top: 10px; left: 10px; background: #e74c3c; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem;">SALE</span>
                        @endif
                      </div>

                      <div class="pc__info" style="flex-grow: 1; display: flex; flex-direction: column;">
                        <p class="pc__category text-muted mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">{{ $product->category->name ?? '' }}</p>
                        <h6 class="pc__title mb-2" style="font-size: 0.95rem; min-height: 2.4em; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                          <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}" style="color: #333; text-decoration: none; font-weight: 600;">{{ $product->name }}</a>
                        </h6>
                        <div class="product-card__price mb-3" style="margin-top: auto;">
                          @if ($product->sale_price)
                            <span class="text-muted text-decoration-line-through me-2" style="font-size: 0.85rem;">RM{{ number_format($product->regular_price, 2) }}</span>
                            <span class="fw-bold" style="color: #d4a574; font-size: 1rem;">RM{{ number_format($product->sale_price, 2) }}</span>
                          @else
                            <span class="fw-bold" style="color: #d4a574; font-size: 1rem;">RM{{ number_format($product->regular_price, 2) }}</span>
                          @endif
                        </div>
                        
                        <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}" 
                           class="btn w-100 mt-auto" 
                           style="background: linear-gradient(135deg, #d4a574, #c9956a); color: white; border: none; padding: 10px 15px; border-radius: 25px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem; transition: all 0.3s ease;">
                          Order Now
                        </a>
                      </div>
                    </div>
                  </div>
                  @endforeach
                </div>
              </div>
                
                <!-- Next Button -->
                <button class="products-carousel__next btn position-absolute" style="width: 40px; height: 40px; padding: 0; right: -15px; top: 50%; transform: translateY(-50%); z-index: 10; background: white; border: 2px solid #d4a574; color: #d4a574; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 8px;">
                  <svg width="8" height="14" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L5.5 5.5L1 10" stroke="currentColor" stroke-width="2" fill="none"/>
                  </svg>
                </button>
              </div>
              
              <!-- Pagination -->
              <div class="d-flex justify-content-center mt-4">
                <div class="products-pagination"></div>
              </div>
            </div>
            <!-- End Featured Products Carousel -->
            
            <!-- Products by Category -->
            <div class="products-by-category mt-5">
              @foreach ($categories as $category)
                @if($category->products->count() > 0)
                <div class="category-section mb-5">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold" style="color: #333; border-left: 4px solid #d4a574; padding-left: 12px;">{{ $category->name }}</h5>
                    <span class="badge" style="background: #d4a574; color: white;">{{ $category->products->count() }} products</span>
                  </div>
                  
                  <div class="row g-3">
                    @foreach ($category->products as $product)
                    <div class="col-6 col-md-4 col-lg-4">
                      <div class="category-product-card h-100" style="background: #fff; border-radius: 12px; padding: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease;">
                        <div class="position-relative mb-2">
                          <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
                            @if ($product->getFirstMediaUrl('product', 'display'))
                              <img src="{{ $product->getFirstMediaUrl('product', 'display') }}"
                                   alt="{{ $product->name }}"
                                   style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px;">
                            @else
                              <div class="bkr-img-placeholder bkr-img-placeholder--sm" style="width: 100%; height: 120px; border-radius: 8px;">
                                <i class="bi bi-cake2" aria-hidden="true"></i>
                              </div>
                            @endif
                          </a>
                          @if($product->sale_price)
                            <span class="badge position-absolute" style="top: 5px; left: 5px; background: #e74c3c; font-size: 0.65rem;">SALE</span>
                          @endif
                        </div>
                        <h6 class="mb-1" style="font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4em;">
                          <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}" style="color: #333; text-decoration: none;">{{ $product->name }}</a>
                        </h6>
                        <div class="mb-2">
                          @if ($product->sale_price)
                            <span class="text-muted text-decoration-line-through" style="font-size: 0.75rem;">RM{{ number_format($product->regular_price, 2) }}</span>
                            <span class="fw-bold" style="color: #d4a574; font-size: 0.9rem;">RM{{ number_format($product->sale_price, 2) }}</span>
                          @else
                            <span class="fw-bold" style="color: #d4a574; font-size: 0.9rem;">RM{{ number_format($product->regular_price, 2) }}</span>
                          @endif
                        </div>
                        <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}" 
                           class="btn btn-sm w-100" 
                           style="background: linear-gradient(135deg, #d4a574, #c9956a); color: white; border: none; border-radius: 20px; font-size: 0.75rem; font-weight: 600; padding: 6px 10px;">
                          Order Now
                        </a>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
                @endif
              @endforeach
            </div>
            <!-- End Products by Category -->
            
          </div>
        </div>
      </div>
    </section>
    
    <style>
      .products-carousel .carousel-wrapper {
        padding: 0 25px;
      }
      .products-carousel .swiper-wrapper {
        align-items: stretch;
      }
      .products-carousel .swiper-slide {
        height: auto;
      }
      .products-carousel .product-card {
        height: 100%;
      }
      .products-carousel .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(212, 165, 116, 0.25) !important;
      }
      .products-carousel .product-card:hover .pc__img {
        transform: scale(1.05);
      }
      .products-carousel .product-card .btn:hover {
        background: linear-gradient(135deg, #c9956a, #b8845a) !important;
        transform: scale(1.02);
      }
      .products-carousel__prev:hover,
      .products-carousel__next:hover {
        background: #d4a574 !important;
        color: white !important;
      }
      .products-pagination .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
        background: #d4a574;
        opacity: 0.4;
        margin: 0 5px;
      }
      .products-pagination .swiper-pagination-bullet-active {
        opacity: 1;
        background: #d4a574;
      }
      .category-product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(212, 165, 116, 0.2) !important;
      }
      .category-product-card .btn:hover {
        background: linear-gradient(135deg, #c9956a, #b8845a) !important;
      }
    </style>
@endsection 