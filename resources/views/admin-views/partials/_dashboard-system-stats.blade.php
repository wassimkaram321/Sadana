<div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
    <!-- Card -->
    <a class="card card-hover-shadow h-100" href="{{route('admin.orders.list',['pending'])}}" style="background: #FFFFFF">
        <div class="card-body">
            <div class="flex-between align-items-center mb-1">
                <div style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                    <h6 class="card-subtitle" style="color: #413d4b!important;">{{\App\CPU\translate('visitors')}}</h6>
                    <span class="card-title h2" style="color: #413d4b!important;">
                       55
                    </span>
                </div>
                <div class="mt-2">
                    <i class="tio-incognito nav-icon" style="font-size: 30px;color: #413d4b;"></i>
                </div>
            </div>
            <!-- End Row -->
        </div>
    </a>
    <!-- End Card -->
</div>

<div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
    <!-- Card -->
    <a class="card card-hover-shadow h-100" href="{{route('admin.orders.list',['confirmed'])}}" style="background: #FFFFFF;">
        <div class="card-body">
            <div class="flex-between align-items-center mb-1">
                <div style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                    <h6 class="card-subtitle" style="color: #413d4b!important;">{{\App\CPU\translate('pharmacies')}}</h6>
                     <span class="card-title h2" style="color: #413d4b!important;">
                       15
                     </span>
                </div>

                <div class="mt-2">
                    <i class="tio-pharmacy" style="font-size: 30px;color: #413d4b"></i>
                </div>
            </div>
            <!-- End Row -->
        </div>
    </a>
    <!-- End Card -->
</div>

<div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
    <!-- Card -->
    <a class="card card-hover-shadow h-100" href="{{route('admin.orders.list',['processing'])}}" style="background: #FFFFFF">
        <div class="card-body">
            <div class="flex-between align-items-center gx-2 mb-1">
                <div style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                    <h6 class="card-subtitle" style="color: #413d4b!important;">{{\App\CPU\translate('Sales_Man')}}</h6>
                    <span class="card-title h2" style="color: #413d4b!important;">
                        12
                    </span>
                </div>

                <div class="mt-2">
                    <i class="fa fa-user-md" style="font-size: 30px;color: #413d4b"></i>
                </div>
            </div>
            <!-- End Row -->
        </div>
    </a>
    <!-- End Card -->
</div>

<div class="col-sm-6 col-lg-3 mb-3 mb-lg-5">
    <!-- Card -->
    <a class="card card-hover-shadow h-100" href="{{route('admin.orders.list',['out_for_delivery'])}}" style="background: #FFFFFFff">
        <div class="card-body">
            <div class="flex-between align-items-center gx-2 mb-1">
                <div style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                    <h6 class="card-subtitle" style="color: #413d4b!important;">{{\App\CPU\translate('Delivery_Man')}}</h6>
                    <span class="card-title h2" style="color: #413d4b!important;">
                       10
                    </span>
                </div>

                <div class="mt-2">
                    <i class="tio-bike" style="font-size: 30px;color: #413d4b"></i>
                </div>
            </div>
            <!-- End Row -->
        </div>
    </a>
    <!-- End Card -->
</div>

