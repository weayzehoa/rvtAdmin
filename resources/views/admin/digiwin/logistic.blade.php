@extends('admin.layouts.master')

@section('title', '後台選單管理')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>物流單號匯入匯出</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('digiwin/logistic') }}">物流單號匯入匯出</a></li>
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
                        <div class="card-body">
                            <h5>此功能可透過 iCarry 、各渠道訂單編號撈出已於 iCarry 後台登記的物流公司與物流訂單。</h5>
                            <div class="col-4">
                                <form  id="logisticImportForm" action="{{ url('digiwin/logisticImport') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" name="filename" class="custom-file-input" id="logisticImportFile">
                                        <label class="custom-file-label" for="logisticImportFile" id="logisticImportLabel">選擇檔案</label>
                                    </div>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-primary" id="logisticImportBtn">匯入</span>
                                    </div>
                                </div>
                                </form>
                            </div>
                            <a href="{{ asset('sample/鼎新物流單號匯入匯出範例.xlsx') }}">範例檔下載</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
@endsection

@section('script')
<script src="{{ asset('vendor/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    $(document).ready(function () {
        bsCustomFileInput.init()
    })
    $('#logisticImportBtn').click(function (e) {
        $('#logisticImportForm').submit();
        $('#logisticImportFile').val('');
        $('#logisticImportLabel').html('選擇檔案');
    });
</script>
@endsection
