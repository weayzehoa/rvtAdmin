@extends('admin.layouts.master')

@section('title', '使用者管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>使用者管理</b><small> ({{ isset($user) ? '修改' : '新增' }})</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('users') }}">使用者管理</a></li>
                        <li class="breadcrumb-item active">{{ isset($user) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <nav class="w-100">
                    <div class="nav nav-tabs" id="user-tab" role="tablist">
                        <a class="nav-item nav-link active" id="user-desc-tab" data-toggle="tab" href="#user-desc" role="tab" aria-controls="user-desc" aria-selected="true">使用者資料</a>
                        <a class="nav-item nav-link" id="user-cart-tab" data-toggle="tab" href="#user-cart" role="tab" aria-controls="user-cart" aria-selected="false">購物車</a>
                        <a class="nav-item nav-link" id="user-sms-tab" data-toggle="tab" href="#user-sms" role="tab" aria-controls="user-sms" aria-selected="false">簡訊</a>
                    </div>
                </nav>
                <div class="tab-content p-3" id="nav-tabContent">
                    <div class="tab-pane fade {{ Session::get('userCartShow') || Session::get('userServiceShow') || Session::get('userSmsShow') ? '' : 'show active' }}" id="user-desc" role="tabpanel" aria-labelledby="user-desc-tab">
                        @if(isset($user))
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title">使用者 {{ $user->id }} 資料</h3>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($user))
                                        <form id="myform" action="{{ route('admin.users.update', $user->id) }}" method="POST">
                                            <input type="hidden" name="_method" value="PATCH">
                                        @else
                                        <form id="myform" action="{{ route('admin.users.store') }}" method="POST">
                                        @endif
                                            @csrf
                                            <div class="row">
                                                <div class="form-group col-2">
                                                    <label for="name">姓名</label>
                                                    <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $user->name ?? '' }}" placeholder="姓名">
                                                    @if ($errors->has('name'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('name') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-1">
                                                    <label for="nation"><span class="text-red">* </span>國碼</label>
                                                    <input type="text" class="form-control {{ $errors->has('nation') ? ' is-invalid' : '' }}" id="nation" name="nation" value="{{ $user->nation ?? '' }}" placeholder="國碼">
                                                    @if ($errors->has('nation'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('nation') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-2">
                                                    <label for="mobile"><span class="text-red">* </span>行動電話</label>
                                                    <input type="text" class="form-control {{ $errors->has('mobile') ? ' is-invalid' : '' }}" id="mobile" name="mobile" value="{{ $user->mobile ?? '' }}" placeholder="行動電話">
                                                    @if ($errors->has('mobile'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('mobile') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-3">
                                                    <label for="email">電子郵件</label>
                                                    <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" id="email" name="email" value="{{ $user->email ?? '' }}" placeholder="電子郵件">
                                                    @if ($errors->has('email'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('email') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-4">
                                                    <label for="address">地址</label>
                                                    <input type="text" class="form-control {{ $errors->has('address') ? ' is-invalid' : '' }}" id="address" name="address" value="{{ $user->address ?? '' }}" placeholder="地址">
                                                    @if ($errors->has('address'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('address') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-1">
                                                    <label for="verify_code">手機驗證碼</label>
                                                    <input type="text" class="form-control {{ $errors->has('verify_code') ? ' is-invalid' : '' }}" id="verify_code" name="verify_code" value="{{ $user->verify_code ?? '' }}" placeholder="手機驗證碼">
                                                    @if ($errors->has('verify_code'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('verify_code') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-11">
                                                    <label for="verify_code">其他資訊</label>
                                                    <div class="input-group">
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                購物金餘額
                                                            </div>
                                                        </div>
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text bg-white"><b class="text-danger">{{ number_format($user->points) }}</b></span>
                                                        </div>
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                推薦人ID
                                                            </div>
                                                        </div>
                                                        <div class="input-group-prepend">
                                                            @if($user->refer_id > 0)
                                                            <span class="input-group-text bg-white"><a href="{{ route('admin.users.show', $user->refer_id ) }}"><b>{{ $user->refer_id }}</b></a></span>
                                                            @else
                                                            <span class="input-group-text bg-white">{{ $user->refer_id > 0 ? $user->id : '無' }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                其他聯絡方式
                                                            </div>
                                                        </div>
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text bg-white"><b>{{ $user->other_contact ?? '未提供' }}</b></span>
                                                        </div>
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                登入方式
                                                            </div>
                                                        </div>
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text bg-white">{{ $user->from_site }}</span>
                                                        </div>
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                註冊日期
                                                            </div>
                                                        </div>
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text bg-white">{{ $user->create_time }}</span>
                                                        </div>
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                狀態
                                                            </div>
                                                        </div>
                                                        <div class="input-group-prepend">
                                                            @if($user->status == -1)
                                                            <span class="input-group-text bg-white"><b class="text-danger">停用中</b></span>
                                                            @elseif($user->status == 0)
                                                            <span class="input-group-text bg-white"><b>未驗證</b></span>
                                                            @elseif($user->status == 1)
                                                            <span class="input-group-text bg-white"><b class="text-success">已驗證，啟用中</b></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group col-4">
                                                    <label for="mark">備註 (此欄位僅提供給後台管理者使用)</label>
                                                    <input type="text" class="form-control {{ $errors->has('mark') ? ' is-invalid' : '' }}" id="mark" name="mark" value="{{ $user->mark ?? '' }}" placeholder="僅提供給後台管理者使用，前台不顯示">
                                                    @if ($errors->has('mark'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('mark') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                {{-- <div class="form-group col-2">
                                                    <label for="status">啟用/停用</label>
                                                    <div class="form-group form-group-lg">
                                                    @if($user->status == 0)
                                                    <span class="text-danger"><b>未完成驗證程序，無法啟用</b></span>
                                                    @else
                                                        <input type="checkbox" name="status" value="{{ $user->status }}" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="danger" data-on-color="primary" {{ isset($user) ? $user->status == 1 ? 'checked' : '' : '' }}>
                                                    @endif
                                                    </div>
                                                </div> --}}
                                            </div>
                                            <div class="text-center bg-white">
                                                @if(in_array(isset($user) ? $menuCode['user'].'M' : $menuCode['user'].'N', explode(',',Auth::user()->power)))
                                                <button type="submit" class="btn btn-primary">{{ isset($user) ? '修改' : '新增' }}</button>
                                                @endif
                                                <a href="{{ url('users') }}" class="btn btn-info">
                                                    <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">購物金紀錄</h3>
                                    </div>
                                    <div class="card-body">
                                        <form id="addpoints" action="{{ url('users/addpoints/'. $user->id) }}" method="POST">
                                            @csrf
                                            <div class="form-group col-8">
                                                <label for="verify_code">手動調整購物金</label>
                                                <div class="input-group">
                                                    <div class="input-group-append">
                                                        <div class="input-group-text">
                                                            原因
                                                        </div>
                                                    </div>
                                                        <input type="text" class="form-control" name="point_type" placeholder="請填寫原因" required>
                                                    <div class="input-group-append">
                                                        <div class="input-group-text">
                                                            購物金
                                                        </div>
                                                    </div>
                                                    <div class="input-group-append">
                                                        <input type="number" class="form-control" name="points" placeholder="請填寫整數" required>
                                                    </div>
                                                    <div class="input-group-prepend">
                                                        <a href="javascript:" class="btn btn-danger btn-points">調整</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <div class="card-primary card-outline"></div>
                                        @if(count($points)>0)
                                        <table class="table table-hover table-sm">
                                            <thead>
                                                <tr>
                                                    <th class="text-left" width="15%">新增時間</th>
                                                    <th class="text-left" width="40%">原因</th>
                                                    <th class="text-left" width="15%">增減額度</th>
                                                    <th class="text-left" width="15%">過期日</th>
                                                    <th class="text-right" width="15%">新增後餘額</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($points as $point)
                                                <tr>
                                                    <td class="text-left align-middle text-sm">{{ $point->create_time }}</td>
                                                    <td class="text-left align-middle text-sm">{{ $point->point_type }}</td>
                                                    <td class="text-left align-middle text-sm"><span class="{{ $point->points < 0 ? 'text-danger' : '' }}">{{ $point->points }}</span></td>
                                                    <td class="text-left align-middle text-sm">{{ $point->dead_time }}</td>
                                                    <td class="text-right align-middle text-sm">{{ number_format($point->balance) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @else
                                        <h3>尚無購物金紀錄</h3>
                                        @endif
                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="float-right">
                                            @if(count($points) > 0)
                                            {{ $points->render() }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="tab-pane fade {{ Session::get('userCartShow') ?? '' }}" id="user-cart" role="tabpanel" aria-labelledby="user-cart-tab">
                        @if(isset($user))
                        @if(count($shoppingCarts)>0)
                        <div class="card-body">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="15%">商家</th>
                                        <th class="text-left" width="15%">品號</th>
                                        <th class="text-left" width="30%">品名</th>
                                        <th class="text-right" width="8%">單重</th>
                                        <th class="text-right" width="8%">單價</th>
                                        <th class="text-right" width="8%">數量</th>
                                        <th class="text-right" width="8%">總價</th>
                                        <th class="text-right" width="8%">總重</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shoppingCarts as $shoppingCart)
                                    <tr>
                                        <td class="text-left align-middle text-sm">
                                            <a href="{{ url('vendors/'.$shoppingCart->vendor_id) }}">{{ $shoppingCart->vendor_name }}</a>
                                        </td>
                                        <td class="text-left align-middle text-sm">{{ $shoppingCart->digiwin_no }}</td>
                                        <td class="text-left align-middle text-sm">
                                            <a href="{{ url('products/'.$shoppingCart->product_id) }}">{{ $shoppingCart->product_name }}</a>
                                        </td>
                                        <td class="text-right align-middle text-sm">{{ $shoppingCart->gross_weight }}</td>
                                        <td class="text-right align-middle text-sm">{{ $shoppingCart->price }}</td>
                                        <td class="text-right align-middle text-sm">{{ $shoppingCart->quantity }}</td>
                                        <td class="text-right align-middle text-sm">{{ $shoppingCart->quantity * $shoppingCart->price}}</td>
                                        <td class="text-right align-middle text-sm">{{ $shoppingCart->quantity * $shoppingCart->gross_weight }}</td>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <td class="text-right align-middle text-sm"></td>
                                        <td class="text-right align-middle text-sm"></td>
                                        <td class="text-right align-middle text-sm"></td>
                                        <td class="text-right align-middle text-sm"></td>
                                        <td class="text-right align-middle text-sm">總計：</td>
                                        <td class="text-right align-middle text-sm">{{ $shoppingCarts->totalQtys }}</td>
                                        <td class="text-right align-middle text-sm">{{ number_format($shoppingCarts->totalPrice) }}</td>
                                        <td class="text-right align-middle text-sm">{{ number_format($shoppingCarts->totalWeights) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="card-body">
                            <h3>尚無購物車資料</h3>
                        </div>
                        @endif
                        @endif
                    </div>
                    <div class="tab-pane fade {{ Session::get('userSmsShow') ?? '' }}" id="user-sms" role="tabpanel" aria-labelledby="user-sms-tab">
                        @if(isset($user))
                        <div class="card-body">
                            <form id="sendsms" action="{{ url('users/sendsms/'. $user->id) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-8">
                                        <input type="text" class="form-control {{ $errors->has('message') ? ' is-invalid' : '' }}" name="message" placeholder="輸入要發送的簡訊內容，限制 75 字。" autocomplete="off" onkeyup="inputTextCount(this);">
                                        <h5>限制 75 個字，已使用 <span class="text-danger"> 0 </span> 個字</h5>
                                        @if ($errors->has('message'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('message') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-2">
                                        <button type="submit" class="btn btn-primary">發訊簡訊到 {{ $user->nation.$user->mobile }}</button>
                                    </div>
                                </div>
                            </form>
                            <div class="card-primary card-outline"></div>
                            @if(count($user->smsLogs)>0)
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="15%">發送時間</th>
                                        <th class="text-left" width="15%">手機號碼</th>
                                        <th class="text-left" width="15%">簡訊廠商</th>
                                        <th class="text-left" width="40%">簡訊內容</th>
                                        <th class="text-right" width="15%">簡訊狀態</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user->smsLogs as $sms)
                                    <tr>
                                        <td class="text-left align-middle text-sm">{{ $sms->created_at }}</td>
                                        <td class="text-left align-middle text-sm">{{ $user->nation.$user->mobile }}</td>
                                        <td class="text-left align-middle text-sm">{{ $sms->vendor }}</td>
                                        <td class="text-left align-middle text-sm">{{ $sms->message }}</td>
                                        <td class="text-right align-middle text-sm">{{ $sms->status }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <h3>尚無簡訊紀錄</h3>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
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
                <div class="form-group col-10 offset-1" id="memoform"></div>
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
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
@endsection

@section('script')
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UsersRequest', '#myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        $('[data-toggle="popover"]').popover({
            html: true,
            sanitize: false,
        });

        var tab = window.location.hash;
        if(tab){
            $('#user-desc-tab').removeClass('active');
            $('#user-desc').removeClass('active');
            $('#user-desc').removeClass('show');
            $(tab+'-tab').addClass('active');
            $(tab).addClass('active');
            $(tab).addClass('show');
        }else{
            $('#user-desc-tab').addClass('active');
            $('#user-desc').addClass('active');
            $('#user-desc').addClass('show');
        }

        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('input[name=points]').change(function(){
            let points = $('input[name=points]').val();
            if(points > 0){
                $('.btn-points').html('新增');
            }else if(points == 0){
                $('.btn-points').html('調整');
            }else{
                $('.btn-points').html('扣除');
            }
        });

        $('.btn-points').click(function(){
            $('#addpoints').submit();
            if(confirm('請確認是否要調整購物金??')){
                let points = $('input[name=points]').val();
                let reason = $('input[name=point_type]').val();
                if(!reason || !points || points == 0){
                    alert('請填寫原因(必填)及購物金數量(不可為0)');
                }else{
                    $('#addpoints').submit();
                }
            };
        });
    })(jQuery);

    var inputTextCount = function(o) {
            $(o).next().find("span").html($(o).val().toString().length);
        };

        function modify(order_number,order_id,column_name,column_value,e,item_id){
        let token = '{{ csrf_token() }}';
        let itemIds = [];
        let id = [];
        let datepicker = '';
        let dateFormat = 'yy-mm-dd';
        let timeFormat = 'HH:mm:ss';
        let note = '';
        !Array.isArray(order_id)? id[0] = order_id : id = order_id;
        $('#myform').html('');
        $('#record').html('');
        $('#myrecord').addClass('d-none');
        if(column_name == 'admin_memo'){
            title = '管理者註記';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，請輸入'+title;
            placeholder = '請輸入'+title+'內容';
            note = '<div><span class="text-primary">清空內容為取消註記</span></div>';
        }else if(column_name == 'cancel'){
            title = '取消訂單';
            id.length >=2 ? label = title : label = '訂單編號：'+order_number+'，'+title;
            placeholder = '請輸入取消訂單原因，例如：客戶要求取消';
        }

        html = '<div class="input-group"><span class="input-group-text">輸入內容</span><input type="text" class="form-control col-12" id="data" name="data" value="'+column_value+'" placeholder="'+placeholder+'" autocomplete="off"><button type="button" class="btn btn-primary modifysend">確定</button><button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">取消</span></button></div>'+note+datepicker;

        if( id.length == 1 ){
            if(column_name != 'order_item_modify'){
                $('#myrecord').removeClass('d-none');
                $.ajax({
                    type: "post",
                    url: '../orders/getlog',
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
        $('#memoform').html(html);
        $('#myModal').modal('show');

        $('.modifysend').click(function () {
            let column_data = $('#data').val();
            column_data ? column_data = column_data : column_data = '';
            if(column_name == 'cancel'){
                if(confirm('請確認是否真的要取消該訂單？')){
                    modifysend(id,column_name,column_data,itemIds,e)
                }else{
                    $('#myModal').modal('hide');
                }
            }else{
                modifysend(id,column_name,column_data,itemIds,e);
            }
        });
    }

    function modifysend(id,column_name,column_data,itemIds,e){
        let token = '{{ csrf_token() }}';
        $.ajax({
            type: "post",
            url: '../unpayorders/modify',
            data: { id: id, column_name: column_name, column_data: column_data, item_ids: itemIds, _token: token },
            success: function(orders) {
                if(orders){
                    for(i=0;i<orders.length;i++){
                        target = '.'+column_name+'_'+orders[i]['id'];
                        value = orders[i][column_name];
                        if(column_name == 'admin_memo'){
                            nullText = '<span><i class="fas fa-info-circle"></i></span>';
                            text = '<span><i class="fas fa-info-circle"></i>('+value+')</span>';
                            $(target).attr('onclick','modify('+orders[i]['order_number']+','+orders[i]['id']+',\''+column_name+'\',\''+value+'\',this)');
                            value ? $(target).html(text) : $(target).html(nullText);
                        }else if(column_name == 'cancel'){
                            target = '.admin_memo_'+orders[i]['id'];
                            value = orders[i]['admin_memo'];
                            nullText = '<span><i class="fas fa-info-circle"></i></span>';
                            text = '<span><i class="fas fa-info-circle"></i>('+value+')</span>';
                            target2 = '.status_'+orders[i]['id'];
                            text2 = '後台取消訂單';
                            $(target).attr('onclick','modify('+orders[i]['order_number']+','+orders[i]['id']+',\''+target+'\',\''+value+'\',this)');
                            value ? $(target).html(text) : $(target).html(nullText);
                            $(target2).html(text2);
                            $(e).remove();
                        }
                    }
                    $('#myModal').modal('hide');
                }
            }
        });
    }

</script>
@endsection
