@extends('admin.layouts.master')

@section('title', '發送簡訊')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>發送簡訊</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('sms') }}">簡訊管理</a></li>
                        <li class="breadcrumb-item active">發送簡訊</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">發送簡訊</h3>
                </div>
                <form id="myform" action="{{ route('admin.sendSMS.send') }}" method="POST" role="form">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>簡訊服務商</label><br>
                            <div class="icheck-primary d-inline mr-2">
                                <input type="radio" id="vendor" name="vendor" value="" checked>
                                <label for="vendor">系統預設</label>
                            </div>
                            <div class="icheck-primary d-inline mr-2">
                                <input type="radio" id="vendor1" name="vendor" value="twilio">
                                <label for="vendor1">Twilio</label>
                            </div>
                            {{-- <div class="icheck-primary d-inline mr-2">
                                <input type="radio" id="vendor2" name="vendor" value="nexmo" disabled>
                                <label for="vendor2">Nexmo</label>
                            </div> --}}
                            <div class="icheck-primary d-inline mr-2">
                                <input type="radio" id="vendor3" name="vendor" value="aws">
                                <label for="vendor3">AWS</label>
                            </div>
                            <div class="icheck-primary d-inline mr-2">
                                <input type="radio" id="vendor4" name="vendor" value="mitake" >
                                <label for="vendor4">台灣三竹</label>
                            </div>
                            <div class="icheck-primary d-inline mr-2">
                                <input type="radio" id="vendor5" name="vendor" value="alibaba" disabled>
                                <label for="vendor5">中國阿里巴巴</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">To:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input class="form-control {{ $errors->has('phones') ? ' is-invalid' : '' }}" id="phones" name="phones" placeholder="輸入行動電話號碼(含+號及國碼)，多筆電話請用逗號分開。 EX: +886987654321" autocomplete="off">
                                @if ($errors->has('phones'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('phones') }}</strong>
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label>內容</label>
                            <input type="text" class="form-control {{ $errors->has('content') ? ' is-invalid' : '' }} " id="content" name="content" placeholder="輸入訊息內容" autocomplete="off" onkeyup="inputTextCount(this);">
                            <h4>限制 75 個字，已使用 <span class="text-danger"> 0 </span> 個字</h4>
                            @if ($errors->has('content'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('content') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button type="submit" class="btn btn-primary">發送</button>
                    </div>
                </form>
            </div>
        </div>
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\SendSMSRequest', '#myform'); !!}
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
    })(jQuery);

    var inputTextCount = function(o) {
        $(o).next().find("span").html($(o).val().toString().length);
    };
</script>
@endsection
