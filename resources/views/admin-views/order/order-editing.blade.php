@extends('layouts.back-end.app')

@section('title', \App\CPU\translate('Order Details'))

@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .sellerName {
        height: fit-content;
        margin-top: 10px;
        margin-left: 10px;
        font-size: 16px;
        border-radius: 25px;
        text-align: center;
        padding-top: 10px;
    }
</style>
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header d-print-none p-3" style="background: white">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-no-gutter">
                        <li class="breadcrumb-item"><a class="breadcrumb-link"
                                href="{{route('admin.orders.list',['status'=>'all'])}}">{{\App\CPU\translate('Orders')}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{\App\CPU\translate('Order')}}
                            {{\App\CPU\translate('details')}} </li>
                    </ol>
                </nav>

                <div class="d-sm-flex align-items-sm-center">
                    <h1 class="page-header-title">{{\App\CPU\translate('Order')}} #{{$order['id']}}</h1>

                    @if($order['payment_status']=='paid')
                    <span class="badge badge-soft-success ml-sm-3">
                        <span class="legend-indicator bg-success"></span>{{\App\CPU\translate('Paid')}}
                    </span>
                    @else
                    <span class="badge badge-soft-danger ml-sm-3">
                        <span class="legend-indicator bg-danger"></span>{{\App\CPU\translate('Unpaid')}}
                    </span>
                    @endif

                    @if($order['order_status']=='pending')
                    <span class="badge badge-soft-info ml-2 ml-sm-3 text-capitalize">
                        <span class="legend-indicator bg-info text"></span>{{str_replace('_','
                        ',$order['order_status'])}}
                    </span>
                    @elseif($order['order_status']=='failed')
                    <span class="badge badge-danger ml-2 ml-sm-3 text-capitalize">
                        <span class="legend-indicator bg-info"></span>{{str_replace('_',' ',$order['order_status'])}}
                    </span>
                    @elseif($order['order_status']=='processing' || $order['order_status']=='out_for_delivery')
                    <span class="badge badge-soft-warning ml-2 ml-sm-3 text-capitalize">
                        <span class="legend-indicator bg-warning"></span>{{str_replace('_',' ',$order['order_status'])}}
                    </span>
                    @elseif($order['order_status']=='delivered' || $order['order_status']=='confirmed')
                    <span class="badge badge-soft-success ml-2 ml-sm-3 text-capitalize">
                        <span class="legend-indicator bg-success"></span>{{str_replace('_',' ',$order['order_status'])}}
                    </span>
                    @else
                    <span class="badge badge-soft-danger ml-2 ml-sm-3 text-capitalize">
                        <span class="legend-indicator bg-danger"></span>{{str_replace('_',' ',$order['order_status'])}}
                    </span>
                    @endif
                    <span class="ml-2 ml-sm-3">
                        <i class="tio-date-range"></i> {{date('d M Y H:i:s',strtotime($order['created_at']))}}
                    </span>

                    @if(\App\CPU\Helpers::get_business_settings('order_verification'))
                    <span class="ml-2 ml-sm-3">
                        <b>
                            {{\App\CPU\translate('order_verification_code')}} : {{$order['verification_code']}}
                        </b>
                    </span>
                    @endif

                </div>
                <div class="col-md-6 mt-2">
                    <a class="text-body mr-3" target="_blank"
                        href={{route('admin.orders.generate-excel',[$order['id']])}}>
                        <i class="tio-print mr-1"></i> {{\App\CPU\translate('Export')}} {{\App\CPU\translate('excel')}}
                    </a>
                </div>
                <!-- End Unfold -->
            </div>
        </div>
    </div>

    <!-- End Page Header -->

    <div class="row" id="printableArea">
        <div class="col-lg-12">
            <!-- Card -->
            <div class="card mb-3 mb-lg-5">
                <!-- Header -->
                <div class="card-header" style="display: block!important;">
                    <div class="row">
                        <div class="col-12 pb-2 border-bottom">
                            <h4 class="card-header-title">
                                {{\App\CPU\translate('Order')}} {{\App\CPU\translate('details')}}
                                <span
                                    class="badge badge-soft-dark rounded-circle ml-1">{{$order->details->count()}}</span>
                            </h4>
                        </div>

                        <div class="col-6 pt-2">
                            @if ($order->order_note !=null)
                            <span class="font-weight-bold text-capitalize">
                                {{\App\CPU\translate('order_note')}} :
                            </span>
                            <p class="pl-1">
                                {{$order->order_note}}
                            </p>
                            @endif
                        </div>

                    </div>
                </div>
                <!-- End Header -->

                <!-- Body -->
                <div class="card-body">
                    <div class="media">


                        <div class="media-body">
                            <div class="row">
                                <div class="col-md-3 product-name">
                                    <p> {{\App\CPU\translate('Name')}}</p>
                                </div>

                                <div class="col-md-1 align-self-center p-0 ">
                                    <p> {{\App\CPU\translate('price')}}</p>
                                </div>

                                <div class="col-md-1 align-self-center">
                                    <p>Q</p>
                                </div>

                                <div class="col-md-1 align-self-center  p-0 product-name">
                                    <p> {{\App\CPU\translate('Q_Offer')}}</p>
                                </div>

                                <div class="col-md-2 d-flex align-self-center justify-content-end p-0 product-name">
                                    <p> {{\App\CPU\translate('Discount')}}</p>
                                </div>

                                <div class="col-md-2 align-self-center text-right  ">
                                    <p> {{\App\CPU\translate('Subtotal')}}</p>
                                </div>

                                <div class="col-md-2 align-self-center text-right  ">
                                    <p> {{\App\CPU\translate('Actions')}}</p>
                                </div>

                            </div>
                        </div>
                    </div>
                    @php($subtotal=0)
                    @php($total=0)
                    @php($shipping=0)
                    @php($discount=0)
                    @php($tax=0)
                    @foreach($order->details as $key=>$detail)
                    @if($detail->product)
                    <!-- Media -->
                    <div class="media">

                        <div class="media-body">
                            <div class="row">
                                <div class="col-md-3 mb-3 mb-md-0 product-name">

                                    <a href="{{route('admin.product.view',[$detail['product_id']])}}" target="_blank"
                                        rel="noopener noreferrer">{{substr($detail->product['name'],0,65)}}{{strlen($detail->product['name'])>45?'':''}}</a>

                                </div>

                                <div class="col-md-1 align-self-center p-0 ">
                                    <h6>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($detail['price']))}}
                                    </h6>
                                </div>

                                <div class="col-md-1 align-self-center">

                                    <h5>{{$detail->qty}}</h5>
                                </div>


                                <div class="col-md-1 align-self-center  p-0 product-name">

                                    <h5>{{$detail['total_qty']}}</h5>
                                </div>
                                <div class="col-md-2 align-self-center d-flex  justify-content-end p-0 product-name">

                                    <h5>
                                        {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($detail['discount']))}}
                                    </h5>
                                </div>

                                <div class="col-md-2 align-self-center text-right  ">
                                    @php($subtotal=$detail['price']*$detail->qty+$detail['tax']-$detail['discount'])

                                    <h5 style="font-size: 12px">
                                        {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($subtotal))}}
                                    </h5>
                                </div>

                                <div class="col-md-2 align-self-center text-right  ">
                                    {{-- <a class="btn btn-primary btn-sm"
                                        href="{{route('admin.bag.edit',[$order['id']])}}">
                                        <i class="tio-edit"></i>
                                    </a> --}}
                                    <button href="" id="editCompany" data-toggle="modal" data-target='#practice_modal'
                                        class="btn btn-primary btn-sm " data-product_id="{{$detail['product_id']}}"
                                        data-id="{{ $order['id'] }}"><i class="tio-edit"></i></button>

                                    <button onclick="add('{{$detail['product_id']}}','{{$order['id']}}');"
                                        class="btn btn-danger btn-sm delete" id="{{$order['id']}}">
                                        <i class="tio-add-to-trash"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                    {{-- seller info old --}}

                    @php($discount+=$detail['discount'])
                    @php($tax+=$detail['tax'])
                    @php($total+=$subtotal)
                    <!-- End Media -->
                    <hr>
                    @endif
                    @php($sellerId=$detail->seller_id)
                    @endforeach
                    @php($shipping=$order['shipping_cost'])
                    @php($coupon_discount=$order['discount_amount'])

                    <div class="row justify-content-md-end mb-3">
                        <div class="col-md-9 col-lg-8">
                            <dl class="row text-sm-right">
                                <dt class="col-sm-6">{{\App\CPU\translate('Shipping')}}</dt>
                                <dd class="col-sm-6 border-bottom">
                                    <strong>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($shipping))}}</strong>
                                </dd>

                                <dt class="col-sm-6">{{\App\CPU\translate('coupon_discount')}}</dt>
                                <dd class="col-sm-6 border-bottom">
                                    <strong>-
                                        {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($coupon_discount))}}</strong>
                                </dd>

                                <dt class="col-sm-6">{{\App\CPU\translate('Total')}}</dt>
                                <dd class="col-sm-6">
                                    <strong>{{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($total+$shipping-$coupon_discount))}}</strong>
                                </dd>
                            </dl>
                            <!-- End Row -->
                        </div>
                    </div>
                    <!-- End Row -->
                </div>
                <!-- End Body -->
            </div>
            <!-- End Card -->
        </div>


    </div>
    <!-- End Row -->

    <div class="modal fade" id="practice_modal">
        <div class="modal-dialog">
            <form class="row w-100 align-items-center" action="{{route('admin.orders.update-order')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-content" style="min-height: 150px; padding: 20px">
                    <input type="hidden" id="order_id" name="order_id" value="">
                    <input type="hidden" id="product_id" name="product_id" value="">
                    <div class="modal-body mb-4" style="padding: 0px;">
                        <div>
                            <p> {{\App\CPU\translate('Quantity')}}</p>
                        </div>
                        <input type="number" name="qty" id="qty" value="" class="form-control">
                    </div>
                    <input type="submit" value="Submit" id="submit" class="btn btn-sm btn-primary py-0"
                        style="font-size: 1.2em; height: 30px; width: 100%;">
                </div>
            </form>
        </div>
    </div>

