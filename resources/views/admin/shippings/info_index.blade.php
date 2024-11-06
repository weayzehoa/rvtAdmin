@extends('admin.layouts.master')

@section('title', '渠道出貨資訊')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>渠道出貨資訊</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('shippinginfo') }}">渠道出貨資訊</a></li>
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
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-left" width="15%">時間</th>
                                        <th class="text-left" width="85%">訊息內容</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($serviceMessages as $serviceMessage)
                                    <tr>
                                        <td class="text-left align-middle">
                                            {{ $serviceMessage->create_time }}
                                        </td>
                                        <td class="text-left align-middle">
                                            {{ $serviceMessage->admin->name }}：{{ $serviceMessage->message }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('css')
@endsection

@section('script')
@endsection

@section('CustomScript')
@endsection
