@extends('layouts.back-end.app')
{{-- @section('title', 'Customer') --}}
@section('title', \App\CPU\translate('Salesman Details'))

    <!-- Bootstrap Links -->
<style>
.select2-container--default .select2-selection--multiple .select2-selection__choice {
   
   background-color: #377dff !important;

}

/* .select2 .select2-container .select2-container--default .select2-container--below{
    width: 100% !important;
} */

.select2-container {
    width: 100% !important;
}

.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {

   color: #ffffff !important;

}

</style>

    <!-- Jquery -->

@push('css_or_js')
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-print-none pb-2">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.sales-man.list') }}">
                                    {{ \App\CPU\translate('Salesman') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ \App\CPU\translate('Salesman details') }}</li>
                        </ol>
                    </nav>

                    <div class="d-sm-flex align-items-sm-center">
                        <h1 class="page-header-title">{{ \App\CPU\translate('Salesman ID') }} #{{ $sm['id'] }}</h1>
                        <span class="{{ Session::get('direction') === 'rtl' ? 'mr-2 mr-sm-3' : 'ml-2 ml-sm-3' }}">
                            <i class="tio-date-range">
                            </i> {{ \App\CPU\translate('Joined At') }} :
                            {{ date('d M Y H:i:s', strtotime($sm['created_at'])) }}
                        </span>
                    </div>
                    <div class="row border-top pt-3 mt-3 mb-5">
                        <div class="col-md-6">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                                <i class="tio-home-outlined"></i> {{ \App\CPU\translate('Dashboard') }}
                            </a>
                        </div>
                        <div class="col-md-6">
                            {{-- <a href="{{route('admin.sales-man.add')}}" class="btn btn-primary pull-right"><i
                                    class="tio-add-circle"></i> {{\App\CPU\translate('assign')}} {{\App\CPU\translate('pharmacy')}}
                            </a> --}}
                            <button type="button" class="btn btn-primary pull-right" data-toggle="modal"
                                data-target="#exampleModal">
                                <i class="tio-add-circle"></i> {{ \App\CPU\translate('assign') }}
                                {{ \App\CPU\translate('pharmacy') }}
                            </button>


                            <!-- Modal -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel"><i
                                                    class="tio-add-circle mr-2"></i>Assign Pharmacy</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true" style="font-size: 25px;">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{route('admin.sales-man.assign',['id'=>$sm->id])}}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                {{-- <div style="border: 1px solid black; padding:100px" class="container">
                                                    <div style="padding: 100px;">
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <input  class="prompt" type="text" placeholder="Search countries...">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <a  onclick="btn_handler()" class="btn btn-primary">Submit</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> --}}

                                                <select class="js-example-basic-multiple" name="assigned_pharmacies[]" multiple="multiple">
                                                    @foreach($all_pharmacies as $allp)
                                                    <option value="{{$allp->id}}">{{$allp->name}}</option>
                                                    @endforeach
                                                    
                                                  </select>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>


            </div>
        </div>
        <!-- End Page Header -->

        <div class="row" id="printableArea">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <h5 class="card-header-title">{{ \App\CPU\translate('Pharmacies') }}</h5>
                <div class="card">
                    <div class="card-header">

                        <div class="table-responsive datatable-custom">
                            <table
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ \App\CPU\translate('#') }}</th>
                                        <th style="width: 30%">{{ \App\CPU\translate('name') }}</th>

                                        <th>{{ \App\CPU\translate('region') }}</th>

                                        <th>{{ \App\CPU\translate('action') }}</th>
                                    </tr>
                                </thead>

                                <tbody id="set-rows">
                                    @foreach ($pharmacies as $pharma)
                                        <tr>


                                            <th></th>
                                            <td>
                                                {{ $pharma['name'] }}
                                            </td>
                                            <td>
                                                {{ $pharma['region'] }}
                                            </td>

                                            {{-- <td>
                                            <a class="btn btn-info btn-sm"
                                               href="{{route('admin.sales-man.preview',[$sm['id']])}}">
                                                <i class="tio-visible"></i>{{\App\CPU\translate('view')}}
                                            </a>
                                        </td> --}}
                                            <td>
                                                <!-- Dropdown -->
                                                <div class="dropdown">
                                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="tio-settings"></i>
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                        {{-- <a class="dropdown-item"
                                                       href="{{route('admin.sales-man.edit',[$pharma['id']])}}">{{\App\CPU\translate('edit')}}</a> --}}
                                                        <a class="dropdown-item" href="javascript:"
                                                            onclick="form_alert('sales-man-{{ $pharma['id'] }}','Want to unassign this information ?')">{{ \App\CPU\translate('unassign') }}</a>
                                                        <form
                                                            action="{{ route('admin.sales-man.unassign', [$pharma['id']]) }}"
                                                            method="post" id="sales-man-{{ $pharma['id'] }}">
                                                            @csrf
                                                        </form>
                                                    </div>
                                                </div>
                                                <!-- End Dropdown -->
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <hr>

                            <div class="page-area">
                                <table>
                                    <tfoot>
                                        {!! $pharmacies->links() !!}
                                    </tfoot>
                                </table>
                            </div>

                        </div>

                    </div>
                    <!-- Table -->
                    {{-- <div class="table-responsive datatable-custom">
                        <table
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true
                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CPU\translate('sl')}}#</th>
                                <th style="width: 50%" class="text-center">{{\App\CPU\translate('Order ID')}}</th>
                                <th style="width: 50%">{{\App\CPU\translate('Total')}}</th>
                                <th style="width: 10%">{{\App\CPU\translate('Action')}}</th>
                            </tr>

                            </thead>

                            <tbody>
                            @foreach ($orders as $key => $order)
                                <tr>
                                    <td>{{$orders->firstItem()+$key}}</td>
                                    <td class="table-column-pl-0 text-center">
                                        <a href="{{route('admin.orders.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
                                    </td>
                                    <td> {{\App\CPU\BackEndHelper::set_symbol(\App\CPU\BackEndHelper::usd_to_currency($order['order_amount']))}}</td>

                                    <td>
                                        <!-- Dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                    id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                <i class="tio-settings"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item"
                                                   href="{{route('admin.orders.details',['id'=>$order['id']])}}"><i
                                                        class="tio-visible"></i> {{\App\CPU\translate('View')}}</a>
                                                <a class="dropdown-item" target="_blank"
                                                   href="{{route('admin.orders.generate-invoice',[$order['id']])}}"><i
                                                        class="tio-download"></i> {{\App\CPU\translate('Invoice')}}</a>
                                            </div>
                                        </div>
                                        <!-- End Dropdown -->
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if (count($orders) == 0)
                            <div class="text-center p-4">
                                <img class="mb-3" src="{{asset('public/assets/back-end')}}/svg/illustrations/sorry.svg" alt="Image Description" style="width: 7rem;">
                                <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                            </div>
                        @endif
                        <!-- Footer -->
                        <div class="card-footer">
                            <!-- Pagination -->
                        {!! $orders->links() !!}
                        <!-- End Pagination -->
                        </div>
                        <!-- End Footer -->
                    </div> --}}
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Card -->
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <h4 class="card-header-title">{{ \App\CPU\translate('Salesman') }}</h4>
                    </div>
                    <!-- End Header -->

                    <!-- Body -->


                    @if ($sm)
                        <div class="card-body">
                            <div class="media align-items-center" href="javascript:">

                                <div class="media-body">
                                    <span class="text-body text-hover-primary">{{ $sm['f_name'] . ' ' . $sm['l_name'] }}</span>
                                </div>
                                <div class="media-body text-right">
                                    {{-- <i class="tio-chevron-right text-body"></i> --}}
                                </div>
                            </div>

                            <hr>

                            <div class="media align-items-center" href="javascript:">
                                <div
                                    class="icon icon-soft-info icon-circle {{ Session::get('direction') === 'rtl' ? 'ml-3' : 'mr-3' }}">
                                    <i class="tio-shopping-basket-outlined"></i>
                                </div>

                                <div class="media-body text-right">
                                    {{-- <i class="tio-chevron-right text-body"></i> --}}
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center">
                                <h5>{{ \App\CPU\translate('Contact info') }}</h5>
                            </div>

                            <ul class="list-unstyled list-unstyled-py-2">
                                <li>
                                    <i class="tio-online {{ Session::get('direction') === 'rtl' ? 'ml-2' : 'mr-2' }}"></i>
                                    {{ $sm['email'] }}
                                </li>
                                <li>
                                    <i
                                        class="tio-android-phone-vs {{ Session::get('direction') === 'rtl' ? 'ml-2' : 'mr-2' }}"></i>
                                    {{ $sm['phone'] }}
                                </li>
                            </ul>

                            <hr>


                            {{-- <div class="d-flex justify-content-between align-items-center">
                                <h5>{{\App\CPU\translate('shipping_address')}}</h5>

                            </div>


                            <span class="d-block">
                                @if (isset($order))
                                    {{\App\CPU\translate('Name')}} :
                                    <strong>{{$order->shippingAddress ? $order->shippingAddress['contact_person_name'] : "empty"}}</strong>
                                    <br>
                                    {{\App\CPU\translate('City')}}:
                                    <strong>{{$order->shipping ? $order->shipping['city'] : "Empty"}}</strong><br>
                                    {{\App\CPU\translate('zip_code')}} :
                                    <strong>{{$order->shipping ? $order->shipping['zip']  : "Empty"}}</strong><br>
                                    {{\App\CPU\translate('Phone')}}:
                                    <strong>{{$order->shipping ? $order->shipping['phone']  : "Empty"}}</strong>

                            </span>
                            @endif --}}

                        </div>
                    @endif

                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- End Row -->
    </div>
@endsection

@push('script_2')
    <script>
        $(document).on('ready', function() {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function() {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });


            $('#column3_search').on('change', function() {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function() {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>



    <script>
    var content = [
        {id: 0, text: "Nitin Shah", email:"Nitin@xy"},
        {id: 1, text: "Suhash Shah ",  email:"Suhash@xy"},
        {id: 2, text: "Soumil Shah  ",  email:"Soumil@xy"},
    ];


     $(".prompt").select2({
         data:content,
         // minimumInputLength: 2,
         width: '100%',
         multiple:true,
         placeholder:"Enter First Name",
         // templateResult:formatState

     });



    function btn_handler() {
        var names = $('.prompt').select2('data');
        
        for(let name of names){
            
            console.log(name)
            console.log(name.text)
            console.log(name.email)
            
            alert(`${name.text} - ${name.email}`)

        }
    }

</script>

<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
        console.log('dddddddddddddddddddddddd');
    });
</script>

@endpush
