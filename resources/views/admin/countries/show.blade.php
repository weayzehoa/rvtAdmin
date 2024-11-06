@extends('admin.layouts.master')

@section('title', '國家資料設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>國家資料設定</b><small> ({{ isset($country) ? '修改' : '新增' }})</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('countries') }}">國家資料設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($country) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        @if(isset($country))
        <form id="myform" action="{{ route('admin.countries.update', $country->id) }}" method="POST">
            <input type="hidden" name="_method" value="PATCH">
        @else
        <form id="myform" action="{{ route('admin.countries.store') }}" method="POST">
        @endif
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">{{ $country->name ?? ''}}資料</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-4">
                                            <label for="account"><span class="text-red">* </span>國家名稱</label>
                                            <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ old('name') ?? $country->name ?? '' }}" placeholder="請輸入國家中文名稱">
                                            @if ($errors->has('name'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-4">
                                        <label for="lang"><span class="text-red">* </span>國家代碼</label>
                                        <input type="text" class="form-control {{ $errors->has('lang') ? ' is-invalid' : '' }}" id="lang" name="lang" value="{{ old('lang') ?? $country->lang ?? '' }}" placeholder="請輸入國家代碼">
                                        @if ($errors->has('lang'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('lang') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-4">
                                            <label for="code"><span class="text-red">* </span>電話國際碼</label>
                                            <input type="text" class="form-control {{ $errors->has('code') ? ' is-invalid' : '' }}" id="code" name="code" value="{{ old('code') ?? $country->code ?? '' }}" placeholder="請輸入國際電話代碼">
                                            @if ($errors->has('code'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('code') }}</strong>
                                            </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-4">
                                            <label for="name_en"><span class="text-red">* </span>英文名稱</label>
                                            <input type="text" class="form-control {{ $errors->has('name_en') ? ' is-invalid' : '' }}" id="name_en" name="name_en" value="{{ old('name_en') ?? $country->name_en ?? '' }}" placeholder="請輸入國家英文名稱">
                                            @if ($errors->has('name_en'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name_en') }}</strong>
                                            </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-4">
                                            <label for="name_jp">日文名稱</label>
                                            <input type="text" class="form-control {{ $errors->has('name_jp') ? ' is-invalid' : '' }}" id="name_jp" name="name_jp" value="{{ old('name_jp') ?? $country->name_jp ?? '' }}" placeholder="請輸入國家日文名稱">
                                            @if ($errors->has('name_jp'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name_jp') }}</strong>
                                            </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-4">
                                            <label for="name_kr">韓文名稱</label>
                                            <input type="text" class="form-control {{ $errors->has('name_kr') ? ' is-invalid' : '' }}" id="name_kr" name="name_kr" value="{{ old('name_kr') ?? $country->name_kr ?? '' }}" placeholder="請輸入國家韓文名稱">
                                            @if ($errors->has('name_kr'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name_kr') }}</strong>
                                            </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-4">
                                            <label for="name_th">泰文名稱</label>
                                            <input type="text" class="form-control {{ $errors->has('name_th') ? ' is-invalid' : '' }}" id="name_th" name="name_th" value="{{ old('name_th') ?? $country->name_th ?? '' }}" placeholder="請輸入國家泰文名稱">
                                            @if ($errors->has('name_th'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name_th') }}</strong>
                                            </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-4">
                                            <label for="code">簡訊商</label>
                                            <select class="form-control {{ $errors->has('sms_vendor') ? ' is-invalid' : '' }}" id="sms_vendor" name="sms_vendor">
                                                <option value="">系統預設</option>
                                                <option value="mitake" {{ isset($country) && $country->sms_vendor == 'mitake' ? 'selected' : '' }}>Mitake (三竹) (餘額: {{ $mitakePoints }})</option>
                                                <option value="alibaba" {{ isset($country) && $country->sms_vendor == 'alibaba' ? 'selected' : '' }}>Alibaba (阿里巴巴)</option>
                                                <option value="aws" {{ isset($country) && $country->sms_vendor == 'aws' ? 'selected' : '' }}>AWS (Amazon)</option>
                                                <option value="twilio" {{ isset($country) && $country->sms_vendor == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                            </select>
                                            @if ($errors->has('code'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('code') }}</strong>
                                            </span>
                                            @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-center bg-white">
                                @if(in_array(isset($country) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                <button type="submit" class="btn btn-primary">{{ isset($country) ? '修改' : '新增' }}</button>
                                @endif
                                <a href="{{ url('countries') }}" class="btn btn-info">
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
@endsection

@section('script')
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CountriesRequest', '#myform'); !!}
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

        $('select').change(function (e) {
            let val = $(this).val();
            if( val!=0 ){
                $('#url_d').removeClass('d-none');
                $('#url_type_d').removeClass('d-none');
                $('#power_d').removeClass('d-none');
                $('input[name=url]').val('');
            }else{
                $('#url_d').addClass('d-none');
                $('#url_type_d').addClass('d-none');
                $('#power_d').addClass('d-none');
            }
        });
    })(jQuery);
</script>
@endsection
