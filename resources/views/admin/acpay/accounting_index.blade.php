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
                    <h1 class="m-0 text-dark"><b>帳務管理</b></h1>
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
                                <button class="btn btn-sm btn-info export export_multi mr-2" value="machine_table" disabled>匯出帳務總表</button>
                                <button class="btn btn-sm btn-info export export_multi mr-2" value="machine_detail" disabled>匯出明細</button>
                                <input type="checkbox" id="chkallitem"><span id="chkallitem_text"></span>
                                @endif
                            </div>
                            <div class="float-right">
                                <div class="input-group input-group-sm align-middle align-items-middle">
                                    <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($machines->total()) ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <form id="myForm" action="{{ url('acpayaccounting') }}" method="get">
                            <div id="searchForm" class="card card-primary" style="display: none">
                                <div class="card-body">
                                    <div class="row col-8 offset-2">
                                        <div class="col-6 mt-2">
                                            <label for="pay_time"><span class="text-danger">* </span>付款時間區間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datepicker" id="pay_time" name="pay_time" placeholder="格式：2016-06-06" value="{{ isset($pay_time) && $pay_time ? $pay_time : '' }}" autocomplete="off" required>
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datepicker" id="pay_time_end" name="pay_time_end" placeholder="格式：2016-06-06" value="{{ isset($pay_time_end) && $pay_time_end ? $pay_time_end : '' }}" autocomplete="off" required>
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="account">帳號:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="account" name="account" placeholder="輸入帳號" value="{{ isset($account) && $account ? $account : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="vendor_name">商家名稱:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="輸入商家名稱" value="{{ isset($vendor_name) && $vendor_name ? $vendor_name : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="shop">店名:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="shop" name="shop" placeholder="輸入店名" value="{{ isset($shop) && $shop ? $shop : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label for="shop">每頁筆數: (匯出時忽略此參數)</label>
                                            <select class="form-control" name="list">
                                                <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                                <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                                <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                                <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            </select>
                                        </div>
                                        <div class="col-12 text-center mt-2">
                                            <button type="button" class="btn btn-primary submit-btn">查詢</button>
                                            @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                            <button type="button" class="btn btn-danger export mr-2" value="machine_condition">匯出帳務總表(by條件)</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="card-body table-responsive">
                            @if(!empty($machines))
                            <h4>日期範圍：{{ substr($pay_time,0,10) }} ~ {{ substr($pay_time_end,0,10) }}</h4>
                            <table class="table table-hover table-sm mb-3 text-sm">
                                <thead>
                                    <tr>
                                        @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                        <th class="text-center align-middle" width="2%"></th>
                                        @endif
                                        <th class="text-left align-middle" width="6%">機台編號</th>
                                        <th class="text-left align-middle" width="10%">店名</th>
                                        <th class="text-left align-middle" width="5%">帳號</th>
                                        <th class="text-left align-middle" width="15%">商家名稱</th>
                                        <th class="text-left align-middle" width="8%">訂單筆數</th>
                                        <th class="text-right align-middle" width="8%">商品金額</th>
                                        <th class="text-right align-middle" width="8%">客人實付</th>
                                        <th class="text-right align-middle" width="8%">運費<br>(含基本費)</th>
                                        <th class="text-right align-middle" width="8%">金流抽成</th>
                                        <th class="text-right align-middle" width="8%">iCarry實收</th>
                                        <th class="text-center align-middle" width="8%">收款行</th>
                                        <th class="text-center align-middle" width="5%">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($machines as $machine)
                                    <tr>
                                        @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <input type="checkbox" class="chk_item_{{ $machine->id }}" name="chk_item" value="{{ $machine->id }}">
                                        </td>
                                        @endif
                                        <td class="text-left align-middle">
                                            <a href="{{ route('admin.acpaymachines.show', $machine->id) }}">
                                                {{ $machine->machine_number }}
                                            </a>
                                        </td>
                                        <td class="text-left align-middle">{{ $machine->name }}</td>
                                        <td class="text-left align-middle">{{ $machine->account }}</td>
                                        <td class="text-left align-middle">{{ $machine->vendor_name }}</td>
                                        <td class="text-left align-middle">{{ number_format($machine->count) }}</td>
                                        <td class="text-right align-middle">{{ number_format($machine->productPrice) }}</td>
                                        <td class="text-right align-middle">{{ number_format($machine->amount) }}</td>
                                        <td class="text-right align-middle">{{ number_format($machine->shippingFee) }}</td>
                                        <td class="text-right align-middle">{{ number_format($machine->payDraw) }}</td>
                                        <td class="text-right align-middle">{{ number_format($machine->icarryIncome) }}</td>
                                        <td class="text-center align-middle">{{ $machine->bank }}</td>
                                        <td class="text-center align-middle">
                                            <a href="{{ route('admin.acpayaccounting.show', $machine->id.$detail) }}"><span class="badge badge-primary">明細</span></a>
                                        </td>
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
                                        <span class="badge badge-primary text-lg ml-1">總筆數：{{ number_format($machines->total()) ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $machines->appends($appends)->render() }}
                                @else
                                {{ !empty($machines) ? $machines->render() : '' }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form id="export" action="{{ url('acpayaccounting/export') }}" method="POST">
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
                let id = $('<input type="hidden" class="formappend" name="ids['+i+']">').val(itemIds[i]);
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
