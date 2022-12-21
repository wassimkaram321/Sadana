@extends('layouts.back-end.app')

@section('title', \App\CPU\translate('Plan Details'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid" >
        <!-- Page Header -->
        <div class="page-header mb-1">
            <div class="flex-between align-items-center">
                <div>
                    <h1 class="page-header-title">{{\App\CPU\translate('Plan Pharmacies')}}<span
                            class="badge badge-soft-dark mx-2">{{$PharmaciesPlan->count()}}</span></h1>
                </div>
                <div>
                    <i class="tio-shopping-cart" style="font-size: 30px"></i>
                </div>
            </div>
            <!-- End Row -->

            <!-- Nav Scroller -->
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev" style="display: none;">
              <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                <i class="tio-chevron-left"></i>
              </a>
            </span>

                <span class="hs-nav-scroller-arrow-next" style="display: none;">
              <a class="hs-nav-scroller-arrow-link" href="javascript:;">
                <i class="tio-chevron-right"></i>
              </a>
            </span>

                <!-- Nav -->
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">{{\App\CPU\translate('plan_list')}}</a>
                    </li>
                </ul>
                <!-- End Nav -->
            </div>
            <!-- End Nav Scroller -->
        </div>
        <!-- End Page Header -->

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="row flex-between justify-content-between flex-grow-1">
                    <div class="col-12 col-md-4">
                        {{-- <form action="{{ url()->current() }}" method="GET">
                            <!-- Search -->
                            <div class="input-group input-group-merge input-group-flush">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input id="datatableSearch_" type="search" name="search" class="form-control"
                                       placeholder="{{\App\CPU\translate('Search pharmacy')}}" aria-label="Search pharmacy" value="{{ $search }}"
                                       required>
                                <button style="margin-left: 20px !important;"  type="submit" class="btn btn-primary">{{\App\CPU\translate('search')}}</button>
                            </div>
                            <!-- End Search -->
                        </form> --}}
                    </div>
                    <div class="col-12 col-md-6 mt-2 mt-md-0">


                        {{-- <form style="width: 100%;" action="{{ url()->current() }}">
                            <div class="row justify-content-end text-{{ Session::get('direction') === 'rtl' ? 'right' : 'left' }}">
                                <div style="width: 250px;" class="d-flex">
                                        <select class="js-select2-custom form-control" name="customer_type">
                                            <option value="all" selected>{{ \App\CPU\translate('All') }}</option>
                                            <option value="visited">{{ \App\CPU\translate('Visited') }}
                                            </option>
                                            <option value="not_visited">{{ \App\CPU\translate('Not visited') }}
                                            </option>
                                        </select>

                                     <button style="margin-left: 20px !important;"  type="submit" class="btn btn-primary">
                                        {{ \App\CPU\translate('Filter') }}
                                    </button>
                                 </div>

                            </div>
                        </form> --}}


                    </div>
                </div>
                <!-- End Row -->
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom" style="min-height:150px">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       style="width: 100%; text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}}">
                    <thead class="thead-light">
                    <tr>
                        <th>
                            {{\App\CPU\translate('SL')}}#
                        </th>
                        <th>{{\App\CPU\translate('Pharmacy_Name')}}</th>
                        <th>{{\App\CPU\translate('Note')}}</th>
                        <th>{{\App\CPU\translate('Area')}}</th>
                        <th>{{\App\CPU\translate('Street_address')}}</th>
                        <th>{{\App\CPU\translate('Visit_time ')}}</th>
                        <th>{{\App\CPU\translate('Visit_status')}}</th>
                        <th>{{\App\CPU\translate('Site_match ')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($PharmaciesPlan as $key=>$pharmacyPlan)

                        <tr class="class-all">
                            <td >
                                {{$pharmacyPlan->id}}
                            </td>
                            <td>
                                <a href="#">{{$pharmacyPlan['pharmacy_name']}}</a>
                            </td>

                            <td>
                                @if($pharmacyPlan->Wnote)
                                    <label class="text-body text-capitalize">{{$pharmacyPlan->Wnote}}</label>
                                @else
                                    <label class="badge badge-danger">{{\App\CPU\translate('invalid_note')}}</label>
                                @endif
                            </td>

                            <td>
                                @if($pharmacyPlan->area)
                                    <label class="text-body text-capitalize">{{$pharmacyPlan->area}}</label>
                                @else
                                    <label class="badge badge-danger">{{\App\CPU\translate('invalid_area')}}</label>
                                @endif
                            </td>

                            <td>
                                @if($pharmacyPlan->street_address)
                                    <label class="text-body text-capitalize">{{$pharmacyPlan->street_address}}</label>
                                @else
                                    <label class="badge badge-danger">{{\App\CPU\translate('invalid_address')}}</label>
                                @endif
                            </td>


                            <td>
                                @if($pharmacyPlan->visit_time)
                                    <label class="text-body text-capitalize">{{date('d M Y',strtotime($pharmacyPlan->visit_time))}}</label>
                                @else
                                    <label class="badge badge-danger">{{\App\CPU\translate('invalid_visit_time')}}</label>
                                @endif
                            </td>

                            <td>
                                @if($pharmacyPlan->visited=='visited' || $pharmacyPlan->visited==1)
                                    <span class="badge badge-soft-success">
                                      <span class="legend-indicator bg-success"
                                            style="{{Session::get('direction') === "rtl" ? 'margin-right: 0;margin-left: .4375rem;' : 'margin-left: 0;margin-right: .4375rem;'}}"></span>{{\App\CPU\translate('Visited')}}
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger">
                                      <span class="legend-indicator bg-danger"
                                            style="{{Session::get('direction') === "rtl" ? 'margin-right: 0;margin-left: .4375rem;' : 'margin-left: 0;margin-right: .4375rem;'}}"></span>{{\App\CPU\translate('UnVisited')}}
                                    </span>
                                @endif
                            </td>


                            <td>
                                @if($pharmacyPlan->site_match==1)
                                    <span class="badge badge-soft-success">
                                      <span class="legend-indicator bg-success"
                                            style="{{Session::get('direction') === "rtl" ? 'margin-right: 0;margin-left: .4375rem;' : 'margin-left: 0;margin-right: .4375rem;'}}"></span>{{\App\CPU\translate('Matching')}}
                                    </span>
                                @else
                                    <span class="badge badge-soft-danger">
                                      <span class="legend-indicator bg-danger"
                                            style="{{Session::get('direction') === "rtl" ? 'margin-right: 0;margin-left: .4375rem;' : 'margin-left: 0;margin-right: .4375rem;'}}"></span>{{\App\CPU\translate('Not_matching')}}
                                    </span>
                                @endif
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            <!-- Footer -->
            {{-- <div class="card-footer">
                <!-- Pagination -->
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm-auto">
                        <div class="d-flex justify-content-center justify-content-sm-end">
                            <!-- Pagination -->
                            {!! $PharmaciesPlan->links() !!}
                        </div>
                    </div>
                </div>
                <!-- End Pagination -->
            </div> --}}
            <!-- End Footer -->
        </div>
        <!-- End Card -->
    </div>
@endsection

