@extends('admin.layouts.master')

@section('title', '無法派送關鍵字管理')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>無法派送關鍵字管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('vendorSellImports') }}">無法派送關鍵字管理</a></li>
                        <li class="breadcrumb-item active">清單</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                </div>
                                <div class="col-6">
                                    <div class="float-right">
                                        <div class="input-group input-group-sm align-middle align-items-middle">
                                            <span class="badge badge-purple text-lg mr-2">總筆數：{{ !empty($addressDisables) ? number_format($addressDisables->total()) : 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-body">
                            @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                            <div class="col-10 offset-1">
                                <form class="myform" action="{{ route('admin.addressDisable.store') }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-text">
                                                <span class="">關鍵字組</span>
                                            </div>
                                            <input type="text" class="form-control" id="keywords" name="keywords" value="{{ old('account') ?? '' }}" placeholder="輸入關鍵字組，請用逗號分開" required autocomplete="off">
                                            <div class="input-group-text">
                                                <span class="">顯示原因</span>
                                            </div>
                                            <input type="text" class="form-control" id="reason" name="reason" placeholder="輸入前台顯示原因" required autocomplete="off">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-md btn-success btn-block">新增</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            @endif
                            @if(count($addressDisables) > 0)
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left text-sm" width="45%">關鍵字組</th>
                                        <th class="text-left text-sm" width="45%">顯示原因</th>
                                        <th class="text-center text-sm" width="5%">修改</th>
                                        <th class="text-center text-sm" width="5">刪除</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($addressDisables as $addressDisable)
                                    <tr>
                                        <form class="myform" action="{{ route('admin.addressDisable.update', $addressDisable->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PATCH">
                                        @csrf
                                        <td class="text-left text-sm align-middle">
                                            <input type="text" class="form-control form-control-sm text-sm {{ $errors->has('keywords') ? ' is-invalid' : '' }}" name="keywords" value="{{ $addressDisable->keywords }}" required >
                                            @if ($errors->has('keywords'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('keywords') }}</strong>
                                            </span>
                                            @endif
                                        </td>
                                        <td class="text-left text-sm align-middle">
                                            <input type="text" class="form-control form-control-sm text-sm {{ $errors->has('reason') ? ' is-invalid' : '' }}" name="reason" value="{{ $addressDisable->reason }}" required >
                                            @if ($errors->has('reason'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('reason') }}</strong>
                                            </span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                                <button type="submit" class="btn btn-sm btn-primary">修改</button>
                                            @endif
                                        </td>
                                        </form>
                                        <td class="text-center align-middle">
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                            <form action="{{ route('admin.addressDisable.destroy', $addressDisable->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <h3>尚無資料</h3>
                            @endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <span class="badge badge-primary text-lg ml-1">總筆數：{{ !empty($addressDisables) ? number_format($addressDisables->total()) : 0 }}</span>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $addressDisables->appends($appends)->render() }}
                                @else
                                {{ $addressDisables->render() }}
                                @endif
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
@endsection

@section('script')
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\AddressDisableRequest', '.myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).parents('form').submit();
            };
        });
    })(jQuery);
</script>
@endsection
