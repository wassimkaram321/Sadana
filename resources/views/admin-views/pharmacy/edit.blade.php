@extends('layouts.back-end.app')

@section('title',\App\CPU\translate('Update Pharmacy'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-edit"></i> {{\App\CPU\translate('update')}} {{\App\CPU\translate('Pharmacy')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">

                        <form action="{{route('admin.customer.update',[$user['id']])}}" method="post"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('first')}} {{\App\CPU\translate('name')}}</label>
                                        <input type="text" value="{{$user['f_name']}}" name="f_name"
                                               class="form-control" placeholder="First Name"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('last')}} {{\App\CPU\translate('name')}}</label>
                                        <input type="text" value="{{$user['l_name']}}" name="l_name"
                                               class="form-control" placeholder="Last Name"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('phone')}}</label>
                                        <input type="number" value="{{$user['phone']}}" name="phone"
                                               class="form-control" placeholder="Phone"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('email')}}</label>
                                        <input type="email" value="{{$user['email']}}" name="email"
                                               class="form-control" placeholder="Email"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('street_address')}}</label>
                                        <input type="text" value="{{$user['street_address']}}" name="street_address"
                                               class="form-control" placeholder="Street address"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('country')}}</label>
                                        <input type="text" value="{{$user['country']}}" name="country"
                                               class="form-control" placeholder="Country"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('city')}}</label>
                                        <input type="text" value="{{$user['city']}}" name="city"
                                               class="form-control" placeholder="City"
                                               required>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('region')}}</label>
                                        <input type="text" value="{{$user->pharmacy['region']}}" name="region"
                                               class="form-control" placeholder="Region"
                                               required>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('zip')}}</label>
                                        <input type="number" value="{{$user['zip']}}" name="zip"
                                               class="form-control" placeholder="Zip code"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('user_type')}}</label>
                                        <input type="text" value="{{$user['user_type']}}" name="user_type" class="form-control"
                                               placeholder="User type"
                                               required>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('latitude')}}</label>
                                        <input type="text" value="{{$user->pharmacy['lat']}}" name="lat"
                                               class="form-control" placeholder="Latitude"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('longitude')}}</label>
                                        <input type="text" value="{{$user->pharmacy['lan']}}" name="lan" class="form-control"
                                               placeholder="Longitude"
                                               required>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('pharmacy name')}}</label>
                                        <input type="text" value="{{$user->pharmacy['name']}}" name="name"
                                               class="form-control" placeholder="Pharmacy name"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('address')}}</label>
                                        <input type="text" value="{{$user->pharmacy['Address']}}" name="Address" class="form-control"
                                               placeholder="Address"
                                               required>
                                    </div>
                                </div>

                            </div>


                            <div class="row">

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('Work_start_time')}}</label>
                                        <input type="time" value="{{$user->pharmacy['from']}}" name="from"
                                               class="form-control" placeholder="9:00AM"
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('Work_end_time')}}</label>
                                        <input type="time" value="{{$user->pharmacy['to']}}" name="to" class="form-control"
                                               placeholder=""
                                               required>
                                    </div>
                                </div>

                            </div>

                             <div class="row">

                                <div class="col-md-6 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{\App\CPU\translate('land_number')}}</label>
                                        <input type="number" value="{{$user->pharmacy['land_number']}}" name="land_number"
                                               class="form-control" placeholder="Land_number"
                                               required>
                                    </div>
                                </div>

                            </div>

                            <button type="submit" class="btn btn-primary">{{\App\CPU\translate('update')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });
    </script>

    <script src="{{asset('public/assets/back-end/js/spartan-multi-image-picker.js')}}"></script>
    <script type="text/javascript">
        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: '120px',
                groupClassName: 'col-2',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('public/assets/back-end/img/400x400/img2.jpg')}}',
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
                onExtensionErr: function (index, file) {
                    toastr.error('Please only input png or jpg type file', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('File size too big', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>
@endpush
