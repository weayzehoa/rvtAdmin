@extends('admin.layouts.master')

@section('title', '商品管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    @if(isset($product) && !isset($copy))
                    <h1 class="m-0 text-dark"><b>商品管理</b><small>(修改)</small></h1>
                    @elseif(isset($copy))
                    <h1 class="m-0 text-dark"><b>商品管理</b><small> (複製)</small></h1>
                    @else
                    <h1 class="m-0 text-dark"><b>商品管理</b><small> (新增)</small></h1>
                    @endif
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('products') }}">商品管理</a></li>
                        @if(isset($product) && !isset($copy))
                        <li class="breadcrumb-item active">修改</li>
                        @elseif(isset($copy))
                        <li class="breadcrumb-item active">複製</li>
                        @else
                        <li class="breadcrumb-item active">新增</li>
                        @endif
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <nav class="w-100">
                    <div class="nav nav-tabs" id="product-tab" role="tablist">
                        <a class="nav-item nav-link active" id="product-desc-tab" data-toggle="tab" href="#product-desc" role="tab" aria-controls="product-desc" aria-selected="true">基本資料</a>
                        {{-- <a class="nav-item nav-link" id="product-image-tab" data-toggle="tab" href="#product-image" role="tab" aria-controls="product-image" aria-selected="false">新版商品照片</a> --}}
                        @if(isset($product))
                        <a class="nav-item nav-link" id="old-product-image-tab" data-toggle="tab" href="#old-product-image" role="tab" aria-controls="old-product-image" aria-selected="false">舊版商品照片</a>
                        @endif
                    </div>
                </nav>
                <div class="tab-content p-3" id="nav-tabContent">
                    <div class="tab-pane fade {{ Session::get('ProductImageShow') || Session::get('ProductModelShow') ? '' : 'show active' }}" id="product-desc" role="tabpanel" aria-labelledby="product-desc-tab">
                        @if(isset($product) && !isset($copy))
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link chinese active" href="#chinese" data-toggle="tab">繁體中文</a></li>
                            @for($i=0;$i<count($langs);$i++)
                            <li class="nav-item"><a class="nav-link lang-{{ $langs[$i]['code']}}" href="#lang-{{ $langs[$i]['code'] }}" data-toggle="tab">{{ $langs[$i]['name'] }}</a></li>
                            @endfor
                        </ul>
                        @endif
                        <div class="card card-primary card-outline">
                            {{-- <div class="card-header">
                                <h3 class="card-title">商品資料</h3>
                            </div> --}}
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="active tab-pane" id="chinese">
                                        @if(isset($product) && !isset($copy))
                                        <form id="myform" action="{{ route('admin.products.update', $product->id) }}" method="POST">
                                            <input type="hidden" name="_method" value="PATCH">
                                        @else
                                        <form id="myform" action="{{ route('admin.products.store') }}" method="POST">
                                        @endif
                                            @csrf
                                            @if(isset($product) && isset($copy))
                                            <input type="hidden" name="copy" value="{{ $product->id }}">
                                            <input type="hidden" name="method" value="copy">
                                            @for($i=0;$i<count($oldImages);$i++)
                                            @if(!empty($oldImages[$i]))
                                            @if(isset($copy))
                                            <input type="hidden" name="new_photo{{ $i+1 }}" value="{{ $oldImages[$i] }}">
                                            @endif
                                            @endif
                                            @endfor
                                            @else
                                            <input type="hidden" name="method" value="new">
                                            @endif
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="row">
                                                        @if(isset($product))
                                                            @if(isset($copy))
                                                            <div class="form-group col-12">
                                                                <label for="brand">變更所屬商家：</label>
                                                                <input type="number" name="vendor_id" value="{{ $product->vendor->id }}">
                                                            </div>
                                                            @else
                                                            <div class="form-group col-12">
                                                                <label for="brand">所屬商家：<a href="{{ route('admin.vendors.show', $product->vendor_id) }}">{{ $product->vendor['name'] }}</a></label>
                                                                <input type="hidden" name="vendor_id" value="{{ $product->vendor['id'] }}">
                                                            </div>
                                                            @endif
                                                        @else
                                                        <div class="form-group col-12">
                                                            <label for="brand">所屬商家：{{ isset($vendor) ? $vendor->name : '' }}</label>
                                                            <input type="hidden" name="vendor_id" value="{{ isset($vendor) ? $vendor->id : '' }}">
                                                        </div>
                                                        @endif
                                                        <div class="form-group col-12">
                                                            <label for="brand"><span class="text-red">* </span>廠牌</label>
                                                            <input type="text" class="form-control {{ $errors->has('brand') ? ' is-invalid' : '' }}" id="brand" name="brand" value="{{ $product->brand ?? '' }}" placeholder="輸入廠牌名稱">
                                                            @if ($errors->has('brand'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('brand') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="name"><span class="text-red">* </span>商品名稱</label>
                                                            <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $product->name ?? '' }}" placeholder="輸入商品名稱">
                                                            @if ($errors->has('name'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('name') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="eng_name">出口用英文名稱</label>
                                                            <input type="text" class="form-control {{ $errors->has('eng_name') ? ' is-invalid' : '' }}" id="eng_name" name="eng_name" value="{{ $product->eng_name ?? '' }}" placeholder="輸入出口用英文名稱">
                                                            @if ($errors->has('eng_name'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('eng_name') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="serving_size"><span class="text-red">* </span>內容(規格)</label>
                                                            <input type="text" class="form-control {{ $errors->has('serving_size') ? ' is-invalid' : '' }}" id="serving_size" name="serving_size" value="{{ $product->serving_size ?? '' }}" placeholder="輸入內容物或規格">
                                                            @if ($errors->has('serving_size'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('serving_size') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="title"><span class="text-red">* </span>特色 (小標題) <span class="badge badge-info">此欄位將作為商品頁中字體放大的標題</span><small>(建議輸入13個中文字以內)</small></label>
                                                            <input type="text" class="form-control {{ $errors->has('title') ? ' is-invalid' : '' }}" id="title" name="title" value="{{ $product->title ?? '' }}" placeholder="請用簡單文字(建議輸入13個中文字以內)說明商品特色">
                                                            @if ($errors->has('title'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('title') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="intro"><span class="text-red">* </span>簡介 (商品描述) <span class="badge badge-info">此欄位將作為商品頁中標題下方的簡單說明</span><small>(建議輸入70個中文字以內的簡單介紹)</small></label>
                                                            <textarea rows="5" class="form-control {{ $errors->has('intro') ? ' is-invalid' : '' }}" id="intro" name="intro" placeholder="輸入70個中文字以內的簡單介紹">{{ $product->intro ?? '' }}</textarea>
                                                            @if ($errors->has('intro'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('intro') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group {{ isset($product) && $product->model_type == 1 ? 'col-6' : 'col-12' }}">
                                                            <label for="unable_buy">無法結帳提示</label>
                                                            <input type="text" class="form-control {{ $errors->has('unable_buy') ? ' is-invalid' : '' }}" id="unable_buy" name="unable_buy" value="{{ $product->unable_buy ?? '' }}" placeholder="輸入無法結帳提示">
                                                            @if ($errors->has('unable_buy'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('unable_buy') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        @if(isset($product) && $product->model_type == 1)
                                                        <div class="form-group col-6">
                                                            <label for="unable_buy">轉倉時間區間</label>
                                                            <div class="input-group">
                                                                <input type="datetime" class="form-control datepicker" id="trans_start_date" name="trans_start_date" placeholder="格式：2016-06-06" value="{{ isset($product) && $product->trans_start_date ? $product->trans_start_date : '' }}" autocomplete="off" />
                                                                <span class="input-group-addon bg-primary">~</span>
                                                                <input type="datetime" class="form-control datepicker" id="trans_end_date" name="trans_end_date" placeholder="格式：2016-06-06" value="{{ isset($product) && $product->trans_end_date ? $product->trans_end_date : '' }}" autocomplete="off" />
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="row">
                                                        <div class="form-group col-3">
                                                            <label for="price"><span class="text-red">* </span>單價(售價) @if(isset($product))<a href="javascript:" onclick="history('price',{{ $product->id }},'單價修改紀錄')">歷史紀錄</a>@endif</label>
                                                            <input type="number" class="text-danger text-bold form-control {{ $errors->has('price') ? ' is-invalid' : '' }}" id="price" name="price" value="{{ isset($product) ? $product->price ?? '' : '' }}" min="0" placeholder="輸入單價">
                                                            @if ($errors->has('price'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('price') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="fake_price">原價(僅顯示用) @if(isset($product))<a href="javascript:" onclick="history('fake_price',{{ $product->id }},'原價修改紀錄')">歷史</a>@endif</label>
                                                            <input type="number" class="form-control {{ $errors->has('fake_price') ? ' is-invalid' : '' }}" id="fake_price" name="fake_price" value="{{ isset($product) ? $product->fake_price ==0 ? '' : $product->fake_price : '' }}" min="0" placeholder="輸入原價">
                                                            @if ($errors->has('fake_price'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('fake_price') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="vendor_price">廠商進價 @if(isset($product))<a href="javascript:" onclick="history('vendor_price',{{ $product->id }},'廠商進價修改紀錄')">歷史</a>@endif</label>
                                                            <input type="number" step=".01" class="form-control {{ $errors->has('vendor_price') ? ' is-invalid' : '' }}" id="vendor_price" name="vendor_price" value="{{ isset($product) ? $product->vendor_price == 0 ? '' : $product->vendor_price : '' }}" min="0" placeholder="輸入廠商進價">
                                                            @if ($errors->has('vendor_price'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('vendor_price') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="is_tax_free"><span class="text-red">* </span>應稅/免稅</label>
                                                            <div class="input-group">
                                                                <select class="form-control{{ $errors->has('is_tax_free') ? ' is-invalid' : '' }}" id="is_tax_free" name="is_tax_free">
                                                                    <option value="0" {{ isset($product) ? $product->is_tax_free == 0 ? 'selected' : 'selected' : 'selected' }}>應稅</option>
                                                                    <option value="1" {{ isset($product) ? $product->is_tax_free == 1 ? 'selected' : '' : '' }}>免稅</option>
                                                                </select>
                                                            </div>
                                                            @if ($errors->has('shipping_verdor_percent'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('shipping_verdor_percent') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                       <div class="form-group col-3">
                                                        <label for="type">商品類型</span></label>
                                                            <select class="form-control" id="type" name="type">
                                                                <option value="1" {{ isset($product) ? $product->type == 1 ? 'selected' : '' : 'selected' }}>一般商品</option>
                                                                <option value="2" {{ isset($product) ? $product->type == 2 ? 'selected' : '' : '' }}>加購品</option>
                                                                <option value="3" {{ isset($product) ? $product->type == 3 ? 'selected' : '' : '' }}>贈品</option>
                                                            </select>
                                                        @if ($errors->has('type'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('type') }}</strong>
                                                        </span>
                                                        @endif
                                                       </div>
                                                        <div class="form-group col-3">
                                                            <label for="unit_name_id"><span class="text-red">* </span>商品單位</label>
                                                            <select class="form-control{{ $errors->has('unit_name_id') ? ' is-invalid' : '' }}" id="unit_name_id" name="unit_name_id">
                                                                <option value="">請選擇商品單位</option>
                                                                @foreach($unitNames as $unitName)
                                                                <option value="{{ $unitName->id }}" {{ isset($product) ? $product->unit_name_id == $unitName->id ? 'selected' : '' : '' }}>{{ $unitName->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->has('unit_name_id'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('unit_name_id') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="gross_weight"><span class="text-red">* </span>毛重 (g)，含包材</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">
                                                                        <i class="fas fa-weight"></i>
                                                                    </span>
                                                                </div>
                                                                <input type="number" class="form-control {{ $errors->has('gross_weight') ? ' is-invalid' : '' }}" id="gross_weight" name="gross_weight" value="{{ isset($product) ? ($product->gross_weight > 0 ? ceil($product->gross_weight) : '') : '' }}" min="1" placeholder="輸入毛重(g)">
                                                                <div class="input-group-append">
                                                                    <div class="input-group-text"><i class="fab fa-goodreads-g"></i></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="net_weight">淨重 (g)，不含包材</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">
                                                                        <i class="fas fa-weight"></i>
                                                                    </span>
                                                                </div>
                                                                <input type="number" class="form-control {{ $errors->has('net_weight') ? ' is-invalid' : '' }}" id="net_weight" name="net_weight" value="{{ isset($product) ? ($product->net_weight > 0 ? ceil($product->net_weight) : '') : '' }}" min="0" placeholder="輸入產品淨重(g)">
                                                                <div class="input-group-append">
                                                                    <div class="input-group-text"><i class="fab fa-goodreads-g"></i></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="category_id"><span class="text-red">* </span>商品分類</label>
                                                            <select class="form-control{{ $errors->has('category_id') ? ' is-invalid' : '' }}" id="category_id" name="category_id">
                                                                <option value="">請選擇商品分類</option>
                                                                @foreach($categories as $category)
                                                                <option value="{{ $category->id }}" {{ isset($product) ? $product->category_id == $category->id ? 'selected' : '' : '' }}>{{ $category->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->has('category_id'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('category_id') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="digiwin_product_category"><span class="text-red">* </span>鼎新商品分類</label>
                                                            <select class="form-control{{ $errors->has('digiwin_product_category') ? ' is-invalid' : '' }}" id="digiwin_product_category" name="digiwin_product_category">
                                                                <option value="">請選擇商品分類</option>
                                                                @foreach($digiwinProductCates as $cate)
                                                                <option value="{{ $cate->code }}" {{ isset($product) ? $product->digiwin_product_category == $cate->code ? 'selected' : '' : '' }}>{{ $cate->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->has('digiwin_product_category'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('digiwin_product_category') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label for="storage_life"><span class="text-red">* </span>保存天數 (無期限請填0或留空白) @if(isset($product))<a href="javascript:" onclick="history('storage_life',{{ $product->id }},'保存天數修改紀錄')">歷史</a>@endif</label>
                                                            <input type="number" class="form-control {{ $errors->has('storage_life') ? ' is-invalid' : '' }}" id="storage_life" name="storage_life" value="{{ isset($product) ? $product->storage_life ?? '' : '' }}" min="0" placeholder="輸入保存天數">
                                                            @if ($errors->has('storage_life'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('storage_life') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="category_id"><span class="text-red">* </span>商品次分類 <span class="text-danger">(至少選擇一個)</span></label><br>
                                                            <div id="subcate" class="form-control {{ $errors->has('sub_categories') ? ' is-invalid' : '' }}">
                                                            @if(isset($product))
                                                            @foreach($subCategories as $subCate)
                                                            <span class="mr-3"><input type="checkbox" id="subcate{{ $subCate->id }}" name="sub_categories[]" value="{{ $subCate->id }}" {{ isset($product) ? in_array($subCate->id,!empty($product->sub_categories) ? explode(',',$product->sub_categories) : []) ? 'checked' : '' : '' }}> {{ $subCate->name }}</span>
                                                            @endforeach
                                                            @else
                                                            <span class="text-danger text-bold">請先選擇商品分類</span>
                                                            @endif
                                                            </div>
                                                            @if ($errors->has('sub_categories'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('sub_categories') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="from_country_id"><span class="text-red">* </span>發貨地區</label>
                                                            <select class="form-control {{ $errors->has('from_country_id') ? ' is-invalid' : '' }}" id="from_country_id" name="from_country_id">
                                                                <option value="">請選擇發貨地區</option>
                                                                @foreach($countries as $country)
                                                                @if($country->id == 1 || $country->id == 5)
                                                                <option value="{{ $country->id }}" {{ isset($product) ? $product->from_country_id == $country->id ? 'selected' : '' : '' }}>{{ $country->name }}</option>
                                                                @endif
                                                                @endforeach
                                                            </select>
                                                            @if ($errors->has('from_country_id'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('from_country_id') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="direct_shipment"><span class="text-red">* </span>廠商直寄</label>
                                                            <div class="input-group">
                                                                <select class="form-control{{ $errors->has('direct_shipment') ? ' is-invalid' : '' }}" id="direct_shipment" name="direct_shipment">
                                                                    <option value="1" {{ isset($product) ? $product->direct_shipment == 1 ? 'selected' : '' : '' }}>是</option>
                                                                    <option value="0" {{ isset($product) ? $product->direct_shipment == 0 ? 'selected' : '' : 'selected' }}>否</option>
                                                                </select>
                                                            </div>
                                                            @if ($errors->has('direct_shipment'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('direct_shipment') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label for="airplane_days"><span class="text-red">* </span>機場提貨指定天數</label>
                                                            <input type="number" class="form-control {{ $errors->has('airplane_days') ? ' is-invalid' : '' }}" id="airplane_days" name="airplane_days" value="{{ $product->airplane_days ?? '' }}" min="0" placeholder="輸入機場提貨指定天數">
                                                            @if ($errors->has('airplane_days'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('airplane_days') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-3">
                                                             <label for="hotel_days"><span class="text-red">* </span>旅店提貨指定天數</label>
                                                             <input type="number" class="form-control {{ $errors->has('hotel_days') ? ' is-invalid' : '' }}" id="hotel_days" name="hotel_days" value="{{ $product->hotel_days ?? '' }}" min="0" placeholder="輸入旅店提貨指定天數">
                                                             @if ($errors->has('hotel_days'))
                                                             <span class="invalid-feedback" role="alert">
                                                                 <strong>{{ $errors->first('hotel_days') }}</strong>
                                                             </span>
                                                             @endif
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label for="shipping_methods">可用物流方式</label>
                                                            <div>
                                                                @foreach($shippingMethods as $shippingMethod)
                                                                <span class="mr-3"><input type="checkbox" id="cachk{{ $shippingMethod->id }}" name="shipping_methods[]" value="{{ $shippingMethod->id }}" {{ isset($product) ? in_array($shippingMethod->id,!empty($product->shipping_methods) ? explode(',',$product->shipping_methods) : []) ? 'checked' : '' : '' }}> {{ $shippingMethod->name }}</span>
                                                                @endforeach
                                                            </div>
                                                            @if ($errors->has('categories'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('categories') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-6" id="allow_country" {{ isset($product) && in_array($shippingMethod->id,!empty($product->shipping_methods) ? explode(',',$product->shipping_methods) : []) ? 'style="display:none"' : '' }}>
                                                            <label for="allow_country_ids">可寄送國家<span class="text-red"> (未選擇代表全部皆可)</span></label>
                                                            <div class="select2-purple">
                                                                <select class="select2" id="allow_country_ids" name="allow_country_ids[]" multiple="multiple" data-placeholder="選擇可寄送國家" data-dropdown-css-class="select2-purple" style="width: 100%;">
                                                                    @foreach($countries as $country)
                                                                    <option value="{{ $country->id }}" {{ isset($product) ? $product->allow_country_ids ? in_array($country->id,explode(',',$product->allow_country_ids)) ? 'selected' : '' : '' : '' }}>{{ $country->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            @if ($errors->has('allow_country_ids'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('allow_country_ids') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label for="vendor_earliest_delivery_date">廠商最快出貨日</label>
                                                            <input type="text" class="form-control datepicker {{ $errors->has('vendor_earliest_delivery_date') ? ' is-invalid' : '' }}" id="vendor_earliest_delivery_date" name="vendor_earliest_delivery_date" value="{{ $product->vendor_earliest_delivery_date ?? '' }}" min="0" placeholder="輸入廠商最快出貨日">
                                                            @if ($errors->has('vendor_earliest_delivery_date'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('vendor_earliest_delivery_date') }}</strong>
                                                            </span>
                                                            @endif
                                                            <span class="text-sm">若設定廠商最快出貨日，將於商品前台提示此商品最快出貨時間，提醒顧客注意。設定後，也會影響顧客挑選配送的送達日期。不需要請留空。</span>
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label for="vendor_latest_delivery_date">廠商最後出貨日</label>
                                                            <input type="text" class="form-control datepicker {{ $errors->has('vendor_latest_delivery_date') ? ' is-invalid' : '' }}" id="vendor_latest_delivery_date" name="vendor_latest_delivery_date" value="{{ $product->vendor_latest_delivery_date ?? '' }}" min="0" placeholder="輸入廠商最後出貨日">
                                                            @if ($errors->has('vendor_latest_delivery_date'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('vendor_latest_delivery_date') }}</strong>
                                                            </span>
                                                            @endif
                                                            <span class="text-sm">若有設定最快出貨日，最快出貨日不能晚於最後出貨時間。</span>
                                                        </div>
                                                        <div class="col-12" id="ticket" {!! isset($product) ?  $product->category_id != 17 ? 'style="display:none"' : '' : 'style="display:none"' !!}>
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <label for="ticket_price"><span class="text-red">* </span>票券面額</label>
                                                                    <input type="text" class="form-control {{ $errors->has('ticket_price') ? ' is-invalid' : '' }}" id="ticket_price" name="ticket_price" value="{{ $product->ticket_price ?? '' }}" min="1" placeholder="輸入票券面額">
                                                                    @if ($errors->has('ticket_price'))
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $errors->first('ticket_price') }}</strong>
                                                                    </span>
                                                                    @endif
                                                                </div>
                                                                <div class="col-4 mt-1">
                                                                        <label for="ticket_merchant_no"><span class="text-red">* </span>票券特店代號</label>
                                                                        <input type="text" class="form-control {{ $errors->has('ticket_merchant_no') ? ' is-invalid' : '' }}" id="ticket_merchant_no" name="ticket_merchant_no" value="{{ $product->ticket_merchant_no ?? '' }}" placeholder="ACPAY票券特店代號">
                                                                        @if ($errors->has('ticket_merchant_no'))
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $errors->first('ticket_merchant_no') }}</strong>
                                                                        </span>
                                                                        @endif
                                                                </div>
                                                                <div class="col-4 mt-1">
                                                                        <label for="ticket_group"><span class="text-red">* </span>票券群組</label>
                                                                        <input type="text" class="form-control {{ $errors->has('ticket_group') ? ' is-invalid' : '' }}" id="ticket_group" name="ticket_group" value="{{ $product->ticket_group ?? '' }}" placeholder="ACPAY票券群組">
                                                                        @if ($errors->has('ticket_group'))
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $errors->first('ticket_group') }}</strong>
                                                                        </span>
                                                                        @endif
                                                                </div>
                                                                <div class="col-12">
                                                                    <label for="ticket_memo">票券使用說明</span></label>
                                                                    <textarea class="form-control" rows="4" id="ticket_memo" name="ticket_memo" placeholder="請輸入票券使用說明. (500字以內)">{{ $product->ticket_memo ?? '' }}</textarea>
                                                                    @if ($errors->has('ticket_memo'))
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $errors->first('ticket_memo') }}</strong>
                                                                    </span>
                                                                    @endif
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- 商品款式選項及資料 --}}
                                                @if(isset($product) && !isset($copy))
                                                    @if($product->model_type == 1)
                                                    <div class="form-group col-12">
                                                        <label><span class="text-red">* </span>商品款式設定</label><br>
                                                        <div class="icheck-primary d-inline mr-2">
                                                            <input type="radio" id="model_one" name="model_type" value="1" {{ $product->model_type == 1 ? 'checked' : '' }}>
                                                            <label for="model_one">單一款式</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        @foreach($product->models as $model)
                                                        @if(in_array($product->status,[1,-3,-9]))
                                                        <span class="text-danger">補貨中、已下架、上架中狀態時無法修改，若需要修改請先將商品狀態改為未送審。</span>
                                                        @endif
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">款式</span>
                                                            </div>
                                                            <input type="text" class="form-control" value="單一款式" disabled>
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">貨號</span>
                                                            </div>
                                                            <input type="hidden" name="product_model_id" value="{{ $model->id ?? '' }}">
                                                            <input type="text" class="form-control" name="sku" value="{{ $model->sku ?? '' }}" disabled>
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">庫存</span>
                                                            </div>
                                                            <input type="number" class="form-control" name="quantity" value="{{ $model->quantity ?? '' }}" min="0" placeholder="輸入庫存量" required>
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">安全庫存</span>
                                                            </div>
                                                            <input type="number" class="form-control" name="safe_quantity" value="{{ $model->safe_quantity ?? '' }}" min="1" placeholder="輸入安全庫存量" required>
                                                            <div class="input-group-append">
                                                                <div class="input-group-text">
                                                                    國際條碼
                                                                </div>
                                                            </div>
                                                            <input type="text" class="form-control" name="gtin13" value="{{ $model->gtin13 ?? '' }}" placeholder="輸入國際碼，共13碼數字">
                                                            <div class="input-group-append">
                                                                <div class="input-group-text">
                                                                    <a href="javascript:" class="text-primary" onclick="getGtin13History({{ $model->id }},'{{ $model->gtin13 }}')">歷史</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @elseif($product->model_type == 2)
                                                    <div class="form-group col-12">
                                                        <label><span class="text-red">* </span>商品款式設定</label><br>
                                                        <div class="icheck-danger d-inline mr-2">
                                                            <input type="radio" id="model_multiple" name="model_type" value="2" {{ $product->model_type == 2 ? 'checked' : '' }}>
                                                            <label for="model_multiple">多種款式</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <div class="form-group col-3">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" id="model_name" name="model_name" value="{{ $product->model_name ?? '' }}" placeholder="範例：顏色、尺寸、形狀..." disabled>
                                                                <div class="input-group-prepend">
                                                                    <a href="javascript:add_model();void(0)" class="btn btn-primary"><span>新增</span></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="mt-2">若有不同顏色包裝或差異時可填寫，並新增款式以區隔</p>
                                                        <div class="form-group col-md-12">
                                                            <table class="table table-hover text-nowrap table-sm">
                                                                <thead>
                                                                  <tr>
                                                                    <th width="10%" class="text-left align-middle">款式分類</th>
                                                                    <th width="25%" class="text-left align-middle">款式名稱</th>
                                                                    <th width="15%" class="text-left align-middle">貨號</th>
                                                                    <th width="10%" class="text-left align-middle">庫存</th>
                                                                    <th width="10%" class="text-left align-middle">安全庫存</th>
                                                                    <th width="15%" class="text-left align-middle">國際條碼</th>
                                                                    <th width="3%" class="text-center align-middle">歷史</th>
                                                                    <th width="2%" class="text-center align-middle">刪除</th>
                                                                  </tr>
                                                                </thead>
                                                                <tbody id="add_model">
                                                                    @foreach($product->models as $model)
                                                                    <tr class="add_model">
                                                                        <td class="text-left align-middle">
                                                                            <span class="input-group-text">{{ $product->model_name ? $product->model_name : '　' }}</span>
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            @if(in_array($product->status,[1,-3,-9]))
                                                                            <input type="hidden" class="form-control" name="model_data[{{ $loop->iteration - 1 }}][name]" value="{{ $model->name ?? '' }}">
                                                                            @endif
                                                                            <input type="text" class="form-control" name="model_data[{{ $loop->iteration - 1 }}][name]" value="{{ $model->name ?? '' }}" {{ in_array($product->status,[1,-3,-9]) ? 'disabled' : '' }} placeholder="範例：紅色" required>
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            <span class="input-group-text">{{ $model->sku }}</span>
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            <input type="number" class="form-control" name="model_data[{{ $loop->iteration - 1 }}][quantity]" value="{{ $model->quantity ?? '' }}" min="0" placeholder="輸入庫存" required>
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            <input type="number" class="form-control" name="model_data[{{ $loop->iteration - 1 }}][safe_quantity]"  value="{{ $model->safe_quantity ?? ''}}" min="1" placeholder="輸入安全庫存" required>
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            <input type="text" class="form-control" name="model_data[{{ $loop->iteration - 1 }}][gtin13]" value="{{ $model->gtin13 ?? ''}}" placeholder="輸入國際碼，共13碼數字">
                                                                            <input type="hidden" name="model_data[{{ $loop->iteration - 1 }}][product_model_id]" value="{{ $model->id ?? '' }}">
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            {{-- <span class="btn btn-sm bg-danger" style="cursor:pointer" onclick="getGtin13History({{ $model->id }},'{{ $model->gtin13 }}')">歷史</span> --}}
                                                                            <a href="javascript:" onclick="getGtin13History({{ $model->id }},'{{ $model->gtin13 }}')">歷史</a>
                                                                        </td>
                                                                        <td class="text-center align-middle">
                                                                            {{-- @if(!in_array($product->status,[1,-3,-9])) --}}
                                                                            {{-- @if(in_array($menuCode.'D',explode(',',Auth::user()->power))) --}}
                                                                                <span class="btn btn-sm bg-danger" style="cursor:pointer" onclick="del_model({{ $model->id }})"><i class="far fa-trash-alt"></i></span>
                                                                            {{-- @endif --}}
                                                                            {{-- @endif --}}
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    @elseif($product->model_type == 3)
                                                    <div class="form-group col-12">
                                                        <label><span class="text-red">* </span>商品款式設定</label><br>
                                                        <div class="icheck-green d-inline mr-2">
                                                            <input type="radio" id="model_package" name="model_type" value="3" {{ $product->model_type == 3 ? 'checked' : '' }}>
                                                            <label for="model_package">組合商品</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12"><span>組合商品功能，可以挑選已新增的單一商品，將各種單一商品依不同數量組合成多種規格。</span></div>
                                                    <div class="form-group input-group col-md-4">
                                                        <input type="text" class="form-control" id="package_name" placeholder="請填寫組合名稱...">
                                                        <div class="input-group-prepend">
                                                            <a href="javascript:add_package();void(0)" class="btn btn-primary"><span>新增</span></a>
                                                        </div>
                                                    </div>
                                                    <div id="add_package" class="form-group col-md-12">
                                                        @foreach($product->packages as $package)
                                                        <div class="card card-outline card-primary add_package">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <table class="table table-bordered table-sm mb-0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>組合名稱</td>
                                                                                <td>
                                                                                    <input type="hidden" class="form-control" name="packageData[{{ $loop->iteration -1}}][product_package_id]" value="{{ $package->id }}">
                                                                                    <input type="text" class="form-control" name="packageData[{{ $loop->iteration -1 }}][name]" value="{{ $package->name }}" placeholder="範例：紅色、尺寸大小" required>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>組合貨號</td>
                                                                                <td>
                                                                                    <input type="hidden" name="packageData[{{ $loop->iteration -1 }}][sku]" value="{{ $package->sku }}">
                                                                                    <span class="input-text">{{ $package->sku }}</span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>庫存</td>
                                                                                <td>
                                                                                    <input type="number" class="form-control" name="packageData[{{ $loop->iteration -1 }}][quantity]" value="{{ $package->quantity }}" min="0" required>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>安全庫存</td>
                                                                                <td>
                                                                                    <input type="number" class="form-control" name="packageData[{{ $loop->iteration -1 }}][safe_quantity]" value="{{ $package->safe_quantity }}" min="0" required>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    <a href="javascript:" class="float-right text-sm text-danger" onclick="del_package({{ $package->id }})"><b>>刪除此組合<</b></a>
                                                                </div>
                                                                <div class="col-8">
                                                                    <table class="table table-bordered table-sm mb-0">
                                                                        <tbody id="add_product_{{ $loop->iteration -1 }}">
                                                                            @foreach($package->lists as $list)
                                                                            <tr>
                                                                                <td width="5%" class="bg-gray">貨號</td>
                                                                                <td width="20%">{{ $list->sku }}</td>
                                                                                <td width="5%" class="bg-gray">品名</td>
                                                                                <td width="40%">{{ $list->name }}</td>
                                                                                <td width="5%" class="bg-gray">數量</td>
                                                                                <td width="10%">
                                                                                    {{ $list->quantity }}
                                                                                    <input type="hidden" class="form-control form-control-sm" name="packageData[{{ $loop->parent->iteration -1 }}][list][{{ $loop->iteration -1 }}][quantity]" value="{{ $list->quantity }}">
                                                                                    <input type="hidden" name="packageData[{{ $loop->parent->iteration -1 }}][list][{{ $loop->iteration -1 }}][product_package_id]" value="{{ $package->id }}">
                                                                                    <input type="hidden" name="packageData[{{ $loop->parent->iteration -1 }}][list][{{ $loop->iteration -1 }}][product_model_id]" value="{{ $list->product_model_id }}">
                                                                                    <input type="hidden" name="packageData[{{ $loop->parent->iteration -1 }}][list][{{ $loop->iteration -1 }}][product_package_list_id]" value="{{ $list->id }}">
                                                                                </td>
                                                                            </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @endif
                                                @else
                                                <div class="form-group col-12">
                                                    <div class="form-group col-12">
                                                        <label><span class="text-red">* </span>商品款式設定</label><br>
                                                        <div class="icheck-primary d-inline mr-2">
                                                            <input type="radio" id="model_one" name="model_type" value="1" checked>
                                                            <label for="model_one">單一款式</label>
                                                        </div>
                                                        <div class="icheck-danger d-inline mr-2">
                                                            <input type="radio" id="model_multiple" name="model_type" value="2">
                                                            <label for="model_multiple">多種款式</label>
                                                        </div>
                                                        <div class="icheck-green d-inline mr-2">
                                                            <input type="radio" id="model_package" name="model_type" value="3">
                                                            <label for="model_package">組合商品</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="models" class="form-group col-md-12">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">款式</span>
                                                        </div>
                                                        <input type="text" class="form-control" value="單一款式" disabled>
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">庫存</span>
                                                        </div>
                                                        <input type="number" class="form-control" name="quantity" min="0" placeholder="輸入庫存量" required>
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">安全庫存</span>
                                                        </div>
                                                        <input type="number" class="form-control" name="safe_quantity" min="1" placeholder="輸入安全庫存量" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">國際條碼</span>
                                                        </div>
                                                        <input type="text" class="form-control" name="gtin13" placeholder="輸入國際碼，共13碼數字">
                                                    </div>
                                                </div>
                                                <div id="add_model_block" class="form-group col-md-12 d-none">
                                                    <table class="table table-hover text-nowrap table-sm">
                                                        <thead>
                                                          <tr>
                                                            <th width="10%" class="text-left align-middle">款式分類</th>
                                                            <th width="25%" class="text-left align-middle">款式名稱</th>
                                                            <th width="15%" class="text-left align-middle">貨號</th>
                                                            <th width="10%" class="text-left align-middle">庫存</th>
                                                            <th width="10%" class="text-left align-middle">安全庫存</th>
                                                            <th width="15%" class="text-left align-middle">國際條碼</th>
                                                            <th width="5%" class="text-center align-middle">刪除</th>
                                                          </tr>
                                                        </thead>
                                                        <tbody id="add_model">
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div id="add_package" class="form-group col-md-12"></div>
                                                @endif
                                                <div class="form-group col-10">
                                                    <label for="description"><span class="text-red">* </span>詳細說明(商品規格)</span><span class="badge badge-secondary">此欄位將作為獨立說明頁面</span></label>
                                                    <textarea class="form-control {{ $errors->has('specification') ? ' is-invalid' : '' }} " rows="3" id="specification" name="specification" placeholder="詳細說明(商品規格)..." required>{{ isset($product) ? $product->specification ?? '' : ''}}</textarea>
                                                    @if ($errors->has('specification'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('specification') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-12">
                                                    <div class="card card-danger">
                                                        <div class="card-header">
                                                            <h3 class="card-title">管理端設定</h3>
                                                        </div>
                                                        <div class="card-body row">
                                                            <div class="form-group col-3">
                                                                <label for="status"><span class="text-red">* </span>商品狀態</label>
                                                                <div class="input-group">
                                                                    @if(isset($copy))
                                                                    <select class="form-control form-control-sm {{ $errors->has('status') ? ' is-invalid' : '' }}" id="status" name="status">
                                                                        <option value="-1" class="text-danger">未送審</option>
                                                                        <option value="0" class="text-info" selected>送審中</option>
                                                                        <option value="-2" class="text-danger">審查退回(不通過)</option>
                                                                        <option value="-3" class="text-danger">停售中</option>
                                                                        <option value="-9" class="text-secondary">已下架</option>
                                                                        <option value="1" class="text-success">上架中</option>
                                                                    </select>
                                                                    @else
                                                                    <select class="form-control form-control-sm {{ $errors->has('status') ? ' is-invalid' : '' }}" id="status" name="status">
                                                                        <option value="-1" class="text-danger" {{ isset($product) ? $product->status == -1 ? 'selected' : '' : 'selectd' }}>未送審</option>
                                                                        <option value="0" class="text-info" {{ isset($product) ? $product->status == 0 ? 'selected' : '' : '' }}>送審中</option>
                                                                        <option value="-2" class="text-danger" {{ isset($product) ? $product->status == -2 ? 'selected' : '' : '' }}>審查退回(不通過)</option>
                                                                        <option value="-3" class="text-danger" {{ isset($product) ? $product->status == -3 ? 'selected' : '' : '' }}>停售中</option>
                                                                        <option value="-9" class="text-secondary" {{ isset($product) ? $product->status == -9 ? 'selected' : '' : '' }}>已下架</option>
                                                                        <option value="1" class="text-success" {{ isset($product) ? $product->status == 1 ? 'selected' : '' : '' }}>上架中</option>
                                                                    </select>
                                                                    @endif
                                                                    @if ($errors->has('category_id'))
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $errors->first('category_id') }}</strong>
                                                                    </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-3">
                                                                <label for="is_hot">設定為熱門商品</label>
                                                                <div class="input-group">
                                                                    <input type="checkbox" name="is_hot" value="{{ isset($product) ? $product->is_hot == 1 ? 0 : 1 : 1 }}" data-bootstrap-switch data-on-text="是" data-off-text="否" data-off-color="secondary" data-on-color="primary" {{ isset($product) ? $product->is_hot == 1 ? 'checked' : '' : '' }}>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-12">
                                                                <label for="verification_reason">狀態變更原因<span class="badge badge-secondary">例如審核退回之原因，或是下架時的備註</span></label>
                                                                <textarea rows="2" class="form-control {{ $errors->has('verification_reason') ? ' is-invalid' : '' }}" id="verification_reason" name="verification_reason" placeholder="狀態變更原因">{{ $product->verification_reason ?? '' }}</textarea>
                                                                @if ($errors->has('verification_reason'))
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $errors->first('verification_reason') }}</strong>
                                                                </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center bg-white">
                                                @if(in_array(isset($product) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                                <button type="submit" class="btn btn-primary">{{ isset($product) && !isset($copy) ? '修改' : '新增' }}</button>
                                                @endif
                                                <a href="{{ url('products') }}" class="btn btn-info">
                                                    <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                                </a>
                                                @if(isset($product) && !isset($copy))
                                                @if(in_array($menuCode.'CP', explode(',',Auth::user()->power)))
                                                <a href="{{ url('products/copy/'.$product->id) }}" class="btn btn-secondary">
                                                    <span class="text-white"><i class="fas fa-copy mr-1"></i>複製</span>
                                                </a>
                                                @endif
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                    {{-- 多國語言資料 --}}
                                    @if(isset($product))
                                    @for($i=0;$i<count($langs);$i++)
                                    <div class="tab-pane" id="lang-{{ $langs[$i]['code'] }}">
                                        <form class="myform_lang" action="{{ route('admin.products.lang', $product->id) }}" method="POST">
                                            <input type="hidden" name="lang" value="{{ $langs[$i]['code'] }}">
                                            <input type="hidden" name="langId" value="{{ $langs[$i]['data']['id'] ?? '' }}">
                                            <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
                                            @csrf
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="row">
                                                        <div class="form-group col-12">
                                                            <label for="brand"><span class="text-red">* </span>廠牌</label>
                                                            <input type="text" class="form-control {{ $errors->has('brand') ? ' is-invalid' : '' }}" id="brand_{{ $langs[$i]['code'] }}" name="brand" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['brand'] : '' }}" placeholder="輸入{{ $langs[$i]['name'] }}廠牌名稱">
                                                            @if ($errors->has('brand'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('brand') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="name"><span class="text-red">* </span>商品名稱</label>
                                                            <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name_{{ $langs[$i]['code'] }}" name="name" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['name'] : '' }}" placeholder="輸入{{ $langs[$i]['name'] }}商品名稱">
                                                            @if ($errors->has('name'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('name') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="serving_size"><span class="text-red">* </span>內容(規格)</label>
                                                            <input type="text" class="form-control {{ $errors->has('serving_size') ? ' is-invalid' : '' }}" id="serving_size_{{ $langs[$i]['code'] }}" name="serving_size" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['serving_size'] : '' }}" placeholder="輸入{{ $langs[$i]['name'] }}內容物或規格">
                                                            @if ($errors->has('serving_size'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('serving_size') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="title"><span class="text-red">* </span>特色 (小標題) <span class="badge badge-info">此欄位將作為商品頁中字體放大的標題</span><small>(建議輸入13個中文字以內)</small></label>
                                                            <input type="text" class="form-control {{ $errors->has('title') ? ' is-invalid' : '' }}" id="title_{{ $langs[$i]['code'] }}" name="title" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['title'] : '' }}" placeholder="請用{{ $langs[$i]['name'] }}說明商品特色">
                                                            @if ($errors->has('title'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('title') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="row">
                                                        <div class="form-group col-12">
                                                            <label for="intro"><span class="text-red">* </span>簡介 (商品描述) <span class="badge badge-info">此欄位將作為商品頁中標題下方的簡單說明</span></label>
                                                            <textarea rows="7" class="form-control {{ $errors->has('intro') ? ' is-invalid' : '' }}" id="intro_{{ $langs[$i]['code'] }}" name="intro" placeholder="輸入{{ $langs[$i]['name'] }}的簡單介紹">{{ $langs[$i]['data']['intro'] ?? '' }}</textarea>
                                                            @if ($errors->has('intro'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('intro') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label for="unable_buy">無法結帳提示</label>
                                                            <input type="text" class="form-control {{ $errors->has('unable_buy') ? ' is-invalid' : '' }}" id="unable_buy_{{ $langs[$i]['code'] }}" name="unable_buy" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['unable_buy'] : '' }}" placeholder="輸入{{ $langs[$i]['name'] }}無法結帳提示">
                                                            @if ($errors->has('unable_buy'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('unable_buy') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                {{-- 多國語言商品款式選項及資料 --}}
                                                @if(isset($product))
                                                    @if($product->model_type == 1)
                                                    <div class="form-group col-12">
                                                        <label><span class="text-red">* </span>商品款式設定</label><br>
                                                        <div class="icheck-primary d-inline mr-2">
                                                            <input type="radio" name="model_type" value="1" {{ $product->model_type == 1 ? 'checked' : '' }}>
                                                            <label for="model_one">單一款式</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        @foreach($product->models as $model)
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">款式</span>
                                                            </div>
                                                            <input type="text" class="form-control" value="單一款式" disabled>
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">貨號</span>
                                                            </div>
                                                            <input type="text" class="form-control" value="{{ $model->sku ?? '' }}" disabled>
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">庫存</span>
                                                            </div>
                                                            <input type="number" class="form-control" value="{{ $model->quantity ?? '' }}" disabled>
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">安全庫存</span>
                                                            </div>
                                                            <input type="number" class="form-control" value="{{ $model->safe_quantity ?? '' }}" min="1" placeholder="輸入安全庫存量" disabled>
                                                            <div class="input-group-append">
                                                                <div class="input-group-text">
                                                                    國際條碼
                                                                </div>
                                                            </div>
                                                            <input type="text" class="form-control" value="{{ $model->gtin13 ?? '' }}" disabled>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @elseif($product->model_type == 2)
                                                    <div class="form-group col-12">
                                                        <label><span class="text-red">* </span>商品款式設定</label><br>
                                                        <div class="icheck-danger d-inline mr-2">
                                                            <input type="radio" name="model_type" value="2" {{ $product->model_type == 2 ? 'checked' : '' }}>
                                                            <label for="model_multiple">多種款式</label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        @if(!in_array($product->status,[1,-3,-9]))
                                                        <div class="form-group col-3">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" name="model_name" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['model_name'] : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}，範例：顏色、尺寸、形狀...">
                                                            </div>
                                                        </div>
                                                        <p class="mt-2">若有不同顏色包裝或差異時可填寫，並新增款式以區隔</p>
                                                        @else
                                                        <div class="form-group col-md-12"><span class="text-danger">補貨中、已下架、上架中狀態時無法修改，若需要修改請先將商品狀態改為未送審。</span></div>
                                                        @endif
                                                        <div class="form-group col-md-12">
                                                            <table class="table table-hover text-nowrap table-sm">
                                                                <thead>
                                                                <tr>
                                                                    <th width="25%" class="text-left align-middle">款式名稱</th>
                                                                    <th width="15%" class="text-left align-middle">貨號</th>
                                                                    <th width="10%" class="text-left align-middle">庫存</th>
                                                                    <th width="10%" class="text-left align-middle">安全庫存</th>
                                                                    <th width="15%" class="text-left align-middle">國際條碼</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($product->models as $model)
                                                                    <input type="hidden" name="model_data[{{ $loop->iteration - 1 }}][product_model_id]" value="{{ $model->id ?? '' }}" {{ in_array($product->status,[1,-3,-9]) ? 'disabled' : '' }}>
                                                                    <tr>
                                                                        <td class="text-left align-middle">
                                                                            @if($langs[$i]['code'] == 'en')
                                                                            <input type="text" class="form-control" name="model_data[{{ $loop->iteration - 1 }}][name_{{ $langs[$i]['code'] }}]" value="{{ $model->name_en ?? '' }}" {{ in_array($product->status,[1,-3,-9]) ? 'disabled' : '' }} placeholder="請輸入{{ $langs[$i]['name'] }}，範例：紅色">
                                                                            @elseif($langs[$i]['code'] == 'jp')
                                                                            <input type="text" class="form-control" name="model_data[{{ $loop->iteration - 1 }}][name_{{ $langs[$i]['code'] }}]" value="{{ $model->name_jp ?? '' }}" {{ in_array($product->status,[1,-3,-9]) ? 'disabled' : '' }} placeholder="請輸入{{ $langs[$i]['name'] }}，範例：紅色">
                                                                            @elseif($langs[$i]['code'] == 'kr')
                                                                            <input type="text" class="form-control" name="model_data[{{ $loop->iteration - 1 }}][name_{{ $langs[$i]['code'] }}]" value="{{ $model->name_kr ?? '' }}" {{ in_array($product->status,[1,-3,-9]) ? 'disabled' : '' }} placeholder="請輸入{{ $langs[$i]['name'] }}，範例：紅色">
                                                                            @elseif($langs[$i]['code'] == 'th')
                                                                            <input type="text" class="form-control" name="model_data[{{ $loop->iteration - 1 }}][name_{{ $langs[$i]['code'] }}]" value="{{ $model->name_th ?? '' }}" {{ in_array($product->status,[1,-3,-9]) ? 'disabled' : '' }} placeholder="請輸入{{ $langs[$i]['name'] }}，範例：紅色">
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            <span class="input-group-text">{{ $model->sku }}</span>
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            <span class="input-group-text">{{ $model->quantity }}</span>
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            <span class="input-group-text">{{ $model->safe_quantity }}</span>
                                                                        </td>
                                                                        <td class="text-left align-middle">
                                                                            <span class="input-group-text">{{ $model->gtin13 ?? '　' }}</span>
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    @elseif($product->model_type == 3)
                                                    <div class="form-group col-12">
                                                        <label><span class="text-red">* </span>商品款式設定</label><br>
                                                        <div class="icheck-green d-inline mr-2">
                                                            <input type="radio" id="model_package_{{ $langs[$i]['code'] }}" name="model_type" value="3" {{ $product->model_type == 3 ? 'checked' : '' }}>
                                                            <label for="model_package_{{ $langs[$i]['code'] }}">組合商品</label>
                                                        </div>
                                                    </div>
                                                    @if(in_array($product->status,[1,-3,-9]))
                                                    <div class="form-group col-md-12"><span class="text-danger">補貨中、已下架、上架中狀態時無法修改，若需要修改請先將商品狀態改為未送審。</span></div>
                                                    @endif
                                                    <div class="form-group col-md-12">
                                                        @foreach($product->packages as $package)
                                                        <div class="card card-outline card-primary">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <table class="table table-bordered table-sm mb-0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>組合名稱</td>
                                                                                <td>
                                                                                    <input type="hidden" class="form-control" name="packageData[{{ $loop->iteration -1}}][product_model_id]" value="{{ $package->product_model_id }}">
                                                                                    @if($langs[$i]['code'] == 'en')
                                                                                    <input type="text" class="form-control" name="packageData[{{ $loop->iteration -1 }}][name_{{ $langs[$i]['code'] }}]" value="{{ $package->name_en }}" placeholder="請輸入{{ $langs[$i]['name'] }}組合名稱">
                                                                                    @elseif($langs[$i]['code'] == 'jp')
                                                                                    <input type="text" class="form-control" name="packageData[{{ $loop->iteration -1 }}][name_{{ $langs[$i]['code'] }}]" value="{{ $package->name_jp }}" placeholder="請輸入{{ $langs[$i]['name'] }}組合名稱">
                                                                                    @elseif($langs[$i]['code'] == 'kr')
                                                                                    <input type="text" class="form-control" name="packageData[{{ $loop->iteration -1 }}][name_{{ $langs[$i]['code'] }}]" value="{{ $package->name_kr }}" placeholder="請輸入{{ $langs[$i]['name'] }}組合名稱">
                                                                                    @elseif($langs[$i]['code'] == 'th')
                                                                                    <input type="text" class="form-control" name="packageData[{{ $loop->iteration -1 }}][name_{{ $langs[$i]['code'] }}]" value="{{ $package->name_th }}" placeholder="請輸入{{ $langs[$i]['name'] }}組合名稱">
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>組合貨號</td>
                                                                                <td>
                                                                                    <span class="input-text">{{ $package->sku }}</span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>庫存</td>
                                                                                <td>
                                                                                    <span class="input-text">{{ $package->quantity }}</span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>安全庫存</td>
                                                                                <td>
                                                                                    <span class="input-text">{{ $package->safe_quantity }}</span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div class="col-8">
                                                                    <table class="table table-bordered table-sm mb-0">
                                                                        @foreach($package->lists as $list)
                                                                        <tbody id="add_product_{{ $loop->parent->iteration -1 }}">
                                                                            <tr>
                                                                                <td width="5%" class="bg-gray">貨號</td>
                                                                                <td width="20%">{{ $list->sku }}</td>
                                                                                <td width="5%" class="bg-gray">品名</td>
                                                                                <td width="40%">{{ $list->name }}</td>
                                                                                <td width="5%" class="bg-gray">數量</td>
                                                                                <td width="10%">{{ $list->quantity }}</td>
                                                                            </tr>
                                                                        </tbody>
                                                                        @endforeach
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @endif
                                                @endif
                                                </div>
                                                <div class="form-group col-10">
                                                    <label for="description"><span class="text-red">* </span>詳細說明(商品規格)</span><span class="badge badge-secondary">此欄位將作為獨立說明頁面</span></label>
                                                    <textarea class="form-control {{ $errors->has('specification') ? ' is-invalid' : '' }} " rows="3" id="specification_{{ $langs[$i]['code'] }}" name="specification" placeholder="{{ $langs[$i]['name'] }}詳細說明(商品規格)..." required>{{ isset($langs[$i]['data']) ? $langs[$i]['data']['specification'] : '' }}</textarea>
                                                    @if ($errors->has('specification'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('specification') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-center bg-white">
                                                @if(in_array(isset($product) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                                <button type="submit" class="btn btn-primary">{{ isset($langs[$i]['data']) ? '修改' : '新增' }}</button>
                                                @endif
                                                <a href="{{ url('products') }}" class="btn btn-info">
                                                    <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                    @endfor
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- 照片資料 --}}
                    <div class="tab-pane fade {{ Session::get('ProductImageShow') ?? '' }}" id="product-image" role="tabpanel" aria-labelledby="product-image-tab">
                        @if(isset($product))
                        <div class="row">
                            <div class="card card-primary card-outline col-4">
                                <form class="img_upload" action="{{ route('admin.productimages.upload', $product->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <div class="card-body">
                                        <div class="text-center mb-2">
                                            <img width="100%" class="filename" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        </div>
                                        <br>
                                        @if(in_array($menuCode.'M', explode(',',Auth::user()->power)))
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" id="filename" name="filename" class="custom-file-input {{ $errors->has('filename') ? ' is-invalid' : '' }}" accept="image/*" required>
                                                    <label class="custom-file-label" for="filename">瀏覽選擇新圖片</label>
                                                </div>
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-md btn-primary btn-block">儲存</button>
                                                </div>
                                            </div>
                                            @if ($errors->has('filename'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('filename') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <p>※ 檔案上傳後，若尺寸過大則自動等比縮小為 1440 x 760 (縮小後最大尺寸) 且並設定為未啟用，請先瀏覽檢查確認後再將其啟用。</p>
                                        @else
                                        <h3 class="text-danger">※ 上傳功能已被關閉，請確認您的帳號擁有修改商品照片權限。</h3>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            @if(count($product->images) > 0)
                            <div class="card card-primary card-outline col-8">
                                <p>置頂圖片順序最優先，排序次之，啟用最後，若要使用置頂功能請先啟用該圖片。</p>
                                <div class="row">
                                    @foreach($product->images as $image)
                                    <div class="col-4 mt-1">
                                        <div class="small-box bg-gray">
                                            <div class="inner">
                                                <a href="{{ $image->filename }}" data-toggle="lightbox" data-title="{{ $product->name .' 圖片 '.$loop->iteration }}" data-gallery="gallery" data-max-width="1024">
                                                    <img src="{{ $image->filename }}" alt="Product Image" class="img-fluid">
                                                </a>
                                            </div>
                                            @if(in_array(isset($product) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                            <div class="bg-white">
                                                <table>
                                                    <tr>
                                                        <td><span class="ml-1">{{ $image->sort }}. </span></td>
                                                        <td>
                                                            <form action="{{ route('admin.productimages.top', $image->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="is_top" value="{{ $image->is_top == 0 ? 1 : 0 }}">
                                                                <button type="submit" class="btn" title="{{ $image->is_top == 0 ? '置頂' : '取消置頂' }}"><i class="fas fa-arrow-circle-{{ $image->is_top == 0 ? 'up' : 'down' }} mr-1 ml-1 {{ $image->is_top == 0 ? 'text-secondary' : 'text-primary' }}"></i></button>
                                                            </form>
                                                        </td>
                                                        <td>
                                                            @if($image->sort != 1)
                                                            <a href="{{ url('productimages/sortup/' . $image->id) }}" class="btn" title="向上排序"><i class="fas fa-arrow-circle-left mr-1 ml-1"></i></a>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($image->sort != count($product->images))
                                                            <a href="{{ url('productimages/sortdown/' . $image->id) }}" class="btn" title="向下排序"><i class="fas fa-arrow-circle-right mr-1 ml-1"></i></a>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="custom-control custom-switch custom-switch-on-success" title="{{ $image->is_on == 1 ? '啟用中' : '停用中' }}">
                                                                <form action="{{ url('productimages/active/' . $image->id) }}" method="POST">
                                                                    @csrf
                                                                    <input type="checkbox" name="is_on" class="custom-control-input" id="active{{ $image->id }}"
                                                                        onclick="submit(this)" {{ $image->is_on == 1 ? 'checked' : '' }}>
                                                                    <label style="cursor:pointer" class="custom-control-label" for="active{{ $image->id }}"></label>
                                                                </form>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <form action="{{ route('admin.productimages.destroy', $image->id) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="_method" value="DELETE">
                                                                <a href="javascript:" class="btn text-primary delete-btn" title="刪除"><i class="fa fa-trash-alt text-danger mr-1 ml-1"></i></a>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <div class="card-body">
                                <h3>尚未上傳圖片，請從左邊瀏覽選擇圖片上傳</h3>
                            </div>
                            @endif
                        </div>
                        @else
                        <div class="card-body">
                            <h3>請先建立商品資料</h3>
                        </div>
                        @endif
                    </div>
                    {{-- 舊站照片資料 --}}
                    @if(isset($product))
                    <div class="tab-pane fade {{ Session::get('OldProductImageShow') ?? '' }}" id="old-product-image" role="tabpanel" aria-labelledby="old-product-image-tab">
                        <div>
                            <p>※ 此為目前前台使用照片，未來新版商品照片請由左邊標籤建立新版商品照片。</p>
                            <p>※ 檔案上傳後，若尺寸過大則自動等比縮小為 1440 x 760 (縮小後最大尺寸)。</p>
                            @if(!in_array($menuCode.'M', explode(',',Auth::user()->power)))
                            <p class="text-danger">※ 目前上傳功能已關閉，請確認你的權限。</p>
                            @endif
                        </div>
                        <div class="row">
                            @for($i=0;$i<count($oldImages);$i++)
                            @if(in_array($menuCode.'M', explode(',',Auth::user()->power)))
                            <div class="card card-primary card-outline col-3">
                                <form class="" action="{{ route('admin.products.upload') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="column_name" value="new_photo{{ $i+1 }}">
                                    <div class="card-body">
                                        <div class="form-group mb-2">
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" id="new_photo{{ $i+1 }}" name="new_photo{{ $i+1 }}" class="custom-file-input {{ $errors->has('filename') ? ' is-invalid' : '' }}" accept="image/*" required>
                                                    <label class="custom-file-label" for="new_photo{{ $i+1 }}">瀏覽選擇新圖片</label>
                                                </div>
                                                <div class="input-group-append">
                                                    <button type="submit" class="btn btn-md btn-primary btn-block">儲存</button>
                                                </div>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-md btn-danger btn-block btn-delete" value="{{ $product->id.'-new_photo'.($i+1) }}">刪除</button>
                                                </div>
                                            </div>
                                            @if ($errors->has('new_photo'.($i+1)))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('new_photo'.($i+1)) }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        @if(!empty($oldImages[$i]))
                                        <div class="text-center">
                                            <img width="100%" class="new_photo{{ $i+1 }}" src="{{ env('AWS_FILE_URL').$oldImages[$i] }}" alt="">
                                        </div>
                                        @else
                                        <div class="text-center">
                                            <img width="100%" class="new_photo{{ $i+1 }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            @else
                            @if(!empty($oldImages[$i]))
                            <div class="card card-primary card-outline col-3">
                            <div class="text-center mb-2">
                                <img width="100%" class="filename" src="{{ env('AWS_FILE_URL').$oldImages[$i] }}" alt="">
                            </div>
                            </div>
                            @endif
                            @endif
                            @endfor
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <form id="delModelForm" action="{{ route('admin.products.delmodel') }}" method="POST">
            @csrf
        </form>
        <form id="delPackageForm" action="{{ route('admin.products.delpackage') }}" method="POST">
            @csrf
        </form>
        <form id="delListForm" action="{{ route('admin.products.dellist') }}" method="POST">
            @csrf
        </form>
        <form id="delOldImageForm" action="{{ route('admin.products.deloldimage') }}" method="POST">
            @csrf
        </form>
    </section>
</div>
@endsection

@section('modal')

{{-- 搜尋產品 Modal --}}
<div id="myModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">選擇商品(商品名稱有英文逗號的請不要用於組合商品)</h4><br><br>
                    <div class="form-group input-group">
                        <input type="text" class="form-control" name="search" placeholder="搜尋商品，可輸入商家名稱、商品名稱或貨號做模糊搜尋" title="搜尋商品，可輸入商家名稱、商品名稱或貨號做模糊搜尋" aria-label="Search">
                        <button id="search" class="btn btn-info" title="搜尋商品，可輸入貨號或商品名稱做模糊搜尋"><i class="fas fa-search"></i>搜尋</button>
                    </div>
                </div>
                <div id="result"></div>
            </div>
        </div>
    </div>
</div>

{{-- 歷史紀錄 Modal --}}
<div id="historyModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title" id="historyModalTitle"></h4><br><br>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th width="10%">#</th>
                            <th width="25%">原值</th>
                            <th width="20%">修改後</th>
                            <th width="20%">註記者</th>
                            <th width="25%">新增時間</th>
                        </tr>
                    </thead>
                    <tbody id="historyRecord"></tbody>
                </table>
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
{{-- Ekko Lightbox --}}
<link rel="stylesheet" href="{{ asset('vendor/ekko-lightbox/dist/ekko-lightbox.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
@endsection

@section('script')
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{{-- Ckeditor 4.x --}}
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
{{-- Ekko Lightbox --}}
<script src="{{ asset('vendor/ekko-lightbox/dist/ekko-lightbox.min.js') }}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\ProductsRequest', '#myform'); !!}
{!! JsValidator::formRequest('App\Http\Requests\Admin\ProductImagesUploadRequest', '.img_upload'); !!}
{!! JsValidator::formRequest('App\Http\Requests\Admin\ProductsLangRequest', '.myform_lang'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        var editor = CKEDITOR.replace( 'specification', {
            height : '40em',
            extraPlugins: 'font,justify,panelbutton,colorbutton,colordialog,editorplaceholder',
            editorplaceholder: '請填寫詳細說明描述商品或規格...',
            // removeButtons: "Image,Scayt,PasteText,PasteFromWord,Outdent,Indent", // 不要的按鈕
        });
        // editor.on( 'required', function( evt ) {
        //     editor.showNotification( '請輸入資料再按儲存.', 'warning' );
        //     evt.cancel();
        // } );
        if($('#specification_en').length > 0){
            var editor_en = CKEDITOR.replace( 'specification_en', { height : '20em', extraPlugins: 'editorplaceholder', editorplaceholder: '請用英文詳細說明描述商品或規格...' });
        }
        if($('#specification_jp').length > 0){
            var editor_jp = CKEDITOR.replace( 'specification_jp', { height : '20em', extraPlugins: 'editorplaceholder', editorplaceholder: '請用日文詳細說明描述商品或規格...' });
        }
        if($('#specification_kr').length > 0){
            var editor_kr = CKEDITOR.replace( 'specification_kr', { height : '20em', extraPlugins: 'editorplaceholder', editorplaceholder: '請用韓文詳細說明描述商品或規格...' });
        }
        if($('#specification_th').length > 0){
            var editor_th = CKEDITOR.replace( 'specification_th', { height : '20em', extraPlugins: 'editorplaceholder', editorplaceholder: '請用泰文詳細說明描述商品或規格...' });
        }

        var tab = window.location.hash;
        if(tab){
            if(tab.split('-')[0] == '#lang'){
                let lang = tab.split('#')[1];
                $('#chinese').removeClass('active');
                $('.chinese').removeClass('active');
                $(tab).addClass('active');
                $('.'+lang).addClass('active');
                $('#product-desc-tab').addClass('active');
                $('#product-desc').addClass('active');
                $('#product-desc').addClass('show');
            }else{
                $('#product-desc-tab').removeClass('active');
                $('#product-desc').removeClass('active');
                $('#product-desc').removeClass('show');
                $(tab+'-tab').addClass('active');
                $(tab).addClass('active');
                $(tab).addClass('show');
            }
        }else{
            $('#product-desc-tab').addClass('active');
            $('#product-desc').addClass('active');
            $('#product-desc').addClass('show');
            $('#chinese').addClass('active');
            $('.chinese').addClass('active');
        }

        //Initialize Select2 Elements
        $('.select2').select2({
            closeOnSelect: false,
            scrollAfterSelect: true,
        });

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            closeOnSelect: false,
            scrollAfterSelect: true,
            selectOnClose: true,
        });

        $('.datepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                alwaysShowClose: true
            });
        });

        // 防止重複點擊
        $('button[type=submit]').click(function() {
            const btn = $(this);
            btn.prop('disabled', true);
            setTimeout(function() {
                btn.prop('disabled', false);
            }, 2000);
            $(this).parents('form').submit();
        });

        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).prop('disabled', true);
                $(this).parents('form').submit();
            };
        });

        $('#cachk4').click(function(){
            $('#allow_country').toggle('display');
        });

        $('#result').html('');
        $('input[name=model_type]').change(function (e) {
            let type1 = '<div class="input-group"><div class="input-group-prepend"><span class="input-group-text">款式</span></div><input type="text" class="form-control" value="單一款式" disabled><div class="input-group-prepend"><span class="input-group-text">庫存</span></div><input type="number" class="form-control" name="quantity[]" min="0" placeholder="輸入庫存量" required><div class="input-group-prepend"><span class="input-group-text">安全庫存</span></div><input type="number" class="form-control" name="safe_quantity[]" min="1" placeholder="輸入安全庫存量" required><div class="input-group-append"><div class="input-group-text">國際條碼</div></div><input type="text" class="form-control" name="gtin13[]" placeholder="輸入國際碼，共13碼數字"></div>';
            let type2 = '<div class="form-group col-3"><div class="input-group"><input type="hidden" name="model_name"><input type="text" class="form-control" id="model_name" placeholder="範例：顏色、尺寸、形狀..."><div class="input-group-prepend"><a href="javascript:add_model();void(0)" class="btn btn-primary"><span>新增</span></a></div></div></div>';
            let type3 = '<div class="form-group col-md-12"><span>組合商品功能，可以挑選已新增的單一商品，將各種單一商品依不同數量組合成多種規格。</span></div><div class="form-group input-group col-md-4"><input type="text" class="form-control" id="package_name" placeholder="請填寫組合名稱..."><div class="input-group-prepend"><a href="javascript:add_package();void(0)" class="btn btn-primary"><span>新增</span></a></div></div>';
            let model_type = $(this).val();
            $('#models').html('');
            $('#result').html('');
            if(model_type == 1){
                $('#models').html(type1);
                $('#add_model').html('');
                $('#add_package').html('');
                $('#add_model_block').addClass('d-none');
            }else if(model_type ==2){
                $('#models').html(type2);
                $('#add_package').html('');
            }else if(model_type ==3){
                $('#models').html(type3);
                $('#add_model').html('');
                $('#add_model_block').addClass('d-none');
            }
        });
        $('#search').click(function (e) {
            let search = $('input[name=search]').val();
            let token = '{{ csrf_token() }}';
            let t = $('input[name=t]').val();
            let m = $('input[name=method]').val();
            let url = 'getlist'
            m == 'copy' ? url = '../getlist' : '';
            if(search){
                $.ajax({
                    type: "post",
                    url: url,
                    data: { search: search, _token: token },
                    success: function(output) {
                        if(output.length > 0){
                            let h = '<div class="card-body"><table class="table table-striped projects"><thead><tr><th width="10%">貨號</th><th width="20%">商家名稱</th><th width="20%">款式/款式名稱</th><th width="40%">產品名稱</th><th width="10%">選擇</th></tr></thead><tbody>';
                            let c = '';
                            let f = '</tbody></table></div>';
                            for(let i=0 ; i<output.length ; i++){
                                c = c + '<tr><td>'+output[i]['sku']+'</td><td>'+output[i]['vendor_name']+'</td><td>'+output[i]['model_name']+'</td><td>'+output[i]['name']+'</td><td><button class="btn btn-primary" onclick="selectProduct(this)" value="'+output[i]['product_model_id']+'_'+output[i]['sku']+'_'+output[i]['name']+'_'+t+'">選我</button></td></tr>';
                            }
                            $('#result').html(h+c+f);
                        }else{
                            let h = '<div class="card-body"><h3>查無資料</h3></div>';
                            $('#result').html(h);
                        }
                    }
                });
            }else{
                alert('請輸入貨號或商品名稱');
            }
        });
        $('#category_id').change(function(){
            let token = '{{ csrf_token() }}';
            let productId = '{{ isset($product) ? $product->id : '' }}';
            let cateId = $(this).val();
            let html = '';
            if(cateId == ''){
                html += '<span class="text-danger text-bold">請先選擇商品分類</span>';
                $('#subcate').html(html);
            }else{
                $.ajax({
                    type: "post",
                    url: 'getSubCate',
                    data: { product_id: productId, category_id: cateId, _token: token },
                    success: function(output) {
                        console.log(output);
                        if(output.length > 0){
                            for(let i=0;i<output.length;i++){
                                let chk = output[i]['chk'];
                                html +='<span class="mr-3"><input type="checkbox" id="subcate'+i+'" name="sub_categories[]" value="'+output[i]['id']+'"  '+chk+'> '+output[i]['name']+'</span>';
                            }
                        }else{
                            html += '<span class="text-danger text-bold">查無次分類資料，請先建立對應的次分類資料。</span>';
                        }
                        $('#subcate').html(html);
                    }
                });
            }
            if($(this).val() == 17){
                $('#ticket_group').prop('disabled',false);
                $('#ticket_price').prop('disabled',false);
                $('#ticket_memo').prop('disabled',false);
                $('#ticket').show();
            }else{
                $('#ticket_group').prop('disabled',true);
                $('#ticket_price').prop('disabled',true);
                $('#ticket_memo').prop('disabled',true);
                $('#ticket').hide();
            }
        })

        $('.btn-delete').click(function(){
            let form = $('#delOldImageForm');
            let id = $(this).val().split('-')[0];
            let columnName = $(this).val().split('-')[1];
            if(confirm("請確認是否刪除此圖片?")){
                form.append($('<input type="hidden" class="formappend" name="id" value="'+id+'">'));
                form.append($('<input type="hidden" class="formappend" name="columnName" value="'+columnName+'">'));
                $(this).prop('disabled', true);
                form.submit();
            }
        });
    })(jQuery);

    function add_model(){
        let status = $('#status').val();
        let model_name = $('#model_name').val();
        let x = 0;
        // if(status == 1 || status == -9 || status == -3){
        //     alert('補貨中、已下架、上架中狀態時無法使用！');
        // }else{
            if(model_name){
                $('#add_model_block').removeClass('d-none');
                $('input[name=model_name]').val(model_name);
                $('#model_name').prop('disabled',true);
                $('.add_model input').length > 0 ? x = $('.add_model input').length / 5 : x = 0;
                let add_model = '<tr class="add_model"><td class="text-left align-middle"><span class="input-group-text">'+model_name+'</span></td><td class="text-left align-middle"><input type="text" class="form-control" name="model_data['+x+'][name]" placeholder="範例：紅色" required></td><td class="text-left align-middle"><span class="input-group-text">自動產生免填</span></td><td class="text-left align-middle"><input type="number" class="form-control" name="model_data['+x+'][quantity]" min="0" placeholder="輸入庫存" required></td><td class="text-left align-middle"><input type="number" class="form-control" name="model_data['+x+'][safe_quantity]" min="1" placeholder="輸入安全庫存" required></td><td class="text-left align-middle"><input type="text" class="form-control" name="model_data['+x+'][gtin13]" placeholder="輸入國際碼，共13碼數字"></td><td class="text-center align-middle"><input type="hidden" name="model_data['+x+'][product_model_id]" value=""><span class="btn btn-sm bg-danger" style="cursor:pointer" onclick="remove_model(this)"><i class="far fa-trash-alt"></i></span></td></tr>';
                $('#add_model').prepend(add_model);
            }else{
                alert('請先輸入款式，範例:顏色、尺寸、形狀');
            }
        // }
    }

    function remove_model(o){
        if(confirm('remove_model 請確認是否要移除這筆資料?')){
            $(o).parent().parent().remove();
            if($('.add_model input').length == 0){
                let type2 = '<div class="form-group col-3"><div class="input-group"><input type="hidden" name="model_name"><input type="text" class="form-control" id="model_name" placeholder="範例：顏色、尺寸、形狀..."><div class="input-group-prepend"><a href="javascript:add_model();void(0)" class="btn btn-primary"><span>新增</span></a></div></div></div>';
                $('#models').html(type2);
                $('#add_model_block').addClass('d-none');
            }else{
                $('#add_model_block').removeClass('d-none');
            }
        }
        let model = $('#add_model').html();
        let check = '{{  isset($product) ? true : false }}';
        if(model){
            $('#model_name').attr('disabled','disabled');
        }else{
            if(check == 'false'){
                $('#model_name').removeAttr('disabled');
                $('#model_name').val('');
            }
        }
    }

    function add_package(){
        let status = $('#status').val();
        let package_name = $('#package_name').val();
        let timestamp=Math.floor((new Date()).getTime() / 1000);
        let vid = "{{ isset($vendor) ? $vendor->id : '' }}";
        let vendorId = String(vid).padStart(5, '0');
        let sku="BOM"+vendorId+timestamp;
        // if(status == 1 || status == -9 || status == -3){
        //     alert('補貨中、已下架、上架中狀態時無法使用！');
        // }else{
            if(package_name){
                $('.add_package .table').length > 0 ? t = ($('.add_package table').length / 2) : t = 0;
                let add_package = '<div class="card card-outline card-primary add_package"><div class="row"><div class="col-4"><table class="table table-bordered table-sm mb-0"><tbody><tr><td class="align-middle">組合名稱</td><td><input type="hidden" class="form-control" name="packageData['+t+'][product_package_id]" value=""><input type="text" class="form-control" name="packageData['+t+'][name]" value="'+package_name+'"></td></tr><tr><td class="align-middle">組合貨號</td><td><input type="hidden" class="form-control" name="packageData['+t+'][sku]" value="'+sku+'">'+sku+'</td></tr><tr><td class="align-middle">庫存</td><td><input type="text" class="form-control" name="packageData['+t+'][quantity]" required></td></tr><tr><td class="align-middle">安全庫存</td><td><input type="text" class="form-control" min="1" name="packageData['+t+'][safe_quantity]" required></td></tr></tbody></table><a href="javascript:" class="float-right text-sm text-danger" onclick="remove_package(this)"><b>>刪除此組合<</b></a></div><div class="col-8"><table class="table table-bordered table-sm mb-0"><a href="javascript:add_product('+t+');void(0)"><span class="text-sm"><b>增加商品</b></span></a><tbody id="add_product_'+t+'"></tbody></table></div></div></div>';
                $('#add_package').prepend(add_package);
                $('#package_name').val('');
            }else{
                alert('請先輸入組合商品名稱');
            }
        // }
    }

    function add_product(t){
        $('#result').html(''); //開啟modal前清除搜尋資料
        $('#result').html('<input type="hidden" name="t" value="'+t+'">'); //x為第幾個新增的組合商品須帶入到陣列中
        $('#myModal').modal('show');
    }

    function selectProduct(o){
        product_model_id = o.value.split('_')[0];
        sku = o.value.split('_')[1];
        name = o.value.split('_')[2];
        t = o.value.split('_')[3];
        $('#result').html(''); //選擇完成後清除搜尋資料
        $('#myModal').modal('hide'); //關閉搜尋框
        $('#add_product_'+t+' input').length > 0 ? x = $('#add_product_'+t+' input').length / 4 : x = 0;
        let add_product = '<tr><input type="hidden" name="packageData['+t+'][list]['+x+'][product_package_id]" value=""><input type="hidden" name="packageData['+t+'][list]['+x+'][product_model_id]" value="'+product_model_id+'"><input type="hidden" name="packageData['+t+'][list]['+x+'][sku]" value="'+sku+'"><td width="5%" class="bg-gray align-middle">貨號</td><td width="20%" class="align-middle">'+sku+'</td><td width="5%" class="bg-gray align-middle">品名</td><td width="40%" class="align-middle">'+name+'</td><td width="5%" class="bg-gray align-middle">數量</td><td width="10%" class="align-middle"><input type="number" class="form-control" name="packageData['+t+'][list]['+x+'][quantity]" value="0" min="0"></td><td width="5%" class="align-middle"><div class="input-group-append"><span class="input-group-text bg-danger" style="cursor:pointer" onclick="remove_package_product(this)"><i class="far fa-trash-alt"></i></span></div></td></tr>';
        $('#add_product_'+t).prepend(add_product);
    }

    function remove_package(o){
        if(confirm('remove_package 請確認是否要移除這筆資料?')){
            $(o).parent().parent().parent().remove();
        };
    }
    function remove_package_product(o){
        if(confirm('remove_package_product 請確認是否要移除這個組合商品資料?')){
            $(o).parent().parent().parent().remove();
        };
    }

    function del_model(o){
        if(confirm('del_model 請確認是否要刪除這筆資料?')){
            let form = $('#delModelForm');
            form.append($('<input type="hidden" class="formappend" name="id" value="'+o+'">'));
            form.submit();
        };
    }

    function del_package(o){
        if(confirm('del_package 請確認是否要刪除這筆資料?')){
            let form = $('#delPackageForm');
            form.append($('<input type="hidden" class="formappend" name="id" value="'+o+'">'));
            form.submit();
        };
    }

    function del_list(o){
        if(confirm('del_list 請確認是否要刪除這筆資料?')){
            let form = $('#delListForm');
            form.append($('<input type="hidden" class="formappend" name="id" value="'+o+'">'));
            form.submit();
        };
    }

    $('input[type=file]').change(function(x) {
        defaultimg = '{{ asset('img/sample_upload.png') }}';
        name = this.name;
        file = x.currentTarget.files;
        if (file.length >= 1) {
            filename = checkMyImage(file);
            filename = file[0].name; //不檢查檔案直接找出檔名
            if (filename) {
                readURL(this, '.' + name);
                $('label[for=' + name + ']').html(filename);
            } else {
                $(this).val('');
                $('label[for=' + name + ']').html('瀏覽選擇新圖片');
                $('.' + name).attr('src', defaultimg); //沒照片時還原成預設照片
            }
        } else {
            $(this).val('');
            $('label[for=' + name + ']').html('瀏覽選擇新圖片');
            $('.' + name).attr('src', defaultimg); //沒照片時還原成預設照片
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

    function getGtin13History(productModelId,gtin13){
        $('#historyModalTitle').html('商品條碼修改紀錄');
        $('#historyRecord').html('');
        let token = '{{ csrf_token() }}';
        let html = '';
        $.ajax({
            type: "post",
            url: 'getGtin13History',
            data: { product_model_id: productModelId, gtin13: gtin13, _token: token },
            success: function(data) {
                if(data.length > 0){
                    for(i=0;i<data.length;i++){
                        let admin = data[i]['admin_name'];
                        admin == null ? admin = data[i]['vendor_name'] : '';
                        let before = data[i]['before_gtin13'];
                        let after = data[i]['after_gtin13'];
                        let time = data[i]['createTime'];
                        let reason = data[i]['reason'];
                        before == null ? before = '' : '';
                        i == 0 && before == '' ? before = '初始建立' : '';
                        html += '<tr><td>'+(i+1)+'</td><td>'+before+'</td><td>'+after+'</td><td>'+reason+'</td><td>'+admin+'</td><td>'+time+'</td></tr>';
                    }
                    $('#historyRecord').html(html);
                }else{
                    $('#historyRecord').html('查無資料');
                }
                $('#historyModal').modal('show');
            }
        });
    }

    function history(columnName, productId,title){
        $('#historyModalTitle').html(title);
        $('#historyRecord').html('');
        let token = '{{ csrf_token() }}';
        let html = '';
        $.ajax({
            type: "post",
            url: 'getHistory',
            data: { column : columnName, product_id: productId, _token: token },
            success: function(data) {
                if(data.length > 0){
                    for(i=0;i<data.length;i++){
                        let admin = data[i]['admin_name'];
                        admin == null ? admin = $data[i]['vendor_name'] : '';
                        let before = data[i]['before_value'];
                        let after = data[i]['after_value'];
                        let time = data[i]['createTime'];
                        before == null ? before = '' : '';
                        i == 0 && before == '' ? before = '初始建立' : '';
                        html += '<tr><td>'+(i+1)+'</td><td>'+before+'</td><td>'+after+'</td><td>'+admin+'</td><td>'+time+'</td></tr>';
                    }
                    $('#historyRecord').html(html);
                }else{
                    $('#historyRecord').html('查無資料');
                }
                $('#historyModal').modal('show');

            }
        });
    }
</script>
@endsection