</div>


@endsection

@push('script_2')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>


<script>
    $(document).ready(function () {

    $.ajaxSetup({
        headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
    });

    $('body').on('click', '#editCompany', function (event) {

        event.preventDefault();
        var id = $(this).data('id');
        var product_id = $(this).data('product_id');
        $.ajax({
          url: 'product/'+id,
          type: "POST",
          data: {
            product_id: product_id,
          },
          dataType: 'json',
          success: function (data) {
            $('#userCrudModal').html("Edit Product");
             $('#submit').val("Edit Product");
            // $('#practice_modal').modal('show');
             $('#order_id').val(data.data.order_id);
             $('#product_id').val(data.data.product_id);
             $('#qty').val(data.data.qty);
          }
      });

    });

   });
</script>


<script>
    function add(product_id,order_id)
    {
        Swal.fire({
                title: '{{\App\CPU\translate('Are you sure you want to remove the product?')}}!',
                text: "{{\App\CPU\translate('Think before you proceed')}}.",
                showCancelButton: true,
                confirmButtonColor: '#377dff',
                cancelButtonColor: 'secondary',
                confirmButtonText: '{{\App\CPU\translate('Yes, Change it')}}!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.orders.delete-product')}}",
                        method: 'POST',
                        data: {
                            "product_id": product_id,
                            "order_id": order_id
                        },
                        success: function (data) {
                            if (data.success == 0) {
                                toastr.success('{{\App\CPU\translate('You can not change it')}} !!');
                                location.reload();
                            } else {
                                toastr.success('{{\App\CPU\translate('Product deleted successfully')}}!');
                                location.reload();
                            }

                        }
                    });
                }
            })
    }

</script>

@endpush
