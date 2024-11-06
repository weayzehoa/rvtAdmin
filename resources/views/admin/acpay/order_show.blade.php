@extends('admin.layouts.master')

@section('title', 'ACPay訂單管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>ACPay訂單管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('acpayorders') }}">ACPay訂單管理</a></li>
                        <li class="breadcrumb-item active">{{ isset($order) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <form id="myform" action="{{ route('admin.acpayorders.update', $order->id) }}" method="POST">
            <input type="hidden" name="_method" value="PATCH">
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">訂單資料 {{ $order->order_number }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12 mb-2">
                                        <span class="text-bold">收件資訊</span>
                                    </div>
                                    <div class="col-12 col mb-2">
                                        <div class="input-group">
                                            <select class="form-control col-2" disabled>
                                                @foreach($shippingMethods as $shippingMethod)
                                                <option value="{{ $shippingMethod->id }}" {{ $order->shipping_method == $shippingMethod->id ? 'selected' : '' }}>{{ $shippingMethod->name }}</option>
                                                @endforeach
                                            </select>
                                            @if($order->shipping_method == 1 || $order->shipping_method == 2)
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    @if($order->shipping_method == 1)
                                                    班機航班
                                                    @elseif($order->shipping_method == 2)
                                                    旅店名稱
                                                    @endif
                                                </div>
                                            </div>
                                            <input type="text" class="form-control col-4" name="receiver_keyword" value="{{ $order->receiver_keyword ?? '' }}">
                                            @endif
                                            @if($order->shipping_method == 1 || $order->shipping_method == 2)
                                            <div class="input-group-append">
                                                <div class="input-group-text">旅店房號</div>
                                            </div>
                                            <input type="text" class="form-control col-4" name="room_number" value="{{ $order->room_number ?? '' }}">
                                            @endif
                                            @if(!empty($order->shipping_time))
                                            <div class="input-group-append">
                                                <div class="input-group-text">提貨時間</div>
                                            </div>
                                            <input type="text" class="form-control" value="{{ $order->shipping_time ?? '' }}">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col mb-2">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">收件人</div>
                                            </div>
                                            <input type="text" class="form-control col-2" name="receiver_name" value="{{ $order->receiver_name ?? '' }}">
                                            <div class="input-group-append">
                                                <div class="input-group-text">國碼</div>
                                            </div>
                                            <select class="form-control col-1" name="nation">
                                                @foreach($countries as $country)
                                                <option value="{{ $country->code }}">{{ $country->code }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group-append">
                                                <div class="input-group-text">電話</div>
                                            </div>
                                            <input type="text" class="form-control col-2" name="mobile" value="{{ $order->mobile ?? '' }}">
                                            <div class="input-group-append">
                                                <div class="input-group-text">email</div>
                                            </div>
                                            <input type="text" class="form-control" name="receiver_email" value="{{ $order->receiver_email ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col mb-2">
                                        <div class="input-group">
                                            @if(!empty($order->receiver_province))
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">省縣市</div>
                                            </div>
                                            <input type="text" class="form-control col-1" name="receiver_province" value="{{ $order->receiver_province ?? '' }}">
                                            @endif
                                            @if(!empty($order->receiver_city))
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">鄉鎮市區</div>
                                            </div>
                                            <input type="text" class="form-control col-1" name="receiver_city" value="{{ $order->receiver_city ?? '' }}">
                                            @endif
                                            @if(!empty($order->receiver_area))
                                            <div class="input-group-append">
                                                <div class="input-group-text">區域</div>
                                            </div>
                                            <input type="text" class="form-control col-1" name="receiver_area" value="{{ $order->receiver_area ?? '' }}">
                                            @endif
                                            @if(!empty($order->receiver_zip_code))
                                            <div class="input-group-append">
                                                <div class="input-group-text">郵遞區號</div>
                                            </div>
                                            <input type="text" class="form-control col-1" name="receiver_zip_code" value="{{ $order->receiver_zip_code ?? '' }}">
                                            @endif
                                            <div class="input-group-append">
                                                <div class="input-group-text">地址</div>
                                            </div>
                                            <input type="text" class="form-control" name="receiver_address" value="{{ $order->receiver_address ?? '' }}">
                                        </div>
                                    </div>
                                    @if($order->user_memo)
                                    <div class="col-12 col mb-2">
                                        <span>訂單備註：</span><span class="text-danger">{{ $order->user_memo }}</span>
                                    </div>
                                    @endif
                                    @if(count($order->shippings) > 0)
                                    <div class="card-primary card-outline col-12 mb-2"></div>
                                    <div class="col-12 mb-2">
                                        <span class="text-bold">訂單出貨資訊</span>
                                    </div>
                                    @foreach($order->shippings as $shipping)
                                    <div class="col-12 col mb-2">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">運貨單時間</span>
                                            </div>
                                            <input type="text" class="form-control col-2" value="{{ $shipping['shipping_time'] }}" disabled>
                                            <div class="input-group-append">
                                                <div class="input-group-text">快遞公司</div>
                                            </div>
                                            <input type="text" class="form-control col-2" value="台灣宅配通" disabled>
                                            <div class="input-group-append">
                                                <div class="input-group-text">快遞單號</div>
                                            </div>
                                            <input type="text" class="form-control col-2" value="{{ $shipping['shipping_number'] ?? '' }}" disabled>
                                            <div class="input-group-prepend">
                                                <a href="{{ $order->express_api }}{{ $shipping['shipping_number'] }}" target="_blank" class="btn btn-primary"><span>包裹查詢</span></a>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                    <div class="card-primary card-outline col-12 mb-2"></div>
                                    <div class="col-12 mb-2">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">物流單號</span>
                                            </div>
                                            <input type="text" class="form-control {{ $errors->has('shipping_number') ? ' is-invalid' : '' }}" id="shipping_number" name="shipping_number" value="{{ $order->shipping_number ?? '' }}" placeholder="填寫物流單號，多筆物流單請用逗號隔開">
                                            @if ($errors->has('shipping_number'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('shipping_number') }}</strong>
                                            </span>
                                            @endif
                                            <div class="input-group-append">
                                                <span class="input-group-text">訂單狀態</span>
                                            </div>
                                            <select class="form-control" id="status" name="status">
                                                <option value="-2" {{ $order->status == -2 ? 'selected' : '' }}  class="text-danger">已退貨</option>
                                                <option value="-1" {{ $order->status == -1 ? 'selected' : '' }}  class="text-danger">取消訂單</option>
                                                <option value="0"  {{ $order->status == 0 ? 'selected' : ''  }} class="text-secondary">訂單成立，尚未付款</option>
                                                <option value="1"  {{ $order->status == 1 ? 'selected' : ''  }} class="text-primary">已付款，等待出貨</option>
                                                <option value="2"  {{ $order->status == 2 ? 'selected' : ''  }} class="text-info">訂單集貨中</option>
                                                <option value="3"  {{ $order->status == 3 ? 'selected' : ''  }} class="text-success">訂單已出貨</option>
                                            </select>
                                            @if ($errors->has('admin_memo'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('admin_memo') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <p class="text-danger">填寫物流單號，多筆物流單請用逗號隔開</p>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">管理員備註</span>
                                            </div>
                                            <input type="text" class="form-control {{ $errors->has('admin_memo') ? ' is-invalid' : '' }}" id="admin_memo" name="admin_memo" value="{{ $order->admin_memo ?? '' }}" placeholder="填寫管理員備註">
                                            @if ($errors->has('admin_memo'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('admin_memo') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-center bg-white">
                                @if(in_array(isset($order) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                <button type="submit" class="btn btn-primary">更新訂單資訊</button>
                                @endif
                                <a href="{{ url('acpayorders') }}" class="btn btn-info">
                                    <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Vendor\OrderUpdateRequest', '#myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
    })(jQuery);
</script>
@endsection
