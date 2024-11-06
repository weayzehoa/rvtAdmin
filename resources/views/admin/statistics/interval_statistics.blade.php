@extends('admin.layouts.master')

@section('title', '訂單區間統計')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>訂單區間統計</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('intervalstatistics') }}">訂單區間統計</a></li>
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
                            <form id="myForm" action="{{ url('intervalstatistics') }}" method="get">
                                <div class="row col-8 offset-2">
                                    <div class="col-8 mt-2">
                                        <label for="pay_time">訂單時間區間: <span class="text-danger">(必填)</span></label>
                                        <div class="input-group">
                                            <input type="datetime" class="form-control datetimepicker" id="pay_time" name="pay_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pay_time) && $pay_time ? $pay_time : '' }}" autocomplete="off" required>
                                            <span class="input-group-addon bg-primary">~</span>
                                            <input type="datetime" class="form-control datetimepicker" id="pay_time_end" name="pay_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pay_time_end) && $pay_time_end ? $pay_time_end : '' }}" autocomplete="off" required>
                                        </div>
                                    </div>
                                    <div class="col-4 mt-2">
                                        <label for="ship_to">寄送國家:</label>
                                        <select class="form-control" id="ship_to" name="to">
                                            <option value="">不拘</option>
                                            @foreach($countries as $country)
                                            <option value="{{ $country->name }}" {{ !empty($to) && $to == $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label>渠道: (ctrl+點選可多選)</label>
                                        <select class="form-control" id="source" size="12" multiple>
                                            @foreach($sources as $s)
                                            <option value="{{ $s->source }}" {{ !empty($source) && in_array($s->source,$source) ? 'selected' : '' }}>{{ $s->name }}</option>
                                            @endforeach
                                        </select><input type="hidden" value="" name="source" id="source_hidden" />
                                    </div>
                                    <p class="text-danger text-bold">(選擇區間範圍太大或太多渠道，可能造成執行速度過慢，請耐心等候)</p>
                                    <div class="col-12 text-center mt-2">
                                        <button type="button" onclick="formSearch()" class="btn btn-primary">查詢</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        @if(!empty($orders))
                        @if(empty($source))
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="20%">日期</th>
                                        <th class="text-right" width="20%">有付款訂單數量</th>
                                        <th class="text-right" width="20%">有付款訂單總金額</th>
                                        <th class="text-right" width="20%">平均訂單金額</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-secondary">
                                        <td class="text-left align-middle">
                                            {{ substr($pay_time,0,10).'~'.substr($pay_time_end,0,10) }}
                                            @foreach($countries as $country)
                                            @if(!empty($to) && $to == $country->id)
                                            {{ '寄送'.$country->name }}
                                            @break
                                            @endif
                                            @endforeach
                                            {{ $total['text']  }}
                                        </td>
                                        <td class="text-right align-middle">{{ number_format($total['count']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total['total']) }}</td>
                                        <td class="text-right align-middle">{{ $total['avg'] }}</td>
                                    </tr>
                                    @foreach ($orders as $order)
                                    <tr>
                                        <td class="text-left align-middle">{{ $order['date'] }}</td>
                                        <td class="text-right align-middle">{{ number_format($order['count']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order['total']) }}</td>
                                        <td class="text-right align-middle">{{ $order['avg'] }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-secondary">
                                        <td class="text-left align-middle">
                                            {{ substr($pay_time,0,10).'~'.substr($pay_time_end,0,10) }}
                                            @foreach($countries as $country)
                                            @if(!empty($to) && $to == $country->id)
                                            {{ '寄送'.$country->name }}
                                            @break
                                            @endif
                                            @endforeach
                                            {{ $total['text']  }}
                                        </td>
                                        <td class="text-right align-middle">{{ number_format($total['count']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total['total']) }}</td>
                                        <td class="text-right align-middle">{{ $total['avg'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                        </div>
                        @else
                        <div class="card-body table-responsive">
                            <table class="table table-hover table-sm text-nowrap">
                                <thead>
                                    <tr>
                                        <th class="text-left">日期</th>
                                        @for($i = 0; $i < count($source); $i++)
                                        @foreach($sources as $s)
                                        @if($source[$i] == $s->source)
                                        <th class="text-right">[{{ $s->name }}]<br>訂單數量</th>
                                        <th class="text-right">[{{ $s->name }}]<br>訂單總金額</th>
                                        @endif
                                        @endforeach
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-secondary">
                                        <td class="text-left align-middle">總計</td>
                                        @for($i = 0; $i < count($total); $i++)
                                        <td class="text-right align-middle">{{ number_format($total[$i]['orders']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total[$i]['money']) }}</td>
                                        @endfor
                                    </tr>
                                    @foreach ($orders as $key => $value)
                                    <tr>
                                        <td class="text-left align-middle">{{ $key }}</td>
                                        @foreach($value as $v)
                                        <td class="text-right align-middle">{{ number_format($v['orders']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($v['money']) }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                    <tr class="bg-secondary">
                                        <td class="text-left align-middle">總計</td>
                                        @for($i = 0; $i < count($total); $i++)
                                        <td class="text-right align-middle">{{ number_format($total[$i]['orders']) }}</td>
                                        <td class="text-right align-middle">{{ number_format($total[$i]['money']) }}</td>
                                        @endfor
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                        </div>
                        @endif
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

    function formSearch(){
        let sel="";
        $("#source>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#source_hidden").val(sel.substring(1));
        if($('#pay_time').val() == '' || $('#pay_time_end').val() == ''){
            alert('交易區間的開始時間或結束時間不可為空白!');
        }else if($('#pay_time').val() > $('#pay_time_end').val()){
            alert('交易區間的開始時間不能小於結束時間!');
        }else{
            $("#myForm").submit();
        }
    }
</script>
@endsection
