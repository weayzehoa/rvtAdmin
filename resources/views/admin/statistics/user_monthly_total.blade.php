@extends('admin.layouts.master')

@section('title', '註冊人數統計')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>註冊人數統計</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('usermonthlytotal') }}">註冊人數統計</a></li>
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
                            <div class="float-left">
                                已驗證註冊人數： <span class="badge badge-primary">{{ number_format($totalUsers) }}</span>
                            </div>
                            <div class="float-right">
                                <form action="{{ url('usermonthlytotal') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        <select class="form-control form-control-sm" name="year" onchange="submit(this)">
                                            <option value="">選擇年份</option>
                                            @foreach($years as $y)
                                            <option value="{{ $y->yyyy }}" {{ !empty($year) && $year == $y->yyyy ? 'selected' : '' }}>{{ $y->yyyy }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group-sm">
                                        <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                            <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="50%">年-月</th>
                                        <th class="text-right" width="25%">註冊人數</th>
                                        <th class="text-left" width="25%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                    <tr>
                                        <td class="text-center align-middle">{{ $user->yyyymm }}</td>
                                        <td class="text-right align-middle">{{ number_format($user->total) }}</td>
                                        <td class="text-right align-middle"></td>
                                    </tr>
                                    @endforeach
                                    @if(!empty($year))
                                    <tr>
                                        <td class="text-center align-middle">{{ $year }}年</td>
                                        <td class="text-right align-middle">{{ number_format($yearTotal) }}</td>
                                        <td class="text-right align-middle"></td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <div class="form-group">
                                    <form action="{{ url('countries') }}" method="GET" class="form-inline" role="search">
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $users->appends($appends)->render() }}
                                @else
                                {{ $users->render() }}
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

        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).parents('form').submit();
            };
        });
    })(jQuery);
</script>
@endsection
