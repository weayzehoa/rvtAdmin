@extends('admin.layouts.master')

@section('title', '國家資料設定')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>國家資料設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('countries') }}">國家資料設定</a></li>
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
                                <div class="input-group">
                                    <div class="input-group-append">
                                        @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                                        <a href="{{ route('admin.countries.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i>新增</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="float-right">
                                <form action="{{ url('countries') }}" method="GET" class="form-inline" role="search">
                                    <span class="right badge badge-primary mr-1">{{ $keyword ? '搜尋到' : '全部共' }} {{ $totals }} 筆</span> 選擇：
                                    <div class="form-group-sm">
                                        <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                            <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                        </select>
                                        <input type="search" class="form-control form-control-sm" name="keyword" value="{{ isset($keyword) ? $keyword : '' }}" placeholder="輸入關鍵字搜尋" title="搜尋所有欄位" aria-label="Search">
                                        <button type="submit" class="btn btn-sm btn-info" title="搜尋所有欄位">
                                            <i class="fas fa-search"></i>
                                            搜尋
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="5%">順序</th>
                                        <th class="text-left" width="15%">國家名稱</th>
                                        <th class="text-left" width="20%">英文名稱</th>
                                        <th class="text-left" width="20%">日文名稱</th>
                                        <th class="text-left" width="5%">代碼</th>
                                        <th class="text-left" width="10%">國際碼</th>
                                        <th class="text-left" width="10%">簡訊供應商</th>
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="10%">排序</th>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">刪除</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($countries as $country)
                                    <tr>
                                        <td class="text-center align-middle">{{ $country->sort }}</td>
                                        <td class="text-left align-middle">
                                            <a href="{{ route('admin.countries.show', $country->id ) }}">{{ $country->name }}</a>
                                        </td>
                                        <td class="text-left align-middle">{!! $country->name_en !!}</td>
                                        <td class="text-left align-middle">{!! $country->name_jp !!}</td>
                                        <td class="text-left align-middle">{!! $country->lang !!}</td>
                                        <td class="text-left align-middle">{!! $country->code !!}</td>
                                        <td class="text-left align-middle">
                                            @if($country->sms_vendor)
                                            @if($country->sms_vendor == 'alibaba')
                                                Alibaba (阿里巴巴)
                                            @elseif($country->sms_vendor == 'mitake')
                                                Mitake (三竹資訊)
                                            @else
                                            {{ $country->sms_vendor }}
                                            @endif
                                            @else
                                            系統預設
                                            @endif
                                        </td>
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            @if($country->sort != 1)
                                            <a href="{{ url('countries/sortup/' . $country->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                            </a>
                                            @endif
                                            @if($country->sort != count($countries))
                                            <a href="{{ url('countries/sortdown/' . $country->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                            </a>
                                            @endif
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.countries.destroy', $country->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <div class="form-group">
                                    <form action="{{ url('countries') }}" method="GET" class="form-inline" role="search">
                                        <input type="hidden" name="is_on" value="{{ $is_on ?? '' }}">
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="15" {{ $list == 15 ? 'selected' : '' }}>每頁 15 筆</option>
                                            <option value="30" {{ $list == 30 ? 'selected' : '' }}>每頁 30 筆</option>
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                        </select>
                                        <input type="hidden" name="keyword" value="{{ $keyword ?? '' }}">
                                    </form>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($list) || isset($keyword))
                                @isset($list)
                                @isset($keyword)
                                {{ $countries->appends(['keyword' => $keyword, 'list' => $list])->render() }}
                                @else
                                {{ $countries->appends(['list' => $list])->render() }}
                                @endisset
                                @else
                                {{ $countries->appends(['keyword' => $keyword])->render() }}
                                @endisset
                                @else
                                {{ $countries->render() }}
                                @endisset
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
