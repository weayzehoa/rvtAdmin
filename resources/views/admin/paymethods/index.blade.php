@extends('admin.layouts.master')

@section('title', '付款方式設定')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>付款方式設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('paymethods') }}">付款方式設定</a></li>
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
                            <div class="float-left">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                                        <a href="{{ route('admin.paymethods.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i>新增付款方式</a>
                                        @endif
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
                                        <th class="text-left" width="15%">值(value)</th>
                                        <th class="text-left" width="10%">類別</th>
                                        <th class="text-left" width="10%">圖片</th>
                                        <th class="text-left" width="15%">顯示名稱</th>
                                        <th class="text-left" width="15%">英文名稱</th>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="10%">啟用狀態</th>
                                        @endif
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="10%">排序</th>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">刪除</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payMethods as $payMethod)
                                    <tr>
                                        <td class="text-left align-middle"><a href="{{ route('admin.paymethods.show', $payMethod->id ) }}">{{ $payMethod->value }}</a></td>
                                        <td class="text-left align-middle">{{ $payMethod->type }}</td>
                                        <td class="text-left align-middle">
                                            @if($payMethod->image)
                                            <img src="{{ env('AWS_FILE_URL').$payMethod->image }}">
                                            @endif
                                        </td>
                                        <td class="text-left align-middle">{{ $payMethod->name }}</td>
                                        <td class="text-left align-middle">{{ $payMethod->name_en }}</td>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ url('paymethods/active/' . $payMethod->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $payMethod->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($payMethod) ? $payMethod->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            @if($payMethod->sort != 1)
                                            <a href="{{ url('paymethods/sortup/' . $payMethod->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                            </a>
                                            @endif
                                            @if($payMethod->sort != count($payMethods))
                                            <a href="{{ url('paymethods/sortdown/' . $payMethod->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                            </a>
                                            @endif
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.paymethods.destroy', $payMethod->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
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
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('input[data-bootstrap-switch]').on('switchChange.bootstrapSwitch', function (event, state) {
            $(this).parents('form').submit();
        });

        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).parents('form').submit();
            };
        });
    })(jQuery);
</script>
@endsection
