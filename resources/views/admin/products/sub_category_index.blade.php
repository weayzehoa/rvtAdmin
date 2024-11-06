@extends('admin.layouts.master')

@section('title', '商品次分類設定')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商品次分類設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('subCategories') }}">商品次分類設定</a></li>
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
                                        <a href="{{ route('admin.subCategories.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i>新增分類</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="float-right">
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="8%">次分類ID</th>
                                        <th class="text-left" width="18%">次分類名稱</th>
                                        <th class="text-center" width="8%">主分類ID</th>
                                        <th class="text-left" width="18%">主分類名稱</th>
                                        <th class="text-left" width="28%">簡介</th>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="10%">啟用狀態</th>
                                        @endif
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="10%">排序</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subCategories as $category)
                                    <tr>
                                        <td class="text-center align-middle">
                                            {{ $category->id }}
                                        </td>
                                        <td class="text-left align-middle">
                                            <a href="{{ route('admin.subCategories.show', $category->id ) }}">{{ $category->name }}</a>
                                        </td>
                                        <td class="text-center align-middle">{{ $category->mainCate->id }}</td>
                                        <td class="text-left align-middle">{{ $category->mainCate->name }}</td>
                                        <td class="text-left align-middle">{{ $category->intro }}</td>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            <form action="{{ url('subCategories/active/' . $category->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="is_on" value="{{ $category->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($category) ? $category->is_on == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                        </td>
                                        @endif
                                        @if(in_array($menuCode.'S',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            @if($category->sort_id != 1)
                                            <a href="{{ url('subCategories/sortup/' . $category->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                            </a>
                                            @endif
                                            @if($category->sort_id != count($subCategories))
                                            <a href="{{ url('subCategories/sortdown/' . $category->id) }}" class="text-navy">
                                                <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                            </a>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="float-left">
                                <span class="badge badge-purple text-lg mr-2">總筆數：{{ $subCategories->total() != 0 ? number_format($subCategories->total()) : 0 }}</span>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $subCategories->appends($appends)->render() }}
                                @else
                                {{ $subCategories->render() }}
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
