@extends('admin.layouts.master')

@section('title', '物流廠商管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>物流廠商管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('shippingvendors') }}">物流廠商管理</a></li>
                        <li class="breadcrumb-item active">{{ isset($vendor) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ $vendor->name ?? '' }} 廠商資料</h3>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="chinese">
                                    @if(isset($vendor))
                                    <form class="myform" action="{{ route('admin.shippingvendors.update', $vendor->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PATCH">
                                        <input type="hidden" name="from" value="{{ $from ?? '' }}">
                                    @else
                                    <form class="myform" action="{{ route('admin.shippingvendors.store') }}" method="POST">
                                        <input type="hidden" name="vendor_id" value="{{ $vendorId ?? '' }}">
                                    @endif
                                        @csrf
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="form-group col-6">
                                                        <label for="name"><span class="text-red">* </span>廠商名稱</label>
                                                        <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $vendor->name ?? old('name') }}" placeholder="輸入廠商名稱">
                                                        @if ($errors->has('name'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('name') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="name_en">廠商英文名稱</label>
                                                        <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name_en" name="name_en" value="{{ $vendor->name_en ?? old('name_en') }}" placeholder="輸入廠商英文名稱">
                                                        @if ($errors->has('name_en'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('name_en') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    {{-- <div class="form-group col-6">
                                                        <label for="tel">廠商電話</label>
                                                        <input type="text" class="form-control {{ $errors->has('tel') ? ' is-invalid' : '' }}" id="tel" name="tel" value="{{ $vendor->tel ?? old('tel') }}" placeholder="輸入廠商電話">
                                                        @if ($errors->has('tel'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('tel') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div> --}}
                                                    <div class="form-group col-6">
                                                        <label for="api_url">API URL</label>
                                                        <input type="text" class="form-control {{ $errors->has('api_url') ? ' is-invalid' : '' }}" id="api_url" name="api_url" value="{{ $vendor->api_url ?? old('api_url') }}" placeholder="輸入API URL，包含 http:// or https://">
                                                        @if ($errors->has('api_url'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('api_url') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-3">
                                                        <label for="is_foreign">國內或國外廠商</label>
                                                        <div class="input-group">
                                                            <input type="checkbox" name="is_foreign" value="1" data-bootstrap-switch data-on-text="國外" data-off-text="國內" data-off-color="secondary" data-on-color="primary" {{ isset($vendor) ? $vendor->is_foreign == 1 ? 'checked' : '' : '' }}>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="form-group col-3">
                                                        <label for="is_on">狀態</label>
                                                        <div class="input-group">
                                                            <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="停權" data-off-color="secondary" data-on-color="primary" {{ isset($vendor) ? $vendor->is_on == 1 ? 'checked' : '' : '' }}>
                                                        </div>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center bg-white">
                                            @if(in_array(isset($vendor) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                            <button type="submit" class="btn btn-primary">{{ isset($vendor) ? '修改' : '新增' }}</button>
                                            @endif
                                            <a href="{{ route('admin.shippingvendors.index') }}" class="btn btn-info">
                                                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\ShippingVendorsRequest', '.myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
    })(jQuery);
</script>
@endsection
