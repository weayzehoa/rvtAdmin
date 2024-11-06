@extends('admin.layouts.master')

@section('title', '優惠活動設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>優惠活動設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('promoboxes') }}">優惠活動設定</a></li>
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
                            <div class="row">
                                <div class="col-5">
                                    @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                                    <a href="{{ route('admin.promoboxes.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i>新增</a>
                                    @endif
                                </div>
                                <div class="col-7">
                                    <div class=" float-right">
                                        <div class="input-group input-group-sm align-middle align-items-middle">
                                            <span class="badge badge-purple text-lg mr-2">總筆數：{{ number_format($promoBoxes->total()) ?? 0 }}</span>
                                            <form action="" method="GET" class="form-inline" role="search">
                                                <span class="badge badge-primary text-sm mr-2">快搜 <i class="fas fa-hand-point-right"></i></span>
                                            </form>
                                            <form action="{{ url('promoboxes') }}" method="GET" class="form-inline" role="search">
                                                <input type="search" class="form-control form-control-sm" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="輸入關鍵字搜尋" title="搜尋標題及內文" aria-label="Search">
                                                <button type="submit" class="btn btn-sm btn-info" title="搜尋標題及內文" >
                                                    <i class="fas fa-search"></i>
                                                    搜尋
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(count($promoBoxes) > 0)
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left align-middle" width="20%">活動標題</th>
                                        <th class="text-left align-middle" width="35%">介紹文(前)</th>
                                        <th class="text-center align-middle" width="15%">開始時間</th>
                                        <th class="text-center align-middle" width="15%">結束時間</th>
                                        <th class="text-center align-middle" width="5%">狀態</th>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <th class="text-center align-middle" width="10%">啟用</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($promoBoxes as $promoBox)
                                    <tr>
                                        <td class="text-left align-middle">
                                            <a href="{{ route('admin.promoboxes.show',$promoBox->id) }}">{{ $promoBox->title }}</a>
                                        </td>
                                        <td class="text-left align-middle">{{ $promoBox->text_teaser }}</td>
                                        <td class="text-center align-middle">{{ $promoBox->start_time }}</td>
                                        <td class="text-center align-middle">{{ $promoBox->end_time }}</td>
                                        <td class="text-center align-middle">
                                            @if($promoBox->is_on == 1)
                                                @if(empty($promoBox->start_time) && empty($promoBox->end_time))
                                                <span class="text-success text-bold">活動中</span>
                                                @elseif(date('Y-m-d H:i:s') < $promoBox->start_time)
                                                    <span class="text-danger text-bold">活動尚未開始</span>
                                                @elseif(date('Y-m-d H:i:s') > $promoBox->end_time)
                                                    <span class="text-secondary">已過期</span>
                                                @elseif(date('Y-m-d H:i:s') > $promoBox->start_time && date('Y-m-d H:i:s') < $promoBox->end_time)
                                                    <span class="text-success text-bold">活動中</span>
                                                @endif
                                            @else
                                                <span class="text-secondary text-bold">已停用</span>
                                            @endif
                                        </td>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ url('promoboxes/active/' . $promoBox->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $promoBox->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="secondary" data-on-color="primary" {{ isset($promoBox) ? $promoBox->is_on == 1 ? 'checked' : '' : '' }}>
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
                                    <form action="{{ url('invoices') }}" method="GET" class="form-inline" role="search">
                                        @if(isset($keyword))<input type="hidden" name="keyword" value="{!! $keyword ?? '' !!}">@endif
                                        @if(isset($status))<input type="hidden" name="status" value="{!! $status ?? '' !!}">@endif
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                        </select>
                                        <span class="badge badge-primary text-lg ml-1">總筆數：{{ number_format($promoBoxes->total()) ?? 0 }}</span>
                                    </form>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $promoBoxes->appends($appends)->render() }}
                                @else
                                {{ $promoBoxes->render() }}
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
    })(jQuery);
</script>
@endsection
