@extends('admin.layouts.master')

@section('title', '物流廠商管理')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>物流廠商管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('shippingvendors') }}">物流廠商管理</a></li>
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
                                        <a href="{{ route('admin.shippingvendors.create') }}" class="btn-sm btn-primary mr-2"><i class="fas fa-plus mr-1"></i>新增</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="float-right">
                                <form action="{{ url('shippingvendors') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        {{-- <span class="right badge badge-primary mr-1">{{ $keyword || $is_foreign < 2 ? '搜尋到' : '全部共' }} {{ $totals }} 筆</span> 選擇： --}}
                                        {{-- <span class="right badge badge-primary mr-1">{{ $keyword || $is_foreign < 2 || $is_on < 2 ? '搜尋到' : '全部共' }} {{ $totals }} 筆</span> 選擇： --}}
                                        {{-- <span class="text-bold">共 {{ count($vendors) > 0 ? number_format($vendors->total()) : 0 }} 筆</span> --}}
                                        <span class="badge badge-purple mr-2">總筆數：{{ count($vendors) > 0 ? number_format($vendors->total()) : 0 }}</span>
                                        <select class="form-control form-control-sm" name="is_foreign" onchange="submit(this)">
                                            <option value="" {{ isset($is_foreign) && $is_foreign == '' ? 'selected' : '' }}>國內外</option>
                                            <option value="1" {{ isset($is_foreign) && $is_foreign == 1 ? 'selected' : '' }}>國外</option>
                                            <option value="0" {{ isset($is_foreign) && $is_foreign == 0 ? 'selected' : '' }}>國內</option>
                                        </select>
                                        <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                            <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                        </select>
                                        <input type="search" class="form-control form-control-sm" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="輸入關鍵字搜尋" title="搜尋廠商名稱" aria-label="Search">
                                        <button type="submit" class="btn btn-sm btn-info" title="搜尋廠商名稱" >
                                            <i class="fas fa-search"></i>
                                            搜尋
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            {{-- 文字不斷行 table中加上 class="text-nowrap" --}}
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="5%">順序</th>
                                        <th class="text-left" width="12%">廠商名稱</th>
                                        <th class="text-left" width="15%">廠商英文名稱</th>
                                        <th class="text-left" width="35%">API URL</th>
                                        <th class="text-left" width="5%">國外</th>
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="7%">排序</th>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">刪除</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vendors as $vendor)
                                    <tr>
                                        <td class="text-center align-middle text-sm">{{ $vendor->sort_id }}</td>
                                        <td class="text-left align-middle text-sm">
                                            <a href="{{ route('admin.shippingvendors.show', $vendor->id ) }}">{{ $vendor->name }}</a>
                                        </td>
                                        <td class="text-left align-middle text-sm">{{ $vendor->name_en }}</td>
                                        <td class="text-left align-middle text-sm pr-5" style="word-break: break-all">
                                            {{ $vendor->api_url }}
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            <input type="checkbox" disabled data-bootstrap-switch data-on-text="是" data-off-text="否" data-off-color="secondary" data-on-color="success" {{ isset($vendor) ? $vendor->is_foreign == 1 ? 'checked' : '' : '' }}>
                                        </td>
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            @if($vendor->sort_id != 1)
                                            <a href="{{ url('shippingvendors/sortup/' . $vendor->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                            </a>
                                            @endif
                                            @if($vendor->sort_id != count($vendors))
                                            <a href="{{ url('shippingvendors/sortdown/' . $vendor->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                            </a>
                                            @endif
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.shippingvendors.destroy', $vendor->id) }}" method="POST">
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
                            <div class="float-left">
                                <div class="form-group">
                                    <form action="{{ url('shippingvendors') }}" method="GET" class="form-inline" role="search">
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                        </select>
                                        <input type="hidden" name="keyword" value="{{ $keyword ?? '' }}">
                                    </form>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $vendors->appends($appends)->render() }}
                                @else
                                {{ $vendors->render() }}
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
