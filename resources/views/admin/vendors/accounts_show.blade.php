@extends('admin.layouts.master')

@section('title', '商家帳號管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家帳號管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('vendoraccounts') }}">商家帳號管理</a></li>
                        <li class="breadcrumb-item active">{{ isset($account) ? '修改' : '新增' }}</li>
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
                            <h3 class="card-title">{{ $account->name ?? '' }} 帳號資料</h3>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="chinese">
                                    @if(isset($account))
                                    <form class="myform" action="{{ route('admin.vendoraccounts.update', $account->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PATCH">
                                        <input type="hidden" name="from" value="{{ $from ?? '' }}">
                                    @else
                                    <form class="myform" action="{{ route('admin.vendoraccounts.store') }}" method="POST">
                                        <input type="hidden" name="vendor_id" value="{{ $vendorId ?? '' }}">
                                    @endif
                                        @csrf
                                        <div class="row">
                                            @if(isset($account))
                                            <div class="col-12">
                                                <label><span class="text-red">* </span>所屬商家店名或品牌</label>
                                                @if(in_array('M2S1',explode(',',Auth::user()->power)))
                                                <p><label><a href="{{ url('vendors/'.$account->vendor_id) }}">　{{ $account->vendor->name }}</a></label></p>
                                                @else
                                                <p><label>　{{ $account->vendor->name }}</label></p>
                                                @endif
                                            </div>
                                            @endif
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="form-group col-6">
                                                        <label for="account"><span class="text-red">* </span>帳號</label>
                                                        <input type="text" class="form-control {{ $errors->has('account') ? ' is-invalid' : '' }}" id="account" name="account" value="{{ $account->account ?? old('account') }}" placeholder="輸入帳號">
                                                        @if ($errors->has('account'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('account') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="password"><span class="text-red">* </span>密碼</label>
                                                        <input type="text" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" id="password" name="password" value="{{ old('password') }}" placeholder="{{ isset($account) ? '留空白代表不修改密碼' : '輸入密碼' }}">
                                                        @if ($errors->has('password'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('password') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="name"><span class="text-red">* </span>姓名</label>
                                                        <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $account->name ?? old('name') }}" placeholder="店名或品牌">
                                                        @if ($errors->has('name'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('name') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="email">電子信箱</label>
                                                        <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" id="email" name="email" value="{{ $account->email ?? old('email') }}" placeholder="聯絡人電子信箱">
                                                        @if ($errors->has('email'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('email') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="address"><span class="text-red">* </span>所屬分店</label>
                                                        <select class="form-control" name="shop_id">
                                                            <option value="0" {{ isset($account) ? $account->shop_id == 0 ? 'selected' : '' : 'selected' }}>Default (無分店)</option>
                                                            @foreach($shops as $shop)
                                                                <option value="{{ $shop->id }}" {{ isset($account) ? $account->shop_id == $shop->id ? 'selected' : '' : '' }}>{{ $shop->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('address'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('address') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="factory_address">權限</label>
                                                        <div class="row col-6">
                                                            <div class="icheck-primary col-6">
                                                                <input type="checkbox" class="form-control {{ $errors->has('shop_admin') ? ' is-invalid' : '' }}" id="shop_admin" name="shop_admin" {{ isset($account) ? $account->shop_admin == 1 ? 'checked' : '' : ''}}>
                                                                <label for="shop_admin">舊商家後台</label>
                                                                @if ($errors->has('shop_admin'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('shop_admin') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="icheck-primary col-6">
                                                                <input type="checkbox" class="form-control {{ $errors->has('pos_admin') ? ' is-invalid' : '' }}" id="pos_admin" name="pos_admin" {{ isset($account) ? $account->pos_admin == 1 ? 'checked' : '' : ''}}>
                                                                <label for="pos_admin">POS</label>
                                                                @if ($errors->has('pos_admin'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('pos_admin') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="is_on">啟用狀態</label>
                                                        <div class="input-group">
                                                            <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="停權" data-off-color="secondary" data-on-color="primary" {{ isset($account) ? $account->is_on == 1 ? 'checked' : '' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="is_on">鎖定狀態</label>
                                                        <div class="input-group">
                                                            <input type="checkbox" name="lock_on" value="1" data-bootstrap-switch data-on-text="已鎖定" data-off-text="未鎖定" data-off-color="secondary" data-on-color="primary" {{ isset($account) ? $account->lock_on >= 10 ? 'checked' : '' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center bg-white">
                                            @if(in_array(isset($account) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                            <button type="submit" class="btn btn-primary">{{ isset($account) ? '修改' : '新增' }}</button>
                                            @endif
                                            <a href="{{ !empty($vendorId) ? route('admin.vendors.show', $vendorId.'#vendor-account') : 'javascript:history.back()' }}" class="btn btn-info">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\VendorAccountsRequest', '.myform'); !!}
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
