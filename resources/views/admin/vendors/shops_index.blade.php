@extends('admin.layouts.master')

@section('title', '商家分店列表')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家分店列表</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('vendorshops') }}">商家分店列表</a></li>
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
                            </div>
                            <div class="float-right">
                                <form action="{{ url('vendorshops') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        選擇：
                                        <select class="form-control form-control-sm" name="is_on" onchange="submit(this)">
                                            <option value="" {{ isset($is_on) && $is_on == '' ? 'selected' : '' }}>所有狀態 ({{ $totalShops }})</option>
                                            <option value="1" {{ isset($is_on) && $is_on == 1 ? 'selected' : '' }}>啟用 ({{ $totalEnable }})</option>
                                            <option value="0" {{ isset($is_on) && $is_on == 0 ? 'selected' : '' }}>停用 ({{ $totalDisable }})</option>
                                        </select>
                                        <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                            <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                        </select>
                                        <input type="search" class="form-control form-control-sm" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="輸入關鍵字搜尋" title="搜尋店名、電話、地址" aria-label="Search">
                                        <button type="submit" class="btn btn-sm btn-info" title="搜尋店名、電話、地址" >
                                            <i class="fas fa-search"></i>
                                            搜尋
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- 文字不斷行 table中加上 class="text-nowrap" --}}
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="20%">分店名稱</th>
                                        <th class="text-left" width="20%">所屬商家店名/品牌</th>
                                        <th class="text-left" width="10%">分店電話</th>
                                        <th class="text-left" width="25%">分店地址</th>
                                        <th class="text-left" width="10%">建立時間</th>
                                        <th class="text-center" width="5%">查證</th>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">啟用</th>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">刪除</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shops as $shop)
                                    <tr>
                                        <td class="text-left align-middle text-sm">
                                            <a href="{{ route('admin.vendorshops.show', $shop->id ) }}">{{ $shop->name }}</a>
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            @if(in_array('M2S1',explode(',',Auth::user()->power)))
                                            <a href="{{ route('admin.vendors.show', $shop->vendor->id) }}">{{ $shop->vendor->name }}</a>
                                            @else
                                            {{ $shop->vendor->name }}
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">{{ $shop->tel }}</td>
                                        <td class="text-left align-middle text-sm">{{ $shop->address }}</td>
                                        <td class="text-left align-middle text-sm">{{ $shop->create_time }}</td>
                                        <td class="text-center align-middle text-sm"><a href="https://maps.google.com/maps?q={{ $shop->address }}" class="btn btn-sm btn-success" target="_blank">查證</a></td>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ url('vendorshops/active/' . $shop->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $shop->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($shop) ? $shop->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.vendorshops.destroy', $shop->id) }}" method="POST">
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
                                    <form action="{{ url('vendorshops') }}" method="GET" class="form-inline" role="search">
                                        <input type="hidden" name="is_on" value="{{ $is_on ?? '' }}">
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="200" {{ $list == 200 ? 'selected' : '' }}>每頁 200 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                        </select>
                                        <input type="hidden" name="keyword" value="{{ $keyword ?? '' }}">
                                    </form>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $shops->appends($appends)->render() }}
                                @else
                                {{ $shops->render() }}
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
