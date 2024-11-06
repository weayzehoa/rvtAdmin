@extends('admin.layouts.master')

@section('title', '商家帳號列表')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家帳號列表</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('vendoraccounts') }}">商家帳號列表</a></li>
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
                                <form action="{{ url('vendoraccounts') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        選擇：
                                        <select class="form-control form-control-sm" name="is_on" onchange="submit(this)">
                                            <option value="" {{ isset($is_on) && $is_on == '' ? 'selected' : '' }}>所有狀態 ({{ $totalAccounts }})</option>
                                            <option value="1" {{ isset($is_on) && $is_on == 1 ? 'selected' : '' }}>啟用 ({{ $totalEnable }})</option>
                                            <option value="0" {{ isset($is_on) && $is_on == 0 ? 'selected' : '' }}>停用 ({{ $totalDisable }})</option>
                                        </select>
                                        <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                            <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                        </select>
                                        <input type="search" class="form-control form-control-sm" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="輸入關鍵字搜尋" title="搜尋帳號、姓名、電子郵件" aria-label="Search">
                                        <button type="submit" class="btn btn-sm btn-info" title="搜尋帳號、姓名、電子郵件" >
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
                                        <th class="text-left" width="10%">帳號</th>
                                        <th class="text-left" width="10%">姓名</th>
                                        <th class="text-left" width="20%">電子郵件</th>
                                        <th class="text-left" width="20%">所屬商家店名/品牌</th>
                                        {{-- <th class="text-left" width="15%">所屬分店</th> --}}
                                        {{-- <th class="text-center" width="10%">權限</th> --}}
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">啟用</th>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">刪除</th>
                                        @endif
                                        @if(in_array($menuCode.'T',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">傳送門</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($accounts as $account)
                                    <tr>
                                        <td class="text-left align-middle text-sm">
                                            <a href="{{ route('admin.vendoraccounts.show', $account->id ) }}">{{ $account->account }}</a>
                                            @if($account->lock_on >= 10)
                                            <form class="d-inline" action="{{ route('admin.vendoraccounts.unlock', $account->id) }}" method="POST">
                                                @csrf
                                                <span>(已鎖定)</span>
                                                <button type="button" class="btn btn-sm btn-success unlock-btn">
                                                    <i class="fas fa-unlock-alt"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">{{ $account->name }}</td>
                                        <td class="text-left align-middle text-sm">{{ $account->email }}</td>
                                        <td class="text-left align-middle text-sm">
                                            @if(in_array('M2S1',explode(',',Auth::user()->power)))
                                            <a href="{{ route('admin.vendors.show', $account->vendor->id) }}">{{ $account->vendor->name }}</a>
                                            @else
                                            {{ $account->vendor->name }}
                                            @endif
                                        </td>
                                        {{-- <td class="text-left align-middle text-sm">
                                            @if(in_array('M2S2',explode(',',Auth::user()->power)))
                                            <a href="{{ route('admin.vendorshops.show', $account->shop_id) }}">{{ $account->shop_id != 0 ? $account->shop->name : '' }}</a>
                                            @else
                                            {{ $account->shop_id != 0 ? $account->shop->name : '' }}
                                            @endif
                                        </td> --}}
                                        {{-- <td class="text-center align-middle text-sm">
                                            <div class="row col-12">
                                                <div class="icheck-primary col-6">
                                                    <input type="checkbox" id="shop_admin" name="shop_admin" {{ $account->shop_admin == 1 ? 'checked' : ''}} disabled>
                                                    <label for="shop_admin">後台</label>
                                                </div>
                                                <div class="icheck-primary col-6">
                                                    <input type="checkbox" id="pos_admin" name="pos_admin" {{ $account->pos_admin == 1 ? 'checked' : ''}} disabled>
                                                    <label for="pos_admin">POS</label>
                                                </div>
                                            </div>
                                        </td> --}}
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ url('vendoraccounts/active/' . $account->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $account->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($account) ? $account->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.vendoraccounts.destroy', $account->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'T',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <a href="https://{{ env('VENDOR_DOMAIN') }}/login?account={{ $account->account }}&icarryToken={{ $account->icarry_token }}" class="btn btn-sm btn-info btn-door" target="_blank">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </a>
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
                                    <form action="{{ url('vendoraccounts') }}" method="GET" class="form-inline" role="search">
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
                                {{ $accounts->appends($appends)->render() }}
                                @else
                                {{ $accounts->render() }}
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
        $('.btn-door').click(function(){
            setTimeout(function() {
                location.reload()
            }, 1000);
        });

        $('.unlock-btn').click(function (e) {
            if(confirm('請確認是否要解除鎖定?')){
                $(this).parents('form').submit();
            };
        });
    })(jQuery);
</script>
@endsection
