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
                    <h1 class="m-0 text-dark"><b>商品貨號轉換</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('digiwin/ec2no') }}">商品貨號轉換</a></li>
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
                            <h5>此功能可轉換商品貨號。</h5>
                            <span>A欄：EC或BOM貨號</span><br>
                            <span>B欄：鼎新貨號</span>
                            <div class="col-4">
                                <form  id="ec2noImportForm" action="{{ url('digiwin/ec2noImport') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" name="filename" class="custom-file-input" id="ec2noImportFile">
                                        <label class="custom-file-label" for="ec2noImportFile" id="ec2noImportLabel">選擇檔案</label>
                                    </div>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-primary" id="ec2noImportBtn">匯入</span>
                                    </div>
                                </div>
                                </form>
                            </div>
                            <a href="{{ asset('sample/商品貨號轉換範例.xlsx') }}">範例檔下載</a>
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
    $('#ec2noImportBtn').click(function (e) {
        $('#ec2noImportForm').submit();
        $('#ec2noImportFile').val('');
        $('#ec2noImportLabel').html('選擇檔案');
    });
</script>
@endsection
