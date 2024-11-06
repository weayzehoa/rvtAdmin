@extends('admin.layouts.master')

@section('title', 'ACPay出貨統計')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>ACPay出貨統計</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('acpaystatistics') }}">ACPay出貨統計</a></li>
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
                            {{-- <div class="float-left">
                                <button id="showForm" class="btn btn-sm btn-success" title="使用欄位查詢">使用欄位查詢</button>
                            </div>
                            <div class="float-right">
                            </div> --}}
                            <form id="myForm" action="{{ url('acpaystatistics') }}" method="get">
                                <div class="row col-8 offset-2">
                                    <div class="col-6 mt-2">
                                        <label for="pay_time">預定出貨日區間:</label>
                                        <div class="input-group">
                                            <input type="datetime" class="form-control datetimepicker" id="book_shipping_date" name="book_shipping_date" placeholder="格式：2016-06-06" value="{{ isset($book_shipping_date) && $book_shipping_date ? $book_shipping_date : '' }}" autocomplete="off">
                                            <span class="input-group-addon bg-primary">~</span>
                                            <input type="datetime" class="form-control datetimepicker" id="book_shipping_date_end" name="book_shipping_date_end" placeholder="格式：2016-06-06" value="{{ isset($book_shipping_date_end) && $book_shipping_date_end ? $book_shipping_date_end : '' }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <label class="control-label" for="free_shipping">每頁筆數:</label>
                                        <select class="form-control" name="list">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                            <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="text-danger text-bold">(選擇區間範圍太大，可能造成執行速度過慢，請耐心等候)</span>
                                    </div>
                                    <div class="col-12 text-center mt-2">
                                        <button type="submit" class="btn btn-primary">查詢</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        @if(!empty($orders))
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="10%">預定出貨日</th>
                                        <th class="text-left" width="20%">商家</th>
                                        <th class="text-left" width="20%">店名</th>
                                        <th class="text-left" width="30%">門市地址</th>
                                        <th class="text-right" width="10%">訂單數</th>
                                        <th class="text-right" width="10%">箱數</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td class="text-left align-middle">{{ $order->book_shipping_date }}</td>
                                        <td class="text-left align-middle">{{ $order->vendor_name }}</td>
                                        <td class="text-left align-middle">{{ $order->name }}</td>
                                        <td class="text-left align-middle">{{ $order->address }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->count) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->boxes) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <td class="text-left align-middle">訂單箱數小計</td>
                                        <td class="text-left align-middle"></td>
                                        <td class="text-left align-middle"></td>
                                        <td class="text-left align-middle"></td>
                                        <td class="text-right align-middle">{{ number_format($total['count']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total['boxes']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <span class="badge badge-primary text-lg ml-1">總筆數：{{ number_format($orders->total()) ?? 0 }}</span>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $orders->appends($appends)->render() }}
                                @else
                                {{ $orders->render() }}
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('css')
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $('.datetimepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
    })(jQuery);
</script>
@endsection
