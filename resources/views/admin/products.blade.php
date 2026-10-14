@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>All Products</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a href="{{route('admin.index')}}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">All Products</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <form class="form-search">
                            <fieldset class="name">
                                <input type="text" placeholder="Search here..." class="" name="name"
                                    tabindex="2" value="" aria-required="true" required="">
                            </fieldset>
                            <div class="button-submit">
                                <button class="" type="submit"><i class="icon-search"></i></button>
                            </div>
                        </form>
                    </div>
                    <a class="tf-button style-1 w208" href="{{route('admin.product.add')}}"><i class="icon-plus"></i>Add new</a>
                </div>
                <div class="table-responsive">
                    @if (Session::has('status'))
                        <p class="alert alert-success">{{Session::get('status')}}</p>
                    @endif
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>SalePrice</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Visible</th>
                                <th>Stock</th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                            <tr>
                                <td>{{$product->id}}</td>
                                <td class="pname">
                                    <div class="image">
                                        <img src="{{ $product->getFirstMediaUrl('product', 'thumb') }}" alt="{{$product->name}}" class="image">
                                    </div>
                                    <div class="name">
                                        <a href="#" class="body-title-2">{{$product->name}}</a>
                                        <div class="text-tiny mt-3">{{$product->slug}}</div>
                                    </div>
                                </td>
                                <td>RM {{$product->regular_price}}</td>
                                <td>RM {{$product->sale_price}}</td>
                                <td>{{$product->category->name}}</td>
                                <td>{{$product->brand->name}}</td>
                                <td>
                                    <div class="visibility-form">
                                        <label class="bk-switch" title="Toggle customer visibility">
                                            <input type="checkbox" class="bk-switch__input js-visibility-toggle"
                                                   data-url="{{ route('admin.product.toggle', ['id' => $product->id]) }}"
                                                   {{ $product->is_visible ? 'checked' : '' }}>
                                            <span class="bk-switch__slider"></span>
                                        </label>
                                        <span class="bk-switch__label text-tiny">{{ $product->is_visible ? 'Shown' : 'Hidden' }}</span>
                                    </div>
                                </td>
                                <td>{{$product->stock_status}}</td>
                                <td>{{$product->quantity}}</td>
                                <td>
                                    <div class="list-icon-function">
                                        <a href="#" target="_blank">
                                            <div class="item eye">
                                                <i class="icon-eye"></i>
                                            </div>
                                        </a>
                                        <a href="{{route('admin.product.edit',['id'=>$product->id])}}">
                                            <div class="item edit">
                                                <i class="icon-edit-3"></i>
                                            </div>
                                        </a>
                                        <form action="{{ route('admin.product.delete',['id'=>$product->id]) }}" method="POST">
                                            @csrf
                                            @method('DElETE')
                                            <div class="item text-danger delete">
                                                <i class="icon-trash-2"></i>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">

                    {{$products->links('pagination::bootstrap-5')}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            // Toggle product visibility via AJAX — no page reload
            $('.js-visibility-toggle').on('change', function() {
                var $input = $(this);
                var $label = $input.closest('.visibility-form').find('.bk-switch__label');
                $input.prop('disabled', true);

                $.ajax({
                    url: $input.data('url'),
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        _method: 'PUT',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }
                }).done(function(res) {
                    $label.text(res.is_visible ? 'Shown' : 'Hidden');
                }).fail(function() {
                    // Revert the switch if the update failed
                    $input.prop('checked', !$input.prop('checked'));
                    alert('Could not update visibility. Please try again.');
                }).always(function() {
                    $input.prop('disabled', false);
                });
            });

            $('.delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, you will not be able to recover this brand",
                    type: "warning",
                    buttons: ["No", "Yes"],
                    confirmButtonColor: '#dc3564',
                }).then(function(result) {
                    if (result) {
                        form.submit();
                    }
                })
            });
        });
    </script>
@endpush
