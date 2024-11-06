@extends('admin.layouts.master')

@section('title', '系統參數設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>系統參數設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('systemsettings') }}">系統參數設定</a></li>
                        <li class="breadcrumb-item active">修改系統參數</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            @if(isset($siteSetup))
            <form id="myform" action="{{ route('admin.systemsettings.update', $siteSetup->id) }}" method="POST">
            <input type="hidden" name="_method" value="PATCH">
                @csrf
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">預設參數</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="form-group col-md-2">
                                        <label for="exchange_rate_RMB"><span class="text-red">* </span>人民幣匯率</label>
                                        <input type="number" step="0.1" class="form-control {{ $errors->has('exchange_rate_RMB') ? ' is-invalid' : '' }}" id="exchange_rate_RMB" name="exchange_rate_RMB" value="{{ $siteSetup->exchange_rate ?? '' }}" placeholder="請輸入人民幣匯率">
                                        @if ($errors->has('exchange_rate_RMB'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('exchange_rate_RMB') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="exchange_rate_HKD"><span class="text-red">* </span>港幣匯率</label>
                                        <input type="number" step="0.1" class="form-control {{ $errors->has('exchange_rate_HKD') ? ' is-invalid' : '' }}" id="exchange_rate_HKD" name="exchange_rate_HKD" value="{{ $siteSetup->exchange_rate_HKD ?? '' }}" placeholder="請輸入港幣匯率">
                                        @if ($errors->has('exchange_rate_HKD'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('exchange_rate_HKD') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="exchange_rate_SGD"><span class="text-red">* </span>新加坡幣匯率</label>
                                        <input type="number" step="0.1" class="form-control {{ $errors->has('exchange_rate_SGD') ? ' is-invalid' : '' }}" id="exchange_rate_SGD" name="exchange_rate_SGD" value="{{ $siteSetup->exchange_rate_SGD ?? '' }}" placeholder="請輸入新加坡幣匯率">
                                        @if ($errors->has('exchange_rate_SGD'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('exchange_rate_SGD') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="exchange_rate_MYR"><span class="text-red">* </span>馬來西亞幣匯率</label>
                                        <input type="number" step="0.1" class="form-control {{ $errors->has('exchange_rate_MYR') ? ' is-invalid' : '' }}" id="exchange_rate_MYR" name="exchange_rate_MYR" value="{{ $siteSetup->exchange_rate_MYR ?? '' }}" placeholder="請輸入馬來西亞幣匯率">
                                        @if ($errors->has('exchange_rate_MYR'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('exchange_rate_MYR') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="exchange_rate_USD"><span class="text-red">* </span>美金匯率</label>
                                        <input type="number" step="0.1" class="form-control {{ $errors->has('exchange_rate_USD') ? ' is-invalid' : '' }}" id="exchange_rate_USD" name="exchange_rate_USD" value="{{ $siteSetup->exchange_rate_USD ?? '' }}" placeholder="請輸入美金匯率">
                                        @if ($errors->has('exchange_rate_USD'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('exchange_rate_USD') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label>最後修改管理員</label>
                                        <br><span><b>{{ $system->admin->name }}</b> {{ $system->updated_at }}</span>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="airport_shipping_fee"><span class="text-red">* </span>機場提貨運費</label>
                                        <input type="number" step="1" class="form-control {{ $errors->has('airport_shipping_fee') ? ' is-invalid' : '' }}" id="airport_shipping_fee" name="airport_shipping_fee" value="{{ $siteSetup->airport_shipping_fee ?? '' }}" placeholder="機場提貨酌收費用">
                                        @if ($errors->has('airport_shipping_fee'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('airport_shipping_fee') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="airport_shipping_fee_over_free"><span class="text-red">* </span>New!機場提貨免運費門檻</label>
                                        <input type="number" step="1" class="form-control {{ $errors->has('airport_shipping_fee_over_free') ? ' is-invalid' : '' }}" id="airport_shipping_fee_over_free" name="airport_shipping_fee_over_free" value="{{ $siteSetup->airport_shipping_fee_over_free ?? '' }}" placeholder="大於等於 >= 多少台幣免運費">
                                        @if ($errors->has('airport_shipping_fee_over_free'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('airport_shipping_fee_over_free') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="shipping_fee"><span class="text-red">* </span>國內基本運費(旅店或寄送)</label>
                                        <input type="number" step="1" class="form-control {{ $errors->has('shipping_fee') ? ' is-invalid' : '' }}" id="shipping_fee" name="shipping_fee" value="{{ $siteSetup->shipping_fee ?? '' }}" placeholder="國內件一律酌收費用">
                                        @if ($errors->has('shipping_fee'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('shipping_fee') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="shipping_fee_over_free"><span class="text-red">* </span>國內免運費門檻</label>
                                        <input type="number" step="0.1" class="form-control {{ $errors->has('shipping_fee_over_free') ? ' is-invalid' : '' }}" id="shipping_fee_over_free" name="shipping_fee_over_free" value="{{ $siteSetup->shipping_fee_over_free ?? '' }}" placeholder="大於等於 >= 多少台幣免運費">
                                        @if ($errors->has('shipping_fee_over_free'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('shipping_fee_over_free') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="pre_order_start_date">提貨日設定＋1天物流日（預購檔期專用）:</label>
                                        <div class="input-group">
                                            <input type="datetime" class="form-control datepicker" id="pre_order_start_date" name="pre_order_start_date" placeholder="格式：2016-06-06" value="{{ $siteSetup->pre_order_start_date }}">
                                            @if ($errors->has('pre_order_start_date'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('pre_order_start_date') }}</strong>
                                            </span>
                                            @endif
                                            <span class="input-group-addon bg-primary">~</span>
                                            <input type="datetime" class="form-control datepicker" id="pre_order_end_date" name="pre_order_end_date" placeholder="格式：2016-06-06" value="{{ $siteSetup->pre_order_end_date }}">
                                            @if ($errors->has('pre_order_end_date'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('pre_order_end_date') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center bg-white">
                        @if(in_array(isset($siteSetup) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                        <button type="submit" class="btn btn-primary confirm-btn">{{ isset($siteSetup) ? '修改' : '新增' }}</button>
                        @endif
                        <a href="{{ url('dashboard') }}" class="btn btn-info">
                            <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                        </a>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </section>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\SystemSettingsRequest', '#myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $('.datepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $(".confirm-btn").click(function() {
            if(confirm('系統參數設定切換，與全站系統相關，請勿任意修改，\n請確認無誤後再按確定送出。')){
                $('#myform').submit();
            }
        });
    })(jQuery);
</script>
@endsection
