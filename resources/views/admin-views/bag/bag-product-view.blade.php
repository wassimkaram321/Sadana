@extends('layouts.back-end.app')

@section('title', \App\CPU\translate('bag products List'))

@push('css_or_js')
<link href="{{asset('public/assets/back-end/css/tags-input.min.css')}}" rel="stylesheet">
<link href="{{ asset('public/assets/select2/css/select2.min.css')}}" rel="stylesheet">
@endpush

@section('content')
<div class="content container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-2">
        <h1 class="h3 mb-0 text-black-50">{{\App\CPU\translate('bag_products_list')}} </h1>
    </div>

    <div class="row" style="margin-top: 20px">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header">

                    {{-- <div > --}}
                        <form class="row w-100 align-items-center" action="{{route('admin.bag.products-store',[$bag_id])}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="col-md-4 w-100">
                                <select
                                    class="w-100 js-example-basic-multiple js-states js-example-responsive form-control"
                                    name="product_id" required>
                                    <option value="{{null}}" selected disabled>---{{\App\CPU\translate('Select')}}---</option>
                                    @foreach($br as $b)
                                        <option value="{{$b['id']}}">{{$b['name']}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-center w-100">
                                <input type="number" min="1" step="1"
                                    placeholder="Count"
                                    name="product_count" class="w-100 js-example-basic-multiple form-control"
                                    style="width: 100%;">

                            </div>


                            <div class="col-md-2 d-flex align-items-center w-100">
                                <input type="checkbox"
                                    value="1"
                                    name="free" class=" regular-checkbox w-25 js-example-basic-multiple form-control"
                                    style="width: 100%;width: 20px !important;">
                                    <label  style="margin-bottom: 0px; margin-left: 8px;">{{\App\CPU\translate('Free')}}</label><br>
                            </div>

                            <div class="col-md-4 w-100">
                                <button type="submit" class="btn btn-primary w-100" style="border: none;appearance: none;border-radius: 5px;height:40px">
                                    {{\App\CPU\translate('Add_product')}}
                                </button>
                            </div>
                        </form>

                    {{-- </div> --}}
                </div>


                <div class="card-body" style="padding: 0">
                    <div class="table-responsive">
                        <table style="text-align: {{Session::get('direction') === " rtl" ? 'right' : 'left' }};"
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" style="width: 100px">
                                        {{ \App\CPU\translate('bag')}} {{ \App\CPU\translate('ID')}}
                                    </th>
                                    <th scope="col">{{ \App\CPU\translate('Name')}}</th>
                                    <th scope="col">{{ \App\CPU\translate('price')}}</th>
                                    <th scope="col">{{ \App\CPU\translate('Count')}}</th>
                                    <th scope="col">{{ \App\CPU\translate('Total_price')}}</th>
                                    <th scope="col">{{ \App\CPU\translate('Image')}}</th>
                                    <th scope="col" style="width: 100px" class="text-center">
                                        {{ \App\CPU\translate('Action')}}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                $i=1;
                                @endphp

                                @foreach($bag_products as $b)
                                <tr>
                                    <td class="text-center">{{$i}}</td>
                                    <td>
                                        <a href="{{route('admin.product.view',[$b['product_id']])}}">
                                            {{\Illuminate\Support\Str::limit($b['name'],30)}}
                                        </a>
                                    </td>

                                    <td>{{$b['product_price']}}</td>
                                    <td>{{$b['product_count']}}</td>
                                    @if ($b['product_total_price']==0)
                                    <td style="color: blue">{{ \App\CPU\translate('Free')}}</td>
                                    @else
                                    <td>{{$b['product_total_price']}}</td>
                                    @endif

                                    <td>
                                        <img style="width: 60px;height: 60px"
                                            onerror="this.src='{{asset('public/assets/front-end/img/image-place-holder.png')}}'"
                                            src="{{asset('storage/app/public/product/thumbnail')}}/{{$b['"
                                            thumbnail"']}}">
                                    </td>

                                    <td>
                                        <a class="btn btn-danger btn-sm delete" id="{{$b['id']}}">
                                            <i class="tio-add-to-trash"></i> {{ \App\CPU\translate('Delete')}}
                                        </a>
                                    </td>


                                </tr>
                                @php
                                $i++;
                                @endphp
                                @endforeach

                            </tbody>
                        </table>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).on('click', '.delete', function () {
            var id = $(this).attr("id");
            Swal.fire({
                title: '{{ \App\CPU\translate('Are_you_sure_delete_this_bag')}}?',
                text: "{{ \App\CPU\translate('You_will_not_be_able_to_revert_this')}}!",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ \App\CPU\translate('Yes')}}, {{ \App\CPU\translate('delete_it')}}!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.bag.products-delete')}}",
                        method: 'POST',
                        data: {id: id},
                        success: function () {
                            toastr.success('{{ \App\CPU\translate('Product_deleted_successfully')}}');
                            location.reload();
                        }
                    });
                }
            })
        });

        $(".js-example-theme-single").select2({
            theme: "classic"
        });

        $(".js-example-responsive").select2({
            // dir: "rtl",
            width: 'resolve'
        });
</script>
@endpush
