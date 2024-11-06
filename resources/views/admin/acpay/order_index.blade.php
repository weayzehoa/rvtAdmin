@extends('admin.layouts.master')

@section('title', 'ACPay訂單管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>ACPay訂單管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('acpayorders') }}">ACPay訂單管理</a></li>
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
                            <div class="row">
                                <div class="col-5">
                                    {{-- <button id="hidemodify" class="btn btn-sm btn-secondary" title="隱藏所有註記">隱藏所有註記</button> --}}
                                    <button id="showForm" class="btn btn-sm btn-success" title="使用欄位查詢">使用欄位查詢</button>
                                    @if(in_array($menuCode.'MK', explode(',',Auth::user()->power)))
                                    <button class="btn btn-sm btn-info" id="order_multi"><span>多筆處理</span></button>
                                    <input type="checkbox" id="chkallorder"><span id="chkallorder_text"></span>
                                    @endif
                                    <br><span class="text-danger text-sm text-bold">宅配通可用剩餘單數：{{ $mposShippingNumber }} 張</span>
                                </div>
                                <div class="col-7">
                                    <div class=" float-right">
                                        <span class="badge badge-purple text-lg">總筆數：{{ number_format($orders->total()) ?? 0 }}</span>
                                    </div>
                                </div>
                                <div class="col-12 mt-2" id="multiFunc" style="display:none">
                                    @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                    <button class="btn btn-sm btn-primary orderExport" value="export_ecan" title="宅配通出貨檔" disabled>宅配通出貨檔</button>
                                    @endif
                                    @if(in_array($menuCode.'MK', explode(',',Auth::user()->power)))
                                    <button class="btn btn-sm btn-info orderSelect" value="is_print" disabled><span>已列印註記</span></button>
                                    <button class="btn btn-sm btn-primary orderSelect" value="admin_memo" disabled><span>管理員註記</span></button>
                                    <button class="btn btn-sm btn-purple orderSelect" value="book_shipping_date" disabled><span>預定出貨註記</span></button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="orderSearchForm" class="card card-primary" style="display: none">
                                <div class="card-header">
                                    <h3 class="card-title">使用欄位查詢</h3>
                                </div>
                                <form id="searchForm" role="form" action="{{ url('acpayorders') }}" method="get">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6 mt-2">
                                                <label for="order_number">訂單編號:</label>
                                                <input type="number" inputmode="numeric" class="form-control" id="order_number" name="order_number" placeholder="訂單編號" value="{{ isset($order_number) && $order_number ? $order_number : '' }}" autocomplete="off" />
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="pay_time">付款時間區間:</label>
                                                <div class="input-group">
                                                    <input type="datetime" class="form-control datetimepicker" id="pay_time" name="pay_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pay_time) && $pay_time ? $pay_time : '' }}" autocomplete="off" />
                                                    <span class="input-group-addon bg-primary">~</span>
                                                    <input type="datetime" class="form-control datetimepicker" id="pay_time_end" name="pay_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pay_time_end) && $pay_time_end ? $pay_time_end : '' }}" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label class="control-label" for="created_at">建單時間區間:</label>
                                                <div class="input-group">
                                                    <input type="datetime" class="form-control datetimepicker" id="created_at" name="created_at" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($created_at) ? $created_at ?? '' : '' }}" autocomplete="off" />
                                                    <span class="input-group-addon bg-primary">~</span>
                                                    <input type="datetime" class="form-control datetimepicker" id="created_at_end" name="created_at_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($created_at_end) ? $created_at_end ?? '' : '' }}" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label class="control-label" for="shipping_time">出貨時間區間:</label>
                                                <div class="input-group">
                                                    <input type="datetime" class="form-control datetimepicker" id="shipping_time" name="shipping_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($shipping_time) ? $shipping_time ?? '' : '' }}" autocomplete="off" />
                                                    <span class="input-group-addon bg-primary">~</span>
                                                    <input type="datetime" class="form-control datetimepicker" id="shipping_time_end" name="shipping_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($shipping_time_end) ? $shipping_time_end ?? '' : '' }}" autocomplete="off" />
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="status">訂單狀態:</label>
                                                <select class="form-control" id="status" size="6" multiple>
                                                    <option value="-2" {{ isset($status) ? in_array(-2,explode(',',$status)) ? 'selected' : '' : 'selected' }}  class="text-danger">已退貨</option>
                                                    <option value="-1" {{ isset($status) ? in_array(-1,explode(',',$status)) ? 'selected' : '' : 'selected' }}  class="text-danger">取消訂單</option>
                                                    <option value="0"  {{ isset($status) ? in_array(0,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-secondary">訂單成立，尚未付款</option>
                                                    <option value="1"  {{ isset($status) ? in_array(1,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-primary">已付款，等待出貨</option>
                                                    <option value="2"  {{ isset($status) ? in_array(2,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-info">訂單集貨中</option>
                                                    <option value="3"  {{ isset($status) ? in_array(3,explode(',',$status)) ? 'selected' : '' : 'selected' }} class="text-success">訂單已出貨</option>
                                                </select><input type="hidden" value="-2,-1,0,1,2,3" name="status" id="status_hidden" />
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="shipping_method">物流方式: (ctrl+點選可多選)</label>
                                                <select class="form-control" id="shipping_method" size="6" multiple>
                                                    <option value="1" {{ isset($shipping_method) ? in_array(1,explode(',',$shipping_method)) ? 'selected' : '' : 'selected' }}>機場提貨</option>
                                                    <option value="2" {{ isset($shipping_method) ? in_array(2,explode(',',$shipping_method)) ? 'selected' : '' : 'selected' }}>旅店提貨</option>
                                                    <option value="3" {{ isset($shipping_method) ? in_array(3,explode(',',$shipping_method)) ? 'selected' : '' : 'selected' }}>現場提貨</option>
                                                    <option value="4" {{ isset($shipping_method) ? in_array(4,explode(',',$shipping_method)) ? 'selected' : '' : 'selected' }}>寄送海外</option>
                                                    <option value="5" {{ isset($shipping_method) ? in_array(5,explode(',',$shipping_method)) ? 'selected' : '' : 'selected' }}>寄送台灣</option>
                                                    <option value="6" {{ isset($shipping_method) ? in_array(6,explode(',',$shipping_method)) ? 'selected' : '' : 'selected' }}>寄送當地</option>
                                                </select><input type="hidden" value="1,2,3,4,5,6" name="shipping_method" id="shipping_method_hidden" />
                                            </div>
                                        </div>
                                        <div class="row" id="MoreSearch" style="display:none">
                                            <div class="col-6 mt-2">
                                                <label for="vendor_name">商家名稱:</label>
                                                <input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="填寫商家名稱:海邊走走" value="{{ isset($vendor_name) ? $vendor_name ?? '' : '' }}" autocomplete="off" />
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="receiver_name">收件人姓名:</label>
                                                <input type="text" class="form-control" id="receiver_name" name="receiver_name" placeholder="填寫收件人姓名" value="{{ isset($receiver_name) ? $receiver_name ?? '' : '' }}" autocomplete="off" />
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="receiver_tel">收件人電話:</label>
                                                <input type="text" class="form-control" id="receiver_tel" name="receiver_tel" placeholder="填寫收件人電話" value="{{ isset($receiver_tel) ? $receiver_tel ?? '' : '' }}" autocomplete="off" />
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="receiver_address">收件地址:</label>
                                                <input type="text" class="form-control" id="receiver_address" name="receiver_address" placeholder="填寫地址" value="{{ isset($receiver_address) ? $receiver_address ?? '' : '' }}" autocomplete="off" />
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="user_memo">備註搜尋: (有輸入內容則無法勾選有備註)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="memo" name="memo" placeholder="使用者或管理員備註" value="{{ isset($memo) ? $memo ?? '' : '' }}" autocomplete="off" />
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">有備註</span>
                                                    </div>
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <input type="checkbox" id="is_memo" name="is_memo" value="1" {{ isset($is_memo) ? $is_memo == 1 ? 'checked' : '' : '' }}>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="book_shipping_date">預定出貨日:(有輸入日期則無法勾選未預定)</label>
                                                <div class="input-group">
                                                    <input type="datetime" class="form-control datepicker" id="book_shipping_date" name="book_shipping_date" placeholder="格式：2016-06-06" value="{{ isset($book_shipping_date) ? $book_shipping_date ?? '' : '' }}" autocomplete="off" />
                                                    <span class="input-group-addon bg-primary">~</span>
                                                    <input type="datetime" class="form-control datepicker" id="book_shipping_date_end" name="book_shipping_date_end" placeholder="格式：2016-06-06" value="{{ isset($book_shipping_date_end) ? $book_shipping_date_end ?? '' : '' }}" autocomplete="off" />
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">未預定</span>
                                                    </div>
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <input type="checkbox" id="book_shipping_date_not_fill" name="book_shipping_date_not_fill" value="1" {{ isset($book_shipping_date_not_fill) ? $book_shipping_date_not_fill == 1 ? 'checked' : '' : '' }}>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="is_print">已列印註記: (有輸入內容則無法勾選已標記已印)</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="is_print" name="is_print" placeholder="請輸入20180709，或輸入X表示查詢尚無註記" value="{{ isset($is_print) ? $is_print ?? '' : '' }}" autocomplete="off" />
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">已標記已印</span>
                                                    </div>
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <input type="checkbox" id="all_is_print" name="all_is_print" value="ALL" {{ isset($all_is_print) ? $all_is_print == 'ALL' ? 'checked' : '' : ''}}>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <label for="shop">每頁筆數:</label>
                                                <select class="form-control" name="list">
                                                    <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                                    <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                                    <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                                    <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center mb-2">
                                        <button type="button" onclick="formSearch()" class="btn btn-primary">查詢</button>
                                        <button type="reset" class="btn btn-default">清空</button>
                                        <button type="button" class="btn btn-success moreOption">更多選項</button>
                                    </div>
                                </form>
                            </div>
                           @if(count($orders) > 0)
                            <table class="table table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-left" width="25%">訂單資訊 / 訂單狀態 / 物流及金流</th>
                                        <th class="text-left" width="75%">購買人資料 / 購買品項<br></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                    <tr style="border-top:3px #000000 solid;border-bottom:3px #000000 solid;">
                                        <td class="text-left align-top">
                                            <div>
                                                <input type="checkbox" class="chk_order_{{ $order->id }}" name="chk_order" value="{{ $order->id }}">
                                                <a href="{{ route('admin.acpayorders.show', $order->id) }}">
                                                    <span class="text-lg text-bold order_number_{{ $order->id }}">{{ $order->order_number }}</span>
                                                </a>
                                                @if(in_array($menuCode.'MK', explode(',',Auth::user()->power)))
                                                <a href="javascript:" class="badge badge-purple book_shipping_date_{{ $order->id }}" onclick="modify({{ $order->order_number }},{{ $order->id }},'book_shipping_date','{{ $order->book_shipping_date ?? '' }}',this)">{{ $order->book_shipping_date ? '預：'.$order->book_shipping_date : '預定出貨日' }}</a>
                                                @else
                                                @if($order->book_shipping_date)
                                                <span class="badge badge-purple book_shipping_date_{{ $order->id }}">{{ $order->book_shipping_date ? '預：'.$order->book_shipping_date : '預定出貨日' }}</span>
                                                @endif
                                                @endif
                                            </div>
                                            <hr class="mb-1 mt-1">
                                            <div class="row">
                                                <div class="col-6">
                                                    @if($order->created_at)
                                                    <span class="text-sm">建單：{{ $order->created_at }}</span><br>
                                                    @endif
                                                    @if($order->pay_time)
                                                    <span class="text-sm">付款：{{ $order->pay_time }}</span><br>
                                                    @endif
                                                    @if($order->is_invoice == 1)
                                                    <span class="text-sm">發票：{{ $order->invoice_time ?? '' }}</span><br>
                                                    @endif
                                                    @if($order->is_invoice_no)
                                                    <span class="text-sm">發票號碼：{{ $order->is_invoice_no ?? '' }}</span><br>
                                                    @endif
                                                </div>
                                                <div class="col-6">
                                                    <span class="status_{{ $order->id }} text-bold">
                                                    @if($order->deleted_at)
                                                        前台使用者刪除訂單
                                                    @else
                                                        @if($order->status == -2)
                                                        已退貨
                                                        @elseif($order->status == -1)
                                                        取消訂單
                                                        @elseif($order->status == 0)
                                                        訂單成立，等待付款
                                                        @elseif($order->status == 1)
                                                        訂單付款，等待出貨
                                                        @elseif($order->status == 2)
                                                        訂單集貨中
                                                        @elseif($order->status == 3)
                                                        訂單已出貨
                                                        @endif
                                                    @endif
                                                    </span>
                                                    @if($order->deleted_at == '')
                                                    @if(in_array($menuCode.'MK', explode(',',Auth::user()->power)))
                                                    <a href="javascript:" class="admin_memo_{{ $order->id }}" onclick="modify({{ $order->order_number }},{{ $order->id }},'admin_memo','{{ $order->admin_memo ?? '' }}',this)"><i class="fas fa-info-circle"></i>{{ $order->admin_memo ? '('.$order->admin_memo.')' : '' }}</span></a>
                                                    @else
                                                    @if($order->admin_memo)
                                                    <span class="admin_memo_{{ $order->id }} text-primary"><i class="fas fa-info-circle"></i>{{ $order->admin_memo ? '('.$order->admin_memo.')' : '' }}</span></a>
                                                    @endif
                                                    @endif
                                                    <br>
                                                    @endif
                                                    @if($order->status != -1 || $order->status != 0 && $order->deleted_at != '')
                                                    @if(in_array($menuCode.'MK', explode(',',Auth::user()->power)))
                                                        @if(strlen($order->is_print)==8)
                                                        <a href="javascript:" class="forhide badge badge-info is_print_{{ $order->id }}" onclick="modify({{ $order->order_number }},{{ $order->id }},'is_print','{{ $order->is_print ?? '' }}',this)">{{ $order->is_print ? substr($order->is_print,0,4).'/'.substr($order->is_print,4,2).'/'.substr($order->is_print,6,2).' 已列印' : '已列印註記' }}</a>
                                                        @else
                                                        <a href="javascript:" class="forhide badge badge-info is_print_{{ $order->id }}" onclick="modify({{ $order->order_number }},{{ $order->id }},'is_print','{{ $order->is_print ?? '' }}',this)">{{ $order->is_print ? substr($order->is_print,0,2).'/'.substr($order->is_print,2,2).' 已列印' : '已列印註記'}}</a>
                                                        @endif
                                                    @else
                                                        @if($order->is_print)
                                                        @if(strlen($order->is_print)==8)
                                                        <span class="forhide badge badge-info is_print_{{ $order->id }}">{{ $order->is_print ? substr($order->is_print,0,4).'/'.substr($order->is_print,4,2).'/'.substr($order->is_print,6,2).' 已列印' : '已列印註記' }}</span>
                                                        @else
                                                        <span class="forhide badge badge-info is_print_{{ $order->id }}">{{ $order->is_print ? substr($order->is_print,0,2).'/'.substr($order->is_print,2,2).' 已列印' : '已列印註記'}}</span>
                                                        <br>
                                                        @endif
                                                        @endif
                                                        @if($order->shipping_time)
                                                        <span class="forhide badge badge-danger shipping_time_{{ $order->id }}">{{ $order->shipping_time ? str_replace('-','/',substr($order->shipping_time,0,16)).' 已出貨' : '已出貨註記'}}</span>
                                                        @endif
                                                    @endif
                                                    @endif
                                                </div>
                                            </div>
                                            <hr class="mb-1 mt-1">
                                            <div class="row">
                                                <div class="col-6 text-sm">
                                                    物流：<span class="badge badge-primary">{{ $order->shippingMethod->name }}</span>
                                                </div>
                                                @if($order->status != 0)
                                                <div class="col-6 text-sm">
                                                    MPOS：<span class="badge badge-danger">{{ $order->pay_method }} </span>
                                                    @if($order->status == -2)
                                                    <span class="badge badge-purple">{{ number_format($order->amount + $order->refund_amount) }} 元</span>
                                                    @else
                                                    <span class="badge badge-purple">{{ number_format($order->amount) }} 元</span>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                            <hr class="forhide mb-1 mt-1">
                                            <div class="row">
                                                <div class="col-12">
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-left align-top">
                                            <div>
                                                <span class="text-bold">購買人電話：{{ $order->nation.ltrim($order->mobile,0) }}</span>
                                                @if($order->birthday != 0)
                                                <span>生日日期： {{ $order->birthday }}</span>
                                                @endif
                                                @if($order->user_memo)
                                                <span class="text-danger">　註：{!! $order->user_memo !!}</span>
                                                @endif
                                                <br>
                                                @if($order->receiver_name) 收件人：{{ $order->receiver_name }} | @endif
                                                @if($order->receiver_email) E-Mail：{{ $order->receiver_email }} | @endif
                                                @if($order->receiver_keyword) 班機號碼/旅店名稱：<a href="https://www.google.com.tw/search?q={{ $order->receiver_keyword }}" target="_blank">{{ $order->receiver_keyword }}</a> | @endif
                                                @if($order->receiver_key_time) 提貨時間：{{ $order->receiver_key_time }} | @endif
                                                @if($order->room_number) 旅店房號：{{ $order->room_number }} | @endif
                                                @if($order->receiver_zip_code) {{ $order->room_number }} @endif
                                                @if($order->receiver_address) 地址：{{ $order->receiver_province }} {{ $order->receiver_city }} {{ $order->receiver_area }} {{ $order->receiver_address }} |@endif
                                                @if($order->shipping_number) 物流單號：{{ $order->shipping_number }}@endif
                                            </div>
                                            <table class="table mb-0 table-sm">
                                                <thead class="table-info">
                                                    <th width="20%" class="text-left align-middle text-sm">商家</th>
                                                    <th width="30%" class="text-left align-middle text-sm">品名</th>
                                                    <th width="5%" class="text-center align-middle text-sm">免運?</th>
                                                    <th width="5%" class="text-right align-middle text-sm">基本費</th>
                                                    <th width="5%" class="text-right align-middle text-sm">每箱運費</th>
                                                    <th width="5%" class="text-right align-middle text-sm">箱數</th>
                                                    <th width="5%" class="text-right align-middle text-sm">總價</th>
                                                </thead>
                                                <tbody>
                                                    <form id="itemsform_order_{{ $order->id }}" method="POST">
                                                        <tr>
                                                            <td class="text-left align-middle text-sm order_item_modify_{{ $order->id }}">{{ $order->vendor_name }}</td>
                                                            <td class="text-left align-middle text-sm">
                                                                {{ $order->name }} - 線下購物
                                                            </td>
                                                            <td class="text-center align-middle text-sm">{{ $order->free_shipping == 1 ? '是' : '否' }}</td>
                                                            <td class="text-right align-middle text-sm">{{ $order->base_shipping_fee }}</td>
                                                            <td class="text-right align-middle text-sm">{{ $order->each_box_shipping_fee }}</td>
                                                            <td class="text-right align-middle text-sm">{{ $order->boxes }}</td>
                                                            <td class="text-right align-middle text-sm">{{ number_format($order->amount) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" class="text-right align-middle text-sm text-primary text-bold">
                                                                @if($order->free_shipping == 1)
                                                                <span>運費 0</span>
                                                                @else
                                                                <span>運費 {{ number_format($order->base_shipping_fee + $order->boxes * $order->each_box_shipping_fee) }}</span>
                                                                @endif
                                                            </td>
                                                            <td colspan="1" class="text-right align-middle text-sm text-primary text-bold">商品總計</td>
                                                            <td class="text-right align-middle text-sm text-primary text-bold">{{ number_format($order->boxes) }}</td>
                                                            <td class="text-right align-middle text-sm text-primary text-bold">{{ number_format($order->amount) }}</td>
                                                        </tr>
                                                    </form>
                                                </tbody>
                                            </table>
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
                    </div>
                </div>
            </div>
        </div>
    </section>
    <form id="export" action="{{ url('acpayorders/export') }}" method="POST">
        @csrf
    </form>
</div>
@endsection

@section('modal')
{{-- 註記 Modal --}}
<div id="myModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 50%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-10 offset-1" id="myform"></div>
                <div class="form-group form-group-sm" id="myrecord">
                    <label for="message-text" class="col-form-label">修改紀錄</label>
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th width="10%">#</th>
                                        <th width="25%">新增時間</th>
                                        <th width="20%">註記者</th>
                                        <th width="20%">欄位名稱</th>
                                        <th width="25%">紀錄內容</th>
                                    </tr>
                                </thead>
                                <tbody id="record"></tbody>
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
        $('[data-toggle="popover"]').popover({
            html: true,
            sanitize: false,
        });

        // date time picker 設定
        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        $('.datepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $('.timepicker').timepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $('#memo').keydown(function(){
            $('input[name="is_memo"]').prop('checked',false);
        });

        $('#is_memo').click(function(){
            if($('input[name="is_memo"]:checked').length > 0){
                $('#memo').val('');
            };
        });

        $('#is_print').keydown(function(){
            $('input[name="all_is_print"]').prop('checked',false);
        });

        $('#all_is_print').click(function(){
            if($('input[name="all_is_print"]:checked').length > 0){
                $('#is_print').val('');
            };
        });

        $('#book_shipping_date').change(function(){
            $('input[name="book_shipping_date_not_fill"]').prop('checked',false);
        });

        $('#book_shipping_date_end').change(function(){
            $('input[name="book_shipping_date_not_fill"]').prop('checked',false);
        });

        $('#book_shipping_date_not_fill').click(function(){
            if($('input[name="book_shipping_date_not_fill"]:checked').length > 0){
                $('#book_shipping_date').val('');
                $('#book_shipping_date_end').val('');
            };
        });

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

        $('.chkallitem').change(function(){
            let order_id = $(this).val();
            if($(this).prop("checked") == true){
                $('.order_item_id_'+order_id).prop("checked",true);
            }else{
                $('.order_item_id_'+order_id).prop("checked",false);
            }
        });

        var num_all = $('input[name="chk_order"]').length;
        var num = $('input[name="chk_order"]:checked').length;
        $("#chkallorder_text").text("全選("+num+"/"+num_all+")");

        $('#chkallorder').change(function(){
            if($("#chkallorder").prop("checked") == true){
                $('input[name="chk_order"]').prop("checked",true);
                $("#multiFunc>button").attr("disabled",false);
            }else{
                $('input[name="chk_order"]').prop("checked",false);
                $("#multiFunc>button").attr("disabled",true);
            }
            var num_all = $('input[name="chk_order"]').length;
            var num = $('input[name="chk_order"]:checked').length;
            $("#chkallorder_text").text("全選("+num+"/"+num_all+")");
            if(num > 0){
                $('#multiFunc').show();
                $('#order_multi').html('隱藏多筆處理');
            }else{
                $('#multiFunc').hide()
                $('#order_multi').html('多筆處理');
            }
        });

        $('input[name="chk_order"]').change(function(){
            var num_all = $('input[name="chk_order"]').length;
            var num = $('input[name="chk_order"]:checked').length;
            num_all != num ? $("#check_all").prop("checked",false) : $("#check_all").prop("checked",true);
            num > 0 ?  $("#multiFunc>button").attr("disabled",false) : $("#multiFunc>button").attr("disabled",true);
            $("#chkallorder_text").text("全選("+num+"/"+num_all+")");
            if(num > 0){
                $('#multiFunc').show();
                $('#order_multi').html('隱藏多筆處理');
            }else{
                $('#multiFunc').hide()
                $('#order_multi').html('多筆處理');
            }
        });

        $('.orderSelect').click(function (e) {
            let orderids = $('input[name="chk_order"]:checked').serializeArray().map( item => item.value );
            orderids.length > 0 ? modify('',orderids,$(this).val(),'','') : alert('尚未選擇訂單，請重新選擇');
        });

        $('.orderExport').click(function (e){
            if($(this).val() == 'export_ecan'){
                let form = $('#export');
                let orderids = $('input[name="chk_order"]:checked').serializeArray().map( item => item.value );
                let cate = $(this).val().split('_')[0];
                let type = $(this).val().split('_')[1];
                let filename = $(this).html();
                for(let i=0;i<orderids.length;i++){
                    let orderId = $('<input type="hidden" class="formappend" name="ids['+i+']">').val(orderids[i]);
                    form.append(orderId);
                }
                let export_cate = $('<input type="hidden" class="formappend" name="cate" value="'+cate+'">');
                let export_type = $('<input type="hidden" class="formappend" name="type" value="'+type+'">');
                form.append(export_cate);
                form.append(export_type);
                form.append( $('<input type="hidden" class="formappend" name="filename" value="'+filename+'">') );
                if(confirm("是否要自動上傳宅配通FTP？ 將會同時更改狀態為【已出貨】並發出簡訊。 \n 注意! 只有已付款待出貨訂單才會執行，按取消則將會匯出所有選擇的訂單。")){
                    form.append( $('<input type="hidden" class="formappend" name="upload" value="yes">') );
                }
                form.submit();
                $('.formappend').remove();
            }
        });

        $('#order_multi').click(function (e) {
            $('#multiFunc').toggle();
            $(this).html() == '隱藏多筆處理' ? $(this).html('多筆處理') : $(this).html('隱藏多筆處理');
        });

        $('#hidemodify').click(function (e) {
            $('.forhide').toggle('display');
            $(this).html() == '隱藏所有註記' ? $(this).html('顯示所有註記') : $(this).html('隱藏所有註記');
        });

        $('#showForm').click(function(){
            let text = $('#showForm').html();
            $('#orderSearchForm').toggle();
            text == '使用欄位查詢' ? $('#showForm').html('隱藏欄位查詢') : $('#showForm').html('使用欄位查詢');
        });

        $('.moreOption').click(function(){
            $('#MoreSearch').toggle();
            $(this).html() == '更多選項' ? $(this).html('隱藏更多選項') : $(this).html('更多選項');
        });

        $('input[type=file]').change(function(x) {
            let name = this.name;
            let file = x.currentTarget.files;
            let filename = file[0].name; //不檢查檔案直接找出檔名
            if (file.length >= 1) {
                if (filename) {
                    $('label[for=' + name + ']').html(filename);
                } else {
                    $(this).val('');
                    $('label[for=' + name + ']').html('瀏覽選擇EXCEL檔案');
                }
            } else {
                $(this).val('');
                $('label[for=' + name + ']').html('瀏覽選擇EXCEL檔案');
            }
        });
    })(jQuery);

    function formSearch(){
        let sel="";
        $("#shipping_method>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#shipping_method_hidden").val(sel.substring(1));

        sel = "";
        $("#status>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#status_hidden").val(sel.substring(1));

        $("#searchForm").submit();
    }

    function modify(order_number,order_id,column_name,column_value,e,item_id){
        let token = '{{ csrf_token() }}';
        let itemIds = [];
        let id = [];
        let datepicker = '';
        let dateFormat = 'yy-mm-dd';
        let timeFormat = 'HH:mm:ss';
        let note = '<div><span class="text-primary">清空內容為取消註記</span>，<span class="text-danger">訂單狀態為【已付款待出貨 或 集貨中】才會被變更喔(防呆機制)</span></div>';
        !Array.isArray(order_id)? id[0] = order_id : id = order_id;
        $('#myform').html('');
        $('#record').html('');
        $('#myrecord').addClass('d-none');
        if(column_name == 'book_shipping_date'){
            title = '預定出貨日';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            id.length >=2 ? '' : $(e).html() == title ? column_value = '' : column_value = column_value.replace('預：','');
            datepicker = '<div id="data_datepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：2018-07-09';
        }else if(column_name == 'buy_memo'){
            title = '採購日註記';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            id.length >=2 ? '' : $(e).html() == title ? column_value = '' : column_value = column_value.replace('採購日：','');
            datepicker = '<div id="data_datepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：2018-07-09';
            note = '<div><span class="text-primary">清空內容為取消註記</span>';
        }else if(column_name == 'billOfLoading_memo'){
            title = '提單日註記';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            id.length >=2 ? '' : $(e).html() == title ? column_value = '' : column_value = column_value.replace('提單日：','');
            datepicker = '<div id="data_datepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：2018-07-09';
            note = '<div><span class="text-primary">清空內容為取消註記</span>';
        }else if(column_name == 'special_memo'){
            title = '特殊註記';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            id.length >=2 ? '' : $(e).html() == title ? column_value = '' : column_value = column_value.replace('特註：','');
            datepicker = '<div id="data_datepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：2018-07-09';
            note = '<div><span class="text-primary">清空內容為取消註記</span>';
        }else if(column_name == 'new_shipping_memo'){
            title = '物流日註記';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            id.length >=2 ? '' : $(e).html() == title ? column_value = '' : column_value = column_value.replace('物流日：','');
            datepicker = '<div id="data_datepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：2018-07-09';
            note = '<div><span class="text-primary">清空內容為取消註記</span>';
        }else if(column_name == 'is_call'){
            title = '已叫貨註記';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            id.length >=2 ? '' : $(e).html() == title ? column_value = '' : column_value = column_value.replace('已叫貨','').replace('/','').replace('/','');
            dateFormat = 'yymmdd';
            datepicker = '<div id="data_datepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：20180709';
        }else if(column_name == 'is_print'){
            title = '已列印註記';
            dateFormat = 'yymmdd';
            datepicker = '<div id="data_datepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：20180709';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            id.length >=2 ? '' : $(e).html() == title ? column_value = '' : column_value = column_value.replace('已列印','').replace('/','').replace('/','');
        }else if(column_name == 'receiver_key_time'){
            title = '提貨日註記';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            id.length >=2 ? '' : $(e).html() == title ? column_value = '' : column_value = column_value.replace('提貨日：','');
            dateFormat = 'yy-mm-dd';
            datepicker = '<div id="data_datetimepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：2018-07-09 15:30:00';
        }else if(column_name == 'shipping_time'){
            title = '已出貨日註記';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            id.length >=2 ? '' : $(e).html() == title ? column_value = '' : column_value = column_value.replace('已出貨','');
            dateFormat = 'yy-mm-dd';
            datepicker = '<div id="data_datetimepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：2018-07-09 15:30:00';
        }else if(column_name == 'admin_memo'){
            title = '管理者註記';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            placeholder = '請輸入'+title+'內容';
            note = '<div><span class="text-primary">清空內容為取消註記</span></div>';
        }else if(column_name == 'cancel'){
            title = '取消訂單';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，'+title;
            placeholder = '請輸入取消訂單原因，例如：客戶要求取消';
            note = '<div><span class="text-danger">訂單狀態為【已付款待出貨 或 集貨中】才會被變更喔(防呆機制)</span></div>';
        }else if(column_name == 'order_item_modify'){
            title = '商品叫貨註記';
            orderNum = '';
            for(i=0;i<id.length;i++){
                itemIds[i] = $('.order_item_id_'+id[i]).serializeArray().map( item => item.value );
                if(itemIds[i].length == 0){
                    orderNum = orderNum + '訂單編號：' + $('.order_number_'+id[i]).html() + '\n';
                }
            }
            if(orderNum){
                alert('下面訂單未選擇任何商品，無法繼續執行！\n'+orderNum);
                return;
            }
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，'+title;
            dateFormat = 'yymmdd';
            datepicker = '<div id="data_datepicker" style="display:none"></div>';
            placeholder = '請輸入'+title+'日期，格式：20180709';
            note = '';
            if(id.length == 1){
                if(itemIds[0].length < 1){
                    alert('請先選擇要註記的商品');
                    return;
                }
            }
        }else if(column_name == 'item_is_call_clear'){
            if(confirm('請確認是否取消此商品叫貨註記')){
                title = '商品叫貨註記';
                itemIds[0] = [item_id];
                id[0] = [order_id];
                modifysend(id,column_name,'',itemIds);
            }
            return
        }
        html = '<div class="input-group"><span class="input-group-text">輸入內容</span><input type="text" class="form-control col-12" id="data" name="data" value="'+column_value+'" placeholder="'+placeholder+'" autocomplete="off"><button type="button" class="btn btn-primary modifysend">確定</button><button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">取消</span></button></div>'+note+datepicker;
        if( id.length == 1 ){
            if(column_name != 'order_item_modify'){
                $('#myrecord').removeClass('d-none');
                $.ajax({
                    type: "post",
                    url: 'acpayorders/getlog',
                    data: { order_id: id, column_name: column_name , _token: token },
                    success: function(data) {
                        let record = '';
                        if(data.length > 0){
                            for(let i=0; i<data.length; i++){
                                let dateTime = new Date(data[i]['created_at']);
                                let name = data[i]['name'];
                                let log = data[i]['log'];
                                let col_name = data[i]['column_name'];
                                let record = '<tr class="record"><td class="align-middle">'+(data.length - i)+'</td><td class="align-middle">'+dateTime+'</td><td class="align-middle">'+name+'</td><td class="text-left align-middle">'+col_name+'</td><td class="align-middle">'+log+'</td></tr>';
                                $('#record').append(record);
                            }
                        }
                    }
                });
            }
        }

        $('#ModalLabel').html(label);
        $('#myform').html(html);
        $('#myModal').modal('show');

        $('#data').click(function(){
            $('#data_datepicker').toggle();
            $('#data_datetimepicker').toggle();
        });

        $('#data_datepicker').datepicker({
            dateFormat: dateFormat,
            onSelect: function (date) {
                $('input[name=data]').val(date);
                $('#data_datepicker').toggle();
            }
        });

        $('#data_datetimepicker').datetimepicker({
            timeFormat: timeFormat,
            dateFormat: dateFormat,
            onSelect: function (date) {
                $('input[name=data]').val(date);
            }
        });

        $('.modifysend').click(function () {
            let column_data = $('#data').val();
            column_data ? column_data = column_data : column_data = null;
            if(column_name == 'cancel'){
                if(confirm('請確認是否真的要取消該訂單？')){
                    modifysend(id,column_name,column_data,itemIds)
                }else{
                    $('#myModal').modal('hide');
                }
            }else{
                modifysend(id,column_name,column_data,itemIds);
            }
        });
    }

    function modifysend(id,column_name,column_data,itemIds){
        let token = '{{ csrf_token() }}';
        $.ajax({
            type: "post",
            url: 'acpayorders/modify',
            data: { id: id, column_name: column_name, column_data: column_data, item_ids: itemIds, _token: token },
            success: function(orders) {
                if(orders){
                    for(i=0;i<orders.length;i++){
                        target = '.'+column_name+'_'+orders[i]['id'];
                        value = orders[i][column_name];
                        if(column_name == 'book_shipping_date'){
                            nullText = '預定出貨日期';
                            text = '預：'+value;
                        }else if(column_name == 'buy_memo'){
                            nullText = '採購日註記';
                            text = '採購日：'+value;
                        }else if(column_name == 'new_shipping_memo'){
                            nullText = '物流日註記';
                            text = '物流日：'+value;
                        }else if(column_name == 'billOfLoading_memo'){
                            nullText = '提單日註記';
                            text = '提單日：'+value;
                        }else if(column_name == 'special_memo'){
                            nullText = '特殊註記';
                            text = '特註：'+value;
                        }else if(column_name == 'is_call'){
                            nullText = '已叫貨註記';
                            text = value.substring(0,4)+'/'+value.substring(4,6)+'/'+value.substring(6,8)+' 已叫貨';
                        }else if(column_name == 'is_print'){
                            nullText = '已列印註記';
                            text = value.substring(0,4)+'/'+value.substring(4,6)+'/'+value.substring(6,8)+' 已列印';
                        }else if(column_name == 'receiver_key_time'){
                            nullText = '提貨日註記';
                            text = '提貨日：'+value.replace('-','/').replace('-','/').substring(0,16);
                        }else if(column_name == 'shipping_time'){
                            nullText = '出貨日註記';
                            text = value.replace('-','/').replace('-','/').substring(0,16)+' 已出貨';
                        }else if(column_name == 'admin_memo'){
                            nullText = '<span><i class="fas fa-info-circle"></i></span>';
                            text = '<span><i class="fas fa-info-circle"></i>('+value+')</span>';
                        }
                        if(column_name == 'cancel'){
                            target = '.admin_memo_'+orders[i]['id'];
                            value = orders[i]['admin_memo'];
                            nullText = '<span><i class="fas fa-info-circle"></i></span>';
                            text = '<span><i class="fas fa-info-circle"></i>('+value+')</span>';
                            target2 = '.status_'+orders[i]['id'];
                            text2 = '後台取消訂單';
                            $(target).attr('onclick','modify('+orders[i]['order_number']+','+orders[i]['id']+',\''+target+'\',\''+value+'\',this)');
                            value ? $(target).html(text) : $(target).html(nullText);
                            $(target2).html(text2);
                        }else if(column_name == 'order_item_modify' || column_name == 'item_is_call_clear'){
                            items = orders[i]['items'];
                            for(j=0; j<items.length; j++){
                                value = items[j]['is_call'];
                                target = '.order_item_modify_'+items[j]['id'];
                                if(value){
                                    text = value.substring(0,4)+'/'+value.substring(4,6)+'/'+value.substring(6,8)+'叫貨';
                                    html = '<a href="javascript:" class="forhide badge badge-danger item_is_call_'+items[j]['id']+'" onclick="modify('+orders[i]['order_number']+','+orders[i]['id']+',\'item_is_call_clear\',\''+value+'\',this,'+items[j]['id']+')"><span>'+text+'</span></a> '+items[j]['vendor_name'];
                                }else{
                                    html = '<input class="order_item_id_'+orders[i]['id']+'" type="checkbox" name="order_item_id" value="'+items[j]['id']+'"> '+items[j]['vendor_name'];
                                }
                                $(target).html(html);
                            }
                            $('.chk_all_item_'+orders[i]['id']).prop('checked',false);
                            $('.chk_order_'+orders[i]['id']).prop('checked',false);
                            $('#multiFunc').hide()
                            $('#order_multi').html('多筆處理');
                            $("#multiFunc>button").attr("disabled",true);
                            var num_all = $('input[name="chk_order"]').length;
                            var num = $('input[name="chk_order"]:checked').length;
                            $("#chkallorder_text").text("全選("+num+"/"+num_all+")");
                        }else{
                            $(target).attr('onclick','modify('+orders[i]['order_number']+','+orders[i]['id']+',\''+column_name+'\',\''+value+'\',this)');
                            value ? $(target).html(text) : $(target).html(nullText);
                        }
                    }
                    $('#myModal').modal('hide');
                }
            }
        });
    }
</script>
@endsection
