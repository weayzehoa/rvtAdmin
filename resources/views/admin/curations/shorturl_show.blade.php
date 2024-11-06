@extends('admin.layouts.master')

@section('title', '短網址設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>短網址設定</b><small> ({{ isset($shortUrl) ? '修改' : '新增' }})</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('countries') }}">短網址設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($shortUrl) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        @if(isset($shortUrl))
        <form id="myform" action="{{ route('admin.shortUrl.update', $shortUrl->id) }}" method="POST">
            <input type="hidden" name="_method" value="PATCH">
        @else
        <form id="myform" action="{{ route('admin.shortUrl.store') }}" method="POST">
        @endif
            @csrf
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                @if(isset($shortUrl))
                                <h3 class="card-title">https://link.icarry.me/{{ $shortUrl->code ?? ''}} 資料</h3>
                                @else
                                <h3 class="card-title">https://link.icarry.me/{{ $code }} 資料</h3>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-4">
                                            <label for="code"><span class="text-red">* </span>代碼</label>
                                            <input type="text" class="form-control {{ $errors->has('code') ? ' is-invalid' : '' }}" id="code" name="code" value="{{ old('code') ?? $shortUrl->code ?? $code ?? '' }}" placeholder="請輸入隨機代碼" required>
                                            @if ($errors->has('code'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('code') }}</strong>
                                            </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-8">
                                        <label for="url"><span class="text-red">* </span>連結網址</label>
                                        <input type="text" class="form-control {{ $errors->has('url') ? ' is-invalid' : '' }}" id="url" name="url" value="{{ old('url') ?? $shortUrl->url ?? '' }}" placeholder="請輸入連結網址" required>
                                        @if ($errors->has('url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('url') }}</strong>
                                        </span>
                                        @endif
                                    </div>

                                    <div class="form-group col-10">
                                            <label for="memo"><span class="text-red">* </span>備註說明</label>
                                            <input type="text" class="form-control {{ $errors->has('memo') ? ' is-invalid' : '' }}" id="memo" name="memo" value="{{ old('memo') ?? $shortUrl->memo ?? '' }}" placeholder="請輸入備註說明" required>
                                            @if ($errors->has('memo'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('memo') }}</strong>
                                            </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-2">
                                            @if(isset($shortUrl))
                                            <label for="name_jp">點擊次數</label><br><span class="">{{ $shortUrl->clicks }}</span>
                                            @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-center bg-white">
                                @if(in_array(isset($shortUrl) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                <button type="submit" class="btn btn-primary">{{ isset($shortUrl) ? '修改' : '新增' }}</button>
                                @endif
                                <a href="{{ url('shortUrl') }}" class="btn btn-info">
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

@endsection

@section('script')
{{-- Select2 --}}
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\ShortUrlRequest', '#myform'); !!}
@endsection
