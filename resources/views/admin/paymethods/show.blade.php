@extends('admin.layouts.master')

@section('title', '付款方式設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>付款方式設定</b><small> ({{ isset($payMethod) ? '修改' : '新增' }})</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('paymethods') }}">付款方式設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($payMethod) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        @if(isset($payMethod))
        <form id="myform" action="{{ route('admin.paymethods.update', $payMethod->id) }}" method="POST">
            <input type="hidden" name="_method" value="PATCH">
        @else
        <form id="myform" action="{{ route('admin.paymethods.store') }}" method="POST">
        @endif
            @csrf
            <input type="hidden" class="form-control" name="type" value="1">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">{{ $payMethod->name ?? ''}}主選單資料</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="account"><span class="text-red">* </span>值(value)</label>
                                            <input type="text" class="form-control {{ $errors->has('value') ? ' is-invalid' : '' }}" id="value" name="value" value="{{ $payMethod->value ?? '' }}" placeholder="請輸值(value)">
                                            @if ($errors->has('value'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('value') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            <label for="type"><span class="text-red">* </span>類別</label>
                                            <select class="form-control {{ $errors->has('type') ? ' is-invalid' : '' }}" data-dropdown-css-class="select2-primary" name="type">
                                                <option value="信用卡" {{ isset($payMethod) ? $payMethod->type == '信用卡' ? 'selected' : '' : 'selected'}}>信用卡</option>
                                                <option value="支付寶" {{ isset($payMethod) ? $payMethod->type == '支付寶' ? 'selected' : '' : ''}}>支付寶</option>
                                                <option value="銀聯卡" {{ isset($payMethod) ? $payMethod->type == '銀聯卡' ? 'selected' : '' : ''}}>銀聯卡</option>
                                                <option value="行動銀行" {{ isset($payMethod) ? $payMethod->type == '行動銀行' ? 'selected' : '' : ''}}>行動銀行</option>
                                                <option value="ATM" {{ isset($payMethod) ? $payMethod->type == 'ATM' ? 'selected' : '' : ''}}>ATM轉帳</option>
                                                <option value="CVS" {{ isset($payMethod) ? $payMethod->type == 'CVS' ? 'selected' : '' : ''}}>CVS超商代碼繳款</option>
                                                <option value="其它" {{ isset($payMethod) ? $payMethod->type == '其它' ? 'selected' : '' : ''}}>其它</option>
                                            </select>
                                            @if ($errors->has('type'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('type') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            <label for="account"><span class="text-red">* </span>顯示名稱</label>
                                            <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $payMethod->name ?? '' }}" placeholder="請輸入顯示名稱">
                                            @if ($errors->has('name'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            <label for="account"><span class="text-red">* </span>顯示英文名稱</label>
                                            <input type="text" class="form-control {{ $errors->has('name_en') ? ' is-invalid' : '' }}" id="name_en" name="name_en" value="{{ $payMethod->name_en ?? '' }}" placeholder="請輸入顯示英文名稱">
                                            @if ($errors->has('name_en'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name_en') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-2">
                                                    <label for="is_on">啟用狀態</label>
                                                    <div class="input-group">
                                                        <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-off-color="secondary" data-on-color="primary" {{ isset($payMethod) ? $payMethod->is_on == 1 ? 'checked' : '' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="col-8">
                                                    <div class="input-group">
                                                        <label>圖片</label>
                                                    </div>
                                                    <div class="input-group">
                                                        <input type="file" id="image" name="image" class="custom-file-input {{ $errors->has('image') ? ' is-invalid' : '' }} mb-2" accept="image/*">
                                                        <label class="custom-file-label" for="image">瀏覽選擇新圖片</label>
                                                        上傳後等比縮放為240x60
                                                    </div>
                                                </div>
                                                <div class="col-2 text-center align-middle">
                                                    @if(isset($payMethod) && !empty($payMethod->image))
                                                    <img class="image" src="{{ env('AWS_FILE_URL').$payMethod->image }}" alt="">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-center bg-white">
                                @if(in_array(isset($payMethod) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                <button type="submit" class="btn btn-primary">{{ isset($payMethod) ? '修改' : '新增' }}</button>
                                @endif
                                <a href="{{ url('paymethods') }}" class="btn btn-info">
                                    <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\PayMethodRequest', '#myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).parents('form').submit();
            };
        });
    })(jQuery);

    // 舊的image網址及屬性
    img = $('.image').attr('src');
    $('input[type=file]').change(function(x) {
        name = this.name;
        name == 'image' ? oldimg = img : '';
        file = x.currentTarget.files;
        if (file.length >= 1) {
            // filename = checkMyImage(file);
            filename = file[0].name; //不檢查檔案直接找出檔名
            if (filename) {
                readURL(this, '.' + name);
                $('label[for=' + name + ']').html(filename);
            } else {
                $(this).val('');
                $('label[for=' + name + ']').html('瀏覽選擇新圖片');
                $('.' + name).attr('src', oldimg); //沒照片時還原
                $('.' + name).removeClass('col-12');
            }
        } else {
            $(this).val('');
            $('label[for=' + name + ']').html('瀏覽選擇新圖片');
            $('.' + name).attr('src', oldimg); //沒照片時還原
            $('.' + name).removeClass('col-12');
        }
    });

    function readURL(input, imgclass) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(imgclass).attr('src', e.target.result);
                $(imgclass).addClass('col-12');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function checkFileName(filename) {
        var chk = 0;
        var specialSymbols = new Array("`", "~", "!", "@", "#", "$", "%", "^", "&", "*", "+", "=", "(", ")", "[", "]", "{", "}", "<", ">", "/", "?", ":", ";", "'", "\"", "\\", "|");
        for (j = 0; j < specialSymbols.length; j++) {
            if (filename.indexOf(specialSymbols[j]) >= 0) {
                chk++;
            }
        }
        if (chk >= 1) {
            alert("檔案名稱中不可含有兩個\"點\"符號或下列特殊符號。\n(  ` ~ ! @ # $ % ^ & * + = ( ) [ ] { } < > / ? : ; ' \" \\ |  )");
            return false;
        } else {
            return true;
        }
    }

    function checkFileSize(size) {
        if (size > 10240 * 1024) {
            alert('檔案大小超過10MB');
            return false;
        } else {
            return true;
        }
    }

    function checkFileExt(ext) {
        if ($.inArray(ext, ['.png', '.jpg', '.jpeg', '.gif', '.svg']) == -1) {
            alert('檔案格式不被允許，限JPG、PNG、GIF或SVG格式');
            return false;
        } else {
            return true;
        }
    }

    function checkMyImage(input) {
        if (input) {
            var filename = input[0].name;
            var size = input[0].size;
            var ext = filename.substring(filename.lastIndexOf('.')).toLowerCase();
            if (checkFileName(filename)) {
                if (checkFileExt(ext)) {
                    if (checkFileSize(size)) {
                        return filename;
                    }
                }
            }
        }
    }
</script>
@endsection
