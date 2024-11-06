@extends('admin.layouts.master')

@section('title', '訂單物流統計')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>訂單物流統計</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('shippingmonthlytotal') }}">訂單物流統計</a></li>
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
                                <form action="{{ url('shippingmonthlytotal') }}" method="GET" class="form-inline" role="search">
                                    <label class="mr-2">渠道:</label>
                                    <div class="form-group-sm">
                                        <select class="form-control form-control-sm" name="source" onchange="submit(this)">
                                            <option value="">全部</option>
                                            @foreach($sources as $s)
                                            <option value="{{ $s->source }}" {{ !empty($source) && $s->source == $source ? 'selected' : '' }}>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="float-right">
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            @if(!empty($orders))
                            @foreach ($orders as $year => $values)
                            @if($total[$year]['allCount'] > 0)
                            <table class="table table-hover table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="10%">{{ $year.' ' }}年</th>
                                        <th class="text-right" width="10%">機場提貨<br>數量/金額</th>
                                        <th class="text-right" width="10%">旅店提貨<br>數量/金額</th>
                                        <th class="text-right" width="10%">現場提貨<br>數量/金額</th>
                                        <th class="text-right" width="10%">寄送海外<br>數量/金額</th>
                                        <th class="text-right" width="10%">寄送台灣<br>數量/金額</th>
                                        <th class="text-right" width="10%">寄送當地<br>數量/金額</th>
                                        <th class="text-right" width="10%">總計<br>數量/金額</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($values as $order)
                                    <tr>
                                        <td class="text-left">{{ $order->yyyymm }}月</td>
                                        <td class="text-right">{{ number_format($order->S1C) }} ({{ number_format($order->S1) }})</td>
                                        <td class="text-right">{{ number_format($order->S2C) }} ({{ number_format($order->S2) }})</td>
                                        <td class="text-right">{{ number_format($order->S3C) }} ({{ number_format($order->S3) }})</td>
                                        <td class="text-right">{{ number_format($order->S4C) }} ({{ number_format($order->S4) }})</td>
                                        <td class="text-right">{{ number_format($order->S5C) }} ({{ number_format($order->S5) }})</td>
                                        <td class="text-right">{{ number_format($order->S6C) }} ({{ number_format($order->S6) }})</td>
                                        <td class="text-right">{{ number_format($order->allCount) }} ({{ number_format($order->allAmount) }})</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-secondary">
                                        <td class="text-left">{{ $year.' 年總計' }}</td>
                                        <td class="text-right">{{ number_format($total[$year]['S1C']) }} ({{ number_format($total[$year]['S1']) }})</td>
                                        <td class="text-right">{{ number_format($total[$year]['S2C']) }} ({{ number_format($total[$year]['S2']) }})</td>
                                        <td class="text-right">{{ number_format($total[$year]['S3C']) }} ({{ number_format($total[$year]['S3']) }})</td>
                                        <td class="text-right">{{ number_format($total[$year]['S4C']) }} ({{ number_format($total[$year]['S4']) }})</td>
                                        <td class="text-right">{{ number_format($total[$year]['S5C']) }} ({{ number_format($total[$year]['S5']) }})</td>
                                        <td class="text-right">{{ number_format($total[$year]['S6C']) }} ({{ number_format($total[$year]['S6']) }})</td>
                                        <td class="text-right">{{ number_format($total[$year]['allCount']) }} ({{ number_format($total[$year]['allAmount']) }})</td>
                                    </tr>
                                </tbody>
                            </table>
                            @endif
                            @endforeach
                            @else
                            <h3>查無 {{ !empty($source) ? $source : '' }} 資料</h3>
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
