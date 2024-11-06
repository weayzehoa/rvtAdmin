@extends('admin.layouts.master')

@section('title', '商家分店管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家分店管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('vendorshops') }}">商家分店管理</a></li>
                        <li class="breadcrumb-item active">{{ isset($shop) ? '修改' : '新增' }}</li>
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
                            <h3 class="card-title">{{ $shop->name ?? '' }} 分店資料</h3>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="chinese">
                                    @if(isset($shop))
                                    <form id="myform" action="{{ route('admin.vendorshops.update', $shop->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PATCH">
                                        <input type="hidden" name="vendor_id" value="{{ $shop->vendor_id }}">
                                        <input type="hidden" name="from" value="{{ $from ?? '' }}">
                                    @else
                                    <form id="myform_create" action="{{ route('admin.vendorshops.store') }}" method="POST">
                                        <input type="hidden" name="vendor_id" value="{{ $vendorId ?? '' }}">
                                    @endif
                                        @csrf
                                        <div class="row">
                                            @if(isset($shop))
                                            <div class="col-12">
                                                <label><span class="text-red">* </span>所屬商家店名或品牌</label>
                                                @if(in_array('M2S1',explode(',',Auth::user()->power)))
                                                <p><label><a href="{{ url('vendors/'.$shop->vendor->id) }}">　{{ $shop->vendor->name }}</a></label></p>
                                                @else
                                                <p><label>　{{ $shop->vendor->name }}</label></p>
                                                @endif
                                                <div></div>
                                            </div>
                                            @endif
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="form-group col-6">
                                                        <label for="name"><span class="text-red">* </span>分店名稱</label>
                                                        <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $shop->name ?? old('name') }}" placeholder="輸入分店名稱">
                                                        @if ($errors->has('name'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('name') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="tel"><span class="text-red">* </span>分店電話</label>
                                                        <input type="text" class="form-control {{ $errors->has('tel') ? ' is-invalid' : '' }}" id="tel" name="tel" value="{{ $shop->tel ?? old('tel') }}" placeholder="輸入分店電話">
                                                        @if ($errors->has('tel'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('tel') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="address"><span class="text-red">* </span>分店地址</label>
                                                        <input type="text" class="form-control {{ $errors->has('address') ? ' is-invalid' : '' }}" id="address" name="address" value="{{ $shop->address ?? old('address') }}" placeholder="輸入詳細分店地址，ex:台北市中山區南京東路三段103號">
                                                        @if ($errors->has('address'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('address') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-4">
                                                        <label for="location">分店座標 <a href="{{ asset('img/get_location.png') }}" target="_blank">(如何取得?)</a></label>
                                                        <input type="text" class="form-control {{ $errors->has('location') ? ' is-invalid' : '' }}" id="location" name="location" value="{{ $shop->location ?? old('lcation') }}" placeholder="輸入地址開啟Google地圖查詢分店座標">
                                                        @if ($errors->has('location'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('location') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-2">
                                                        <label for="location">Google 地圖查證</label>
                                                        <div>
                                                            <a id="GoogleMapLink" href="https://maps.google.com/maps?q={{ isset($shop) ? $shop->address ?? '' : '' }}" class="btn btn-sm btn-success" target="_blank">{{ isset($shop) ? $shop->address ? '查證' : '開啟Google地圖' : '開啟Google地圖' }}</a>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="is_on">狀態</label>
                                                        <div class="input-group">
                                                            <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="開啟" data-off-text="關閉" data-off-color="secondary" data-on-color="primary" {{ isset($shop) ? $shop->is_on == 1 ? 'checked' : '' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center bg-white">
                                            @if(in_array(isset($shop) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                            <button type="submit" class="btn btn-primary">{{ isset($shop) ? '修改' : '新增' }}</button>
                                            @endif
                                            <a href="javascript:history.back( )" class="btn btn-info">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\VendorShopsRequest', '#myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $("input[name=address]").change(function (e) {
            var q = $("input[name=address]").val();
            $("#GoogleMapLink").attr('href','https://maps.google.com/maps?q='+q);
        });
    })(jQuery);
</script>
@endsection
