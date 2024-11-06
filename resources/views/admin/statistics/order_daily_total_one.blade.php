@extends('admin.layouts.master')

@section('title', '訂單每日統計')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>訂單每日統計</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('orderdailytotalOne') }}">訂單每日統計</a></li>
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

                            </div>
                        </div>
                        <form id="myForm" action="{{ url('orderdailytotalOne') }}" method="get">
                            <div id="searchForm" class="card card-primary" style="display: none">
                                <div class="card-body">
                                    <div class="row col-8 offset-2">
                                        <div class="col-6 mt-2">
                                            <label>選擇月份:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control monthpicker" name="yyyymm" value="{{ !empty($yyyymm) ? $yyyymm : date('Y').'-'.date('m') }}" placeholder="年月格式：2016-06" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label>渠道:</label>
                                            <select class="form-control" id="source" name="source">
                                                <option value="" {{ !empty($source) && $source == null ? 'selected' : '' }}>全部</option>
                                                <option value="iCarryWeb" {{ !empty($source) && $source == 'iCarryWeb' ? 'selected' : '' }}>iCarry Web</option>
                                                @foreach($sources as $s)
                                                <option value="{{ $s->source }}" {{ !empty($source) && $s->source == $source ? 'selected' : '' }}>{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 text-center mb-2">
                                    <button type="button" onclick="formSearch()" class="btn btn-primary">查詢</button>
                                </div>
                            </div>
                        </form>
                        <div class="card-body table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="10%">日期</th>
                                        <th class="text-right" width="15%">有付款訂單數量</th>
                                        <th class="text-right" width="15%">有付款訂單總金額</th>
                                        <th class="text-right" width="10%">平均訂單金額</th>
                                        <th class="text-right" width="10%">運費關稅小計</th>
                                        <th class="text-right" width="10%">未付款訂單數量</th>
                                        <th class="text-right" width="10%">當日註冊人數</th>
                                        <th class="text-right" width="15%">當日不重複消費人數</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- @foreach ($orders as $order)
                                    <tr>
                                        <td class="text-left align-middle">{{ $order->yyyymmdd }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->total_order) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->total_money) }}</td>
                                        <td class="text-right align-middle">{{ $order->avg }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->total_shipping_tax) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->not_ok_total) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->user_total) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->distinct_buyer_total) }}</td>
                                    </tr>
                                    @endforeach --}}
                                    @for($i=0;$i<count($orderData);$i++)
                                    <tr>
                                        <td class="text-left align-middle">{{ $orderData[$i]['yyyymmdd'] }}</td>
                                        <td class="text-right align-middle">{{ number_format($orderData[$i]['total_order']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($orderData[$i]['total_money']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($orderData[$i]['avg']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($orderData[$i]['total_shipping_tax']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($orderData[$i]['not_ok_total']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($orderData[$i]['user_total']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($orderData[$i]['distinct_buyer_total']) }}</td>
                                    </tr>
                                    @endfor
                                    <tr class="bg-secondary">
                                        <td class="text-left align-middle">{{ $total['text'] }}</td>
                                        <td class="text-right align-middle">{{ number_format($total['monthly_order']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total['total_money']) }}</td>
                                        <td class="text-right align-middle">{{ $total['avg'] }}</td>
                                        <td class="text-right align-middle">{{ number_format($total['total_shipping_tax']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total['not_ok_total']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total['user_total']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total['distinct_buyer_total']) }}</td>
                                    </tr>
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
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
@endsection

@section('script')
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/mtz-monthpicker/jquery.mtz.monthpicker.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        var d = new Date();
        $('.monthpicker').monthpicker({
            pattern: 'yyyy-mm',
            selectedYear: d.getFullYear(),
            startYear: {{ $startYear }},
            finalYear: {{ $finalYear }},
            monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月']
        });

        $('#showForm').click(function(){
            let text = $('#showForm').html();
            $('#searchForm').toggle();
            text == '使用欄位查詢' ? $('#showForm').html('隱藏欄位查詢') : $('#showForm').html('使用欄位查詢');
        });
    })(jQuery);

    function formSearch(){
        let sel="";
        $("#source>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#source_hidden").val(sel.substring(1));
        $("#myForm").submit();
    }
</script>
@endsection
