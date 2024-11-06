@extends('admin.layouts.master')

@section('title', '物流運費設定')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>物流運費設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('shippingfees') }}">物流運費設定</a></li>
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
                    <div class="callout callout-danger">
                        <h6><i class="fas fa-info text-danger"></i> 此設定將優先於商品設定，若商品設定可寄送日本，但此處設定寄送日本未啟用。則購物車將一律無法寄送日本。</h6>
                        <h6><i class="fas fa-info text-danger"></i> 計價方式分為固定價格、每公斤計算二種，另外可設定有無免運門檻，若該配送地有關稅或其他費用，將列出於下方。</h6>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="float-left">
                                <div class="input-group">
                                    <div class="input-group-append">
                                        @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                                        <a href="{{ route('admin.shippingfees.create') }}" class="btn-sm btn-primary mr-2"><i class="fas fa-plus mr-1"></i>新增</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="float-right">
                                <form action="{{ url('shippingfees') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        {{-- <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($totals) ?? 0 }}</span> --}}
                                        <form action="" method="GET" class="form-inline" role="search">
                                            <span class="badge badge-primary text-sm mr-2">快搜 <i class="fas fa-hand-point-right"></i></span>
                                        </form>
                                        <select class="form-control form-control-sm" name="product_sold_country" onchange="submit(this)">
                                            <option value="">不限產地</option>
                                            @foreach($origins as $origin)
                                                <option value="{{ $origin->product_sold_country }}" {{ isset($product_sold_country) ? $origin->product_sold_country == $product_sold_country ? 'selected' : '' : '' }}>{{ $origin->product_sold_country }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-control form-control-sm" name="shipping_methods" onchange="submit(this)">
                                            <option value="">目的地</option>
                                            <option value="當地機場" {{ isset($shipping_methods) && $shipping_methods == '當地機場' ? 'selected' : '' }}>當地機場</option>
                                            <option value="當地地址" {{ isset($shipping_methods) && $shipping_methods == '當地地址' ? 'selected' : '' }}>當地地址</option>
                                            <option value="當地旅店" {{ isset($shipping_methods) && $shipping_methods == '當地旅店' ? 'selected' : '' }}>當地旅店</option>
                                            @foreach($countries as $country)
                                            <option value="{{ $country->name }}" {{ isset($shipping_methods) ? $country->name == $shipping_methods ? 'selected' : '' : '' }}>{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-control form-control-sm" name="is_on" onchange="submit(this)">
                                            <option value="" {{ isset($is_on) && $is_on == '' ? 'selected' : '' }}>全部狀態</option>
                                            <option value="1" {{ isset($is_on) && $is_on == 1 ? 'selected' : '' }}>啟用</option>
                                            <option value="0" {{ isset($is_on) && $is_on == 0 ? 'selected' : '' }}>停用</option>
                                        </select>
                                        <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                            <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="10%">操作</th>
                                        <th class="text-center" width="10%">產地</th>
                                        <th class="text-center" width="10%">物流方式</th>
                                        <th class="text-center" width="20%">計價方式及費用(NT)</th>
                                        <th class="text-center" width="10%">跨境稅率(%)</th>
                                        <th class="text-center" width="10%">免運門檻</th>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="10%">啟用</th>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">刪除</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fees as $fee)
                                    <tr>
                                        <td class="text-center align-middle text-sm">
                                            <a href="{{ route('admin.shippingfees.show', $fee->id) }}" class="btn-sm btn-info">修改</a>
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            {{ $fee->product_sold_country }}
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            {{ $fee->shipping_methods }}
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            @if($fee->shipping_base_price > 0)
                                            <span>固定價 NT$ {{ $fee->shipping_base_price }} ，滿 NT$ {{ $fee->free_shipping }} 免費。</span>
                                            @elseif($fee->shipping_kg_price > 0)
                                            <span>每公斤 NT$ {{ $fee->shipping_kg_price }} ，無免運優惠。</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            {!! $fee->tax_rate == 0 ? '' : '<span class="text-danger"><b>'.$fee->tax_rate.'%</b></span>' !!}
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            @if($fee->free_shipping == 0)
                                            免運
                                            @else
                                            {!! number_format($fee->free_shipping) == '999,999,999' ? '無免運' : '<span class="text-danger"><b>'.number_format($fee->free_shipping).'</b></span>' !!}
                                            @endif
                                        </td>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ url('shippingfees/active/' . $fee->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $fee->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($fee) ? $fee->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.shippingfees.destroy', $fee->id) }}" method="POST">
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
                                    <form action="{{ url('vendors') }}" method="GET" class="form-inline" role="search">
                                        <input type="hidden" name="is_on" value="{{ $is_on ?? '' }}">
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
                                {{ $fees->appends($appends)->render() }}
                                @else
                                {{ $fees->render() }}
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
