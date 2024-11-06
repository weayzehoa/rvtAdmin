@extends('admin.layouts.master')

@section('title', 'Dashboard')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h1 class="m-0 text-dark"><img height="40" src="{{ asset('img/icarry-logo-text.png') }}"> <b>資訊看板</b></h1> --}}
                    <h1 class="m-0 text-dark"><b>資訊看板</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('dashboard') }}">Dashboard</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($data['orderNew']) }}</h3>
                        <p>新的訂單！(已付款)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <a href="{{ 'https://'.env('GATE_DOMAIN').'/orders?status=1' }}" class="small-box-footer" target="_blank">
                        請至中繼站查看明細 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($data['orderCollect']) }}</h3>
                        <p>集貨中訂單！</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-luggage-cart"></i>
                    </div>
                    <a href="{{ 'https://'.env('GATE_DOMAIN').'/orders?status=2' }}" class="small-box-footer" target="_blank">
                        請至中繼站查看明細 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3>{{ number_format($data['productWait']) }}</h3>
                        <p>待審核商品！</p>
                    </div>
                    <div class="icon">
                        <i class="fab fa-product-hunt"></i>
                    </div>
                    <a href="{{ url('products?status=0') }}" class="small-box-footer">
                        查看明細 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($data['productReplenishment']) }}</h3>
                        <p>待補貨商品！</p>
                    </div>
                    <div class="icon">
                        <i class="fab fa-product-hunt"></i>
                    </div>
                    <a href="{{ url('products?vendor_ison=1&status=1,-3&zero_quantity=yes') }}" class="small-box-footer">
                        查看明細 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($data['productNeedReplenishment']) }}</h3>
                        <p>低於安全庫存商品！</p>
                    </div>
                    <div class="icon">
                        <i class="fab fa-product-hunt"></i>
                    </div>
                    <a href="{{ url('products?vendor_ison=1&status=1,-3&low_quantity=yes') }}" class="small-box-footer">
                        查看明細 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ number_format($data['productStop']) }}</h3>
                        <p>iCarry停售中商品！</p>
                    </div>
                    <div class="icon">
                        <i class="fab fa-product-hunt"></i>
                    </div>
                    <a href="{{ url('products?status=-3&pause_reason=null') }}" class="small-box-footer">
                        查看明細 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($data['productPause']) }}</h3>
                        <p>廠商停售中商品！</p>
                    </div>
                    <div class="icon">
                        <i class="fab fa-product-hunt"></i>
                    </div>
                    <a href="{{ url('products?status=-3&pause_reason=notNull') }}" class="small-box-footer">
                        查看明細 <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
