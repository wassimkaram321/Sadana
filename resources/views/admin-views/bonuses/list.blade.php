<?php
use \App\Model\Product;
?>
@extends('layouts.back-end.app')

@section('title', \App\CPU\translate('Bonuses List'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-2">
            <h1 class="h3 mb-0 text-black-50">{{\App\CPU\translate('bonuses')}} <span style="color: rgb(252, 59, 10);">({{ $bonuses->total() }})</span></h1>
        </div>

        <div class="row" style="margin-top: 20px">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">


                        <!-- Search -->
                        {{-- <form action="{{ url()->current() }}" method="GET">
                            <div class="input-group input-group-merge input-group-flush">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                    placeholder="{{ \App\CPU\translate('Search')}} {{ \App\CPU\translate('bonuses')}}" aria-label="Search orders" value="{{ $search }}" required>
                                <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('Search')}}</button>
                            </div>
                        </form> --}}
                        <!-- End Search -->


                    </div>
                    <div class="card-body" style="padding: 0">
                        <div class="table-responsive">
                            <table style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                 
                                    <th scope="col">{{ \App\CPU\translate('Main Product')}}</th>
                                    <th scope="col">{{ \App\CPU\translate('Quantity')}}</th>
                                    {{-- <th scope="col">{{ \App\CPU\translate('Second Products')}}</th>
                                    <th scope="col">{{ \App\CPU\translate('Second Products Quantity')}}</th> --}}
                                    <th scope="col" style="width: 100px" class="text-center">
                                        {{ \App\CPU\translate('action')}}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($bonuses as $k=>$b)
                                
                                    <tr>
                                        
                                        {{-- <td class="text-center">{{$bonuses->firstItem()+$k}}</td> --}}
                                        @php
                                            
                                            $master_name = Product::where('id',$b->master_product_id)->pluck('name')->first();
                                            $slave_name = Product::where('id',$b->salve_product_id)->pluck('name')->first();
                                        @endphp
                                        
                                          <td>
                                            {{$master_name}}
                                        {{-- @foreach($master_name as $n)
                                      
                                            {{$n['id']}}<br>
                                        
                                        @endforeach --}}
                                    </td>
                                    {{-- <td>
                                        {{$b['master_product_id']}}
                                    </td> --}}
                                    <td>
                                        {{$b['master_product_quatity']}}
                                    </td>
                                    
                                    {{-- <td>
                                        {{$slave_name}}
                                    </td>
                                    <td>
                                        {{$b['salve_product_id']}}
                                    </td> --}}
                                        
                                        {{-- <td>{{$b['main_product_id']}}</td> --}}
                                        <td>
                                            {{-- <a class="btn btn-primary btn-sm"
                                               href="{{route('admin.points.points_edit',['id'=>$b['id']])}}">
                                                <i class="tio-edit"></i> {{ \App\CPU\translate('Edit')}}
                                            </a> --}}
                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                            data-target="#exampleModal" >
                                            <i class="tio-add-circle"></i> 
                                            {{ \App\CPU\translate('View') }}
                                        </button>
                                            <a class="btn btn-danger btn-sm delete1"
                                               id="{{$b['master_product_id']}}">
                                                <i class="tio-add-to-trash"></i> {{ \App\CPU\translate('Delete')}}
                                            </a>
                                            
                                        </td>
                                        
                                    </tr>
                                    
                                @endforeach

                                </tbody>
                            </table>

                        </div>
                    </div>
                    <div class="card-footer">
                        {{$bonuses->links()}}
                    </div>
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog centered" style="justify-content: center">
                        <div class="modal-content" style="width: 800px !important;height: 800px;">
                            <div class="modal-header">
                                {{-- <h5 class="modal-title" id="exampleModalLabel"><i
                                        class="tio-add-circle mr-2"></i>Seconds Products</h5> --}}
                                {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true" style="font-size: 25px;">&times;</span>
                                </button> --}}
                                <div class="table-responsive">
                                    <table style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                                        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                        <thead class="thead-light">
                                        <tr>
                                         
                                          
                                            <th scope="col">{{ \App\CPU\translate('Second Products')}}</th>
                                            <th scope="col">{{ \App\CPU\translate('Second Products Quantity')}}</th>
                                            <th scope="col" style="width: 100px" class="text-center">
                                                {{ \App\CPU\translate('action')}}
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
        
                                        @foreach($bonuses1 as $b)
                                        @foreach($b as $c)
                                        
                                            <tr>
                                                
                                                {{-- <td class="text-center">{{$bonuses->firstItem()+$k}}</td> --}}
                                                @php
                                                    
                                                    // $master_name = Product::where('id',$c->master_product_id)->pluck('name')->first();
                                                    $slave_name = Product::where('id',$c->salve_product_id)->pluck('name')->first();
                                                @endphp
                                                
                                                  
                                            
                                           
                                            
                                            <td>
                                                {{$slave_name}}
                                            </td>
                                            <td>
                                                {{$c['salve_product_quatity']}}
                                            </td>
                                                
                                                {{-- <td>{{$b['main_product_id']}}</td> --}}
                                                <td>
                                                    {{-- <a class="btn btn-primary btn-sm"
                                                       href="{{route('admin.points.points_edit',['id'=>$b['id']])}}">
                                                        <i class="tio-edit"></i> {{ \App\CPU\translate('Edit')}}
                                                    </a> --}}
                                                    <a class="btn btn-danger btn-sm delete"
                    
                                                       id="{{$c['salve_product_id']}}">
                                                        <i class="tio-add-to-trash"></i> {{ \App\CPU\translate('Delete')}}
                                                    </a>
                                                    
                                                </td>
                                                
                                            </tr>
                                            
                                        @endforeach
                                        @endforeach
        
                                        </tbody>
                                    </table>
        
                                </div>
                            </div>
                           
                        </div>
                    </div>
                </div>
                    @if(count($bonuses)==0)
                        <div class="text-center p-4">
                            <img class="mb-3" src="{{asset('public/assets/back-end')}}/svg/illustrations/sorry.svg" alt="Image Description" style="width: 7rem;">
                            <p class="mb-0">{{ \App\CPU\translate('No_data_to_show')}}</p>
                        </div>
                    @endif
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
                title: '{{ \App\CPU\translate('Are_you_sure_delete_this_product')}}?',
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
                        url: "{{route('admin.bonuses.delete_sec')}}",
                        method: 'POST',
                        data: {id: id},
                        success: function () {
                            toastr.success('{{ \App\CPU\translate('store_deleted_successfully')}}');
                            location.reload();
                        }
                    });
                }
            })
        });
    </script>
    <script>
        $(document).on('click', '.delete1', function () {
            var id = $(this).attr("id");
            Swal.fire({
                title: '{{ \App\CPU\translate('Are_you_sure_delete_this_product')}}?',
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
                        url: "{{route('admin.bonuses.delete')}}",
                        method: 'POST',
                        data: {id: id},
                        success: function () {
                            toastr.success('{{ \App\CPU\translate('store_deleted_successfully')}}');
                            location.reload();
                        }
                    });
                }
            })
        });
    </script>
@endpush
