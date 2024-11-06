@extends('admin.layouts.master')

@section('title', '團購設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>團購設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('groupbuyings') }}">團購設定</a></li>
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
                                <div class="col-3">
                                    @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                                    <a href="{{ route('admin.groupbuyings.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i>新增</a>
                                    @endif
                                    @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                    <button class="sort-btn btn btn-sm btn-success" value="sort"><i class="fas fa-sort-numeric-down mr-1"></i><span id="sort_text">排序</span></button>
                                    @endif
                                </div>
                                <div class="col-9">
                                    <div class=" float-right">
                                        <div class="input-group input-group-sm align-middle align-items-middle">
                                            <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($groupBuyings->total()) ?? 0 }}</span>
                                            {{-- <form action="" method="GET" class="form-inline" role="search">
                                                <span class="badge badge-primary text-sm mr-2">快搜 <i class="fas fa-hand-point-right"></i></span>
                                            </form>
                                            <form action="{{ url('curations') }}" method="GET" class="form-inline" role="search">
                                                <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                                    <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                                    <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                                    <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                                    <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                                </select>
                                                <select class="form-control form-control-sm" name="type" onchange="submit(this)">
                                                    <option value="" {{ isset($type) && $type == '' ? 'selected' : '' }}>全部版型</option>
                                                    <option value="header" {{ isset($type) && $type == 'header' ? 'selected' : '' }}>Header</option>
                                                    <option value="image" {{ isset($type) && $type == 'image' ? 'selected' : '' }}>圖片</option>
                                                    <option value="event" {{ isset($type) && $type == 'event' ? 'selected' : '' }}>活動</option>
                                                    <option value="block" {{ isset($type) && $type == 'block' ? 'selected' : '' }}>宮格</option>
                                                    <option value="product" {{ isset($type) && $type == 'product' ? 'selected' : '' }}>產品</option>
                                                    <option value="vendor" {{ isset($type) && $type == 'vendor' ? 'selected' : '' }}>品牌</option>
                                                </select>
                                                <select class="form-control form-control-sm" name="is_on" onchange="submit(this)">
                                                    <option value="" {{ isset($is_on) && $is_on == '' ? 'selected' : '' }}>狀態</option>
                                                    <option value="on" {{ isset($is_on) && $is_on == 'on' ? 'selected' : '' }}>啟用</option>
                                                    <option value="off" {{ isset($is_on) && $is_on == 'off' ? 'selected' : '' }}>停用</option>
                                                </select>
                                                <input type="text" class="form-control form-control-sm datetimepicker" name="start_time" value="{{ isset($start_time) ? $start_time : '' }}" placeholder="開始時間">
                                                <input type="text" class="form-control form-control-sm datetimepicker" name="end_time" value="{{ isset($end_time) ? $end_time : '' }}" placeholder="結束時間">
                                                <input type="search" class="form-control form-control-sm" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="輸入關鍵字搜尋" title="搜尋主標題、副標題或說明文案" aria-label="Search">
                                                <button type="submit" class="btn btn-sm btn-info" title="搜尋主標題、副標題或說明文案">
                                                    <i class="fas fa-search"></i>
                                                    搜尋
                                                </button>
                                            </form> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(count($groupBuyings) > 0)
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center align-middle" width="5%">團主ID</th>
                                        <th class="text-center align-middle" width="10%">銷售國家</th>
                                        <th class="text-left align-middle" width="30%">標題名稱</th>
                                        <th class="text-left align-middle" width="10%">開始時間</th>
                                        <th class="text-left align-middle" width="10%">結束時間</th>
                                        <th class="text-left align-middle" width="10%">預定出貨日</th>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <th class="text-center align-middle" width="10%">啟用</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($groupBuyings as $groupBuy)
                                    <tr>
                                        <td class="text-center align-middle">{{ $groupBuy->master_user_id }}</td>
                                        <td class="text-center align-middle">{{ $groupBuy->product_sold_country }}</td>
                                        <td class="text-left align-middle">
                                            <a href="{{ route('admin.groupbuyings.show',$groupBuy->id) }}">{{ $groupBuy->description }}</a>
                                        </td>
                                        <td class="text-left align-middle">{{ $groupBuy->start_date }}</td>
                                        <td class="text-left align-middle">{{ $groupBuy->end_date }}</td>
                                        <td class="text-left align-middle">{{ $groupBuy->shipping_date }}</td>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ url('groupbuyings/active/' . $groupBuy->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $groupBuy->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="secondary" data-on-color="primary" {{ isset($groupBuy) ? $groupBuy->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                        </td>
                                        @endif
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
                                    <form action="{{ url('invoices') }}" method="GET" class="form-inline" role="search">
                                        @if(isset($keyword))<input type="hidden" name="keyword" value="{!! $keyword ?? '' !!}">@endif
                                        @if(isset($status))<input type="hidden" name="status" value="{!! $status ?? '' !!}">@endif
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                        </select>
                                        <span class="badge badge-primary text-lg ml-1">總筆數：{{ number_format($groupBuyings->total()) ?? 0 }}</span>
                                    </form>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $groupBuyings->appends($appends)->render() }}
                                @else
                                {{ $groupBuyings->render() }}
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

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('input[data-bootstrap-switch]').on('switchChange.bootstrapSwitch', function (event, state) {
            $(this).parents('form').submit();
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

        $('.sort-btn').click(function(){
            if($(this).val() == 'sort'){
                $('.sort').prop('readonly',false);
                $(this).val('submit');
                $('#sort_text').html('儲存排序');
            }else if($(this).val() == 'submit'){
                let form = $('#myform');
                let ids = $('.sort').serializeArray().map( item => item.name );
                let sorts = $('.sort').serializeArray().map( item => item.value );
                for(let j=0; j<ids.length;j++){
                        let tmp = '';
                        let tmp2 = '';
                        tmp = $('<input type="hidden" class="formappend" name="id['+j+']" value="'+ids[j]+'">');
                        tmp2 = $('<input type="hidden" class="formappend" name="sort['+j+']" value="'+sorts[j]+'">');
                        form.append(tmp);
                        form.append(tmp2);
                    }
                $('#myform').submit();
            }
        });
    })(jQuery);
</script>
@endsection
