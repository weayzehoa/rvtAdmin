@extends('admin.layouts.master')

@section('title', '團購設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1 class="m-0 text-dark"><b>團購設定</b><small> ({{ isset($groupBuying) ? '修改' : '新增' }})</small></h1>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('groupbuyings') }}">團購設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($groupBuying) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">資料設定</h3>
                </div>
                <div class="card-body">
                    @if(isset($groupBuying))
                    <form id="myform" action="{{ route('admin.groupbuyings.update', $groupBuying->id) }}" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="_method" value="PATCH">
                    @else
                    <form id="myform" action="{{ route('admin.groupbuyings.store') }}" method="POST" enctype="multipart/form-data">
                    @endif
                        @csrf
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                    <div class="form-group col-3">
                                        <label for="master_user_id"><span class="text-red">* </span>團主ID</label>
                                        <input type="text" class="form-control {{ $errors->has('master_user_id') ? ' is-invalid' : '' }}" id="master_user_id" name="master_user_id" value="{{ $groupBuying->master_user_id ?? '' }}" placeholder="請輸入團主ID" required>
                                        @if ($errors->has('master_user_id'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('master_user_id') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-7">
                                        <label for="datetime"><span class="text-red">* </span>開團日期區間:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control datepicker" id="start_date" name="start_date" placeholder="格式：2016-06-06" value="{{ isset($groupBuying) ? $groupBuying->start_date : '' }}" autocomplete="off" required>
                                            <span class="input-group-addon bg-primary">~</span>
                                            <input type="text" class="form-control datepicker" id="end_date" name="end_date" placeholder="格式：2018-06-16" value="{{ isset($groupBuying) ? $groupBuying->end_date : '' }}" autocomplete="off" required>
                                        </div>
                                    </div>
                                    <div class="form-group col-2">
                                        <label for="is_on"><span class="text-red">* </span>啟用狀態</label>
                                        <div class="input-group">
                                            <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($groupBuying) ? $groupBuying->is_on == 1 ? 'checked' : '' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="product_sold_country"><span class="text-red">* </span>銷售國家</label>
                                        <div class="input-group">
                                            <select class="form-control" name="product_sold_country" required>
                                                @foreach($countries as $country)
                                                <option value="{{ $country->name }}" {{ isset($groupBuying) && $groupBuying->product_sold_country == $country->name ? 'selected' : '' }}>{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="master_percent"><span class="text-red">* </span>團主分潤(%)</label>
                                        <input type="text" class="form-control {{ $errors->has('master_percent') ? ' is-invalid' : '' }}" id="master_percent" name="master_percent" value="{{ $groupBuying->master_percent ?? '' }}" placeholder="請輸入團主分潤%" required>
                                        @if ($errors->has('master_percent'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('master_percent') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="buyer_discount_percent"><span class="text-red">* </span>購買者折購(%)</label>
                                        <input type="text" class="form-control {{ $errors->has('buyer_discount_percent') ? ' is-invalid' : '' }}" id="buyer_discount_percent" name="buyer_discount_percent" value="{{ $groupBuying->buyer_discount_percent ?? '' }}" placeholder="請輸入購買者折扣%" required>
                                        @if ($errors->has('buyer_discount_percent'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('buyer_discount_percent') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="over_weight"><span class="text-red">* </span>開團最低重量(g)</label>
                                        <input type="text" class="form-control {{ $errors->has('over_weight') ? ' is-invalid' : '' }}" id="over_weight" name="over_weight" value="{{ $groupBuying->over_weight ?? '' }}" placeholder="請輸入開團最低重量限制(g)" required>
                                        @if ($errors->has('over_weight'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('over_weight') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-12">
                                        <label for="description"><span class="text-red">* </span>說明描述</label>
                                        <textarea max="255" rows="5" class="form-control" id="decription" name="description" placeholder="請輸入說明描述" required>{{ $groupBuying->description ?? '' }}</textarea>
                                        @if ($errors->has('description'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('description') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="row">
                                    <div class="form-group col-6">
                                        <label>Logo</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" id="logo" name="logo" class="custom-file-input {{ $errors->has('logo') ? ' is-invalid' : '' }}" accept="image/*">
                                                <label class="custom-file-label" for="logo">{{ isset($groupBuying) ? $groupBuying->logo ?? '瀏覽選擇新圖片' : '瀏覽選擇新圖片' }}</label>
                                            </div>
                                            @if(isset($groupBuying) && $groupBuying->logo)
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-sm bg-danger removeImage" value="logo"><i class="far fa-trash-alt"></i></button>
                                            </div>
                                            @endif
                                        </div>
                                        @if ($errors->has('logo'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('logo') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-6">
                                        <label>封面</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" id="cover" name="cover" class="custom-file-input {{ $errors->has('cover') ? ' is-invalid' : '' }}" accept="image/*">
                                                <label class="custom-file-label" for="cover">{{ isset($groupBuying) ? $groupBuying->cover ?? '瀏覽選擇新圖片' : '瀏覽選擇新圖片' }}</label>
                                            </div>
                                            @if(isset($groupBuying) && $groupBuying->cover)
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-sm bg-danger removeImage" value="cover"><i class="far fa-trash-alt"></i></button>
                                            </div>
                                            @endif
                                        </div>
                                        @if ($errors->has('cover'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('cover') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-6" id="logo_div">
                                        @if(isset($groupBuying) && $groupBuying->logo)
                                        <a href="{{ $groupBuying->logo }}" data-toggle="lightbox" data-title="{{ ' Logo圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                            <img width="300" class="logo" src="{{ $groupBuying->logo }}">
                                        </a>
                                        @else
                                        <img width="300" class="col-12 logo" src="">
                                        @endif
                                    </div>
                                    <div class="form-group col-6" id="cover_div">
                                        @if(isset($groupBuying) && $groupBuying->cover)
                                        <a href="{{ $groupBuying->cover }}" data-toggle="lightbox" data-title="{{ ' Cover圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                            <img width="300" class="cover" src="{{ $groupBuying->cover }}">
                                        </a>
                                        @else
                                        <img width="300" width="200" class="col-12 cover" src="">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-primary card-outline col-12 mb-2"></div>
                            <div class="form-group col-12">
                                <div class="row">
                                <div class="form-group col-2">
                                    <label for="">使用產品類別搜尋產品</label>
                                    <select id="selectByCategory" class="form-control">
                                        <option value="">產品類別</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-3">
                                    <label for="">使用商家名稱搜尋產品</label>
                                    <select id="selectByVendor" class="form-control select2bs4 select2-primary" data-dropdown-css-class="select2-primary" >
                                        <option value="">商家名稱</option>
                                        @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-3">
                                    <label for="">使用關鍵字搜尋產品</label>
                                    <input class="form-control" type="text" id="keyword" name="keyword" placeholder="輸入商家或產品名稱搜尋...">
                                </div>
                            </div>
                            </div>
                            <div class="form-group col-12">
                                <div class="row">
                                    <div class="col-5">
                                        <label>產品列表</label>
                                        <select id="productSelect" class="form-control" size="12" multiple="multiple">
                                        </select>
                                    </div>
                                    <div class="col-1">
                                        <div>
                                            <label>　</label><br><br><br>
                                            <button type="button" id="productSelect_rightAll" class="btn btn-secondary btn-block"><i class="fas fa-angle-double-right"></i></button>
                                            <button type="button" id="productSelect_rightSelected" class="btn btn-primary btn-block"><i class="fas fa-caret-right"></i></button>
                                            <button type="button" id="productSelect_leftSelected" class="btn btn-primary btn-block"><i class="fas fa-caret-left"></i></button>
                                            <button type="button" id="productSelect_leftAll" class="btn btn-secondary btn-block"><i class="fas fa-angle-double-left"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <label>已選擇產品</label>
                                        <select name="product_id[]" id="productSelect_to" class="form-control" size="12" multiple="multiple">
                                            @if(isset($groupBuying))
                                            @if($groupBuying->products)
                                            @foreach($groupBuying->products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                            @endif
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-1 align-items-middle">
                                        <label>　</label><br><br><br><br>
                                        <div class="col-12">
                                            <button type="button" id="productSelect_move_up" class="btn btn-primary"><i class="fas fa-caret-up"></i></button>
                                        </div>
                                        <br><br><br><br>
                                        <div class="col-12">
                                            <button type="button" id="productSelect_move_down" class="btn btn-primary"><i class="fas fa-caret-down"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center bg-white">
                            @if(in_array(isset($groupBuying) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                            <button type="submit" class="btn btn-primary">{{ isset($groupBuying) ? '修改' : '新增' }}</button>
                            @endif
                            <a href="{{ url('groupbuyings') }}" class="btn btn-info">
                                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                            </a>
                        </div>
                    </form>
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
{{-- Ekko Lightbox --}}
<link rel="stylesheet" href="{{ asset('vendor/ekko-lightbox/dist/ekko-lightbox.css') }}">
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
{{-- Ekko Lightbox --}}
<script src="{{ asset('vendor/ekko-lightbox/dist/ekko-lightbox.min.js') }}"></script>
{{-- multiselect --}}
<script src="{{ asset('vendor/multiselect/dist/js/multiselect.min.js') }}"></script>
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });
        $('.datepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).parents('form').submit();
            };
        });
        $('.edit-btn').click(function(e){
            $('.'+$(this).val()).toggle();
            if($(this).html() == '新增'){
                $(this).html('取消');
            }else if($(this).html() == '選擇'){
                $(this).html('取消');
            }else if($(this).html() == '取消'){
                $(this).html('新增');
            }
        });
        $('input[name=rows]').change(function(){
            if($(this).val() == 2){
                $('.checkrows2').show();
            }else{
                $('.checkrows2').hide();
            }
        });
        $('input[name=type]').change(function(){
            $('.type').hide();
            $('#'+$(this).val()).show();
            if($(this).val() == 'header'){
                $('#layoutcolumns').hide();
            }else{
                $('#layoutcolumns').show();
            }
            if($(this).val() == 'image'){
                $('#oldtextlayout').show();
            }else{
                $('#oldtextlayout').hide();
            }
            if($(this).val() != 'block' && $(this).val() != 'nowordblock'){
                $('#layoutrows').hide();
                let html = '';
                let checked = '';
                let columns = '{{ isset($groupBuying) ? $groupBuying->columns : 4}}';
                for(let i=2;i<=6;i++){
                    columns == i ? checked = 'checked' : checked = '';
                    html += '<div class="icheck-success d-inline"><input type="radio" id="columns'+i+'" name="columns" value="'+i+'" '+checked+'><label for="columns'+i+'" class="mr-2">'+i+'欄</label></div>';
                }
                $('#columns').html(html);
            }else{
                $('#layoutrows').show();
                let html = '';
                let checked = '';
                let columns = '{{ isset($groupBuying) ? $groupBuying->columns : 4}}';
                for(let i=4; i<=8; i=i+4){
                    columns == i ? checked = 'checked' : checked = '';
                    html += '<div class="icheck-success d-inline"><input type="radio" id="columns'+i+'" name="columns" value="'+i+'" '+checked+'><label for="columns'+i+'" class="mr-2">'+i+'欄</label></div>';
                }
                $('#columns').html(html);
            }
        });

        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                alwaysShowClose: true
            });
        });

        $(document).ready(function($) {
            $('#vendorSelect').multiselect({
                sort: false,
                search: {
                    left: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，不需要按Enter即可查詢" />',
                    right: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，不需要按Enter即可查詢" />',
                },
                fireSearch: function(value) {
                    return value.length > 0;
                }
            });
            $('#productSelect').multiselect({
                sort: false,
                search: {
                    left: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，查詢下方產品，不需要按Enter即可查詢" />',
                    right: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，查詢下方產品，不需要按Enter即可查詢" />',
                },
                fireSearch: function(value) {
                    return value.length > 0;
                }
            });
        });

        $('.removeBackgroundImage').click(function(){
            if(confirm('確定要移除圖片？\n請注意，該圖片尚未真正被移除，須按修改按鈕後才會真正被移除。')){
                let form = $('#myform');
                form.append($('<input type="hidden" name="background_image" value="">'));
                $('#background_image_div').remove();
                $(this).parent().remove();
                $('label[for=background_image]').html('瀏覽選擇新圖片');
            }
        });

        $('.removeImage').click(function(){
            if(confirm('確定要移除圖片？\n請注意，該圖片尚未真正被移除，須按修改按鈕後才會真正被移除。')){
                var name = $(this).val().split('_')[0];
                var id = $(this).val().split('_')[1];
                if($(this).val() == 'background_image'){
                    var form = $('#myform');
                    form.append($('<input type="hidden" name="background_image" value="">'));
                    $('#background_image_div').remove();
                    $(this).parent().remove();
                    $('label[for=background_image]').html('瀏覽選擇新圖片');
                }else if(name == 'image'){
                    var form = $('.curationImageForm_image_'+id);
                    form.append($('<input type="hidden" name="image" value="">'));
                    $('.image_' + id).attr('src', '{{ asset("img/sample_upload.png") }}');
                    $(this).parent().remove();
                    $('label[for=image_'+id+']').html('瀏覽選擇新圖片');
                }else if(name == 'imglogo' || name == 'imgcover'){
                    var form = $('.curationVendorForm_vendor_'+id);
                    if(name == 'imglogo'){
                        form.append($('<input type="hidden" name="img_logo" value="">'));
                        $('.vendor_image_logo_'+id).attr('src', '{{ asset("img/sample_upload.png") }}');
                        $('label[for=vendor_image_logo_'+id+']').html('瀏覽選擇新圖片');
                    }else if(name == 'imgcover'){
                        form.append($('<input type="hidden" name="img_cover" value="">'));
                        $('.vendor_image_cover_'+id).attr('src', '{{ asset("img/sample_upload.png") }}');
                        $('label[for=vendor_image_cover_'+id+']').html('瀏覽選擇新圖片');
                    }
                }
            }
        });

        $('#selectByCategory').change(function(){
            $('input[name=keyword]').val('');
            $('#selectByVendor').val(null).trigger('change');
            $('#selectByVendor').find('option:not(:first)').prop('selected',false);
            if($(this).val()){
                search($(this).val(),'');
            }
        });

        $('#selectByVendor').change(function(){
            $('#selectByCategory').find('option:not(:first)').prop('selected',false);
            $('input[name=keyword]').val('');
            if($(this).val()){
                search('','',$(this).val());
            }
        });

        $('.search-btn').click(function(){
            // $('#selectByVendor').val(null).trigger('change');
            $('#selectByVendor').find('option:not(:first)').prop('selected',false);
            $('#selectByCategory').find('option:not(:first)').prop('selected',false);
            let keyword = $('#keyword').val();
            if(keyword){
                search('',keyword,'');
            }
        });
        $('input[name=keyword]').keyup(function(){
            // $('#selectByVendor').val(null).trigger('change');
            $('#selectByVendor').find('option:not(:first)').prop('selected',false);
            $('#selectByCategory').find('option:not(:first)').prop('selected',false);
            if($(this).val()){
                search('',$(this).val(),'');
                $('#keyword').val($(this).val());
            }
        });

    })(jQuery);

    $('input[type=file]').change(function(x) {
        // name = this.name;
        name = this.id;
        oldimg = '{{ asset("img/sample_upload.png") }}';
        file = x.currentTarget.files;
        if (file.length >= 1) {
            // filename = checkMyImage(file);
            filename = file[0].name; //不檢查檔案直接找出檔名
            if (filename) {
                readURL(this, '.' + name);
                $('label[for=' + name + ']').html(filename);
                alert('請注意，此圖片尚未實際上傳到伺服器，待按下新增或修改按鈕才會實際上傳到伺服器。');
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

    function search(category,keyword,vendor){
        let token = '{{ csrf_token() }}';
        let selected = $('#productSelect_to').find('option');
        let country = $('select[name=product_sold_country]').val();
        let ids = [];
        for(let x=0;x<selected.length;x++){
            ids[x] = selected[x].value;
        }
        $.ajax({
            type: "post",
            url: 'getproducts',
            data: {ids: ids, category: category, keyword: keyword, vendor: vendor, country:country, _token: token },
            success: function(data) {
                var options = '';
                for(let i=0;i<data.length;i++){
                    if(data[i]['status'] == -9){
                        options +='<option value="'+data[i]['id']+'" class="text-danger text-bold bg-warning"> (已下架)'+data[i]['name']+'</option>';
                    }else{
                        options +='<option value="'+data[i]['id']+'">'+data[i]['name']+'</option>';
                    }
                }
                if(keyword){
                    $('#selectByVendor').val(null).trigger('change');
                    $('#keyword').val(keyword);
                }
                $('#productSelect').html(options);
            }
        });
    }

</script>
@endsection
