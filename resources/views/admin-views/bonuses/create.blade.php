@extends('layouts.back-end.app')
@section('title', \App\CPU\translate('Bonus Add'))
<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice {

        background-color: #377dff !important;

    }


    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {

        color: #ffffff !important;

    }

    .select2-selection {
        /* .selection{ */

        height: 41.89px !important;
        border: 0.0625rem solid #e7eaf3 !important;

    }
</style>
@push('css_or_js')
    <link href="{{ asset('public/assets/back-end/css/croppie.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Custom styles for this page -->
    <link href="{{ asset('public/assets/back-end') }}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a
                        href="{{ route('admin.dashboard') }}">{{ \App\CPU\translate('Dashboard') }}</a></li>
                <li class="breadcrumb-item" aria-current="page">{{ \App\CPU\translate('store') }}</li>
            </ol>
        </nav>

        <!-- Content Row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        {{ \App\CPU\translate('Add') }} {{ \App\CPU\translate('new') }}
                        {{ \App\CPU\translate('Bonus') }}
                    </div>
                    <div class="card-body"
                        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">
                        <form action="{{ route('admin.bonuses.store') }}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="row">


                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="store_name">{{ \App\CPU\translate('Main Product') }}</label>
                                        <select required class="js-example-basic-multiple" name="first_product">
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="form-group">
                                            <label for="quantity">{{ \App\CPU\translate('Quantity') }}</label>
                                            <input type="number" name="first_product_q" class="form-control"
                                                id="first_product_q" value="{{ old('quantity') }}"
                                                placeholder="{{ \App\CPU\translate('Ex') }} : {{ \App\CPU\translate('2') }}">
                                        </div>
                                    </div>
                                </div>


                                <div id="id3" class="col-md-12">
                                    <div class="form-group">
                                        <label for="store_name">{{ \App\CPU\translate('Other Products') }}</label>
                                        <select id="dnd" onchange="myFunction(this)" required
                                            class="js-example-basic-multiple" name="sec_products[]" multiple="multiple">
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        {{-- <div class="form-group">
                                            <label for="quantity">{{ \App\CPU\translate('Quantity') }}</label>
                                            <input type="number" name="sec_products_q" class="form-control"
                                                id="sec_products_q" value="{{ old('quantity') }}"
                                                placeholder="{{ \App\CPU\translate('Ex') }} : {{ \App\CPU\translate('2') }}">
                                        </div> --}}
                                    </div>
                                    <div id="main"class="form-group"></div>
                                </div>
                               

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('public/assets/back-end') }}/js/select2.min.js"></script>
    <script>
        $(".js-example-theme-single").select2({
            theme: "classic"
        });

        $(".js-example-responsive").select2({
            width: 'resolve'
        });
    </script>

    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileUpload").change(function() {
            readURL(this);
        });


        $(document).ready(function() {
            $('#dataTable').DataTable();
        });
        $(document).on('click', '.delete', function() {
            var id = $(this).attr("id");
            Swal.fire({
                title: '{{ \App\CPU\translate('are_you_sure?') }}',
                text: "{{ \App\CPU\translate('You_will_not_be_able_to_revert_this!') }}",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ \App\CPU\translate('Yes') }} {{ \App\CPU\translate('delete_it') }}!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ route('admin.brand.delete') }}",
                        method: 'POST',
                        data: {
                            id: id
                        },
                        success: function() {
                            toastr.success(
                                '{{ \App\CPU\translate('Brand_deleted_successfully') }}');
                            location.reload();
                        }
                    });
                }
            })
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2();
        });
    </script>
    <script type="text/javascript">
        function myFunction(sel) {
            var selected = [];
            for (var option of document.getElementById('dnd').options) {
                if (option.selected) {
                    selected.push(option.value);
                }
            }

                var newForm = document.createElement("input");
                
                var inputContainer = document.getElementById("main");
                newForm.setAttribute("type", "number");
                newForm.setAttribute("class", "form-control");
                if(!document.getElementById("form"+selected[selected.length-1])){
                newForm.setAttribute("id", "form" + selected[selected.length-1]);
                newForm.setAttribute("name", "form" +'[]');
                
                newForm.setAttribute("placeholder", "Quantity of product " + selected[selected.length-1]);
                
                inputContainer.appendChild(newForm);
                }
                // inputContainer.appendChild(document.createElement("br"));
            // for (var i = 0; i < selected.length; i++) {
                
               
            // }
            for (var option of document.getElementById('dnd').options) {
                if (!option.selected) {
                    // selected.push(option.value);
                    // var element = document.getElementById("Photo1");
                    if(document.getElementById("form"+option.value)){
                        var child=document.getElementById("form"+option.value);
                        child.parentNode.removeChild(child);
                    }
                }
            }

            //     var opts = [], opt;
            //     var len = sel.options.length;
            //     for (var i = 0; i < len; i++) {
            //         opt = sel.options[i];

            //         if (opt.selected) {
            //         opts.push(opt);
            //         // console.log(opt);
            //         // alert(opt.value);
            //     }
            // }
            // // console.log('mmm',opts[opts.length-1].value);
            // var name = opts[opts.length-1].value;

            //     $('#main').append('<div id= "div1"class="form-group c"><label></label><input type="number"></div>');
        }
    </script>
@endpush
