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
                        <li class="breadcrumb-item active"><a href="{{ url('searchtitles') }}">優惠活動設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($searchTitle) ? '修改' : '新增' }}</li>
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
                                    @if(isset($searchTitle))
                                    <form class="myform" action="{{ route('admin.searchtitles.update', $searchTitle->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PATCH">
                                    @else
                                    <form class="myform" action="{{ route('admin.searchtitles.store') }}" method="POST">
                                    @endif
                                        @csrf
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="code"><span class="text-red">* </span>活動標題</label>
                                                <input type="text" class="form-control {{ $errors->has('title') ? ' is-invalid' : '' }}" id="title" name="title" value="{{ $searchTitle->title ?? old('title') }}" placeholder="輸入活動標題">
                                                @if ($errors->has('title'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('title') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                            <div class="row col-6">
                                                <div class="form-group col-4">
                                                    <label for="start_time"><span class="text-red">* </span>活動開始時間</label>
                                                    <input type="datetime" class="form-control {{ $errors->has('start_time') ? ' is-invalid' : '' }} datetimepicker" id="start_time" name="start_time" value="{{ $searchTitle->start_time ?? old('start_time') }}" placeholder="輸入活動開始時間">
                                                    @if ($errors->has('start_time'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('start_time') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-4">
                                                    <label for="end_time"><span class="text-red">* </span>活動結束時間</label>
                                                    <input type="datetime" class="form-control {{ $errors->has('end_time') ? ' is-invalid' : '' }} datetimepicker" id="end_time" name="end_time" value="{{ $searchTitle->end_time ?? old('end_time') }}" placeholder="輸入活動結束時間" autocomplete="off">
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
                                                        <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="secondary" data-on-color="primary" {{ isset($searchTitle) ? $searchTitle->is_on == 1 ? 'checked' : '' : 'checked' }} autocomplete="off">
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-center bg-white">
                                            @if(in_array(isset($searchTitle) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                            <button type="submit" class="btn btn-primary">{{ isset($searchTitle) ? '修改' : '新增' }}</button>
                                            @endif
                                            <a href="{{ route('admin.searchtitles.index') }}" class="btn btn-info">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\SearchTitleRequest', '.myform'); !!}
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
</script>
@endsection
