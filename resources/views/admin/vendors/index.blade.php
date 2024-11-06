@extends('admin.layouts.master')

@section('title', '商家管理')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('vendors') }}">商家管理</a></li>
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
                                        @if(in_array($menuCode['Vendors'].'N',explode(',',Auth::user()->power)))
                                        <a href="{{ route('admin.vendors.create') }}" class="btn-sm btn-primary mr-2"><i class="fas fa-plus mr-1"></i>新增</a>
                                        @endif
                                        @if(in_array($menuCode['Vendors'].'EX',explode(',',Auth::user()->power)))
                                        <a href="{{ url('vendors/export') }}" class="btn-sm btn-info"><i class="fas fa-file-download mr-1"></i>匯出全部</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="float-right">
                                <form action="{{ url('vendors') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        選擇：
                                        <select class="form-control form-control-sm" name="is_on" onchange="submit(this)">
                                            <option value="" {{ isset($is_on) && $is_on == '' ? 'selected' : 'selected' }}>所有狀態 ({{ $totalVendors }})</option>
                                            <option value="1" {{ isset($is_on) && $is_on == 1 ? 'selected' : '' }}>啟用 ({{ $totalEnable }})</option>
                                            <option value="0" {{ isset($is_on) && $is_on == 0 ? 'selected' : '' }}>停用 ({{ $totalDisable }})</option>
                                        </select>
                                        <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                            <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                        </select>
                                        <input type="search" class="form-control form-control-sm" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="輸入關鍵字搜尋" title="搜尋店名/品牌、公司、統編及聯絡人" aria-label="Search">
                                        <button type="submit" class="btn btn-sm btn-info" title="搜尋店名/品牌、公司、統編及聯絡人" >
                                            <i class="fas fa-search"></i>
                                            搜尋
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="7%">商家代號</th>
                                        <th class="text-left" width="11%">店名/品牌</th>
                                        <th class="text-left" width="5%">服務費</th>
                                        <th class="text-left" width="20%">公司</th>
                                        <th class="text-left" width="8%">統編</th>
                                        <th class="text-left" width="8%">聯絡人</th>
                                        <th class="text-left" width="10%">電話</th>
                                        <th class="text-left" width="16%">E-Mail</th>
                                        <th class="text-center" width="10%">操作</th>
                                        @if(in_array($menuCode['Vendors'].'O',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">啟用</th>
                                        @endif
                                        {{-- @if(in_array($menuCode['Vendors'].'D',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">刪除</th>
                                        @endif --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vendors as $vendor)
                                    <tr>
                                        <td class="text-left align-middle text-sm">{{ 'A'.str_pad($vendor->id,5,'0',STR_PAD_LEFT) }}</td>
                                        <td class="text-left align-middle text-sm">
                                            <a href="{{ route('admin.vendors.show', $vendor->id ) }}">{{ $vendor->name }}</a>
                                        </td>
                                        <td class="text-left align-middle text-sm">{{ $vendor->iCarryServiceFee }}%</td>
                                        <td class="text-left align-middle text-sm text-warp">{{ $vendor->company }}</td>
                                        <td class="text-left align-middle text-sm">{{ $vendor->VAT_number }}</td>
                                        <td class="text-left align-middle text-sm">{{ $vendor->contact_person }}</td>
                                        <td class="text-left align-middle text-sm">{{ $vendor->tel }}</td>
                                        <td class="text-left align-middle text-sm text-warp">{{ $vendor->email }}</td>
                                        <td class="text-center align-middle text-sm">
                                            {{-- @if(in_array($menuCode['Shops'],explode(',',Auth::user()->power)))
                                            <a href="{{ url('vendors/'.$vendor->id.'#vendor-shop') }}">
                                                <span class="right badge badge-success">分店管理</span>
                                            </a>
                                            @endif --}}
                                            @if(in_array($menuCode['Accounts'],explode(',',Auth::user()->power)))
                                            <a href="{{ url('vendors/'.$vendor->id.'#vendor-account') }}">
                                                <span class="right badge badge-primary">帳號管理</span>
                                            </a>
                                            @endif
                                            @if(in_array($menuCode['Products'],explode(',',Auth::user()->power)))
                                            <a href="{{ url('vendors/'.$vendor->id.'#vendor-product') }}">
                                                <span class="right badge badge-info">商品管理</span>
                                            </a>
                                            @endif
                                            {{-- <a href="{{ url('vendors/'.$vendor->id.'#vendor-order') }}">
                                                <span class="right badge badge-warning">訂單管理</span>
                                            </a> --}}
                                        </td>
                                        @if(in_array($menuCode['Vendors'].'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ url('vendors/active/' . $vendor->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $vendor->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($vendor) ? $vendor->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                        </td>
                                        @endif
                                        {{-- @if(in_array($menuCode['Vendors'].'D',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-sm btn-danger  delete-btn">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endif --}}
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <div class="form-group">
                                    <form action="{{ url('vendors') }}" method="GET" class="form-inline" role="search">
                                        <input type="hidden" name="is_on" value="{{ $is_on ?? '' }}">
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                            <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
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
