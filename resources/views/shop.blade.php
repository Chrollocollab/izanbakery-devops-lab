@extends('layouts.app')
@section('content')
    <main class="pt-90">
        <section class="shop-main container d-flex pt-4 pt-xl-5">

            {{-- ── Sidebar ───────────────────────────────────── --}}
            <div class="shop-sidebar side-sticky bg-body" id="shopFilter">
                <div class="aside-header d-flex d-lg-none align-items-center">
                    <h3 class="text-uppercase fs-6 mb-0">{{ __('messages.shop.filter_by') }}</h3>
                    <button class="btn-close-lg js-close-aside btn-close-aside ms-auto"></button>
                </div>

                <div class="pt-4 pt-lg-0"></div>

                {{-- Product Categories --}}
                <div class="accordion" id="categories-list">
                    <div class="accordion-item mb-4 pb-3">
                        <h5 class="accordion-header" id="accordion-heading-cat">
                            <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button"
                                data-bs-toggle="collapse" data-bs-target="#accordion-filter-cat"
                                aria-expanded="true" aria-controls="accordion-filter-cat">
                                {{ __('messages.shop.categories') }}
                                <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                                    <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                                        <path d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z"/>
                                    </g>
                                </svg>
                            </button>
                        </h5>
                        <div id="accordion-filter-cat" class="accordion-collapse collapse show border-0"
                            aria-labelledby="accordion-heading-cat" data-bs-parent="#categories-list">
                            <div class="accordion-body px-0 pb-0 pt-3">
                                <ul class="list list-inline mb-0">
                                    <li class="list-item">
                                        <a href="{{ route('shop.index') }}" class="menu-link py-1 {{ empty($categorySlug) ? 'active' : '' }}">{{ __('messages.shop.all_products') }}</a>
                                    </li>
                                    @foreach ($categories as $category)
                                    <li class="list-item">
                                        <a href="{{ route('shop.index', ['category' => $category->slug]) }}" class="menu-link py-1 {{ $categorySlug === $category->slug ? 'active' : '' }}">{{ $category->name }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Price Filter --}}
                {{-- <div class="accordion" id="price-filters">
                    <div class="accordion-item mb-4">
                        <h5 class="accordion-header mb-2" id="accordion-heading-price">
                            <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button"
                                data-bs-toggle="collapse" data-bs-target="#accordion-filter-price"
                                aria-expanded="true" aria-controls="accordion-filter-price">
                                {{ __('messages.shop.price') }}
                                <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                                    <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                                        <path d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z"/>
                                    </g>
                                </svg>
                            </button>
                        </h5>
                        <div id="accordion-filter-price" class="accordion-collapse collapse show border-0"
                            aria-labelledby="accordion-heading-price" data-bs-parent="#price-filters">
                            <input class="price-range-slider" type="text" name="price_range" value=""
                                data-slider-min="5" data-slider-max="500" data-slider-step="5"
                                data-slider-value="[5,500]" data-currency="RM" />
                            <div class="price-range__info d-flex align-items-center mt-2">
                                <div class="me-auto">
                                    <span class="text-secondary" style="font-size:0.8rem;">{{ __('messages.shop.min') }} </span>
                                    <span class="price-range__min" style="font-size:0.88rem;font-weight:600;">RM5</span>
                                </div>
                                <div>
                                    <span class="text-secondary" style="font-size:0.8rem;">{{ __('messages.shop.max') }} </span>
                                    <span class="price-range__max" style="font-size:0.88rem;font-weight:600;">RM500</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}

                {{-- Availability --}}
                {{-- <div class="accordion" id="avail-filters">
                    <div class="accordion-item mb-4 pb-3">
                        <h5 class="accordion-header" id="accordion-heading-avail">
                            <button class="accordion-button p-0 border-0 fs-5 text-uppercase" type="button"
                                data-bs-toggle="collapse" data-bs-target="#accordion-filter-avail"
                                aria-expanded="true" aria-controls="accordion-filter-avail">
                                {{ __('messages.shop.availability') }}
                                <svg class="accordion-button__icon type2" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                                    <g aria-hidden="true" stroke="none" fill-rule="evenodd">
                                        <path d="M5.35668 0.159286C5.16235 -0.053094 4.83769 -0.0530941 4.64287 0.159286L0.147611 5.05963C-0.0492049 5.27473 -0.049205 5.62357 0.147611 5.83813C0.344427 6.05323 0.664108 6.05323 0.860924 5.83813L5 1.32706L9.13858 5.83867C9.33589 6.05378 9.65507 6.05378 9.85239 5.83867C10.0492 5.62357 10.0492 5.27473 9.85239 5.06018L5.35668 0.159286Z"/>
                                    </g>
                                </svg>
                            </button>
                        </h5>
                        <div id="accordion-filter-avail" class="accordion-collapse collapse show border-0"
                            aria-labelledby="accordion-heading-avail" data-bs-parent="#avail-filters">
                            <div class="accordion-body px-0 pb-0 pt-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="avail-instock" checked>
                                    <label class="form-check-label" for="avail-instock" style="font-size:0.9rem;color:var(--bk-text-medium);">{{ __('messages.shop.in_stock') }}</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="avail-sale">
                                    <label class="form-check-label" for="avail-sale" style="font-size:0.9rem;color:var(--bk-text-medium);">{{ __('messages.shop.on_sale') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="avail-custom">
                                    <label class="form-check-label" for="avail-custom" style="font-size:0.9rem;color:var(--bk-text-medium);">{{ __('messages.shop.custom_preorder') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
            {{-- End Sidebar --}}

            {{-- ── Shop List ─────────────────────────────────── --}}
            <div class="shop-list flex-grow-1">

                {{-- Bakery Banner --}}
                <div class="bakery-shop-banner mb-4 bkr-reveal bkr-reveal--soft">
                    <div class="bakery-shop-banner__text">
                        <h2 class="bakery-shop-banner__title">{{ __('messages.shop.banner_title') }}</h2>
                        <p class="bakery-shop-banner__desc">
                            {!! __('messages.shop.banner_desc') !!}
                        </p>
                        <a href="{{ route('shop.index') }}" class="bakery-btn-primary" style="font-size:0.78rem;padding:10px 22px;">
                            {{ __('messages.shop.browse_all_products') }}
                        </a>
                    </div>
                    <div class="bakery-shop-banner__icon" aria-hidden="true">🎂</div>
                </div>

                {{-- Breadcrumb + Sort --}}
                <div class="d-flex justify-content-between mb-4 pb-md-2">
                    <div class="breadcrumb mb-0 d-none d-md-flex align-items-center flex-grow-1">
                        <a href="{{ route('home.index') }}" class="menu-link menu-link_us-s text-uppercase fw-medium">{{ __('messages.shop.home') }}</a>
                        <span class="breadcrumb-separator menu-link fw-medium ps-1 pe-1">/</span>
                        <a href="{{ route('shop.index') }}" class="menu-link menu-link_us-s text-uppercase fw-medium">{{ __('messages.shop.our_menu') }}</a>
                    </div>

                    <div class="shop-acs d-flex align-items-center justify-content-between justify-content-md-end flex-grow-1">
                        <select class="shop-acs__select form-select w-auto border-0 py-0 order-1 order-md-0"
                            aria-label="{{ __('messages.shop.sort_items') }}" name="total-number">
                            <option selected>{{ __('messages.shop.default_sorting') }}</option>
                            <option value="1">{{ __('messages.shop.featured') }}</option>
                            <option value="2">{{ __('messages.shop.best_selling') }}</option>
                            <option value="3">{{ __('messages.shop.name_az') }}</option>
                            <option value="4">{{ __('messages.shop.name_za') }}</option>
                            <option value="5">{{ __('messages.shop.price_low_high') }}</option>
                            <option value="6">{{ __('messages.shop.price_high_low') }}</option>
                            <option value="7">{{ __('messages.shop.newest_first') }}</option>
                        </select>

                        <div class="shop-asc__seprator mx-3 bg-light d-none d-md-block order-md-0"></div>

                        <div class="col-size align-items-center order-1 d-none d-lg-flex">
                            <span class="text-uppercase fw-medium me-2" style="font-size:0.78rem;color:var(--bk-text-light);">{{ __('messages.shop.view') }}</span>
                            <button class="btn-link fw-medium me-2 js-cols-size" data-target="products-grid" data-cols="2">2</button>
                            <button class="btn-link fw-medium me-2 js-cols-size" data-target="products-grid" data-cols="3">3</button>
                            <button class="btn-link fw-medium js-cols-size" data-target="products-grid" data-cols="4">4</button>
                        </div>

                        <div class="shop-filter d-flex align-items-center order-0 order-md-3 d-lg-none">
                            <button class="btn-link btn-link_f d-flex align-items-center ps-0 js-open-aside" data-aside="shopFilter">
                                <svg class="d-inline-block align-middle me-2" width="14" height="10"
                                    viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <use href="#icon_filter" />
                                </svg>
                                <span class="text-uppercase fw-medium d-inline-block align-middle">{{ __('messages.shop.filter') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Products Grid --}}
                <div class="products-grid row row-cols-2 row-cols-md-3" id="products-grid" data-reveal-stagger>
                    @foreach ($products as $product)
                    <div class="product-card-wrapper bkr-reveal bkr-reveal--soft">
                        <div class="product-card mb-3 mb-md-4 mb-xxl-5">
                            <div class="pc__img-wrapper">
                                <div class="swiper-container background-img js-swiper-slider"
                                    data-settings='{"resizeObserver": true}'>
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
                                                @if ($product->getFirstMediaUrl('product', 'display'))
                                                    <img loading="lazy"
                                                         src="{{ $product->getFirstMediaUrl('product', 'display') }}"
                                                         width="330" height="400"
                                                         alt="{{ $product->name }}"
                                                         class="pc__img">
                                                @else
                                                    <div class="pc__img bkr-img-placeholder bkr-img-placeholder--md">
                                                        <i class="bi bi-cake2" aria-hidden="true"></i>
                                                    </div>
                                                @endif
                                            </a>
                                        </div>
                                        <div class="swiper-slide">
                                            @foreach ($product->getMedia('gallery') as $galleryItem)
                                            <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
                                                <img loading="lazy"
                                                     src="{{ $galleryItem->getUrl('display') }}"
                                                     width="330" height="400"
                                                     alt="{{ $product->name }}"
                                                     class="pc__img">
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                    <span class="pc__img-prev"><svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg"><use href="#icon_prev_sm" /></svg></span>
                                    <span class="pc__img-next"><svg width="7" height="11" viewBox="0 0 7 11" xmlns="http://www.w3.org/2000/svg"><use href="#icon_next_sm" /></svg></span>
                                </div>

                                @if (Cart::instance('cart')->content()->where('id', $product->id)->count() > 0)
                                    <a href="{{ route('cart.index') }}"
                                       class="pc__atc btn anim_appear-bottom position-absolute border-0 text-uppercase fw-medium btn-warning mb-3">
                                        {{ __('messages.shop.view_in_cart') }}
                                    </a>
                                @else
                                    <form name="addtocart-form" method="post" action="{{ route('cart.add') }}">
                                        @csrf
                                        <input type="hidden" name="id"       value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="name"     value="{{ $product->name }}">
                                        <input type="hidden" name="price"    value="{{ $product->sale_price ?: $product->regular_price }}">
                                        <button type="submit"
                                                class="pc__atc btn anim_appear-bottom position-absolute border-0 text-uppercase fw-medium"
                                                data-aside="cartDrawer" title="{{ __('messages.shop.add_to_cart') }}">
                                            {{ __('messages.shop.add_to_cart') }}
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="pc__info position-relative">
                                <p class="pc__category">{{ $product->category->name }}</p>
                                <h6 class="pc__title">
                                    <a href="{{ route('shop.product.details', ['product_slug' => $product->slug]) }}">
                                        {{ $product->name }}
                                    </a>
                                </h6>
                                <div class="product-card__price d-flex">
                                    <span class="money price">
                                        @if ($product->sale_price)
                                            <s>RM{{ number_format($product->regular_price, 2) }}</s>
                                            RM{{ number_format($product->sale_price, 2) }}
                                        @else
                                            RM{{ number_format($product->regular_price, 2) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="product-card__review d-flex align-items-center">
                                    <div class="reviews-group d-flex">
                                        @for ($i = 0; $i < 5; $i++)
                                        <svg class="review-star" viewBox="0 0 9 9" xmlns="http://www.w3.org/2000/svg">
                                            <use href="#icon_star" />
                                        </svg>
                                        @endfor
                                    </div>
                                    <span class="reviews-note text-lowercase text-secondary ms-1">{{ __('messages.shop.verified') }}</span>
                                </div>
                                <button class="pc__btn-wl position-absolute top-0 end-0 bg-transparent border-0 js-add-wishlist"
                                        title="{{ __('messages.shop.add_to_wishlist') }}">
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <use href="#icon_heart" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
            {{-- End Shop List --}}

        </section>
    </main>
@endsection
