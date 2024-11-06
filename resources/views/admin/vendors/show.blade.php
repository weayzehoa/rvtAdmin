@extends('admin.layouts.master')

@section('title', '商家管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>商家管理</b><small> ({{ isset($vendor) ? '修改' : '新增' }})</small></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('vendors') }}">商家管理</a></li>
                        <li class="breadcrumb-item active">{{ isset($vendor) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <nav class="w-100">
                    <div class="nav nav-tabs" id="vendor-tab" role="tablist">
                        <a class="nav-item nav-link active" id="vendor-desc-tab" data-toggle="tab" href="#vendor-desc" role="tab" aria-controls="vendor-desc" aria-selected="true">資料</a>
                        @if(in_array($menuCode['Shops'],explode(',',Auth::user()->power)))
                        {{-- <a class="nav-item nav-link" id="vendor-shop-tab" data-toggle="tab" href="#vendor-shop" role="tab" aria-controls="vendor-shop" aria-selected="false">分店</a> --}}
                        @endif
                        @if(in_array($menuCode['Accounts'],explode(',',Auth::user()->power)))
                        <a class="nav-item nav-link" id="vendor-account-tab" data-toggle="tab" href="#vendor-account" role="tab" aria-controls="vendor-account" aria-selected="false">帳號</a>
                        @endif
                        @if(in_array($menuCode['Products'],explode(',',Auth::user()->power)))
                        <a class="nav-item nav-link" id="vendor-product-tab" data-toggle="tab" href="#vendor-product" role="tab" aria-controls="vendor-product" aria-selected="false">商品</a>
                        @endif
                        {{-- <a class="nav-item nav-link" id="vendor-order-tab" data-toggle="tab" href="#vendor-order" role="tab" aria-controls="vendor-order" aria-selected="false">訂單</a> --}}
                    </div>
                </nav>
                <div class="tab-content p-3" id="nav-tabContent">
                    <div class="tab-pane fade {{ Session::get('vendorAccountShow') || Session::get('vendorShopShow') ? '' : 'show active' }}" id="vendor-desc" role="tabpanel" aria-labelledby="vendor-desc-tab">
                        {{-- <div class=" card-primary card-outline"> --}}
                            @if(isset($vendor))
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#chinese" data-toggle="tab">繁體中文</a></li>
                                @for($i=0;$i<count($langs);$i++)
                                <li class="nav-item"><a class="nav-link" href="#{{ $langs[$i]['code'] }}" data-toggle="tab">{{ $langs[$i]['name'] }}</a></li>
                                @endfor
                            </ul>
                            @endif
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">商家資料</h3>
                                </div>
                                @if(isset($vendor))
                                <form id="myform" action="{{ route('admin.vendors.update', $vendor->id) }}" method="POST">
                                    <input type="hidden" name="_method" value="PATCH">
                                @else
                                <form id="myform" action="{{ route('admin.vendors.store') }}" method="POST">
                                @endif
                                    @csrf
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="active tab-pane" id="chinese">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="row">
                                                            <div class="form-group col-6">
                                                                <label for="name"><span class="text-red">* </span>店名或品牌</label>
                                                                <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $vendor->name ?? '' }}" placeholder="店名或品牌">
                                                                @if ($errors->has('name'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('name') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="company"><span class="text-red">* </span>公司名稱</label>
                                                                <input type="text" class="form-control {{ $errors->has('company') ? ' is-invalid' : '' }}" id="company" name="company" value="{{ $vendor->company ?? '' }}" placeholder="公司名稱">
                                                                @if ($errors->has('company'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('company') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="VAT_number"><span class="text-red">* </span>公司統編</label>
                                                                <input type="number" class="form-control {{ $errors->has('VAT_number') ? ' is-invalid' : '' }}" id="VAT_number" name="VAT_number" value="{{ $vendor->VAT_number ?? '' }}" placeholder="公司統一編號">
                                                                @if ($errors->has('VAT_number'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('VAT_number') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="boss"><span class="text-red">* </span>負責人</label>
                                                                <input type="text" class="form-control {{ $errors->has('boss') ? ' is-invalid' : '' }}" id="boss" name="boss" value="{{ $vendor->boss ?? '' }}" placeholder="負責人名字">
                                                                @if ($errors->has('boss'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('boss') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="contact_person"><span class="text-red">* </span>聯絡人 <a href="javascript:$('#contact_person').val($('#boss').val());void(0);" class=" btn-link"> (同負責人) </a></label>
                                                                <input type="text" class="form-control {{ $errors->has('contact_person') ? ' is-invalid' : '' }}" id="contact_person" name="contact_person" value="{{ $vendor->contact_person ?? '' }}" placeholder="聯絡人姓名">
                                                                @if ($errors->has('contact_person'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('contact_person') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="tel"><span class="text-red">* </span>電話</label>
                                                                <input type="text" class="form-control {{ $errors->has('tel') ? ' is-invalid' : '' }}" id="tel" name="tel" value="{{ $vendor->tel ?? '' }}" placeholder="聯絡人電話號碼">
                                                                @if ($errors->has('tel'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('tel') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="fax">傳真</label>
                                                                <input type="text" class="form-control {{ $errors->has('fax') ? ' is-invalid' : '' }}" id="fax" name="fax" value="{{ $vendor->fax ?? '' }}" placeholder="傳真號碼">
                                                                @if ($errors->has('fax'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('fax') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="email"><span class="text-red">* </span>電子信箱 (請用逗號 , 或分號 ; 隔開)</label>
                                                                <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" id="email" name="email" value="{{ $vendor->email ?? '' }}" placeholder="聯絡人電子信箱" required>
                                                                @if ($errors->has('email'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('email') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="notify_email"><span class="text-red">* </span>採購通知信箱 (請用逗號 , 或分號 ; 隔開)</label>
                                                                <input type="email" class="form-control {{ $errors->has('notify_email') ? ' is-invalid' : '' }}" id="notify_email" name="notify_email" value="{{ $vendor->notify_email ?? '' }}" placeholder="採購通知電子信箱" required>
                                                                @if ($errors->has('notify_email'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('notify_email') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="bill_email"><span class="text-red">* </span>對帳通知信箱 (請用逗號 , 或分號 ; 隔開)</label>
                                                                <input type="email" class="form-control {{ $errors->has('bill_email') ? ' is-invalid' : '' }}" id="bill_email" name="bill_email" value="{{ $vendor->bill_email ?? '' }}" placeholder="對帳通知電子信箱" required>
                                                                @if ($errors->has('bill_email'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('bill_email') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>

                                                            <div class="form-group col-6">
                                                                <label for="address"><span class="text-red">* </span>地址</label>
                                                                <input type="text" class="form-control {{ $errors->has('address') ? ' is-invalid' : '' }}" id="address" name="address" value="{{ $vendor->address ?? '' }}" placeholder="公司地址">
                                                                @if ($errors->has('address'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('address') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="factory_address"><span class="text-red">* </span>工廠地址(物流收貨地址) <a href="javascript:$('#factory_address').val($('#address').val());void(0);" class=" btn-link"> (點我套用同地址) </a></label>
                                                                <input type="text" class="form-control {{ $errors->has('factory_address') ? ' is-invalid' : '' }}" id="factory_address" name="factory_address" value="{{ $vendor->factory_address ?? '' }}" placeholder="工廠地址(發貨地址/物流收貨地址)">
                                                                @if ($errors->has('factory_address'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('factory_address') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-3">
                                                                <label for="is_on">商家狀態</label>
                                                                <div class="input-group">
                                                                    <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="關閉" data-off-color="secondary" data-on-color="primary" {{ isset($vendor) ? $vendor->is_on == 1 ? 'checked' : '' : '' }}>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-3">
                                                                <label for="use_sf">使用順豐運單取號</label>
                                                                <div class="input-group">
                                                                    <input type="checkbox" name="use_sf" value="1" data-bootstrap-switch data-on-text="是" data-off-text="否" data-off-color="secondary" data-on-color="primary" {{ isset($vendor) ? $vendor->use_sf == 1 ? 'checked' : '' : '' }}>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="shipping_self">海外自行出貨商家<span class="text-red"> (非管理人員請勿開啟)</span></label>
                                                                <div class="input-group">
                                                                    <input type="checkbox" name="shipping_self" value="1" data-bootstrap-switch data-on-text="是" data-off-text="否" data-off-color="secondary" data-on-color="primary" {{ isset($vendor) ? $vendor->shipping_self == 1 ? 'checked' : '' : '' }}>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-12">
                                                                <label for="description">服務費</label>
                                                                <div class="row">
                                                                    @foreach($serviceFees as $serviceFee)
                                                                    <div class="col-3">
                                                                        <div class="input-group input-group-sm">
                                                                            <div class="input-group-prepend">
                                                                                <span class="input-group-text">
                                                                                    {{ $serviceFee->name }}
                                                                                </span>
                                                                            </div>
                                                                            <input type="hidden" name="service_fee[name][]" value="{{ $serviceFee->name }}">
                                                                            <input type="number" class="form-control" id="service_fee{{ $loop->iteration }}" name="service_fee[percent][]" value="{{ isset($serviceFees) ? $serviceFee->percent ? $serviceFee->percent : '0' : '0' }}" placeholder="商家補貼運費(%)">
                                                                            <div class="input-group-append">
                                                                                <div class="input-group-text"><i class="fas fa-percent"></i></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="row">
                                                            <div class="form-group col-12">
                                                                <label for="categories"><span class="text-red">* </span>設定分類</label>
                                                                <div class="row">
                                                                    @foreach($categories as $category)
                                                                    <div class="col-3">
                                                                        <input type="checkbox" id="cachk{{ $category->id }}" name="categories[]" value="{{ $category->id }}" {{ isset($vendor) ? in_array($category->id,explode(',',$vendor->categories)) ? 'checked' : '' : '' }}><span> {{ $category->name }}</span>
                                                                        <br>　<span class="text-purple text-sm">{{ $category->intro }}</span>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                                @if ($errors->has('categories'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('categories') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-4">
                                                                <label for="product_sold_country"><span class="text-red">* </span>發貨地區</label>
                                                                <select class="form-control{{ $errors->has('product_sold_country') ? ' is-invalid' : '' }}" id="product_sold_country" name="product_sold_country">
                                                                    <option value="">請選擇發貨地區</option>
                                                                    <option value="台灣" {{ isset($vendor) ? $vendor->product_sold_country == '台灣' ? 'selected' : '' : '' }}>台灣</option>
                                                                    <option value="日本" {{ isset($vendor) ? $vendor->product_sold_country == '日本' ? 'selected' : '' : '' }}>日本</option>
                                                                </select>
                                                                @if ($errors->has('product_sold_country'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('product_sold_country') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-4">
                                                                <label for="shipping_setup"><span class="text-red">* </span>免運門檻 <a href="javascript:$('#shipping_setup').val(0);void(0);" class=" btn-link"> (免運) </a> <a href="javascript:$('#shipping_setup').val(99999999);void(0);" class="btn-link">(不提供)</a></label>
                                                                <input type="number" class="form-control {{ $errors->has('shipping_setup') ? ' is-invalid' : '' }}" id="shipping_setup" name="shipping_setup" value="{{ $vendor->shipping_setup ?? '99999999' }}" placeholder="設定免運門檻">
                                                                @if ($errors->has('shipping_setup'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('shipping_setup') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-4">
                                                                <label for="shipping_verdor_percent"><span class="text-red">* </span>商家補貼運費</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">
                                                                            <i class="fas fa-truck"></i>
                                                                        </span>
                                                                    </div>
                                                                    <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="shipping_verdor_percent" name="shipping_verdor_percent" value="{{ $vendor->shipping_verdor_percent ?? '0' }}" placeholder="商家補貼運費(%)">
                                                                    <div class="input-group-append">
                                                                        <div class="input-group-text"><i class="fas fa-percent"></i></div>
                                                                    </div>
                                                                </div>
                                                                @if ($errors->has('shipping_verdor_percent'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('shipping_verdor_percent') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            {{-- <div class="form-group col-6">
                                                                <label for="summary">簡介</label>
                                                                <textarea rows="9" class="form-control {{ $errors->has('summary') ? ' is-invalid' : '' }}" name="summary" placeholder="簡短介紹...">{{ $vendor->summary ?? '' }}</textarea>
                                                                @if ($errors->has('summary'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('summary') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div> --}}
                                                            <div class="form-group col-12">
                                                                <label for="description"><span class="text-red">* </span>描述</label>
                                                                <textarea rows="9" class="form-control {{ $errors->has('description') ? ' is-invalid' : '' }}" name="description" placeholder="詳細描述...">{{ $vendor->description ?? '' }}</textarea>
                                                                @if ($errors->has('description'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('description') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label for="digiwin_vendor_no">鼎新廠商代號</label>
                                                                <input type="text" class="form-control" id="digiwin_vendor_no" name="digiwin_vendor_no" placeholder="請輸入鼎新廠商代號" value="{{ $vendor->digiwin_vendor_no ?? '' }}" autocomplete="off" />
                                                                @if ($errors->has('digiwin_vendor_no'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('digiwin_vendor_no') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            {{-- <div class="form-group col-12">
                                                                <label for="pause_start_date">暫停出貨區間<span class="text-red">(設定此區間從起始開始變更商品最快出貨日為結束日期)</span></label>
                                                                <div class="input-group">
                                                                    <input type="datetime" class="form-control datepicker" id="pause_start_date" name="pause_start_date" placeholder="格式：2016-06-06" value="{{ $vendor->pause_start_date ?? '' }}" autocomplete="off" />
                                                                    <span class="input-group-addon bg-primary">~</span>
                                                                    <input type="datetime" class="form-control datepicker" id="pause_end_date" name="pause_end_date" placeholder="格式：2016-06-06" value="{{ $vendor->pause_end_date ?? '' }}" autocomplete="off" />
                                                                </div>
                                                                @if ($errors->has('pause_start_date'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('pause_start_date') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center bg-white">
                                                    @if(in_array(isset($vendor) ? $menuCode['Vendors'].'M' : $menuCode['Vendors'].'N', explode(',',Auth::user()->power)))
                                                    <button type="submit" class="btn btn-primary">{{ isset($vendor) ? '修改' : '新增' }}</button>
                                                    @endif
                                                    <a href="{{ url('vendors') }}" class="btn btn-info">
                                                        <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                                    </a>
                                                </div>
                                        </div>
                                        @if(isset($vendor))
                                        @for($i=0;$i<count($langs);$i++)
                                        <div class="tab-pane" id="{{ $langs[$i]['code'] }}">
                                            <div class="row">
                                                <div class="col-6">
                                                        <div class="row">
                                                            <div class="form-group col-12">
                                                                <label for="name"><span class="text-red">* </span>店名或品牌</label>
                                                                {{-- <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['name'] : '' }}" placeholder="{{ $langs[$i]['name'] }}店名或品牌"> --}}
                                                                <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][name]" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['name'] : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}店名/品牌">
                                                                @if ($errors->has('name'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('name') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-12">
                                                                <label for="summary"><span class="text-red">* </span>簡介</label>
                                                                {{-- <textarea rows="8" class="form-control {{ $errors->has('summary') ? ' is-invalid' : '' }}" name="summary" placeholder="{{ $langs[$i]['name'] }}簡短介紹...">{{ isset($langs[$i]['data']) ? $langs[$i]['data']['summary'] : '' }}</textarea> --}}
                                                                <textarea rows="5" class="form-control" name="langs[{{ $langs[$i]['code'] }}][summary]" placeholder="請輸入{{ $langs[$i]['name'] }}簡介">{{ isset($langs[$i]['data']) ? $langs[$i]['data']['summary'] : '' }}</textarea>
                                                                @if ($errors->has('summary'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('summary') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                            <div class="form-group col-12">
                                                                <label for="description"><span class="text-red">* </span>描述</label>
                                                                {{-- <textarea rows="10" class="form-control {{ $errors->has('description') ? ' is-invalid' : '' }}" name="description" placeholder="{{ $langs[$i]['name'] }}詳細描述...">{{ isset($langs[$i]['data']) ? $langs[$i]['data']['description'] : '' }}</textarea> --}}
                                                                <textarea rows="5" class="form-control" name="langs[{{ $langs[$i]['code'] }}][description]" placeholder="請輸入{{ $langs[$i]['name'] }}描述">{{ isset($langs[$i]['data']) ? $langs[$i]['data']['description'] : '' }}</textarea>
                                                                @if ($errors->has('description'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('description') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-center bg-white">
                                                            @if(in_array(isset($vendor) ? $menuCode['Vendors'].'M' : $menuCode['Vendors'].'N', explode(',',Auth::user()->power)))
                                                            <button type="submit" class="btn btn-primary">{{ isset($langs[$i]['data']) ? '修改' : '新增' }}</button>
                                                            @endif
                                                            <a href="{{ url('vendors') }}" class="btn btn-info">
                                                                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                                            </a>
                                                        </div>
                                                    {{-- </form> --}}
                                                </div>
                                            </div>
                                        </div>
                                        @endfor
                                        @endif
                                    </div>
                                </div>
                                </form>
                            </div>
                        {{-- </div> --}}
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">照片資料</h3>
                            </div>
                            @if(isset($vendor))
                            <form class="img_upload" action="{{ route('admin.vendors.upload', $vendor->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4 card card-primary card-outline">
                                            <div class="card-body">
                                                <div class="text-center mb-2">
                                                    <h3>商家主視覺</h3>
                                                    <img width="100%" class="new_cover" src="{{ $vendor->new_cover ?? '' }}" alt="">
                                                </div>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="file" id="new_cover" name="new_cover" class="custom-file-input {{ $errors->has('new_cover') ? ' is-invalid' : '' }} mb-2" accept="image/*" required>
                                                        <label class="custom-file-label" for="new_cover">瀏覽選擇新圖片</label>
                                                    </div>
                                                    <p>上傳後等比縮放為1440x760</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4 card card-primary card-outline">
                                            <div class="card-body box-profile">
                                                <div class="text-center mb-2">
                                                    <h3>LOGO</h3>
                                                    <img width="100%" class="new_logo" src="{{ $vendor->new_logo ?? '' }}" alt="">
                                                </div>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="file" id="new_logo" name="new_logo" class="custom-file-input {{ $errors->has('new_logo') ? ' is-invalid' : '' }} mb-2" accept="image/*" required>
                                                        <label class="custom-file-label" for="new_logo">瀏覽選擇新圖片</label>
                                                    </div>
                                                    <p>上傳後等比縮放為540x360</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4 card card-primary card-outline">
                                            <div class="card-body box-profile">
                                                <div class="text-center mb-2">
                                                    <h3>網站滿版圖</h3>
                                                    <img width="100%" class="new_site_cover" src="{{ $vendor->new_site_cover ?? '' }}" alt="">
                                                </div>
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="file" id="new_site_cover" name="new_site_cover" class="custom-file-input {{ $errors->has('new_site_cover') ? ' is-invalid' : '' }} mb-2" accept="image/*" required>
                                                        <label class="custom-file-label" for="new_site_cover">瀏覽選擇新圖片</label>
                                                    </div>
                                                    <p>上傳後等比縮放為1440x760</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center bg-white">
                                        @if(in_array(isset($vendor) ? $menuCode['Vendors'].'M' : $menuCode['Vendors'].'N', explode(',',Auth::user()->power)))
                                        <button type="submit" class="btn btn-primary">送出</button>
                                        @endif
                                        <a href="{{ url('vendors') }}" class="btn btn-info">
                                            <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                            @else
                            <div class="card-body">
                                <h3>請先建立商家資料</h3>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade {{ Session::get('vendorShopShow') ?? '' }}" id="vendor-shop" role="tabpanel" aria-labelledby="vendor-shop-tab">
                        @if(isset($vendor))
                            @if(in_array($menuCode['Shops'].'N',explode(',',Auth::user()->power)))
                            <form action="{{ route('admin.vendorshops.create') }}" method="GET">
                                <div class="form-group">
                                    <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                    <button type="submit" class="btn btn-primary">新增</button>
                                </div>
                            </form>
                            @endif
                            <div class="card-primary card-outline"></div>
                        @if(count($vendor->shops)==0)
                            <span class="text-xl">該商家目前尚無分店。</span>
                        @else
                        <div class="col-12">
                            <div class="row">
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-left" width="20%">分店/品牌名稱</th>
                                                <th class="text-left" width="15%">分店電話</th>
                                                <th class="text-left" width="25%">分店地址</th>
                                                <th class="text-left" width="15%">座標</th>
                                                <th class="text-center" width="5%">查證</th>
                                                @if(in_array($menuCode['Shops'].'O',explode(',',Auth::user()->power)))
                                                <th class="text-center" width="5%">啟用</th>
                                                @endif
                                                @if(in_array($menuCode['Shops'].'D',explode(',',Auth::user()->power)))
                                                <th class="text-center" width="5%">刪除</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($vendor->shops as $shop)
                                            <tr>
                                                <td class="text-left align-middle"><a href="{{ url('vendorshops/'.$shop->id.'?from=vendor-shop') }}">{{ $shop->name }}</a></td>
                                                <td class="text-left align-middle">{{ $shop->tel }}</td>
                                                <td class="text-left align-middle">{{ $shop->address }}</td>
                                                <td class="text-left align-middle">{{ $shop->location }}</td>
                                                <td class="text-center align-middle">
                                                    <a href="https://maps.google.com/maps?q={{ $shop->address }}" class="btn btn-sm btn-success" target="_blank">查證</a>
                                                </td>
                                                @if(in_array($menuCode['Shops'].'O',explode(',',Auth::user()->power)))
                                                <td class="text-center align-middle">
                                                    <form action="{{ url('vendorshops/active/' . $shop->id) }}" method="POST">
                                                        @csrf
                                                        <input type="checkbox" name="is_on" value="{{ $shop->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($shop) ? $shop->is_on == 1 ? 'checked' : '' : '' }}>
                                                        <input type="hidden" name="from" value="#vendor-shop">
                                                        <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                                    </form>
                                                </td>
                                                @endif
                                                <td class="text-center align-middle">
                                                    @if(in_array($menuCode['Shops'].'D',explode(',',Auth::user()->power)))
                                                    <form action="{{ route('admin.vendorshops.destroy', $shop->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <input type="hidden" name="from" value="#vendor-shop">
                                                        <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                                        <button type="submit" class="btn btn-sm btn-danger">刪除</button>
                                                    </form>
                                                    @endif
                                                </td class="text-center align-middle">
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                        @else
                        <div class="card-body">
                            <h3>請先建立商家資料</h3>
                        </div>
                        @endif
                    </div>
                    <div class="tab-pane fade {{ Session::get('vendorAccountShow') ?? '' }}" id="vendor-account" role="tabpanel" aria-labelledby="vendor-account-tab">
                        @if(isset($vendor))
                            @if(in_array($menuCode['Accounts'].'N',explode(',',Auth::user()->power)))
                            <form action="{{ route('admin.vendoraccounts.create') }}" method="GET">
                                <div class="form-group">
                                    <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                    <button type="submit" class="btn btn-primary">新增</button>
                                </div>
                            </form>
                            @endif
                            <div class="card-primary card-outline"></div>
                        @if(count($vendor->accounts)<=0)
                            <span class="text-xl">該商家目前尚無建立帳號。</span>
                        @else
                        <div class="col-12">
                            <div class="row">
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-left" width="13%">帳號</th>
                                                <th class="text-left" width="10%">姓名</th>
                                                <th class="text-left" width="15%">電子郵件</th>
                                                {{-- <th class="text-left" width="12%">所屬分店</th> --}}
                                                {{-- <th class="text-left" width="10%">權限</th> --}}
                                                @if(in_array($menuCode['Accounts'].'O',explode(',',Auth::user()->power)))
                                                <th class="text-center" width="5%">啟用</th>
                                                @endif
                                                @if(in_array($menuCode['Accounts'].'D',explode(',',Auth::user()->power)))
                                                <th class="text-center" width="4%">刪除</th>
                                                @endif
                                                @if(in_array($menuCode['Accounts'].'T',explode(',',Auth::user()->power)))
                                                <th class="text-center" width="6%">操作</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($vendor->accounts as $account)
                                            <tr>
                                                <td class="text-left align-middle">
                                                    <a href="{{ url('vendoraccounts/'.$account->id.'?from=vendor-account') }}">{{ $account->account }}</a>
                                                </td>
                                                <td class="text-left align-middle">{{ $account->name }}</td>
                                                <td class="text-left align-middle">{{ $account->email }}</td>
                                                {{-- <td class="text-left align-middle">{{ $account->vendor_shop_id == 0 ? 'Default (無分店)' : $account->shop->name }}</td> --}}
                                                {{-- <td class="text-left align-middle">
                                                    <div class="row col-12">
                                                    <div class="icheck-primary col-6">
                                                        <input type="checkbox" id="shop_admin{{ $account->id }}" name="shop_admin" {{ $account->shop_admin == 1 ? 'checked' : ''}} disabled>
                                                        <label for="shop_admin{{ $account->id }}">後台</label>
                                                    </div>
                                                    <div class="icheck-primary col-6">
                                                        <input type="checkbox" id="pos_admin{{ $account->id }}" name="pos_admin" {{ $account->shop_admin == 1 ? 'checked' : ''}} disabled>
                                                        <label for="pos_admin{{ $account->id }}">POS</label>
                                                    </div>
                                                </div>
                                                </td> --}}
                                                @if(in_array($menuCode['Accounts'].'O',explode(',',Auth::user()->power)))
                                                <td class="text-center align-middle">
                                                    <form action="{{ url('vendoraccounts/active/' . $account->id) }}" method="POST">
                                                        @csrf
                                                        <input type="checkbox" name="is_on" value="{{ $account->is_on == 1 ? 0 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($account) ? $account->is_on == 1 ? 'checked' : '' : '' }}>
                                                        <input type="hidden" name="from" value="#vendor-account">
                                                        <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                                    </form>
                                                </td>
                                                @endif
                                                @if(in_array($menuCode['Accounts'].'D',explode(',',Auth::user()->power)))
                                                <td class="text-center align-middle">
                                                    <form action="{{ route('admin.vendoraccounts.destroy', $account->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <input type="hidden" name="from" value="#vendor-account">
                                                        <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                                        <button type="submit" class="btn btn-sm btn-danger">刪除</button>
                                                    </form>
                                                </td>
                                                @endif
                                                @if(in_array($menuCode['Accounts'].'T',explode(',',Auth::user()->power)))
                                                <td class="text-center align-middle">
                                                    <a href="https://{{ env('VENDOR_DOMAIN') }}/login?account={{ $account->account }}&icarryToken={{ $account->icarry_token }}" class="btn btn-sm btn-info btn-door" target="_blank">
                                                        <i class="fas fa-sign-out-alt">傳送門</i>
                                                    </a>
                                                </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                        @else
                        <div class="card-body">
                            <h3>請先建立商家資料</h3>
                        </div>
                        @endif
                    </div>
                    <div class="tab-pane fade {{ Session::get('vendorProductShow') ?? '' }}" id="vendor-product" role="tabpanel" aria-labelledby="vendor-product-tab">
                        @if(isset($vendor))
                        @if(in_array($menuCode['Products'].'N',explode(',',Auth::user()->power)))
                        <p><a href="{{ route('admin.products.create', 'from_vendor='.$vendor->id) }}" class="btn btn-primary mr-2">新增</a></p>
                        @endif
                        <div class="card-primary card-outline"></div>
                        @if(count($vendor->products)<=0)
                        <p class="text-xl">該商家目前尚未建立商品。</p>
                        @else
                        <div class="col-12">
                            <div class="row">
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="5%">狀態</th>
                                                <th class="text-left" width="25%">品名/內容量</th>
                                                <th class="text-left" width="8%">分類</th>
                                                <th class="text-center" width="5%">款式</th>
                                                <th class="text-left" width="25%">
                                                    <div class="row">
                                                        <div class="col-5 text-left">鼎新貨號/商品貨號</div>
                                                        <div class="col-2 text-right">庫存</div>
                                                        <div class="col-2 text-right">安全</div>
                                                        <div class="col-2 text-center">調整</div>
                                                    </div>
                                                </th>
                                                <th class="text-right" width="5%">單價</th>
                                                @if(in_array($menuCode['Accounts'].'D',explode(',',Auth::user()->power)))
                                                <th class="text-center" width="5%">刪除</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($vendor->products as $product)
                                            <tr>
                                                <td class="text-center align-middle text-sm">
                                                    @if($product->status == 1)
                                                    <span class="right badge badge-success">上架中</span>
                                                    @elseif($product->status == 0)
                                                    <span class="right badge badge-purple">送審中</span>
                                                    @elseif($product->status == -9)
                                                    <span class="right badge badge-secondary">已下架</span>
                                                    @elseif($product->status == -3)
                                                    <span class="right badge badge-danger">停售中</span>
                                                    @elseif($product->status == -2)
                                                    <span class="right badge badge-danger">送審失敗</span>
                                                    @elseif($product->status == -1)
                                                    <span class="right badge badge-warning">未送審</span>
                                                    @endif
                                                </td>
                                                <td class="text-left align-middle text-sm">
                                                    <div class="col-12 text-warp">
                                                    <a href="{{ url('products/'.$product->id.'?from_vendor='.$vendor->id) }}">{{ $product->name }}</a>
                                                    <span class="text-xs bg-info">{{ $product->serving_size }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-left align-middle text-sm">{{ $product->category->name ?? '' }}</td>
                                                <td class="text-center align-middle text-sm">
                                                    @if($product->model_type == 1)
                                                        單一款式
                                                    @elseif($product->model_type == 2)
                                                        多種款式
                                                    @else
                                                        組合商品
                                                    @endif
                                                </td>
                                                <td class="text-left align-middle text-sm">
                                                    <div class="row">
                                                        @foreach($product->models as $model)
                                                            <div class="col-5 text-left">{{ $model->digiwin_no }}<br>{{ $model->sku }}</div>
                                                            <div class="col-2 text-right">
                                                                @if($model->quantity < $model->safe_quantity)
                                                                <span class="text-danger"><b id="quantity_{{ $model->id }}">{{ number_format($model->quantity) }}</b></span>
                                                                @else
                                                                <span id="quantity_{{ $model->id }}">{{ number_format($model->quantity) }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="col-2 text-right">{{ $model->safe_quantity }}</div>
                                                            @if(!in_array($product->status,[1,-3,-9]))
                                                            <div class="col-2 text-center"><a href="javascript:" onclick="getstockrecord({{ $model->id }})"><span class="right badge badge-primary">庫存調整</span></a></div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td class="text-right align-middle"><span class="text-primary"><b>{{ number_format($product->price) }}</b></span></td>
                                                @if(in_array($menuCode['Products'].'D',explode(',',Auth::user()->power)))
                                                <td class="text-center align-middle">
                                                    @if(!in_array($product->status,[1,-3,-9]))
                                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <input type="hidden" name="from" value="#vendor-product">
                                                        <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                                        <button type="submit" class="btn btn-sm btn-danger">刪除</button>
                                                    </form>
                                                    @endif
                                                </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                        @else
                        <div class="card-body">
                            <h3>請先建立商家資料</h3>
                        </div>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="vendor-order" role="tabpanel" aria-labelledby="vendor-order-tab">
                        @if(isset($vendor))
                        <div class="card-body">
                            <h3>功能還沒做</h3>
                        </div>
                        @else
                        <div class="card-body">
                            <h3>請先建立商家資料</h3>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('modal')
{{-- 庫存調整 Modal --}}
<div id="myModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group form-group-sm">
                    <form id="myForm">
                    <input type="hidden" name="product_model_id">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">貨號</span>
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text" name="sku"></span>
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text">庫存</span>
                        </div>
                        <div class="input-group-prepend" style="width:12%">
                            <input type="number" class="form-control" name="quantity">
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text">安全庫存</span>
                        </div>
                        <div class="input-group-prepend" style="width:12%">
                            <input type="number" class="form-control" name="safe_quantity">
                        </div>
                        <div class="input-group-prepend">
                            <span class="input-group-text">調整原因</span>
                        </div>
                        <div class="input-group-prepend" style="width:30%" >
                            <input type="text" class="form-control" name="reason" placeholder="非必填">
                        </div>
                        <div class="input-group-append">
                            <span id="stockmodify" class="btn btn-sm btn-danger">更新</span>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="form-group form-group-sm">
                    <label for="message-text" class="col-form-label">修改紀錄</label>
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">修改前庫存</th>
                                            <th width="15%">增減數量</th>
                                            <th width="15%">修改後庫存</th>
                                            <th width="30%">原因理由</th>
                                            <th width="20%">庫存調整時間</th>
                                        </tr>
                                    </thead>
                                    <tbody id="record"></tbody>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\VendorsRequest', '#myform'); !!}
{!! JsValidator::formRequest('App\Http\Requests\Admin\VendorsUploadRequest', '.img_upload'); !!}
{!! JsValidator::formRequest('App\Http\Requests\Admin\VendorsLangRequest', '.myform_lang'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        // date time picker 設定
        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        $('.datepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        var tab = window.location.hash;
        if(tab){
            $('#vendor-desc-tab').removeClass('active');
            $('#vendor-desc').removeClass('active');
            $('#vendor-desc').removeClass('show');
            $(tab+'-tab').addClass('active');
            $(tab).addClass('active');
            $(tab).addClass('show');
        }else{
            $('#vendor-desc-tab').addClass('active');
            $('#vendor-desc').addClass('active');
            $('#vendor-desc').addClass('show');
        }

        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('#stockmodify').click(function(){
            let product_model_id = $('input[name=product_model_id]').val();
            let quantity = $('input[name=quantity]').val();
            let safe_quantity = $('input[name=safe_quantity]').val();
            let reason = $('input[name=reason]').val();
            let token = '{{ csrf_token() }}';
            let url = '{{ url("admin/products/stockmodify") }}';
            $.ajax({
                type: "post",
                url: url,
                data: { product_model_id: product_model_id, quantity: quantity, safe_quantity: safe_quantity, reason: reason, _token: token },
                success: function(data) {
                    if(data['productQtyRecord']){
                        let x = $('.record').length;
                        let dateTime = new Date(data['productQtyRecord']['created_at']);
                        let timestamp = new Date(data['productQtyRecord']['created_at']).getTime();
                        let count = data['productQtyRecord']['after_quantity'] - data['productQtyRecord']['before_quantity'];
                        let record = '<tr class="record"><td class="align-middle">'+(x+1)+'</td><td class="align-middle">'+data['productQtyRecord']['before_quantity']+'</td><td class="align-middle">'+count+'</td><td class="align-middle">'+data['productQtyRecord']['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord']['reason']+'</td><td class="align-middle">'+dateTime+'</td></tr>';
                        $('#record').prepend(record);
                        $('#quantity_'+product_model_id).html(data['productQtyRecord']['after_quantity']);
                    }else{
                        alert('新舊庫存未改變');
                    }
                }
            });
        });

        $('.btn-door').click(function(){
            setTimeout(function() {
                location.reload()
            }, 1000);
        });
    })(jQuery);

    // 舊的image網址及屬性
    img_cover = $('.new_cover').attr('src');
    img_logo = $('.new_logo').attr('src');
    img_site = $('.new_site_cover').attr('src');
    $('input[type=file]').change(function(x) {
        name = this.name;
        name == 'new_cover' ? oldimg = img_cover : '';
        name == 'new_logo' ? oldimg = img_logo : '';
        name == 'new_site_cover' ? oldimg = img_site : '';
        file = x.currentTarget.files;
        if (file.length >= 1) {
            // filename = checkMyImage(file);
            filename = file[0].name; //不檢查檔案直接找出檔名
            if (filename) {
                readURL(this, '.' + name);
                $('label[for=' + name + ']').html(filename);
            } else {
                $(this).val('');
                $('label[for=' + name + ']').html('瀏覽選擇新圖片');
                $('.' + name).attr('src', oldimg); //沒照片時還原
            }
        } else {
            $(this).val('');
            $('label[for=' + name + ']').html('瀏覽選擇新圖片');
            $('.' + name).attr('src', oldimg); //沒照片時還原
        }
    });

    function readURL(input, imgclass) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $(imgclass).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function checkFileName(filename) {
        var chk = 0;
        var specialSymbols = new Array("`", "~", "!", "@", "#", "$", "%", "^", "&", "*", "+", "=", "(", ")", "[", "]", "{", "}", "<", ">", "/", "?", ":", ";", "'", "\"", "\\", "|");
        for (j = 0; j < specialSymbols.length; j++) {
            if (filename.indexOf(specialSymbols[j]) >= 0) {
                chk++;
            }
        }
        if (chk >= 1) {
            alert("檔案名稱中不可含有兩個\"點\"符號或下列特殊符號。\n(  ` ~ ! @ # $ % ^ & * + = ( ) [ ] { } < > / ? : ; ' \" \\ |  )");
            return false;
        } else {
            return true;
        }
    }

    function checkFileSize(size) {
        if (size > 10240 * 1024) {
            alert('檔案大小超過10MB');
            return false;
        } else {
            return true;
        }
    }

    function checkFileExt(ext) {
        if ($.inArray(ext, ['.png', '.jpg', '.jpeg', '.gif', '.svg']) == -1) {
            alert('檔案格式不被允許，限JPG、PNG、GIF或SVG格式');
            return false;
        } else {
            return true;
        }
    }

    function checkMyImage(input) {
        if (input) {
            var filename = input[0].name;
            var size = input[0].size;
            var ext = filename.substring(filename.lastIndexOf('.')).toLowerCase();
            if (checkFileName(filename)) {
                if (checkFileExt(ext)) {
                    if (checkFileSize(size)) {
                        return filename;
                    }
                }
            }
        }
    }

    function getstockrecord(id){
        $('#result').html(''); //開啟modal前清除搜尋資料
        $('#myModal').modal('show');
        let token = '{{ csrf_token() }}';
        let url = '{{ url("admin/products/getstockrecord") }}';
        $.ajax({
            type: "post",
            url: url,
            data: { id: id, _token: token },
            success: function(data) {
                let type = data['product']['model_type'];
                let spec = '';
                let name = '';
                if(type == 1){
                    spec = '單一規格';
                    name = data['product']['name'];
                }else if(type == 2){
                    spec = '多款規格';
                    name = data['productModel']['name'];
                }else if(type == 3){
                    spec = '組合商品';
                    name = data['productModel']['name'];
                }
                $('#ModalLabel').html('<span class="text-primary">'+name +'</span> > <span class="text-danger">'+spec+'</span> > 商品庫存調整');
                let record = '';
                for(i=0;i<data['productQtyRecord'].length;i++){
                    let x = data['productQtyRecord'].length - i;
                    let dateTime = new Date(data['productQtyRecord'][i]['created_at']);
                    let timestamp = new Date(data['productQtyRecord'][i]['created_at']).getTime();
                    count = data['productQtyRecord'][i]['after_quantity'] - data['productQtyRecord'][i]['before_quantity'];
                    record = record + '<tr class="record"><td class="align-middle">'+(x)+'</td><td class="align-middle">'+data['productQtyRecord'][i]['before_quantity']+'</td><td class="align-middle">'+count+'</td><td class="align-middle">'+data['productQtyRecord'][i]['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord'][i]['reason']+'</td><td class="align-middle">'+dateTime+'</td></tr>';
                }
                $('#record').html(record);
                $('input[name=product_model_id]').val(data['productModel']['id']);
                $('input[name=quantity]').val(data['productModel']['quantity']);
                $('input[name=safe_quantity]').val(data['productModel']['safe_quantity']);
                $('span[name=sku]').html(data['productModel']['sku']);
            }
        });
    }

</script>
@endsection
