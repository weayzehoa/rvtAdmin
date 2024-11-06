@extends('admin.layouts.master')

@section('title', '商家銷量統計')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家銷量統計</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('vendorsales') }}">商家銷量統計</a></li>
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
                                <button id="showForm" class="btn btn-sm btn-success" title="使用欄位查詢">使用欄位查詢</button>
                            </div>
                            <div class="float-right">
                                @if(count($orderItems) > 0)
                                <div class="input-group input-group-sm align-middle align-items-middle">
                                    <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($orderItems->total()) }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <form id="myForm" action="{{ url('vendorsales') }}" method="get">
                            <div id="searchForm" class="card card-primary">
                                <div class="card-body">
                                    <div class="row col-8 offset-2">
                                        <div class="col-12 mt-2">
                                            <label for="pay_time">付款時間區間: <span class="text-danger">(必填) (選擇區間範圍太大，可能造成執行速度過慢，請耐心等候)</span></label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="pay_time" name="pay_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pay_time) && $pay_time ? $pay_time : '' }}" autocomplete="off" required>
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="pay_time_end" name="pay_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pay_time_end) && $pay_time_end ? $pay_time_end : '' }}" autocomplete="off" required>
                                            </div>
                                        </div>
                                        <div class="col-4 mt-2">
                                            <label class="mr-2">訂單渠道:</label>
                                            <select class="form-control" name="source">
                                                <option value="">全部</option>
                                                @foreach($sources as $s)
                                                <option value="{{ $s->source }}" {{ !empty($source) && $s->source == $source ? 'selected' : '' }}>{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4 mt-2">
                                            <label class="mr-2">排序:</label>
                                            <select class="form-control" name="sort">
                                                <option value="totalOrder" {{ isset($sort) ? $sort == 'totalOrder' ? 'selected' : '' : 'selected' }}>訂單數 由高至低</option>
                                                <option value="totalQuantity" {{ isset($sort) ? $sort == 'totalQuantity' ? 'selected' : '' : '' }}>數量 由高至低</option>
                                                <option value="totalPrice" {{ isset($sort) ? $sort == 'totalPrice' ? 'selected' : '' : '' }}>總金額 由高至低</option>
                                                <option value="totalAvg" {{ isset($sort) ? $sort == 'totalAvg' ? 'selected' : '' : '' }}>平均金額 由高至低</option>
                                            </select>
                                        </div>
                                        <div class="col-4 mt-2">
                                            <label class="mr-2">每頁筆數:</label>
                                            <select class="form-control" name="list">
                                                <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                                <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                                <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                                <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                            </select>
                                        </div>
                                        <div class="col-12 text-center mt-2">
                                            <button type="submit" class="btn btn-primary">查詢</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="card-body table-responsive">
                            @if(count($orderItems) > 0)
                            <table class="table table-hover table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="5%">排序</th>
                                        <th class="text-left" width="15%">商家</th>
                                        <th class="text-right" width="15%">訂單數量</th>
                                        <th class="text-right" width="15%">商品數量</th>
                                        <th class="text-right" width="15%">佔比</th>
                                        <th class="text-right" width="15%">總金額</th>
                                        <th class="text-right" width="15%">平均(金額/數量)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orderItems as $item)
                                    <tr>
                                        <td class="text-left">{{ $loop->iteration }}</td>
                                        <td class="text-left">{{ $item->vendor_name }}{{ $item->vendor_ison == 0 ? '(停用)' : '' }}</td>
                                        <td class="text-right">{{ number_format($item->totalOrder) }}</td>
                                        <td class="text-right">{{ number_format($item->totalQuantity) }}</td>
                                        <td class="text-right">{{ $item->percent }}%</td>
                                        <td class="text-right">{{ number_format($item->totalPrice) }}</td>
                                        <td class="text-right">{{ number_format(ceil($item->totalAvg)) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-secondary">
                                        <td class="text-left" colspan="2">統計</td>
                                        <td class="text-right">{{ number_format($total['totalOrder']) }}</td>
                                        <td class="text-right">{{ number_format($total['totalQuantity']) }}</td>
                                        <td class="text-right">{{ number_format($total['totalPercent']) }}</td>
                                        <td class="text-right">{{ number_format($total['totalPrice']) }}</td>
                                        <td class="text-right">{{ number_format($total['totalAvg']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            @endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                @if(count($orderItems) > 0)
                                <div class="form-group">
                                    <span class="badge badge-primary text-lg ml-1">總筆數：{{ number_format($orderItems->total()) }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ !empty($orderItems) ? $orderItems->appends($appends)->render() : ''}}
                                @else
                                {{ !empty($orderItems) ? $orderItems->render() : '' }}
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
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $('#showForm').click(function(){
            let text = $('#showForm').html();
            $('#searchForm').toggle();
            text == '使用欄位查詢' ? $('#showForm').html('隱藏欄位查詢') : $('#showForm').html('使用欄位查詢');
        });

    })(jQuery);
</script>
@endsection
