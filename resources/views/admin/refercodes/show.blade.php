@extends('admin.layouts.master')

@section('title', '推薦註冊碼設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>推薦註冊碼設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('refercodes') }}">推薦註冊碼設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($referCode) ? '修改' : '新增' }}</li>
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
                            <h3 class="card-title"> {{ isset($referCode) ? '推薦註冊碼 '.$referCode->code.' 資料' : '推薦註冊碼資料' }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="chinese">
                                    @if(isset($referCode))
                                    <form class="myform" action="{{ route('admin.refercodes.update', $referCode->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PATCH">
                                    @else
                                    <form class="myform" action="{{ route('admin.refercodes.store') }}" method="POST">
                                    @endif
                                        @csrf
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="form-group col-6">
                                                        <label for="code"><span class="text-red">* </span>推薦註冊碼</label>
                                                        <input type="text" class="form-control {{ $errors->has('code') ? ' is-invalid' : '' }}" id="code" name="code" value="{{ $referCode->code ?? old('code') }}" placeholder="輸入推薦註冊碼">
                                                        @if ($errors->has('code'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('code') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="icarry_point"><span class="text-red">* </span>贈送購物金</label>
                                                        <input type="number" class="form-control {{ $errors->has('icarry_point') ? ' is-invalid' : '' }}" id="icarry_point" name="icarry_point" value="{{ $referCode->icarry_point ?? old('icarry_point') }}" placeholder="輸入贈送購物金">
                                                        @if ($errors->has('icarry_point'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('icarry_point') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="start_time"><span class="text-red">* </span>活動開始時間</label>
                                                        <input type="datetime" class="form-control {{ $errors->has('start_time') ? ' is-invalid' : '' }} datetimepicker" id="start_time" name="start_time" value="{{ $referCode->start_time ?? old('start_time') }}" placeholder="輸入活動開始時間">
                                                        @if ($errors->has('start_time'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('start_time') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="end_time"><span class="text-red">* </span>活動結束時間</label>
                                                        <input type="datetime" class="form-control {{ $errors->has('end_time') ? ' is-invalid' : '' }} datetimepicker" id="end_time" name="end_time" value="{{ $referCode->end_time ?? old('end_time') }}" placeholder="輸入活動結束時間">
                                                        @if ($errors->has('end_time'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('end_time') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="memo">備註</label>
                                                        <input type="text" class="form-control {{ $errors->has('memo') ? ' is-invalid' : '' }}" id="memo" name="memo" value="{{ $referCode->memo ?? old('memo') }}" placeholder="輸入備註">
                                                        @if ($errors->has('memo'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('memo') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="icarry_point_type">獲得購物金的原因，一般情況下不用填寫</label>
                                                        <input type="text" class="form-control {{ $errors->has('icarry_point_type') ? ' is-invalid' : '' }}" id="icarry_point_type" name="icarry_point_type" value="{{ $referCode->icarry_point_type ?? old('icarry_point_type') }}" placeholder="輸入獲得購物金的原因">
                                                        @if ($errors->has('icarry_point_type'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('icarry_point_type') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    @if(in_array($menuCode.'O', explode(',',Auth::user()->power)))
                                                    <div class="form-group col-3">
                                                        <label for="status">啟用狀態</label>
                                                        <div class="input-group">
                                                            <input type="checkbox" name="status" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="secondary" data-on-color="primary" {{ isset($referCode) ? $referCode->status == 1 ? 'checked' : '' : 'checked' }}>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(isset($referCode))
                                                    <div class="form-group col-3">
                                                        <label for="is_on">目前推薦人數</label>
                                                        <div class="input-group text-bold">
                                                            <a href="{{ url('users?referCode='.$referCode->code) }}">{{ count($referCode->users) }}</a>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center bg-white">
                                            @if(in_array(isset($referCode) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                            <button type="submit" class="btn btn-primary">{{ isset($referCode) ? '修改' : '新增' }}</button>
                                            @endif
                                            <a href="{{ route('admin.refercodes.index') }}" class="btn btn-info">
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
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\ReferCodesRequest', '.myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
        // date time picker 設定
        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
    })(jQuery);
</script>
@endsection
