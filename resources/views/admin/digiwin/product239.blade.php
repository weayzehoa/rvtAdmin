@extends('admin.layouts.master')

@section('title', '閃購專區對應貨號')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>閃購專區對應貨號</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('digiwin/product239') }}">閃購專區對應貨號</a></li>
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
                                <button id="showForm" class="btn btn-sm btn-success mr-2" title="使用欄位查詢">使用欄位查詢</button>
                            </div>
                            <div class="float-right">
                                <div class="input-group input-group-sm align-middle align-items-middle">
                                    <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($products->total()) ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <form id="myForm" action="{{ url('digiwin/product293') }}" method="get">
                            <div id="searchForm" class="card card-primary" style="display: none">
                                <div class="card-body">
                                    <div class="row col-8 offset-2">
                                        <div class="col-6 mt-2">
                                            <label for="status">商品狀態: (ctrl+點選可多選)</label>
                                            <select class="form-control" id="status" size="6" multiple>
                                                <option value="1" {{ isset($status) ? in_array(1,explode(',',$status)) ? 'selected' : '' : 'selected' }}>上架中</option>
                                                <option value="2" {{ isset($status) ? in_array(2,explode(',',$status)) ? 'selected' : '' : 'selected' }}>待審核</option>
                                                <option value="-1" {{ isset($status) ? in_array(-1,explode(',',$status)) ? 'selected' : '' : 'selected' }}>未送審(草稿)</option>
                                                <option value="-2" {{ isset($status) ? in_array(-2,explode(',',$status)) ? 'selected' : '' : 'selected' }}>審核不通過</option>
                                                <option value="-3" {{ isset($status) ? in_array(-3,explode(',',$status)) ? 'selected' : '' : 'selected' }}>暫停銷售</option>
                                                <option value="-9" {{ isset($status) ? in_array(-9,explode(',',$status)) ? 'selected' : '' : 'selected' }}>已下架</option>
                                            </select><input type="hidden" value="-9,-3,-2,-1,1,2" name="status" id="status_hidden" />
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="shipping_method">物流方式: (ctrl+點選可多選)</label>
                                            <select class="form-control" id="shipping_methods" size="6" multiple>
                                                <option value="1" {{ isset($shipping_methods) ? in_array(1,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>機場提貨</option>
                                                <option value="2" {{ isset($shipping_methods) ? in_array(2,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>旅店提貨</option>
                                                <option value="3" {{ isset($shipping_methods) ? in_array(3,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>現場提貨</option>
                                                <option value="4" {{ isset($shipping_methods) ? in_array(4,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>寄送海外</option>
                                                <option value="5" {{ isset($shipping_methods) ? in_array(5,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>寄送台灣</option>
                                                <option value="6" {{ isset($shipping_methods) ? in_array(6,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>寄送當地</option>
                                            </select><input type="hidden" value="1,2,3,4,5,6" name="shipping_methods" id="shipping_methods_hidden" />
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">產品名稱:</label>
                                            <input type="text" class="form-control" id="product_name" name="product_name" value="{{ isset($product_name) && $product_name ? $product_name : '' }}" placeholder="產品名稱" autocomplete="off">
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="pay_time">上架時間區間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="created_at" name="created_at" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($created_at) && $created_at ? $created_at : '' }}" autocomplete="off">
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="created_at_end" name="created_at_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($created_at_end) && $created_at_end ? $created_at_end : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="pay_time">送審通過時間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="pass_time" name="pass_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pass_time) && $pass_time ? $pass_time : '' }}" autocomplete="off">
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="pass_time_end" name="pass_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pass_time_end) && $pass_time_end ? $pass_time_end : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="pay_time">單價修改區間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="price_update_time" name="price_update_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($price_update_time) && $price_update_time ? $price_update_time : '' }}" autocomplete="off">
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="price_update_time_end" name="price_update_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($price_update_time_end) && $price_update_time_end ? $price_update_time_end : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="pay_time">保存天數修改區間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="storage_life_update_time" name="storage_life_update_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($storage_life_update_time) && $storage_life_update_time ? $storage_life_update_time : '' }}" autocomplete="off">
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="storage_life_update_time_end" name="storage_life_update_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($storage_life_update_time_end) && $storage_life_update_time_end ? $storage_life_update_time_end : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="pay_time">國際條碼修改區間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="gtin13_update_time" name="gtin13_update_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($gtin13_update_time) && $gtin13_update_time ? $gtin13_update_time : '' }}" autocomplete="off">
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="gtin13_update_time_end" name="gtin13_update_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($gtin13_update_time_end) && $gtin13_update_time_end ? $gtin13_update_time_end : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">產品貨號:</label>
                                            <input type="text" class="form-control" id="sku" name="sku" value="{{ isset($sku) && $sku ? $sku : '' }}" placeholder="產品貨號，EC or BOM" autocomplete="off">
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">鼎新品號:</label>
                                            <input type="text" class="form-control" id="digiwin_no" name="digiwin_no" value="{{ isset($digiwin_no) && $digiwin_no ? $digiwin_no : '' }}" placeholder="鼎新品號" autocomplete="off">
                                        </div>
                                        <div class="col-3 mt-2 text-center">
                                            <label class="mr-2 mt-2">　</label>
                                            <div class="form-group clearfix">
                                                <label class="mr-2">低於安全庫存:</label>
                                                <div class="icheck-green d-inline mr-2">
                                                    <input type="checkbox" id="low_quantity" name="low_quantity" value="yes" {{ isset($low_quantity) && $low_quantity ? 'checked' : '' }}>
                                                    <label for="low_quantity">勾我</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3 mt-2 text-center">
                                            <label class="mr-2 mt-2">　</label>
                                            <div class="form-group clearfix">
                                                <label class="mr-2">庫存小於等於0:</label>
                                                <div class="icheck-green d-inline mr-2">
                                                    <input type="checkbox" id="zero_quantity" name="zero_quantity" value="yes" {{ isset($zero_quantity) && $zero_quantity ? 'checked' : '' }}>
                                                    <label for="zero_quantity">勾我</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2 ">
                                            <label class="mr-2">每頁筆數</label>
                                            <select class="form-control" name="list">
                                                <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                                <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                                <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                                <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                            </select>
                                        </div>
                                        <div class="col-12 text-center mt-2">
                                            <button type="button" onclick="formSearch()" class="btn btn-primary">查詢</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="card-body">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left text-sm" width="35%">商家<br>品名<br>內容量/狀態</th>
                                        <th class="text-left text-sm" width="10%">上架時間<br>
                                            更新時間<br>
                                            送審通過時間</th>
                                        <th class="text-left text-sm" width="5%">分類</th>
                                        <th class="text-center text-sm" width="5%">款式</th>
                                        <th class="text-right text-sm" width="5%">單價</th>
                                        <th class="text-right text-sm" width="40%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    <tr>
                                        <td class="text-left align-middle text-sm text-warp">
                                            @if(in_array('M2S1',explode(',',Auth::user()->power)))
                                            @if($product->vendor_id > 1)
                                            <a href="{{ route('admin.vendors.show', $product->vendor->id ) }}" class="text-danger">{{ $product->vendor->name }}</a>
                                            @endif
                                            @else
                                            <span class="text-secondary">{{ $product->vendor->name }}</span>
                                            @endif
                                            <br>
                                            @if(in_array('M4S1',explode(',',Auth::user()->power)))
                                            <a href="{{ route('admin.products.show', $product->id ) }}">{{ $product->name }}</a>
                                            @else
                                            <span>{{ $product->name }}</span>
                                            @endif
                                            <br>
                                            <span class="badge badge-info">{{ $product->serving_size }}</span>
                                            @if($product->status == 1)
                                            <span class="right badge badge-success">上架中</span>
                                            @elseif($product->status == 2)
                                            <span class="right badge badge-purple">待審核</span>
                                            @elseif($product->status == -9)
                                            <span class="right badge badge-secondary">已下架</span>
                                            @elseif($product->status == -3)
                                            @if(!empty($product->pause_reason))
                                            <span class="right badge badge-warning">商家暫停銷售</span>
                                            @else
                                            <span class="right badge badge-warning">iCarry暫停銷售</span>
                                            @endif
                                            @elseif($product->status == -2)
                                            <span class="right badge badge-danger">審核不通過</span>
                                            @elseif($product->status == -1)
                                            <span class="right badge badge-info">未送審(草稿)</span>
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            {{ substr($product->created_at,2,-3) }}<br>
                                            {{ substr($product->updated_at,2,-3) }}<br>
                                            {{ substr($product->pass_time,2,-3) }}
                                        </td>
                                        <td class="text-left align-middle text-sm">{{ $product->category->name ?? '' }}</td>
                                        <td class="text-center align-middle text-sm">
                                            @if($product->model_type == 1)
                                                單一款式
                                            @elseif($product->model_type == 2)
                                                多種款式
                                            @else
                                                組合商品
                                            @endif
                                        </td>
                                        <td class="text-right align-middle"><span class="text-primary"><b>{{ number_format($product->price) }}</b></span></td>
                                        <td class="text-left align-middle text-sm">
                                            <table width="100%" class="table-hover table-sm">
                                                <thead>
                                                    <tr>
                                                        <th class="text-left" width="30%">icarry貨號</th>
                                                        <th class="text-left" width="30%">鼎新貨號</th>
                                                        <th class="text-left" width="30%">對應原始商品鼎新貨號</th>
                                                        <th class="text-left" width="10%">修改</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($product->models as $model)
                                                    <tr>
                                                        <td class="text-left">{{ $model->sku }}</td>
                                                        <td class="text-left">{{ $model->digiwin_no }}</td>
                                                        <form action="{{ route('admin.productModel.update', $model->id) }}" method="post">
                                                            <input type="hidden" name="_method" value="PATCH">
                                                            @csrf
                                                            <td class="text-left">
                                                                <input type="text" class="form-control form-control-sm" name="origin_digiwin_no" value="{{ $model->origin_digiwin_no ? $model->origin_digiwin_no : null }}" placeholder="{{ $model->origin_digiwin_no ?? '尚未填寫' }}">
                                                            </td>
                                                            <td class="text-left">
                                                                <button type="submit" class="btn btn-sm btn-primary">修改</button>
                                                            </td>
                                                        </form>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($products->total()) ?? 0 }}</span>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $products->appends($appends)->render() }}
                                @else
                                {{ $products->render() }}
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

@section('modal')
{{-- 庫存調整 Modal --}}
<div id="myModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group form-group-sm">
                    <form id="myForm">
                    <input type="hidden" name="product_model_id">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">貨號</span>
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text" name="sku"></span>
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text">庫存</span>
                        </div>
                        <div class="input-group-prepend" style="width:12%">
                            <input type="number" class="form-control" name="quantity">
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text">安全庫存</span>
                        </div>
                        <div class="input-group-prepend" style="width:12%">
                            <input type="number" class="form-control" name="safe_quantity">
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text">調整原因</span>
                        </div>
                        <div class="input-group-prepend" style="width:30%" >
                            <input type="text" class="form-control" name="reason" placeholder="非必填">
                        </div>
                        <div class="input-group-append">
                            <span id="stockmodify" class="btn btn-sm btn-danger">更新</span>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="form-group form-group-sm">
                    <label for="message-text" class="col-form-label">修改紀錄</label>
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">修改前庫存</th>
                                            <th width="15%">增減數量</th>
                                            <th width="15%">修改後庫存</th>
                                            <th width="30%">原因理由</th>
                                            <th width="20%">庫存調整時間</th>
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
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
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

        $('.select2bs4').select2({
            theme: 'bootstrap4'
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

        $('#showForm').click(function(){
            let text = $('#showForm').html();
            $('#searchForm').toggle();
            text == '使用欄位查詢' ? $('#showForm').html('隱藏欄位查詢') : $('#showForm').html('使用欄位查詢');
        });

        $('#stockmodify').click(function(){
            let product_model_id = $('input[name=product_model_id]').val();
            let quantity = $('input[name=quantity]').val();
            let safe_quantity = $('input[name=safe_quantity]').val();
            let reason = $('input[name=reason]').val();
            let token = '{{ csrf_token() }}';
            $.ajax({
                type: "post",
                url: 'products/stockmodify',
                data: { product_model_id: product_model_id, quantity: quantity, safe_quantity: safe_quantity, reason: reason, _token: token },
                success: function(data) {
                    if(data['productQtyRecord']){
                        let x = $('.record').length;
                        let dateTime = new Date(data['productQtyRecord']['created_at']);
                        let timestamp = new Date(data['productQtyRecord']['created_at']).getTime();
                        let count = data['productQtyRecord']['after_quantity'] - data['productQtyRecord']['before_quantity'];
                        let record = '<tr class="record"><td class="align-middle">'+(x+1)+'</td><td class="align-middle">'+data['productQtyRecord']['before_quantity']+'</td><td class="align-middle">'+count+'</td><td class="align-middle">'+data['productQtyRecord']['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord']['reason']+'</td><td class="align-middle">'+dateTime+'</td></tr>';
                        $('#record').prepend(record);
                        $('#quantity_'+product_model_id).html(data['productQtyRecord']['after_quantity']);
                    }else{
                        alert('新舊庫存未改變');
                    }
                }
            });
        });
    })(jQuery);

    function formSearch(){
        let sel="";
        $("#shipping_methods>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#shipping_methods_hidden").val(sel.substring(1));

        sel = "";
        $("#status>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#status_hidden").val(sel.substring(1));

        if($('#created_at').val() && $('#created_at_end').val() && ($('#created_at').val() > $('#created_at_end').val())){
            alert('上架時間區間的開始時間不能小於結束時間!');
        }else if($('#pass_time').val() && $('#pass_time_end').val() && ($('#pass_time').val() > $('#pass_time_end').val())){
            alert('送審通過區間的開始時間不能小於結束時間!');
        }else if($('#price_update_time').val() && $('#price_update_time_end').val() && ($('#price_update_time').val() > $('#price_update_time_end').val())){
            alert('單價修改區間的開始時間不能小於結束時間!');
        }else if($('#storage_life_update_time').val() && $('#storage_life_update_time_end').val() && ($('#storage_life_update_time').val() > $('#storage_life_update_time_end').val())){
            alert('保存天數修改區間的開始時間不能小於結束時間!');
        }else if($('#gtin13_update_time').val() && $('#gtin13_update_time_end').val() && ($('#gtin13_update_time').val() > $('#gtin13_update_time_end').val())){
            alert('國際條碼修改區間的開始時間不能小於結束時間!');
        }else{
            $("#myForm").submit();
        }
    }

    function getstockrecord(id){
        $('#result').html(''); //開啟modal前清除搜尋資料
        $('#myModal').modal('show');
        let token = '{{ csrf_token() }}';
        $.ajax({
            type: "post",
            url: 'products/getstockrecord',
            data: { id: id, _token: token },
            success: function(data) {
                let type = data['product']['model_type'];
                let spec = '';
                let name = '';
                if(type == 1){
                    spec = '單一規格';
                    name = data['product']['name'];
                }else if(type == 2){
                    spec = '多款規格';
                    name = data['productModel']['name'];
                }else if(type == 3){
                    spec = '組合商品';
                    name = data['productModel']['name'];
                }
                $('#ModalLabel').html('<span class="text-primary">'+name +'</span> > <span class="text-danger">'+spec+'</span> > 商品庫存調整');
                let record = '';
                for(i=0;i<data['productQtyRecord'].length;i++){
                    let x = data['productQtyRecord'].length - i;
                    let dateTime = new Date(data['productQtyRecord'][i]['created_at']);
                    let timestamp = new Date(data['productQtyRecord'][i]['created_at']).getTime();
                    count = data['productQtyRecord'][i]['after_quantity'] - data['productQtyRecord'][i]['before_quantity'];
                    record = record + '<tr class="record"><td class="align-middle">'+(x)+'</td><td class="align-middle">'+data['productQtyRecord'][i]['before_quantity']+'</td><td class="align-middle">'+count+'</td><td class="align-middle">'+data['productQtyRecord'][i]['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord'][i]['reason']+'</td><td class="align-middle">'+dateTime+'</td></tr>';
                }
                $('#record').html(record);
                $('input[name=product_model_id]').val(data['productModel']['id']);
                $('input[name=quantity]').val(data['productModel']['quantity']);
                $('input[name=safe_quantity]').val(data['productModel']['safe_quantity']);
                $('span[name=sku]').html(data['productModel']['sku']);
            }
        });
    }

</script>
@endsection
