@extends('admin.layouts.master')

@section('title', '提貨日設定')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>提貨日設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('receiverbase') }}">提貨日設定</a></li>
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
                        <div class="card-body">
                            <table class="table table-hover table-sm text-sm">
                                <thead>
                                    <tr>
                                        <td colspan="7">
                                            <div class="row">
                                                <div class="col-8 float-left">
                                                    <form id="myForm" role="form" action="{{ url('receiverbase') }}" method="get">
                                                        <div class="input-group input-group-sm align-middle align-items-middle">
                                                            <button type="button" class="changeMonth btn btn-sm btn-info" {{ $nowYear <= 2021 && $nowMonth == '01' ? 'disabled' : '' }} value="lastMonth">上月</button>
                                                            <div class="col-3">
                                                                <select class="form-control" id="nowYear" name="nowYear" onchange="submit(this)">
                                                                    @for($i = 2021; $i <= 2032; $i++)
                                                                    <option value="{{ $i }}" {{ $nowYear == $i ? 'selected' : '' }}>{{ $i }} 年</option>
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <div class="col-3">
                                                                <select class="form-control" id="nowMonth" name="nowMonth" onchange="submit(this)">
                                                                    @for($i = 1; $i <= 12; $i++)
                                                                    @if($i<=9)
                                                                    <option value="0{{ $i }}" {{ $nowMonth == '0'.$i ? 'selected' : '' }}>{{ $i }} 月</option>
                                                                    @else
                                                                    <option value="{{ $i }}" {{ $nowMonth == $i ? 'selected' : '' }}>{{ $i }} 月</option>
                                                                    @endif
                                                                    @endfor
                                                                </select>
                                                            </div>
                                                            <button type="button" class="changeMonth btn btn-sm btn-info mr-2" value="nextMonth">下月</button>
                                                            <button type="button" class="changeMonth btn btn-sm btn-primary" value="thisMonth">本月</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div class="col-4 ">
                                                    <div class=" float-right">
                                                        <div class="input-group input-group-sm align-middle align-items-middle">
                                                            @if(in_array($menuCode.'M', explode(',',Auth::user()->power)))
                                                            <button class="multi-btn btn btn-sm btn-primary mr-2" value="" disabled>批次修改</button>
                                                            @endif
                                                            <button class="search-btn btn btn-sm btn-primary" value="stockDays">列出指定提貨日商品</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background-color:#EEEEEE">
                                        <td class="text-center align-middle text-bold">星期日</td>
                                        <td class="text-center align-middle text-bold">星期一</td>
                                        <td class="text-center align-middle text-bold">星期二</td>
                                        <td class="text-center align-middle text-bold">星期三</td>
                                        <td class="text-center align-middle text-bold">星期四</td>
                                        <td class="text-center align-middle text-bold">星期五</td>
                                        <td class="text-center align-middle text-bold">星期六</td>
                                    </tr>
                                    {{-- 五周迴圈 --}}
                                    @foreach($receiverBases as $receiverBase)
                                    <tr>
                                        {{-- 七天群組 key值就是select_date的日期 --}}
                                        @foreach($receiverBase as $date => $tmps)
                                        <td class="text-center align-top" style="{{ $date == date('Y-m-d') && substr($date,5,2) == $nowMonth ? 'background-color:#00FFFF' : '' }}">
                                            {{-- @if(substr($date,5,2) == $nowMonth ) --}}
                                            <div>
                                                <span class="text-bold text-info" style="font-size: 3em; cursor:pointer" onclick="select('{{ $date }}');">{{ substr($date,8,2) }}</span>
                                            </div>
                                            <div class="icheck-primary">
                                                <input type="checkbox" class="chk" id="{{ $date }}" name="date[]" value="{{ $date }}">
                                                <label for="{{ $date }}"></label>
                                            </div>
                                            @foreach($tmps as $tmp)
                                                <input type="hidden" class="{{ $date }}" name="{{ $tmp->type }}" value="{{ $tmp->is_ok }}">
                                                <input type="hidden" class="{{ $date }}_memo" name="{{ $tmp->type }}" value="{{ $tmp->memo }}">
                                                @if($tmp->type == 'pickup' && $tmp->is_ok == 1)
                                                <div class="bg-primary text-white">提貨日</div>
                                                @endif
                                                @if($tmp->type == 'call' && $tmp->is_ok == 1)
                                                <div class="bg-danger text-white">叫貨日</div>
                                                @endif
                                                @if($tmp->type == 'logistics' && $tmp->is_ok == 1)
                                                <div class="bg-warning text-white">物流日</div>
                                                @endif
                                                @if($tmp->type == 'out' && $tmp->is_ok == 1)
                                                <div class="bg-success text-white">出貨日</div>
                                                @endif
                                            @endforeach
                                            {{-- @endif --}}
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <div class="form-group">
                                    <form action="{{ url('invoices') }}" method="GET" class="form-inline" role="search">

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('modal')
{{-- 修改 Modal --}}
<div id="updateModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 70%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="updateForm" action="{{ url('receiverbase/update') }}" method="POST">
                    <input type="hidden" name="_method" value="PATCH">
                    @csrf
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="icheck-primary d-inline mr-2">
                                    <input type="radio" class="type_call" id="call_yes" name="is_ok[call]" value="1">
                                    <label for="call_yes">可</label>
                                </div>
                                <div class="icheck-danger d-inline mr-2">
                                    <input type="radio" class="type_call" id="call_no" name="is_ok[call]" value="0">
                                    <label for="call_no">不可</label>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">叫貨日</span>
                            </div>
                            <input type="text" class="form-control" id="memo_call" name="memo[call]" value="" placeholder="請填寫不可理由">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="icheck-primary d-inline mr-2">
                                    <input type="radio" id="logistics_yes" name="is_ok[logistics]" value="1">
                                    <label for="logistics_yes">可</label>
                                </div>
                                <div class="icheck-danger d-inline mr-2">
                                    <input type="radio" id="logistics_no" name="is_ok[logistics]" value="0">
                                    <label for="logistics_no">不可</label>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">物流日</span>
                            </div>
                            <input type="text" class="form-control" id="memo_logistics" name="memo[logistics]" value="" placeholder="請填寫不可理由">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="icheck-primary d-inline mr-2">
                                    <input type="radio" id="out_yes" name="is_ok[out]" value="1">
                                    <label for="out_yes">可</label>
                                </div>
                                <div class="icheck-danger d-inline mr-2">
                                    <input type="radio" id="out_no" name="is_ok[out]" value="0">
                                    <label for="out_no">不可</label>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">出貨日</span>
                            </div>
                            <input type="text" class="form-control" id="memo_out" name="memo[out]" value="" placeholder="請填寫不可理由">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="icheck-primary d-inline mr-2">
                                    <input type="radio" id="pickup_yes" name="is_ok[pickup]" value="1">
                                    <label for="pickup_yes">可</label>
                                </div>
                                <div class="icheck-danger d-inline mr-2">
                                    <input type="radio" id="pickup_no" name="is_ok[pickup]" value="0">
                                    <label for="pickup_no">不可</label>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">提貨日</span>
                            </div>
                            <input type="text" class="form-control" id="memo_pickup" name="memo[pickup]" value="" placeholder="若選擇不可，此欄必填，將顯示此原因於前台給顧客">
                        </div>
                    </div>
                    @if(in_array($menuCode.'M', explode(',',Auth::user()->power)))
                    <div class="form-group">
                        <button type="button" class="submit-btn btn btn-md btn-primary">修改</button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
{{-- 註記 Modal --}}
<div id="searchModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 70%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel">列出指定提貨日商品</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group" id="myform">
                    <form id="searchForm">
                        <input type="hidden" id="search_type" name="search_type" value="stockDays">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="icheck-primary d-inline mr-2">
                                        <input type="radio" id="shipping_method_1" name="shipping_method" value="1">
                                        <label for="shipping_method_1">機場提貨</label>
                                    </div>
                                    <div class="icheck-danger d-inline mr-2">
                                        <input type="radio" id="shipping_method_2" name="shipping_method" value="2">
                                        <label for="shipping_method_2">旅店提貨</label>
                                    </div>
                                </div>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">下單日期</span>
                                </div>
                                <input type="text" class="form-control bg-white" id="order_date" name="order_date" value="" placeholder="下單日期" readonly>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">預定提貨日期</span>
                                </div>
                                <input type="text" class="form-control bg-white" id="pickup_date" name="pickup_date" value="" placeholder="預定提貨日期" readonly>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">商家或商品(非必填)</span>
                                </div>
                                <input type="text" class="form-control" id="keyword" name="keyword" placeholder="關鍵字查詢">
                                <button type="button" class="send-btn btn btn-md btn-primary">搜尋</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="form-group form-group-sm" id="myrecord">
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th width="10%">商品ID</th>
                                        <th width="15%">商品類別</th>
                                        <th width="65%">商品名稱</th>
                                        <th width="10%" class="text-right align-middle">備貨天數</th>
                                    </tr>
                                </thead>
                                <tbody id="products"></tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/datetimepicker/build/jquery.datetimepicker.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/datetimepicker/build/jquery.datetimepicker.full.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    $.datetimepicker.setLocale('zh-TW');
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

        $('.changeMonth').click(function(){
            let nowYear =  parseInt($('#nowYear').val(), 10);
            let nowMonth = parseInt($('#nowMonth').val(), 10);
            let newMonth = 0;
            let thisMonth = '{{ date('m') }}';
            thisMonth = parseInt(thisMonth, 10);
            if($(this).val() == 'lastMonth'){
                newMonth = nowMonth - 1;
            }else if($(this).val() == 'nextMonth'){
                newMonth = nowMonth + 1;
            }else if($(this).val() == 'thisMonth'){
                newMonth = thisMonth;
            }
            newMonth <= 9 && newMonth > 0 ? newMonth = '0'+newMonth : '';
            if(newMonth == 0){
                $('#nowYear').val(nowYear-1);
                $('#nowMonth').val(12);
            }else if(newMonth > 12){
                $('#nowYear').val(nowYear+1);
                $('#nowMonth').val('01');
            }else{
                $('#nowYear').val(nowYear);
                $('#nowMonth').val(newMonth);
            }
            $('#myForm').submit();
        });

        $('.chk').change(function(){
            let num = $('.chk:checked').length;
            num > 0 ? $('.multi-btn').prop('disabled',false) : $('.multi-btn').prop('disabled',true);
        });

        $('.multi-btn').click(function(){
            let form = $('#updateForm');
            let date = $('.chk:checked').serializeArray().map( item => item.value );
            $('#call_yes').prop('checked',true);
            $('#logistics_yes').prop('checked',true);
            $('#out_yes').prop('checked',true);
            $('#pickup_yes').prop('checked',true);
            $('#updateModalLabel').html('提貨日設定修改');
            for(let i=0; i<date.length;i++){
                form.append($('<input type="hidden" class="formappend" name="date['+i+']" value="'+date[i]+'">'));
            }
            $('#updateModal').modal('show');
        });

        $('.submit-btn').click(function(){
            let memo = $('#memo_pickup').val();
            $('#call_yes').is(':checked') ? $('#memo_call').val('') : '';
            $('#logistics_yes').is(':checked') ? $('#memo_logistics').val('') : '';
            $('#out_yes').is(':checked') ? $('#memo_out').val('') : '';
            $('#pickup_yes').is(':checked') ? $('#memo_pickup').val('') : '';
            if($('#pickup_no').is(':checked')){
                if(memo == ''){
                    alert('請填寫提貨日不可原因');
                }else{
                    $('#updateForm').submit();
                    $('.formappend').remove();
                    $('#updateModal').modal('hide');
                }
            }else{
                $('#updateForm').submit();
                $('.formappend').remove();
                $('#updateModal').modal('hide');
            }
        });

        // 下單日 date time picker 設定
        // 參考 https://xdsoft.net/jqplugins/datetimepicker
        $('#order_date').datetimepicker({
            lang: 'zh-TW',
            format:'Y-m-d H:i:s',
            step: 30,
            closeOnDateSelect: false,
            closeOnTimeSelect: true,
            minDate:'2021-01-01',
            formatDate:'Y-m-d',
        });

        // 取貨日 date time picker 設定
        // 參考 https://xdsoft.net/jqplugins/datetimepicker
        $('#pickup_date').datetimepicker({
            lang: 'zh-TW',
            format:'Y-m-d H:i:s',
            step: 30,
            closeOnDateSelect: false,
            closeOnTimeSelect: true,
            minDate:'2021-01-01',
            disabledDates: [{!! $disablePickupDates !!}],
            formatDate:'Y-m-d',
        });

        $('.search-btn').click(function(){
            $('#searchModal').modal('show');
        });

        $('.send-btn').click(function(){
            let token = '{{ csrf_token() }}';
            let search_type = $('input[name=search_type]').val();
            let shipping_method = $('input[name=shipping_method]:checked').val();
            let order_date = $('input[name=order_date]').val();
            let pickup_date = $('input[name=pickup_date]').val();
            let keyword = $('#keyword').val();
            if(shipping_method == undefined){
                alert('請選擇提貨方式');
            }else if(order_date == ''){
                alert('請輸入下單日期');
            }else if(pickup_date == ''){
                alert('請輸入預定提貨日期');
            }else if(search_type == 'productAvailable'){
                if(keyword == ''){
                    alert('請輸入商品名稱或商家名稱');
                }
            }else{
                $.ajax({
                    type: "post",
                    url: 'receiverbase/search',
                    data: { search_type: search_type, shipping_method: shipping_method, order_date: order_date, pickup_date: pickup_date , keyword: keyword, _token: token },
                    success: function(data) {
                        let html = '';
                        if(data){
                            for(let i=0;i<data.length;i++){
                                html += '<tr><td>'+data[i]['id']+'</td><td>'+data[i]['category']+'</td><td>'+data[i]['name']+'</td><td class="text-right align-middle">'+data[i]['days']+'</td></tr>';
                            }
                        }
                        $('#products').html(html);
                    }
                });
            }
        });
    })(jQuery);

    function select (date){
        let form = $('#updateForm');
        let v = $('.'+date).serializeArray();
        let m = $('.'+date+'_memo').serializeArray();
        form.append($('<input type="hidden" class="formappend" name="date[0]" value="'+date+'">'));
        $('#updateModalLabel').html(date+' 提貨日設定修改');
        for(let i=0; i<v.length; i++){
            let name = v[i]['name'];
            if(v[i]['value'] == 1){
                $('#'+name+'_yes').prop('checked',true);
                $('#'+name+'_no').prop('checked',false);
            }else{
                $('#'+name+'_yes').prop('checked',false);
                $('#'+name+'_no').prop('checked',true);
            }
            $('#memo_'+name).val(m[i]['value']);
        }
        $('#updateModal').modal('show');
    }
</script>
@endsection
