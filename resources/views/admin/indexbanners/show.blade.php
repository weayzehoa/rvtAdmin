@extends('admin.layouts.master')

@section('title', '優惠活動設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>首頁橫幅圖設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('indexBanners') }}">首頁橫幅圖設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($indexBanner) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ isset($indexBanner) ? '修改' : '新增' }}資料</h3>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="chinese">
                                    @if(isset($indexBanner))
                                    <form class="myform" action="{{ route('admin.indexBanners.update', $indexBanner->id) }}" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="_method" value="PATCH">
                                    @else
                                    <form class="myform" action="{{ route('admin.indexBanners.store') }}" method="POST" enctype="multipart/form-data">
                                    @endif
                                        @csrf
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="name"><span class="text-red">* </span>橫幅圖片名稱</label>
                                                <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $indexBanner->name ?? old('name') }}" placeholder="輸入橫幅圖片名稱">
                                                @if ($errors->has('name'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="row col-6">
                                                <div class="form-group col-4">
                                                    <label for="start_time"><span class="text-red">* </span>開始時間</label>
                                                    <input type="datetime" class="form-control {{ $errors->has('start_time') ? ' is-invalid' : '' }} datetimepicker" id="start_time" name="start_time" value="{{ $indexBanner->start_time ?? old('start_time') }}" placeholder="輸入活動開始時間">
                                                    @if ($errors->has('start_time'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('start_time') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-4">
                                                    <label for="end_time"><span class="text-red">* </span>結束時間</label>
                                                    <input type="datetime" class="form-control {{ $errors->has('end_time') ? ' is-invalid' : '' }} datetimepicker" id="end_time" name="end_time" value="{{ $indexBanner->end_time ?? old('end_time') }}" placeholder="輸入活動結束時間" autocomplete="off">
                                                    @if ($errors->has('end_time'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('end_time') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                @if(in_array($menuCode.'O', explode(',',Auth::user()->power)))
                                                <div class="form-group col-3">
                                                    <label for="is_on">啟用狀態</label>
                                                    <div class="input-group">
                                                        <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="secondary" data-on-color="primary" {{ isset($indexBanner) ? $indexBanner->is_on == 1 ? 'checked' : '' : '' }} autocomplete="off">
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="form-group col-12">
                                                <label for="url">連結網址</label>
                                                <input type="text" class="form-control {{ $errors->has('url') ? ' is-invalid' : '' }}" id="url" name="url" value="{{ $indexBanner->url ?? old('url') }}" placeholder="輸入連結網址，ex: https://icarry.me">
                                                @if ($errors->has('title'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('url') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="code"><span class="text-red">* </span>電腦版橫幅圖</label>
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" id="img_desktop" name="img_desktop" class="custom-file-input {{ $errors->has('img_desktop') ? ' is-invalid' : '' }}" accept="image/*">
                                                        @if(isset($indexBanner))
                                                        <label class="custom-file-label" for="img_desktop">{{ $indexBanner->img_desktop }}</label>
                                                        @else
                                                        <label class="custom-file-label" for="img_desktop">瀏覽選擇新圖片</label>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(isset($indexBanner))
                                                <br>目前電腦版橫幅圖<br>
                                                <img width="100%" class="img_desktop" src="{{ env('AWS_FILE_URL').$indexBanner->img_desktop }}" alt="">
                                                @else
                                                <img width="100%" height="50" class="img_desktop" src="{{ asset('img/banner_sample_upload.png') }}" alt="">
                                                @endif
                                                @if ($errors->has('img_desktop'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('img_desktop') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="form-group col-6">
                                                <label for="code"><span class="text-red">* </span>手機版橫幅圖</label>
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" id="img_mobile" name="img_mobile" class="custom-file-input {{ $errors->has('img_mobile') ? ' is-invalid' : '' }}" accept="image/*">
                                                        @if(isset($indexBanner))
                                                        <label class="custom-file-label" for="img_mobile">{{ $indexBanner->img_mobile }}</label>
                                                        @else
                                                        <label class="custom-file-label" for="img_mobile">瀏覽選擇新圖片</label>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(isset($indexBanner))
                                                <br>目前電腦版橫幅圖<br>
                                                <img width="100%" class="img_mobile" src="{{ env('AWS_FILE_URL').$indexBanner->img_mobile }}" alt="">
                                                @else
                                                <img width="100%" height="50" class="img_mobile" src="{{ asset('img/banner_sample_upload.png') }}" alt="">
                                                @endif
                                                @if ($errors->has('img_mobile'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('img_mobile') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-center bg-white">
                                            @if(in_array(isset($indexBanner) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                            <button type="submit" class="btn btn-primary">{{ isset($indexBanner) ? '修改' : '新增' }}</button>
                                            @endif
                                            <a href="{{ route('admin.indexBanners.index') }}" class="btn btn-info">
                                                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                            </a>
                                        </div>
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
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
@if(isset($indexBanner))
{!! JsValidator::formRequest('App\Http\Requests\Admin\indexBannerRequest', '.myform'); !!}
@else
{!! JsValidator::formRequest('App\Http\Requests\Admin\indexBannerCreateRequest', '.myform'); !!}
@endif
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
        // date time picker 設定
        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
    })(jQuery);


    $('input[type=file]').change(function(x) {
        name = this.name;
        defaultimg = $('.'+name).attr('src');
        file = x.currentTarget.files;
        if (file.length >= 1) {
            filename = checkMyImage(file);
            if (filename) {
                readURL(this, '.' + name);
                $('label[for=' + name + ']').html(filename);
            } else {
                $(this).val('');
                $('label[for=' + name + ']').html('瀏覽選擇新圖片');
                $('.' + name).attr('src', defaultimg); //沒照片時還原成預設照片
            }
        } else {
            defaultimg = '{{ asset('img/banner_sample_upload.png') }}';
            $(this).val('');
            $('label[for=' + name + ']').html('瀏覽選擇新圖片');
            $('.' + name).attr('src', defaultimg); //沒照片時還原成預設照片
        }
    });

    function readURL(input, imgclass) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(imgclass).attr('src', e.target.result);
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
        if ($.inArray(ext, ['.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp']) == -1) {
            alert('檔案格式不被允許，限JPG、PNG、GIF、SVG或webp格式');
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
                    }else{
                        return null;
                    }
                }else{
                    return null;
                }
            }else{
                return null;
            }
        }else{
            return null;
        }
    }

</script>
@endsection
