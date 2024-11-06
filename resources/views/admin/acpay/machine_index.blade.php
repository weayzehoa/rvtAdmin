@extends('admin.layouts.master')

@section('title', '機台管理')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>機台管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('acpaymachines') }}">機台管理</a></li>
                        <li class="breadcrumb-item active">清單</li>
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
                        <div class="card-header">
                            <div class="float-left form-inline">
                                @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                                <a href="{{ route('admin.acpaymachines.create') }}" class="btn btn-sm btn-primary mr-2"><i class="fas fa-plus mr-1"></i>新增</a>
                                @endif
                                <button id="showForm" class="btn btn-sm btn-success mr-2" title="使用欄位查詢">使用欄位查詢</button>
                                @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                <form id="export" class="" action="{{ url('acpaymachines/export') }}" method="POST" class="mr-2" role="export">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info"><span><i class="fas fa-file-download mr-1"></i>匯出</span></button>
                                </form>
                                @endif
                            </div>
                            <div class="float-right">
                                <div class="input-group input-group-sm align-middle align-items-middle">
                                    <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($machines->total()) ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <form id="myForm" action="{{ url('acpaymachines') }}" method="get">
                            <div id="searchForm" class="card card-primary" style="display: none">
                                <div class="card-body">
                                    <div class="row col-8 offset-2">
                                        <div class="col-6 mt-2">
                                            <label for="id">機台編號:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="sid" name="sid" placeholder="格式：C00123" value="{{ isset($sid) && $sid ? $sid : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="vendor_name">商家名稱:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="vendor_name" name="vendor_name" placeholder="輸入商家名稱" value="{{ isset($vendor_name) && $vendor_name ? $vendor_name : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="account">帳號:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="account" name="account" placeholder="輸入帳號" value="{{ isset($account) && $account ? $account : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="shop">店名:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="shop" name="shop" placeholder="輸入店名" value="{{ isset($shop) && $shop ? $shop : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="company">公司:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="company" name="company" placeholder="輸入公司名稱" value="{{ isset($company) && $company ? $company : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">啟用狀態:</label>
                                            <select class="form-control" name="is_on">
                                                <option value="">選擇狀態</option>
                                                <option value="1" {{ isset($is_on) && $is_on == 1 ? 'selected' : '' }}>開啟</option>
                                                <option value="0" {{ isset($is_on) && $is_on == 0 ? 'selected' : '' }}>關閉</option>
                                            </select>
                                        </div>
                                        <div class="col-12 text-center mt-2">
                                            <button type="submit" class="btn btn-primary">查詢</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="card-body table-responsive">
                            @if(!empty($machines))
                            <table class="table table-hover table-sm mb-3">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="10%">機台編號</th>
                                        <th class="text-left" width="20%">店名</th>
                                        <th class="text-left" width="10%">帳號</th>
                                        <th class="text-left" width="25%">商家名稱</th>
                                        <th class="text-left" width="25%">公司</th>
                                        <th class="text-center" width="10%">狀態</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($machines as $machine)
                                    <tr>
                                        <td class="text-left">
                                            <a href="{{ route('admin.acpaymachines.show', $machine->id) }}">
                                                {{ "C".str_pad($machine->id,5,'0',STR_PAD_LEFT) }}
                                            </a>
                                        </td>
                                        <td class="text-left">{{ $machine->name }}</td>
                                        <td class="text-left">{{ $machine->account->account }}</td>
                                        <td class="text-left">{{ $machine->vendor->name }}</td>
                                        <td class="text-left">{{ $machine->vendor->company }}</td>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ url('acpaymachines/active/' . $machine->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $machine->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="啟用" data-off-text="關閉" data-off-color="secondary" data-on-color="primary" {{ isset($machine) ? $machine->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <h3>無資料</h3>
                            @endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <div class="form-group">
                                        <span class="badge badge-primary text-lg ml-1">總筆數：{{ number_format($machines->total()) ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $machines->appends($appends)->render() }}
                                @else
                                {{ !empty($machines) ? $machines->render() : '' }}
                                @endif
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
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('input[data-bootstrap-switch]').on('switchChange.bootstrapSwitch', function (event, state) {
            $(this).parents('form').submit();
        });

        $('#showForm').click(function(){
            let text = $('#showForm').html();
            $('#searchForm').toggle();
            text == '使用欄位查詢' ? $('#showForm').html('隱藏欄位查詢') : $('#showForm').html('使用欄位查詢');
        });
    })(jQuery);
</script>
@endsection
