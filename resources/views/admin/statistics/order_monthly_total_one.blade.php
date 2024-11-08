@extends('admin.layouts.master')

@section('title', '訂單每月統計')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>訂單每月統計</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('ordermonthlytotal') }}">訂單每月統計</a></li>
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
                        <form id="myForm" action="{{ url('ordermonthlytotalOne') }}" method="get">
                            <div id="searchForm" class="card card-primary" style="display: none">
                                <div class="card-body">
                                    <div class="row col-8 offset-2">
                                            <label>渠道訂單:</label>
                                            <select class="form-control" id="source" name="source">
                                                <option value="" {{ !empty($source) && $source == null ? 'selected' : '' }}>全部</option>
                                                <option value="iCarryWeb" {{ !empty($source) && $source == 'iCarryWeb' ? 'selected' : '' }}>Web</option>
                                                @foreach($sources as $s)
                                                <option value="{{ $s->source }}" {{ !empty($source) && $s->source == $source ? 'selected' : '' }}>{{ $s->name }}</option>
                                                @endforeach
                                            </select>
                                    </div>
                                </div>
                                <div class="col-12 text-center mb-2">
                                    <button type="button" onclick="formSearch()" class="btn btn-primary">查詢</button>
                                </div>
                            </div>
                        </form>
                        <div class="card-body table-responsive">
                            @if(count($orders) > 0)
                            @foreach ($orders as $year => $values)
                            <table class="table table-hover table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="10%">{{ $year.' ' }}年</th>
                                        <th class="text-right" width="15%">有付款訂單數量</th>
                                        <th class="text-right" width="15%">有付款訂單總金額</th>
                                        <th class="text-right" width="10%">平均訂單金額</th>
                                        <th class="text-right" width="10%">運費關稅小計</th>
                                        <th class="text-right" width="10%">未付款訂單數量</th>
                                        <th class="text-right" width="10%">當月註冊人數</th>
                                        <th class="text-right" width="15%">當月不重複消費人數</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($values as $order)
                                    <tr>
                                        <td class="text-left">{{ $order->yyyymm }}</td>
                                        <td class="text-right">{{ number_format($order->pay_orders) }}</td>
                                        <td class="text-right">{{ number_format($order->pay_money_total) }}</td>
                                        <td class="text-right">{{ $order->avg_orders_money }}</td>
                                        <td class="text-right">{{ number_format($order->ffeight_tariff_total) }}</td>
                                        <td class="text-right">{{ number_format($order->no_pay_orders) }}</td>
                                        <td class="text-right">{{ number_format($order->registered_num) }}</td>
                                        <td class="text-right">{{ number_format($order->no_repeat_consumption) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-secondary">
                                        <td class="text-left">{{ $total[$year]['text'] }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['pay_orders']) }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['pay_money_total']) }}</td>
                                        <td class="text-right">{{ $total[$year]['avg_orders_money'] }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['ffeight_tariff_total']) }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['no_pay_orders']) }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['registered_num']) }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['no_repeat_consumption']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            @endforeach
                            @else
                            <h3>查無資料</h3>
                            @endif
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
@endsection

@section('script')
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
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
