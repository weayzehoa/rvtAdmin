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
                    <h1 class="m-0 text-dark"><b>優惠活動設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('promoboxes') }}">優惠活動設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($promoBox) ? '修改' : '新增' }}</li>
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
                            <h3 class="card-title">優惠活動資料</h3>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="chinese">
                                    @if(isset($promoBox))
                                    <form class="myform" action="{{ route('admin.promoboxes.update', $promoBox->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PATCH">
                                    @else
                                    <form class="myform" action="{{ route('admin.promoboxes.store') }}" method="POST">
                                    @endif
                                        @csrf
                                        <nav class="w-100">
                                            <div class="nav nav-tabs" id="curation-tab" role="tablist">
                                                <a class="nav-item nav-link active" id="curation-chinese-tab" data-toggle="tab" href="#curation-chinese" role="tab" aria-controls="curation-chinese" aria-selected="true">中文</a>
                                                @for($i=0;$i<count($langs);$i++)
                                                <a class="nav-item nav-link" id="curation-{{ $langs[$i]['code'] }}-tab" data-toggle="tab" href="#curation-{{ $langs[$i]['code'] }}" role="tab" aria-controls="curation-{{ $langs[$i]['code'] }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                @endfor
                                            </div>
                                        </nav>
                                        <div class="col-12 tab-content p-3" id="nav-tabContent">
                                            <div class="tab-pane fade show active" id="curation-chinese" role="tabpanel" aria-labelledby="curation-chinese-tab">
                                                <div class="row">
                                                    <div class="form-group col-12">
                                                        <label for="code"><span class="text-red">* </span>活動標題</label>
                                                        <input type="text" class="form-control {{ $errors->has('title') ? ' is-invalid' : '' }}" id="title" name="title" value="{{ $promoBox->title ?? old('title') }}" placeholder="輸入活動標題">
                                                        @if ($errors->has('title'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('title') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="text_teaser"><span class="text-red">* </span>介紹文(前) <span class="text-red">*最多300字</span></label>
                                                        <textarea  rows="10" class="form-control {{ $errors->has('text_teaser') ? ' is-invalid' : '' }}" id="text_teaser" name="text_teaser" placeholder="輸入介紹文內容 (前)...">{{ isset($promoBox) && $promoBox->text_teaser ?  $promoBox->text_teaser : old('text_teaser') }}</textarea>
                                                        @if ($errors->has('text_teaser'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('text_teaser') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="text_complete">詳細內文(後) <span class="text-red">最多兩千字</span></label>
                                                        <textarea rows="10" class="form-control {{ $errors->has('text_complete') ? ' is-invalid' : '' }}" id="text_complete" name="text_complete" placeholder="輸入詳細說明內文 (後)...">{{ isset($promoBox) && $promoBox->text_complete ? $promoBox->text_complete : old('text_complete') }}</textarea>
                                                        @if ($errors->has('text_complete'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('text_complete') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @for($i=0;$i<count($langs);$i++)
                                            <div class="tab-pane fade" id="curation-{{ $langs[$i]['code'] }}" role="tabpanel" aria-labelledby="curation-{{ $langs[$i]['code'] }}-tab">
                                                <div class="row">
                                                    <div class="form-group col-12">
                                                        <label>{{ $langs[$i]['name'] }}活動標題 <span class="text-red">*最多150字</span></label>
                                                        <input type="text" class="form-control" name="title_{{ $langs[$i]['code'] }}" value="{{ isset($promoBox) && $promoBox->{"title_".$langs[$i]['code']} ? $promoBox->{"title_".$langs[$i]['code']} : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}活動標題">
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label>{{ $langs[$i]['name'] }}介紹文(前) <span class="text-red">*最多300字</span></label>
                                                        <textarea  rows="10" class="form-control" id="text_teaser_{{ $langs[$i]['code'] }}" name="text_teaser_{{ $langs[$i]['code'] }}" placeholder="輸入{{ $langs[$i]['name'] }}介紹文內容 (前)...">{{ isset($promoBox) && $promoBox->{"text_teaser_".$langs[$i]['code']} ? $promoBox->{"text_teaser_".$langs[$i]['code']} : '' }}</textarea>
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="caption">{{ $langs[$i]['name'] }}詳細內文(後) <span class="text-red">最多兩千字</span></label>
                                                        <textarea rows="10" class="form-control" name="text_complete_{{ $langs[$i]['code'] }}" placeholder="輸入{{ $langs[$i]['name'] }}詳細說明內文 (後)...">{{ isset($promoBox) && $promoBox->{"text_complete_".$langs[$i]['code']} ? $promoBox->{"text_complete_".$langs[$i]['code']} : '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            @endfor
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="img_url">{{ isset($promoBox) && $promoBox->img_url ? '變更圖片' : '新增圖片' }}</label>
                                                <div class="input-group">
                                                    <input type="file" id="img_url" name="img_url" class="custom-file-input {{ $errors->has('img_url') ? ' is-invalid' : '' }} mb-2" accept="image/*" required>
                                                    <label class="custom-file-label" for="img_url">{{ isset($promoBox) && $promoBox->img_url ? $promoBox->img_url : '瀏覽選擇新圖片' }}</label>
                                                </div>
                                                <div class="form-group col-4">
                                                    <img width="100%" class="img_url" src="{{ $promoBox->img_url ?? '' }}" alt="">
                                                </div>
                                            </div>
                                            <div class="row col-6">
                                                <div class="form-group col-4">
                                                    <label for="start_time"><span class="text-red">* </span>活動開始時間</label>
                                                    <input type="datetime" class="form-control {{ $errors->has('start_time') ? ' is-invalid' : '' }} datetimepicker" id="start_time" name="start_time" value="{{ $promoBox->start_time ?? old('start_time') }}" placeholder="輸入活動開始時間">
                                                    @if ($errors->has('start_time'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('start_time') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-4">
                                                    <label for="end_time"><span class="text-red">* </span>活動結束時間</label>
                                                    <input type="datetime" class="form-control {{ $errors->has('end_time') ? ' is-invalid' : '' }} datetimepicker" id="end_time" name="end_time" value="{{ $promoBox->end_time ?? old('end_time') }}" placeholder="輸入活動結束時間">
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
                                                        <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="secondary" data-on-color="primary" {{ isset($promoBox) ? $promoBox->is_on == 1 ? 'checked' : '' : 'checked' }}>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-center bg-white">
                                            @if(in_array(isset($promoBox) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                            <button type="submit" class="btn btn-primary">{{ isset($promoBox) ? '修改' : '新增' }}</button>
                                            @endif
                                            <a href="{{ route('admin.promoboxes.index') }}" class="btn btn-info">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\PromoBoxesRequest', '.myform'); !!}
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

    // 舊的image網址及屬性
    image = $('.image').attr('src');
    $('input[type=file]').change(function(x) {
        name = this.name;
        name == 'image' ? oldimg = image : '';
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
            }
        } else {
            $(this).val('');
            $('label[for=' + name + ']').html('瀏覽選擇新圖片');
            $('.' + name).attr('src', oldimg); //沒照片時還原
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
