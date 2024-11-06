@extends('admin.layouts.master')

@section('title', '訂單每月出貨統計')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>訂單每月出貨統計</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('ordermonthlyselltotal') }}">訂單每月出貨統計</a></li>
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
                        <form id="myForm" action="{{ url('ordermonthlyselltotal') }}" method="get">
                            <div id="searchForm" class="card card-primary" style="display: none">
                                <div class="card-body">
                                    <div class="row col-8 offset-2">
                                            <label>各渠道訂單: (ctrl+點選可多選)(再次ctrl+點擊可取消)</label>
                                            <select class="form-control" id="source" size="13" multiple>
                                                @foreach($sources as $s)
                                                <option value="{{ $s->source }}" {{ !empty($source) && in_array($s->source,$source) ? 'selected' : '' }}>{{ $s->name }}</option>
                                                @endforeach
                                            </select><input type="hidden" value="" name="source" id="source_hidden" />
                                    </div>
                                </div>
                                <div class="col-12 text-center mb-2">
                                    <button type="button" onclick="formSearch()" class="btn btn-primary">查詢</button>
                                </div>
                            </div>
                        </form>
                        @if(empty($source))
                        <div class="card-body table-responsive">
                            @foreach ($orders as $year => $values)
                            <table class="table table-hover table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="10%">{{ $year.' ' }}年</th>
                                        <th class="text-right" width="15%">銷貨訂單數量</th>
                                        <th class="text-right" width="15%">銷貨單總金額(未稅)</th>
                                        <th class="text-right" width="15%">銷退折讓總金額(未稅)</th>
                                        <th class="text-right" width="15%">平均訂單金額(未稅)</th>
                                        <th class="text-right" width="15%">運費關稅小計(未稅)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($values as $order)
                                    <tr>
                                        <td class="text-left">{{ $order->yyyymm }}</td>
                                        <td class="text-right">{{ number_format($order->pay_orders) }}</td>
                                        <td class="text-right">{{ number_format($order->pay_money_total) }}</td>
                                        <td class="text-right">{{ number_format($order->sell_return_total) }}</td>
                                        <td class="text-right">{{ $order->avg_orders_money }}</td>
                                        <td class="text-right">{{ number_format($order->ffeight_tariff_total) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-secondary">
                                        <td class="text-left">{{ $total[$year]['text'] }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['pay_orders']) }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['pay_money_total']) }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['sell_return_total']) }}</td>
                                        <td class="text-right">{{ $total[$year]['avg_orders_money'] }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['ffeight_tariff_total']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            @endforeach
                        </div>
                        <div class="card-footer bg-white">
                        </div>
                        @else
                        <div class="card-body table-responsive">
                            @foreach($orders as $year => $values)
                            <table class="table table-hover table-sm text-nowrap mb-3">
                                <thead>
                                    <tr>
                                        <th class="text-left">{{ $year }} 年</th>
                                        @for($i = 0; $i < count($source); $i++)
                                        @foreach($sources as $s)
                                        @if($source[$i] == $s->source)
                                        <th class="text-right" width="15%">[{{ $s->name }}]<br>銷貨訂單數量</th>
                                        <th class="text-right" width="15%">[{{ $s->name }}]<br>銷貨單總金額(未稅)</th>
                                        <th class="text-right" width="15%">[{{ $s->name }}]<br>銷退折讓總金額(未稅)</th>
                                        @endif
                                        @endforeach
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($values as $key => $value)
                                    <tr>
                                        <td class="text-left align-middle">{{ $key }}</td>
                                        @foreach($value as $v)
                                        <td class="text-right align-middle">{{ number_format($v['orders']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($v['money']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($v['return']) }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                    <tr class="bg-secondary">
                                        <td class="text-left align-middle">{{ $year }} 年總計</td>
                                        @for($i = 0; $i < count($total); $i++)
                                        <td class="text-right align-middle">{{ number_format($total[$i][$year]['orders']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total[$i][$year]['money']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total[$i][$year]['return']) }}</td>
                                        @endfor
                                    </tr>
                                </tbody>
                            </table>
                            @endforeach
                        </div>
                        <div class="card-footer bg-white">
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
