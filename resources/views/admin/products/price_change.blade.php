@extends('admin.layouts.master')

@section('title', '商品變價管理')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商品變價管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('priceChanges') }}">商品變價管理</a></li>
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
                                @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                                <button type="button" id="new" class="btn btn-sm btn-primary">新增</button>
                                @endif
                            </div>
                            <div class="float-right">
                                <form action="{{ url('priceChanges') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        <span class="badge badge-purple text-sm mr-2">總筆數：{{ $priceChanges->total() != 0 ? number_format($priceChanges->total()) : 0 }}</span>
                                        <input type="text" class="form-control form-control-sm datetimepicker" name="colF" value="{{ isset($colF) ? $colF : '' }}" placeholder="輸入生效時間"> ~
                                        <input type="text" class="form-control form-control-sm datetimepicker" name="colG" value="{{ isset($colG) ? $colG : '' }}" placeholder="輸入結束時間">
                                        <input type="text" class="form-control form-control-sm" name="product_id" value="{{ isset($product_id) ? $product_id : '' }}" placeholder="輸入商品ID搜尋" title="搜尋商品ID" aria-label="Search">
                                        <button type="submit" class="btn btn-sm btn-info" title="商品ID" >
                                            <i class="fas fa-search"></i>
                                            搜尋
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="5%">商品ID</th>
                                        <th class="text-left" width="23%">商品名稱</th>
                                        <th class="text-center" width="7%">上下架</th>
                                        <th class="text-left" width="7%">單價</th>
                                        <th class="text-left" width="7%">原價</th>
                                        <th class="text-left" width="7%">廠商進價</th>
                                        <th class="text-left" width="12%">生效時間</th>
                                        <th class="text-left" width="12%">結束時間</th>
                                        <th class="text-center" width="5%">修改</th>
                                        <th class="text-center" width="10%">啟用</th>
                                        <th class="text-center" width="10%">刪除</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($priceChanges as $priceChange)
                                    <tr>
                                        <form id="myform" action="{{ route('admin.priceChanges.update', $priceChange->id) }}" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="_method" value="PATCH">
                                        @csrf
                                        <td class="text-left align-middle text-sm">{{ $priceChange->product_id }}</td>
                                        <td class="text-left align-middle text-sm">{{ $priceChange->colA }}</td>
                                        <td class="text-center align-middle text-sm">{{ $priceChange->status_updown == 1 ? '是' : '' }}</td>
                                        <td class="text-left align-middle text-sm">
                                            @if(strtotime($priceChange->colF) > strtotime(date('Y-m-d H:i:s')) && $priceChange->is_disabled == 0)
                                            <input type="text" class="form-control form-control-sm" name="colC" value="{{ $priceChange->colC }}">
                                            @else
                                            {{ $priceChange->colC }}
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            @if(strtotime($priceChange->colF) > strtotime(date('Y-m-d H:i:s')) && $priceChange->is_disabled == 0)
                                            <input type="text" class="form-control form-control-sm" name="colD" value="{{ $priceChange->colD }}">
                                            @else
                                            {{ $priceChange->colD }}
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            @if(strtotime($priceChange->colF) > strtotime(date('Y-m-d H:i:s')) && $priceChange->is_disabled == 0)
                                            <input type="text" class="form-control form-control-sm" name="colE" value="{{ $priceChange->colE }}">
                                            @else
                                            {{ $priceChange->colE }}
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            @if(strtotime($priceChange->colF) > strtotime(date('Y-m-d H:i:s')) && $priceChange->is_disabled == 0)
                                            <input type="text" class="form-control form-control-sm datetimepicker" name="colF" value="{{ $priceChange->colF }}">
                                            @else
                                            {{ $priceChange->colF }}
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            @if(strtotime($priceChange->colF) > strtotime(date('Y-m-d H:i:s')) && $priceChange->is_disabled == 0)
                                            <input type="text" class="form-control form-control-sm datetimepicker" name="colG" value="{{ $priceChange->colG }}">
                                            @else
                                            {{ $priceChange->colG }}
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if(strtotime($priceChange->colF) > strtotime(date('Y-m-d H:i:s')))
                                            @if($priceChange->is_disabled == 0)
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></button>
                                            @else
                                            @endif
                                            @else
                                            已生效
                                            @endif
                                        </td>
                                        </form>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            @if($priceChange->is_disabled == 0)
                                            <form action="{{ url('priceChanges/active/' . $priceChange->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $priceChange->is_disabled == 0 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($priceChange) ? $priceChange->is_disabled == 0 ? 'checked' : '' : '' }}>
                                            </form>
                                            @else
                                            已結束
                                            @endif
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.priceChanges.destroy', $priceChange->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <div class="form-group">
                                    <form action="{{ url('vendoraccounts') }}" method="GET" class="form-inline" role="search">
                                        <input type="hidden" name="is_on" value="{{ $is_on ?? '' }}">
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                            <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                        </select>
                                        <input type="hidden" name="keyword" value="{{ $keyword ?? '' }}">
                                    </form>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $priceChanges->appends($appends)->render() }}
                                @else
                                {{ $priceChanges->render() }}
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
{{-- 修改入庫 Modal --}}
<div id="newModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newModalLabel">新增商品變價資料</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="newForm" action="{{ route('admin.priceChanges.store') }}" method="POST" class="float-right">
                @csrf
                <div class="modal-body">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th width="10%" class="text-left"><span class="text-danger">*</span> 商品ID</th>
                                <th width="10%" class="text-left"><span class="text-danger">*</span> 單價</th>
                                <th width="10%" class="text-left">原價</th>
                                <th width="10%" class="text-left">廠商進價</th>
                                <th width="20%" class="text-left"><span class="text-danger">*</span> 生效時間</th>
                                <th width="20%" class="text-left">結束時間</th>
                                <th width="10%" class="text-center">
                                    <button type="button" class="btn btn-sm btn-success add-btn">
                                        <i class="fas fa-plus"></i>
                                    </button>

                                </th>
                            </tr>
                        </thead>
                        <tbody id="newItem">
                            <tr>
                                <td class="text-left">
                                    <input type="number" class="form-control" name="data[0][product_id]" placeholder="商品ID" required>
                                </td>
                                <td class="text-left">
                                    <input type="text" class="form-control" name="data[0][colC]" placeholder="單價" required>
                                </td>
                                <td class="text-left">
                                    <input type="text" class="form-control" name="data[0][colD]" placeholder="原價">
                                </td>
                                <td class="text-left">
                                    <input type="text" class="form-control" name="data[0][colE]" placeholder="廠商進價">
                                </td>
                                <td class="text-left">
                                    <input type="text" class="form-control datetimepicker" name="data[0][colF]" placeholder="生效時間" required>
                                </td>
                                <td class="text-left">
                                    <input type="text" class="form-control datetimepicker" name="data[0][colG]" placeholder="結束時間">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger del-btn">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary float-right btn-stockinModify">新增</button>
                </div>
            </form>
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
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('input[data-bootstrap-switch]').on('switchChange.bootstrapSwitch', function (event, state) {
            $(this).parents('form').submit();
        });

        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $('#new').click(function (e) {
            $('#newModal').modal('show');

        });

        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).parents('form').submit();
            };
        });

        $('.del-btn').click(function(e){
            $(this).parents('tr').remove();
        });

        $('.add-btn').click(function(e){
            let c = $('#newItem >tr').length;
            let html = '<tr><td class="text-left"><input type="number" class="form-control" name="data['+c+'][product_id]" placeholder="商品ID" required></td><td class="text-left"><input type="text" class="form-control" name="data['+c+'][colC]" placeholder="單價" required></td><td class="text-left"><input type="text" class="form-control" name="data['+c+'][colD]" placeholder="原價"></td><td class="text-left"><input type="text" class="form-control" name="data['+c+'][colE]" placeholder="廠商進價"></td><td class="text-left"><input type="text" class="form-control datetimepicker" name="data['+c+'][colF]" placeholder="生效時間" required></td><td class="text-left"><input type="text" class="form-control datetimepicker" name="data['+c+'][colG]" placeholder="結束時間"></td><td class="text-center"><button type="button" class="btn btn-sm btn-danger del-btn"><i class="fas fa-trash-alt"></i></button></td></tr>';
            $('#newItem').append(html);
            $('.del-btn').click(function(e){
                $(this).parents('tr').remove();
            });
            $('.datetimepicker').datetimepicker({
                timeFormat: "HH:mm:ss",
                dateFormat: "yy-mm-dd",
            });
        });
    })(jQuery);
</script>
@endsection
