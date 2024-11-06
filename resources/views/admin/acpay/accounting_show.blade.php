@extends('admin.layouts.master')

@section('title', '帳務管理')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>帳務管理</b><small>({{ $machine->name }}明細)</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('acpayaccounting') }}">帳務管理</a></li>
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
                            <div class="float-left form-inline">
                                <button id="showForm" class="btn btn-sm btn-success mr-2" title="使用欄位查詢">使用欄位查詢</button>
                                @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                <button class="btn btn-sm btn-info export export_multi mr-2" value="record_detail" disabled>匯出明細</button>
                                <input type="checkbox" id="chkallitem"><span id="chkallitem_text"></span>
                                @endif
                            </div>
                            <div class="float-right">
                                <div class="input-group input-group-sm align-middle align-items-middle">
                                    <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($orders->total()) ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <form id="myForm" action="{{ route('admin.acpayaccounting.show',$machine->id) }}" method="get">
                            <div id="searchForm" class="card card-primary" style="display: none">
                                <div class="card-body">
                                    <div class="row col-8 offset-2">
                                        <div class="col-12 mt-2">
                                            <label for="pay_time"><span class="text-danger">* </span>付款時間區間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datepicker" id="pay_time" name="pay_time" placeholder="格式：2016-06-06" value="{{ isset($pay_time) && $pay_time ? $pay_time : '' }}" autocomplete="off" required>
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datepicker" id="pay_time_end" name="pay_time_end" placeholder="格式：2016-06-06" value="{{ isset($pay_time_end) && $pay_time_end ? $pay_time_end : '' }}" autocomplete="off" required>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label for="status">訂單狀態:</label>
                                            <select class="form-control" id="status" size="6" multiple>
                                                <option value="-2" {{ isset($status) ? in_array(-2,explode(',',$status)) ? 'selected' : '' : 'selected' }}  class="text-danger">訂單已退貨</option>
                                                <option value="-1" {{ isset($status) ? in_array(-1,explode(',',$status)) ? 'selected' : '' : 'selected' }}  class="text-secondary">已取消訂單</option>
                                                <option value="1"  {{ isset($status) ? in_array(1,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-info">等待出貨</option>
                                                <option value="2"  {{ isset($status) ? in_array(2,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-success">訂單集貨中</option>
                                                <option value="3"  {{ isset($status) ? in_array(3,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-primary">訂單已出貨</option>
                                            </select><input type="hidden" value="-2,-1,1,2,3" name="status" id="status_hidden" />
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="account">訂單編號:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="order_number" name="order_number" placeholder="輸入訂單編號" value="{{ isset($order_number) && $order_number ? $order_number : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="account">交易編號:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="device_order_number" name="device_order_number" placeholder="輸入訂單編號" value="{{ isset($device_order_number) && $device_order_number ? $device_order_number : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="control-label" for="free_shipping">是否免運費:</label>
                                            <select class="form-control" id="free_shipping" name="free_shipping">
                                                <option value="" {{ isset($free_shipping) ? $free_shipping == '' ? 'selected' : '' : 'selected' }}>不拘</option>
                                                <option value="1" {{ isset($free_shipping) ? $free_shipping == 1 ? 'selected' : '' : '' }}>是</option>
                                                <option value="0" {{ isset($free_shipping) ? $free_shipping == 0 ? 'selected' : '' : '' }}>否</option>
                                            </select>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="control-label" for="free_shipping">每頁筆數: (匯出時忽略此參數)</label>
                                            <select class="form-control" name="list">
                                                <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                                <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                                <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                                <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                            </select>
                                        </div>
                                        <div class="col-12 text-center mt-2">
                                            <button type="button" class="btn btn-primary submit-btn">查詢</button>
                                            @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                            <button type="button" class="btn btn-danger export mr-2" value="record_condition">匯出明細(by條件)</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="card-body table-responsive">
                            <h4>日期範圍：{{ substr($pay_time,0,10) }} ~ {{ substr($pay_time_end,0,10) }}</h4>
                            @if(count($orders) > 0)
                            <table class="table table-hover table-sm mb-3 text-sm">
                                <thead>
                                    <tr>
                                        @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                        <th class="text-center align-middle" width="3%"></th>
                                        @endif
                                        <th class="text-left align-middle" width="10%">訂單編號</th>
                                        <th class="text-left align-middle" width="10%">交易時間</th>
                                        <th class="text-center align-middle" width="8%">訂單狀態</th>
                                        <th class="text-left align-middle" width="8%">交易編號</th>
                                        <th class="text-center align-middle" width="8%">是否免運</th>
                                        <th class="text-right align-middle" width="8%">客人實付</th>
                                        <th class="text-right align-middle" width="8%">商品金額</th>
                                        <th class="text-right align-middle" width="8%">運費</th>
                                        <th class="text-right align-middle" width="8%">基本費</th>
                                        <th class="text-right align-middle" width="8%">金流抽成</th>
                                        <th class="text-right align-middle" width="8%">iCarry實收</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <input type="checkbox" class="chk_item_{{ $order->id }}" name="chk_item" value="{{ $order->id }}">
                                        </td>
                                        @endif
                                        <td class="text-left align-middle">{{ $order->order_number }}</td>
                                        <td class="text-left align-middle">{{ $order->pay_time }}</td>
                                        <td class="text-center align-middle">
                                            @if($order->status == -2)
                                            <span class="text-danger">已退貨</span>
                                            @elseif($order->status == -1)
                                            <span class="text-secondary">已取消
                                            @elseif($order->status == 1)
                                            <span class="text-info">待出貨</span>
                                            @elseif($order->status == 2)
                                            <span class="text-success">集貨中</span>
                                            @elseif($order->status == 3)
                                            <span class="text-primary">已出貨</span>
                                            @endif
                                        </td>
                                        <td class="text-left align-middle">{{ $order->device_order_number }}</td>
                                        <td class="text-center align-middle">{{ $order->free_shipping == 0 ? '否' : '是' }}</td>
                                        <td class="text-right align-middle">
                                            {{ number_format($order->amount) }}
                                            @if($order->status == -2)
                                            <br>
                                            <span class="text-danger">{{ number_format($order->refund_amount) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right align-middle">{{ number_format($order->productPice) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->shippingFee) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->base_shipping_fee) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->payDraw) }}</td>
                                        <td class="text-right align-middle">{{ number_format($order->icarryIncome) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <h3>無資料</h3>
                            @endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <div class="form-group">
                                        <span class="badge badge-primary text-lg ml-1">總筆數：{{ number_format($orders->total()) ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $orders->appends($appends)->render() }}
                                @else
                                {{ !empty($orders) ? $orders->render() : '' }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form id="export" action="{{ url('acpayaccounting/export') }}" method="POST">
            <input type="hidden" name="machine_list_id" value="{{ $machine->id }}">
            @csrf
        </form>
    </section>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        $('.datepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('input[data-bootstrap-switch]').on('switchChange.bootstrapSwitch', function (event, state) {
            $(this).parents('form').submit();
        });

        $('#showForm').click(function(){
            let text = $('#showForm').html();
            $('#searchForm').toggle();
            text == '使用欄位查詢' ? $('#showForm').html('隱藏欄位查詢') : $('#showForm').html('使用欄位查詢');
        });

        $('.submit-btn').click(function(){
            let sel = "";
            $("#status>option:selected").each(function(){
                sel+=","+$(this).val();
            });
            $("#status_hidden").val(sel.substring(1));
            if($('#pay_time').val() == '' || $('#pay_time_end').val() == ''){
                alert('付款時間區間必填');
                return;
            }else if($('#pay_time').val() > $('#pay_time_end').val()){
                alert('付款時間區間範圍錯誤，起始時間必須早於結束時間');
                return;
            }else{
                $(this).parents('form').submit();
            }
        });

        var num_all = $('input[name="chk_item"]').length;
        var num = $('input[name="chk_item"]:checked').length;
        $("#chkallitem_text").text("全選("+num+"/"+num_all+")");

        $('#chkallitem').change(function(){
            if($("#chkallitem").prop("checked") == true){
                $('input[name="chk_item"]').prop("checked",true);
                $(".export_multi").attr("disabled",false);
            }else{
                $('input[name="chk_item"]').prop("checked",false);
                $(".export_multi").attr("disabled",true);
            }
            var num_all = $('input[name="chk_item"]').length;
            var num = $('input[name="chk_item"]:checked').length;
            $("#chkallitem_text").text("全選("+num+"/"+num_all+")");
            num > 0 ? $(".export_multi").attr("disabled",false) : $(".export_multi").attr("disabled",true);
        });

        $('input[name="chk_item"]').change(function(){
            var num_all = $('input[name="chk_item"]').length;
            var num = $('input[name="chk_item"]:checked').length;
            num_all != num ? $("#check_all").prop("checked",false) : $("#check_all").prop("checked",true);
            num > 0 ? $(".export_multi").attr("disabled",false) : $(".export_multi").attr("disabled",true);
            $("#chkallitem_text").text("全選("+num+"/"+num_all+")");
        });

        $('.export').click(function (e) {
            let form = $('#export');
            let cate = $(this).val().split('_')[0];
            let type = $(this).val().split('_')[1];
            let itemIds = $('input[name="chk_item"]:checked').serializeArray().map( item => item.value );
            let filename = $(this).html();
            let exportCate = $('<input type="hidden" class="formappend" name="cate" value="'+cate+'">');
            let exportType = $('<input type="hidden" class="formappend" name="type" value="'+type+'">');
            form.append($('<input type="hidden" class="formappend" name="pay_time" value="'+$('#pay_time').val()+'">'));
            form.append($('<input type="hidden" class="formappend" name="pay_time_end" value="'+$('#pay_time_end').val()+'">'));
            form.append(exportCate);
            form.append(exportType);
            form.append( $('<input type="hidden" class="formappend" name="filename" value="'+filename+'">') );
            for(let i=0;i<itemIds.length;i++){
                let id = $('<input type="hidden" class="formappend" name="record_ids['+i+']">').val(itemIds[i]);
                form.append(id);
            }
            if(type == 'condition'){
                let con_val = $('#myForm').serializeArray().map( item => item.value );
                let con_name = $('#myForm').serializeArray().map( item => item.name );
                for(let j=0; j<con_name.length;j++){
                    let tmp = '';
                    tmp = $('<input type="hidden" class="formappend" name="'+con_name[j]+'" value="'+con_val[j]+'">');
                    form.append(tmp);
                }
            }
            form.submit();
            $('.formappend').remove();
        });
    })(jQuery);
</script>
@endsection
