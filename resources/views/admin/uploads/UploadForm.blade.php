@extends('admin.layouts.master')

@section('title', '圖片上傳測試')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>圖片上傳</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('images') }}">圖片管理</a></li>
                        <li class="breadcrumb-item active">圖片上傳</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">圖片上傳至AWS S3測試</h3>
                        </div>
                        <div class="text-center">
                            <img class="img-fluid myimage" src="{{ asset('img/sample_upload.png') }}" alt="User profile picture">
                        </div>
                        <form id="myform" action="{{ route('admin.uploads.imageUpload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="image">選擇圖片</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" id="image" name="image" class="custom-file-input {{ $errors->has('image') ? ' is-invalid' : '' }}" accept="image/*" >
                                            <label class="custom-file-label" for="image">瀏覽選擇圖片</label>
                                        </div>
                                    </div>
                                </div>
                                @if ($errors->has('image'))
                                <div>
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $errors->first('image') }}</strong>
                                    </span>
                                </div>
                                @endif
                            </div>
                            @if(in_array($menuCode.'E' , explode(',',Auth::user()->power)))
                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-primary">送出</button>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
                @if(isset($imgUrl))
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">上傳的圖片</h3>
                        </div>
                        <div class="text-center">
                            <img class="img-fluid" src="{{ $imgUrl }}" alt="User profile picture">
                            <h2>原始檔案大小：{{ number_format($ofileSize) }}</h2>
                            <h2>原始檔案尺寸：{{ $ofileDim }}</h2>
                            <h2>轉換後尺寸：{{ $newFileDim }}</h2>
                            <h2>原始檔案格式：{{ $ext }}</h2>
                            <h2>使用Spatie套件執行時間：{{ round($seTime,3) }}</h2>
                            <h2>使用Spatie套件轉換後檔案大小：{{ number_format($spatieSize) }}</h2>
                            <h2>使用tinyjpg的API執行時間：{{ round($teTime,3) }}</h2>
                            <h2>使用tinyjpg的API轉換後檔案大小：{{ number_format($tinifySize) }}</h2>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@section('css')
@endsection

@section('script')
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UploadRequest', '#myform'); !!}
@endsection

@section('CustomScript')
{{-- 這邊放置 Script 程式用 (在頁面下方) --}}
<script>
    imgclass = '.myimage'; //img的class名稱
    myimage = $(imgclass).attr('src');
    $('input[name=image]').change(function(x) {
        file = x.currentTarget.files;
        if (file.length >= 1) {
            filename = checkMyImage(file);
            // console.log(filename);
            if (filename) {
                readURL(this, imgclass);
                $('label[for=image]').html(filename);
            } else {
                $(this).val('');
                $('label[for=image]').html('瀏覽選擇新圖片');
                $(".myimage").attr('src', myimage); //沒照片時還原
            }
        } else {
            $(this).val('');
            $('label[for=image]').html('瀏覽選擇新圖片');
            $(".myimage").attr('src', myimage); //沒照片時還原
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
