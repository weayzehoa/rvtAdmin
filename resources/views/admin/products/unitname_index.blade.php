@extends('admin.layouts.master')

@section('title', '單位名稱設定')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>單位名稱設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('unitnames') }}">單位名稱設定</a></li>
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
                            @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                            <div class="col-4 offset-4 text-center">
                                <form class="myform" action="{{ route('admin.unitnames.store') }}" method="POST">
                                    @csrf
                                    <div class="input-group">
                                        <input class="mr-2 text-center form-control {{ $errors->has('message') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $unitName->name ?? old('name') }}" placeholder="請輸入單位名稱">
                                        <button type="submit" class="btn btn-sm btn-info">新增</button>
                                        @if ($errors->has('message'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('message') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            @endif
                            <div class="float-left">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        {{-- <a href="{{ route('admin.unitnames.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i>新增</a> --}}
                                    </div>
                                </div>
                            </div>
                            <div class="float-right">
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="10%"></th>
                                        <th class="text-center" width="20%">順序</th>
                                        <th class="text-center" width="20%">名稱</th>
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="20%">排序</th>
                                        @endif
                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="20%">修改</th>
                                        @endif
                                        <th class="text-center" width="10%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($unitNames as $unitName)
                                    <form class="myform" action="{{ route('admin.unitnames.update', $unitName->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PATCH">
                                        @csrf
                                    <tr>
                                        <td class="text-center align-middle"></td>
                                        <td class="text-center align-middle">{{ $unitName->sort_id }}</td>
                                        <td class="text-center align-middle">
                                            <input class="text-center form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ $unitName->name ?? '' }}" placeholder="請輸入單位名稱">
                                            @if ($errors->has('name'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                            @endif
                                        </td>
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            @if($unitName->sort_id != 1)
                                            <a href="{{ url('unitnames/sortup/' . $unitName->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                            </a>
                                            @endif
                                            @if($unitName->sort_id != count($unitNames))
                                            <a href="{{ url('unitnames/sortdown/' . $unitName->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                            </a>
                                            @endif
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <button type="submit" class="btn btn-sm btn-primary">修改</button>
                                        </td>
                                        @endif
                                        <td class="text-center align-middle"></td>
                                    </tr>
                                    </form>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\ProductUnitNamesRequest', '.myform'); !!}
@endsection

@section('CustomScript')
@endsection
