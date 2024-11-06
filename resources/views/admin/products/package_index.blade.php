@extends('admin.layouts.master')

@section('title', '組合商品')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>組合商品</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('packages') }}">組合商品</a></li>
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
                                        @if(in_array($menuCode.'EX',explode(',',Auth::user()->power)))
                                        <a href="{{ url('packages/export') }}" class="btn-sm btn-info"><i class="fas fa-file-download mr-1"></i>匯出全部</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="float-right">
                                <form action="{{ url('packages') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        選擇：
                                        <select class="form-control form-control-sm" name="status" onchange="submit(this)">
                                            <option value="" {{ $status == '' ? 'selected' : '' }}>全部狀態</option>
                                            <option value="2" class="text-info" {{ $status == 2 ? 'selected' : '' }}>送審中</option>
                                            <option value="-1" class="text-danger" {{ $status == -1 ? 'selected' : '' }}>未送審</option>
                                            <option value="-2" class="text-danger" {{ $status == -2 ? 'selected' : '' }}>審查退回(不通過)</option>
                                            <option value="-3" class="text-danger" {{ $status == -3 ? 'selected' : '' }}>停售中</option>
                                            <option value="-9" class="text-secondary" {{ $status == -9 ? 'selected' : '' }}>已下架</option>
                                            <option value="1" class="text-success" {{ $status == 1 ? 'selected' : '' }}>上架中</option>
                                        </select>
                                        <select class="form-control form-control-sm" name="categoryId" onchange="submit(this)">
                                            <option value="">全部分類</option>
                                            @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ $category->id == $categoryId ? 'selected' : '' }}  class="{{ $category->is_on == 0 ? 'bg-secondary' : '' }}"><span>{{ $category->is_on == 1 ? $category->name : $category->name.' (停用)' }}</span></option>
                                            @endforeach
                                        </select>
                                        <select class="form-control form-control-sm" name="vendorId" onchange="submit(this)">
                                            <option value="">全部商家</option>
                                            @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ $vendor->id == $vendorId ? 'selected' : '' }}  class="{{ $vendor->is_on == 0 ? 'bg-secondary' : '' }}"><span>{{ $vendor->is_on == 1 ? $vendor->name : $vendor->name.' (停用)' }}</span></option>
                                            @endforeach
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
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="5%">狀態</th>
                                        <th class="text-left" width="10%">廠商</th>
                                        <th class="text-left" width="35%">商品名稱</th>
                                        <th class="text-left" width="50%">
                                            <div class="row">
                                                <div class="col-4 row text-left">
                                                    <span>組合內容</span>
                                                </div>
                                                <div class="col-8 row text-left">
                                                    <div class="col-3">商品貨號</div>
                                                    <div class="col-7">商品名稱</div>
                                                    <div class="col-2 text-right">數量</div>
                                                </div>
                                            </div>
                                        </th>
                                        {{-- <th class="text-right" width="5%">單價</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    <tr>
                                        <td class="text-center align-middle text-sm">
                                            @if($product->status == 1)
                                            <span class="right badge badge-success">上架中</span>
                                            @elseif($product->status == 0)
                                            <span class="right badge badge-purple">送審中</span>
                                            @elseif($product->status == -9)
                                            <span class="right badge badge-secondary">已下架</span>
                                            @elseif($product->status == -3)
                                            <span class="right badge badge-danger">停售中</span>
                                            @elseif($product->status == -2)
                                            <span class="right badge badge-danger">送審失敗</span>
                                            @elseif($product->status == -1)
                                            <span class="right badge badge-warning">未送審</span>
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            @if(in_array('M2S1',explode(',',Auth::user()->power)))
                                            @if($product->vendor_id > 1)
                                            <a href="{{ route('admin.vendors.show', $product->vendor_id ) }}">{{ $product->vendor_name }}</a>
                                            @endif
                                            @else
                                            {{ $product->vendor->name }}
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            <div class="col-12 text-warp">
                                            @if(in_array('M4S1',explode(',',Auth::user()->power)))
                                                <a href="{{ route('admin.products.show', $product->id ) }}">{{ $product->name }}</a>
                                                <br><span class="text-xs bg-info">{{ $product->serving_size }}</span>
                                            @else
                                            {{ $product->name }}<span class="text-xs bg-info">{{ $product->serving_size }}</span>
                                            @endif
                                            </div>
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            @foreach($product->packages as $package)
                                            <div class="row">
                                                <div class="col-4 row text-left">
                                                    <div class="col-12">
                                                        {{ $package->name }}
                                                    </div>
                                                    <div class="col-12">
                                                        <span class="text-success"><b>{{ $package->sku }}</b></span><br>
                                                    </div>
                                                    <div class="col-12 row">
                                                        <div class="col-6">
                                                            庫存：
                                                            @if($package->quantity < $package->safe_quantity)
                                                            <span class="text-danger"><b>{{ number_format($package->quantity) }}</b></span>
                                                            @else
                                                            {{ number_format($package->quantity) }}
                                                            @endif
                                                        </div>
                                                        <div class="col-6">安全庫存：{{ $package->safe_quantity }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-8 row text-left">
                                                    @foreach($package->lists as $lists)
                                                        <div class="col-3">{{ $lists->sku }}</div>
                                                        <div class="col-7">{{ $lists->name }}</div>
                                                        <div class="col-2 text-right">{{ $lists->quantity }}</div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @if(count($product->packages) != $loop->iteration)
                                            <hr>
                                            @endif
                                            @endforeach
                                        </td>
                                        {{-- <td class="text-right align-middle"><span class="text-danger"><b>{{ number_format($product->price) }}</b></span></td> --}}
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <div class="form-group">
                                    <form action="{{ url('products') }}" method="GET" class="form-inline" role="search">
                                        <input type="hidden" name="keyword" value="{{ $keyword ?? '' }}">
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                            <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $products->appends($appends)->render() }}
                                @else
                                {{ $products->render() }}
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
    })(jQuery);
</script>
@endsection
