@extends('admin.layouts.master')

@section('title', '商品分類設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商品分類</b><small> ({{ isset($category) ? '修改' : '新增' }})</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('categories') }}">商品分類設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($category) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">分類資料</h3>
                </div>
                <div class="card-body">
                    @if(isset($category))
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link chinese active" href="#chinese" data-toggle="tab">繁體中文</a></li>
                        @for($i=0;$i<count($langs);$i++)
                        <li class="nav-item"><a class="nav-link lang-{{ $langs[$i]['code']}}" href="#lang-{{ $langs[$i]['code'] }}" data-toggle="tab">{{ $langs[$i]['name'] }}</a></li>
                        @endfor
                    </ul>
                    <div class="card card-primary card-outline"></div>
                    @endif
                    <div class="tab-content">
                        <div class="active tab-pane" id="chinese">
                            @if(isset($category))
                            <form id="myform" action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                                                <input type="hidden" name="_method" value="PATCH">
                            @else
                            <form id="myform" action="{{ route('admin.categories.store') }}" method="POST">
                            @endif
                                @csrf
                                <div class="row">
                                    <div class="form-group col-3">
                                        <label for="name"><span class="text-red">* </span>名稱</label>
                                        <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ old('name') ?? $category->name ?? '' }}" placeholder="請輸入名稱">
                                        @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-7">
                                        <label for="intro">簡介</label>
                                        <input type="text" class="form-control {{ $errors->has('intro') ? ' is-invalid' : '' }}" id="intro" name="intro" value="{{ old('intro') ?? $category->intro ?? '' }}" placeholder="請輸入簡介">
                                        @if ($errors->has('intro'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('intro') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-2">
                                        <label for="is_on">啟用狀態</label>
                                        <div class="input-group">
                                            <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="關閉" data-off-color="secondary" data-on-color="primary" {{ isset($category) ? $category->is_on == 1 ? 'checked' : '' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center bg-white">
                                    @if(in_array(isset($category) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                    <button type="submit" class="btn btn-primary">{{ isset($category) ? '修改' : '新增' }}</button>
                                    @endif
                                    <a href="{{ url('categories') }}" class="btn btn-info">
                                        <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                        {{-- 多國語言資料 --}}
                        @if(isset($category))
                        @for($i=0;$i<count($langs);$i++)
                        <div class="tab-pane" id="lang-{{ $langs[$i]['code'] }}">
                            <form class="myform_lang" action="{{ route('admin.categories.lang', $category->id) }}" method="POST">
                                <input type="hidden" name="lang" value="{{ $langs[$i]['code'] }}">
                                <input type="hidden" name="langId" value="{{ $langs[$i]['data']['id'] ?? '' }}">
                                <input type="hidden" name="category_id" value="{{ $category->id ?? '' }}">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-3">
                                        <label for="name"><span class="text-red">* </span>{{ $langs[$i]['name'] }}名稱</label>
                                        <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['name'] : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}名稱">
                                        @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-7">
                                        <label for="intro">{{ $langs[$i]['name'] }}簡介</label>
                                        <input type="text" class="form-control {{ $errors->has('intro') ? ' is-invalid' : '' }}" name="intro" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['intro'] : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}簡介">
                                        @if ($errors->has('intro'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('intro') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-center bg-white">
                                    @if(in_array(isset($category) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                    <button type="submit" class="btn btn-primary">{{ isset($langs[$i]['data']) ? '修改' : '新增' }}</button>
                                    @endif
                                    <a href="{{ url('categorys') }}" class="btn btn-info">
                                        <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                        @endfor
                        @endif
                    </div>
                </div>
            </div>
            @if(isset($category))
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">照片資料</h3>
                </div>
                <form class="upload" action="{{ route('admin.categories.upload', $category->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 card card-primary card-outline">
                                    <div class="card-body box-profile">
                                        <div class="text-center mb-2">
                                            <h3>LOGO</h3>
                                            <img width="100%" class="logo" src="{{ $category->logo ?? '' }}" alt="">
                                        </div>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="file" id="logo" name="logo" class="custom-file-input {{ $errors->has('logo') ? ' is-invalid' : '' }} mb-2" accept="image/*" required>
                                                <label class="custom-file-label" for="logo">瀏覽選擇新圖片</label>
                                            </div>
                                            <p>上傳後等比縮放為500x500</p>
                                        </div>
                                    </div>
                            </div>
                            <div class="col-8 card card-primary card-outline">
                                    <div class="card-body box-profile">
                                        <div class="text-center mb-2">
                                            <h3>封面圖</h3>
                                            <img width="100%" class="cover" src="{{ $category->cover ?? '' }}" alt="">
                                        </div>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="file" id="cover" name="cover" class="custom-file-input {{ $errors->has('cover') ? ' is-invalid' : '' }} mb-2" accept="image/*" required>
                                                <label class="custom-file-label" for="cover">瀏覽選擇新圖片</label>
                                            </div>
                                            <p>上傳後等比縮放為1440x760</p>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="text-center bg-white">
                            @if(in_array(isset($category) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                            <button type="submit" class="btn btn-primary">送出</button>
                            @endif
                            <a href="{{ url('categories') }}" class="btn btn-info">
                                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </section>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\CategoriesRequest', '#myform'); !!}
{{-- {!! JsValidator::formRequest('App\Http\Requests\Admin\categoriesUploadRequest', '.upload'); !!} --}}
{{-- {!! JsValidator::formRequest('App\Http\Requests\Admin\categoriesLangRequest', '.myform_lang'); !!} --}}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
    })(jQuery);

    // 舊的image網址及屬性
    cover = $('.cover').attr('src');
    logo = $('.logo').attr('src');
    $('input[type=file]').change(function(x) {
        name = this.name;
        name == 'cover' ? oldimg = cover : '';
        name == 'logo' ? oldimg = logo : '';
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
