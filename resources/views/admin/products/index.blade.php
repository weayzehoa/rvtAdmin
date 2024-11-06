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
                    <h1 class="m-0 text-dark"><b>商品管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('products') }}">商品管理</a></li>
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
                                <button id="showForm" class="btn btn-sm btn-success mr-2" title="使用欄位查詢">使用欄位查詢</button>
                                @if($vendorId)
                                @if(in_array($menuCode.'N',explode(',',Auth::user()->power)))
                                <a href="{{ route('admin.products.create', 'vendor_id='.$vendorId) }}" class="btn btn-sm btn-primary mr-2"><i class="fas fa-plus mr-1"></i>新增</a>
                                @endif
                                @endif
                                @if(in_array($menuCode.'IM',explode(',',Auth::user()->power)))
                                <button id="showImportForm" class="btn btn-sm btn-warning mr-2" title="匯入變價功能">匯入變價功能</button>
                                <button id="showUpDownModal" class="btn btn-sm btn-primary mr-2" title="匯入變價功能">匯入上下架功能</button>
                                {{-- <button id="productImportForm" class="btn btn-sm btn-primary mr-2" title="匯入商品功能">匯入商品功能</button> --}}
                                @endif
                            </div>
                            <div class="float-right">
                                <div class="input-group input-group-sm align-middle align-items-middle">
                                    <span class="badge badge-purple text-lg mr-2">總筆數：{{ $products->total() != 0 ? number_format($products->total()) : 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="col-6 float-left">
                                <span clas="d-flex align-items-center">查詢條件：</span>
                                <span class="badge badge-info mr-1">
                                    @if(!empty($status) && $status != '1,0,-1,-2,-3,-9')
                                    <span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('status')">X</span>
                                    @endif
                                    商品狀態：
                                    @if(empty($status))全部@else
                                    @if($status == '1,0,-1,-2,-3,-9')全部@else
                                    @if(in_array(-1,explode(',',$status)))未送審(草稿),@endif
                                    @if(in_array(-2,explode(',',$status)))審核不通過,@endif
                                    @if(in_array(-3,explode(',',$status)))暫停銷售,@endif
                                    @if(in_array(-9,explode(',',$status)))已下架,@endif
                                    @if(in_array(1,explode(',',$status)))上架中,@endif
                                    @if(in_array(0,explode(',',$status)))送審中,@endif
                                    @endif
                                    @endif
                                </span>
                                <span class="badge badge-info mr-1">
                                    @if(!empty($shipping_method) && $shipping_method != '1,2,3,4,5,6')
                                    <span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('shipping_method')">X</span>
                                    @endif
                                    物流方式：
                                    @if(empty($shipping_method))全部@else
                                    @if($shipping_method == '1,2,3,4,5,6')全部@else
                                    @if(in_array(1,explode(',',$shipping_method)))機場提貨,@endif
                                    @if(in_array(2,explode(',',$shipping_method)))旅店提貨,@endif
                                    @if(in_array(3,explode(',',$shipping_method)))現場提貨,@endif
                                    @if(in_array(4,explode(',',$shipping_method)))寄送海外,@endif
                                    @if(in_array(5,explode(',',$shipping_method)))寄送台灣,@endif
                                    @if(in_array(6,explode(',',$shipping_method)))寄送當地@endif
                                    @endif
                                    @endif
                                </span>
                                @if(!empty($created_at) || !empty($created_at_end))
                                <span class="badge badge-info mr-1"><span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('created_at')">X </span>
                                    上架時間區間：
                                    @if(!empty($created_at)){{ $created_at.' ' }}@else{{ '2015-01-01 00:00:00' }}@endif
                                    @if(!empty($created_at_end)){{ '至 '.$created_at_end.' ' }}@else{{ '至 現在' }}@endif
                                </span>
                                @endif
                                @if(!empty($pass_time) || !empty($pass_time_end))
                                <span class="badge badge-info mr-1"><span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('pass_time')">X </span>
                                    送審通過區間：
                                    @if(!empty($pass_time)){{ $pass_time.' ' }}@else{{ '2015-01-01 00:00:00' }}@endif
                                    @if(!empty($pass_time_end)){{ '至 '.$pass_time_end.' ' }}@else{{ '至 現在' }}@endif
                                </span>
                                @endif
                                @if(!empty($category_id))
                                <span class="badge badge-info mr-1">
                                    <span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('category_id')">X</span> 產品分類：
                                @foreach($categories as $category)
                                    @if($category->id == $category_id)
                                    {{ $category->name }}
                                    @endif
                                @endforeach
                                </span>@endif
                                @if(!empty($vendor_id))
                                <span class="badge badge-info mr-1">
                                    <span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('vendor_id')">X</span> 產品分類：
                                @foreach($vendors as $vendor)
                                    @if($vendor->id == $vendor_id)
                                    {{ $vendor->name }}
                                    @endif
                                @endforeach
                                </span>@endif
                                @if(!empty($product_name))<span class="badge badge-info mr-1"><span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('product_name')">X</span> 產品名稱：{{ $product_name }}</span>@endif
                                @if(!empty($vendor_name))<span class="badge badge-info mr-1"><span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('vendor_name')">X</span> 商家名稱：{{ $vendor_name }}</span>@endif
                                @if(!empty($sku))<span class="badge badge-info mr-1"><span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('sku')">X</span> 產品貨號：{{ $sku }}</span>@endif
                                @if(!empty($digiwin_no))<span class="badge badge-info mr-1"><span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('digiwin_no')">X</span> 鼎新品號：{{ $digiwin_no }}</span>@endif
                                @if(!empty($low_quantity))<span class="badge badge-info mr-1"><span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('low_quantity')">X</span> 低於安全庫存</span>@endif
                                @if(!empty($zero_quantity))<span class="badge badge-info mr-1"><span class="text-danger text-bold remove-btn" style="cursor:pointer" onclick="removeCondition('zero_quantity')">X</span> 庫存小於等於0</span>@endif
                                @if(!empty($list))<span class="badge badge-info mr-1">每頁：{{ $list }} 筆</span>@endif
                            </div>
                            <div class="col-6 float-right">
                                @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                <div class="float-right d-flex align-items-center">
                                        <div class="icheck-primary d-inline mr-2">
                                            <input type="radio" id="selectBox" name="exportMethod" value="selected">
                                            <label for="selectBox">自行勾選 <span id="chkallbox_text"></span></label>
                                        </div>
                                        <div class="icheck-primary d-inline mr-2">
                                            <input type="radio" id="chkallbox" name="exportMethod" value="allOnPage">
                                            <label for="chkallbox">目前頁面全選</label>
                                        </div>
                                        <div class="icheck-primary d-inline mr-2">
                                            <input type="radio" id="querBox" name="exportMethod" value="byQuery">
                                            <label for="querBox">依查詢條件</label>
                                        </div>
                                        {{-- <div class="icheck-primary d-inline mr-2">
                                            <input type="radio" id="allData" name="exportMethod" value="allData">
                                            <label for="allData">全部商品(含已刪除)</label>
                                        </div> --}}
                                    <button class="btn btn-sm btn-info" id="multiProcess" disabled><span>多筆處理</span></button>
                                </div>
                                @endif
                            </div>
                        </div>
                        <form id="myForm" action="{{ url('products') }}" method="get">
                            <div id="searchForm" class="card card-primary" style="display: none">
                                <div class="card-body">
                                    <div class="row col-8 offset-2">
                                        <div class="col-6 mt-2">
                                            <label for="status">商品狀態: (ctrl+點選可多選)</label>
                                            <select class="form-control" id="status" size="6" multiple>
                                                <option value="1" {{ isset($status) ? in_array(1,explode(',',$status)) ? 'selected' : '' : 'selected' }}>上架中</option>
                                                <option value="0" {{ isset($status) ? in_array(0,explode(',',$status)) ? 'selected' : '' : 'selected' }}>送審中</option>
                                                <option value="-1" {{ isset($status) ? in_array(-1,explode(',',$status)) ? 'selected' : '' : 'selected' }}>未送審(草稿)</option>
                                                <option value="-2" {{ isset($status) ? in_array(-2,explode(',',$status)) ? 'selected' : '' : 'selected' }}>審核不通過</option>
                                                <option value="-3" {{ isset($status) ? in_array(-3,explode(',',$status)) ? 'selected' : '' : 'selected' }}>暫停銷售</option>
                                                <option value="-9" {{ isset($status) ? in_array(-9,explode(',',$status)) ? 'selected' : '' : 'selected' }}>已下架</option>
                                            </select><input type="hidden" value="1,0,-1,-2,-3,-9" name="status" id="status_hidden" />
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="shipping_method">物流方式: (ctrl+點選可多選)</label>
                                            <select class="form-control" id="shipping_methods" size="6" multiple>
                                                <option value="1" {{ isset($shipping_methods) ? in_array(1,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>機場提貨</option>
                                                <option value="2" {{ isset($shipping_methods) ? in_array(2,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>旅店提貨</option>
                                                <option value="3" {{ isset($shipping_methods) ? in_array(3,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>現場提貨</option>
                                                <option value="4" {{ isset($shipping_methods) ? in_array(4,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>寄送海外</option>
                                                <option value="5" {{ isset($shipping_methods) ? in_array(5,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>寄送台灣</option>
                                                <option value="6" {{ isset($shipping_methods) ? in_array(6,explode(',',$shipping_methods)) ? 'selected' : '' : 'selected' }}>寄送當地</option>
                                            </select><input type="hidden" value="1,2,3,4,5,6" name="shipping_methods" id="shipping_methods_hidden" />
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="created_at">上架時間區間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="created_at" name="created_at" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($created_at) && $created_at ? $created_at : '' }}" autocomplete="off">
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="created_at_end" name="created_at_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($created_at_end) && $created_at_end ? $created_at_end : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label for="pass_time">送審通過時間:</label>
                                            <div class="input-group">
                                                <input type="datetime" class="form-control datetimepicker" id="pass_time" name="pass_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pass_time) && $pass_time ? $pass_time : '' }}" autocomplete="off">
                                                <span class="input-group-addon bg-primary">~</span>
                                                <input type="datetime" class="form-control datetimepicker" id="pass_time_end" name="pass_time_end" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($pass_time_end) && $pass_time_end ? $pass_time_end : '' }}" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">產品分類: (下拉選單)</label>
                                            <select class="form-control" id="category_id" name="category_id">
                                                <option value="">選擇產品分類</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ !empty($category_id) ? $category_id == $category->id ? 'selected' : '' : '' }}>{{ $category->name }} {{ $category->is_on == 0 ? '(停用)' : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">商家: (下拉選單)</label>
                                            <select class="form-control select2bs4 select2-primary" data-dropdown-css-class="select2-primary" id="vendor_id" name="vendor_id" >
                                                <option value="">選擇商家</option>
                                                @foreach($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}" {{ !empty($vendor_id) ? $vendor_id == $vendor->id ? 'selected' : '' : '' }}>{{ $vendor->name }} {{ $vendor->is_on == 0 ? '(停用)' : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">產品名稱:</label>
                                            <input type="text" class="form-control" id="product_name" name="product_name" value="{{ isset($product_name) && $product_name ? $product_name : '' }}" placeholder="產品名稱" autocomplete="off">
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">商家名稱:</label>
                                            <input type="text" class="form-control" id="vendor_name" name="vendor_name" value="{{ isset($vendor_name) && $vendor_name ? $vendor_name : '' }}" placeholder="輸入商家名稱，例如: 佳德" autocomplete="off">
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">產品貨號:</label>
                                            <input type="text" class="form-control" id="sku" name="sku" value="{{ isset($sku) && $sku ? $sku : '' }}" placeholder="產品貨號，EC or BOM" autocomplete="off">
                                        </div>
                                        <div class="col-6 mt-2">
                                            <label class="mr-2">鼎新品號:</label>
                                            <input type="text" class="form-control" id="digiwin_no" name="digiwin_no" value="{{ isset($digiwin_no) && $digiwin_no ? $digiwin_no : '' }}" placeholder="鼎新品號" autocomplete="off">
                                        </div>
                                        <div class="col-3 mt-2 text-center">
                                            <label class="mr-2 mt-2">　</label>
                                            <div class="form-group clearfix">
                                                <label class="mr-2">低於安全庫存:</label>
                                                <div class="icheck-green d-inline mr-2">
                                                    <input type="checkbox" id="low_quantity" name="low_quantity" value="yes" {{ isset($low_quantity) && $low_quantity ? 'checked' : '' }}>
                                                    <label for="low_quantity">勾我</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3 mt-2 text-center">
                                            <label class="mr-2 mt-2">　</label>
                                            <div class="form-group clearfix">
                                                <label class="mr-2">庫存小於等於0:</label>
                                                <div class="icheck-green d-inline mr-2">
                                                    <input type="checkbox" id="zero_quantity" name="zero_quantity" value="yes" {{ isset($zero_quantity) && $zero_quantity ? 'checked' : '' }}>
                                                    <label for="zero_quantity">勾我</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-3 mt-2 ">
                                            <label class="mr-2">包含被刪除</label>
                                            <select class="form-control" name="is_del">
                                                <option value="N" {{ isset($is_del) && $is_del == 'N' ? 'selected' : '' }}>否</option>
                                                <option value="Y" {{ isset($is_del) && $is_del == 'Y' ? 'selected' : '' }}>是</option>
                                            </select>
                                        </div>
                                        <div class="col-3 mt-2 ">
                                            <label class="mr-2">每頁筆數</label>
                                            <select class="form-control" name="list">
                                                <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                                <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                                <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                                <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                            </select>
                                        </div>
                                        <div class="col-12 text-center mt-2">
                                            <button type="button" onclick="formSearch()" class="btn btn-primary">查詢</button>
                                            <input type="reset" class="btn btn-default" value="清空">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="card-body">
                            {{-- 文字不斷行 table中加上 class="text-nowrap" --}}
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                        <th class="text-center" width="1%"></th>
                                        @endif
                                        <th class="text-center" width="5%">狀態</th>
                                        <th class="text-left" width="9%">上架/更新/通過時間</th>
                                        <th class="text-left" width="24%">品名/內容量</th>
                                        <th class="text-left" width="36%">
                                            <div class="row">
                                                <div class="col-3 text-left">款式名稱</div>
                                                <div class="col-3 text-left">EC貨號</div>
                                                <div class="col-3 text-left">鼎新貨號</div>
                                                <div class="col-1 text-right">庫存</div>
                                                <div class="col-1 text-right">安全</div>
                                                <div class="col-1 text-center">調整</div>
                                            </div>
                                        </th>
                                        <th class="text-right" width="5%">單價</th>
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="4%">操作</th>
                                        @endif
                                        @if(in_array($menuCode.'CP',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="4%">複製</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    <tr class="{{ $product->is_del == 1 ? 'double-del-line' : '' }}">
                                        @if(in_array($menuCode.'EX', explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle text-sm">
                                            <input type="checkbox" class="chk_box_{{ $product->id }}" name="chk_box" value="{{ $product->id }}">
                                        </td>
                                        @endif
                                        <td class="text-center align-middle text-sm">
                                            @if($product->status == 1)
                                            <span class="right badge badge-success">上架中</span>
                                            @elseif($product->status == 0)
                                            <span class="right badge badge-purple">送審中</span>
                                            @elseif($product->status == -9)
                                            <span class="right badge badge-secondary">已下架</span>
                                            @elseif($product->status == -3)
                                            @if(!empty($product->pause_reason))
                                            <span class="right badge badge-warning">商家暫停銷售</span>
                                            @else
                                            <span class="right badge badge-secondary">iCarry暫停銷售</span>
                                            @endif
                                            @elseif($product->status == -2)
                                            <span class="right badge badge-danger">審核不通過</span>
                                            @elseif($product->status == -1)
                                            <span class="right badge badge-info">未送審(草稿)</span>
                                            @endif
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            {{ $product->create_time }}<br>
                                            {{ $product->update_time }}<br>
                                            {{ $product->pass_time }}
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            <div class="col-12 text-warp">
                                            @if($product->is_del == 0)
                                            <a href="{{ route('admin.products.show', $product->id ) }}">{{ $product->vendor_name }} - {{ $product->name }}</a>
                                            @else
                                            {{ $product->vendor_name }} - {{ $product->name }}
                                            @endif
                                            <span class="text-xs bg-info">{{ $product->serving_size }}</span>
                                            <span class="text-xs bg-success">{{ $product->categories }}</span>
                                            {{-- @if(!empty($product->categories))
                                            <span class="text-xs bg-purple">
                                                @if($product->model_type == 1)
                                                單一款式
                                                @elseif($product->model_type == 2)
                                                    多種款式
                                                @elseif($product->model_type == 3)
                                                    組合商品
                                                @else
                                                    資料異常
                                                @endif
                                            </span>
                                            @endif --}}
                                            @if($product->type == 3)
                                            <span class="badge badge-danger">贈品</span>
                                            @elseif($product->type == 2)
                                            <span class="badge badge-primary">加購品</span>
                                            @endif
                                            @if($product->is_del==1)
                                            <span class="text-blod text-danger">(已刪除)</span>
                                            @endif
                                            </div>
                                        </td>
                                        <td class="text-left align-middle text-sm">
                                            <div class="row">
                                                @foreach($product->models as $model)
                                                    <div class="col-3 text-left">
                                                        {{ $model->name }}
                                                        @if($product->service_fee_percent < 25)
                                                        <i class="fas fa-exclamation-circle text-danger" title="{{ $product->service_fee_percent }}"></i>
                                                        @endif
                                                    </div>
                                                    <div class="col-3 text-left">{{ $model->sku }}</div>
                                                    <div class="col-3 text-left">{{ $model->digiwin_no }}</div>
                                                    <div class="col-1 text-right">
                                                        @if($model->quantity < $model->safe_quantity)
                                                        <span class="text-danger"><b id="quantity_{{ $model->id }}">{{ $model->quantity !=0 ? number_format($model->quantity) : $model->quantity }}</b></span>
                                                        @else
                                                        <span id="quantity_{{ $model->id }}">{{ $model->quantity != 0 ? number_format($model->quantity) : $model->quantity }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-1 text-right" id="safe_quantity_{{ $model->id }}">{{ $model->safe_quantity }}</div>
                                                    <div class="col-1 text-center">
                                                        @if($product->is_del==0)
                                                        <a href="javascript:" onclick="getstockrecord({{ $model->id }})"><span class="right badge badge-primary">調整</span></a>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-right align-middle"><span class="text-primary"><b>{{ $product->price != 0 ? number_format($product->price) : $product->price }}</b></span></td>
                                        @if(in_array($menuCode.'D',explode(',',Auth::user()->power)))
                                        @if($product->is_del == 0)
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @else
                                        <td class="text-center align-middle">
                                            <form action="{{ route('admin.products.recover') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $product->id }}">
                                                <button type="button" class="btn btn-sm btn-success recover-btn">
                                                    <i class="fas fa-tools"></i>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                        @endif
                                        @if(in_array($menuCode.'CP',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            @if($product->is_del==0)
                                            <a href="{{ url('products/copy/'.$product->id) }}" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-copy"></i>
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
                                <span class="badge badge-purple text-lg mr-2">總筆數：{{ $products->total() != 0 ? number_format($products->total()) : 0 }}</span>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $products->appends($appends)->render() }}
                                @else
                                {{ $products->render() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <form id="export" action="{{ url('export') }}" method="POST">
        @csrf
    </form>
    <form id="multiProcessForm" action="{{ url('products/multiProcess') }}" method="POST">
        @csrf
    </form>
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
                    <form id="modalForm">
                    <input type="hidden" name="product_model_id">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">貨號</span>
                        </div>
                        <div class="input-group-prepend">
                            <input type="text" class="form-control" name="sku_text" disabled>
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
                                            <th width="10%" class="text-right">修改前庫存</th>
                                            <th width="10%" class="text-right">增減數量</th>
                                            <th width="10%" class="text-right">修改後庫存</th>
                                            <th width="25%">原因理由</th>
                                            <th width="10%">修改者</th>
                                            <th width="10%">庫存調整時間</th>
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
{{-- 多處理Modal --}}
<div id="multiModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="multiModalLabel">請選擇功能</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <button class="btn btn-sm btn-primary multiExport mr-2" id="excel_ProductDetail" value="excel_ProductDetail">商品明細</button>
                <button class="btn btn-sm btn-success multiProcess mr-2" id="changeStatus" value="changeStatus">修改狀態</button>
                <button class="btn btn-sm btn-info multiProcess mr-2" id="changeDate" value="changeDate">修改最快/最後出貨日</button>
                <button class="btn btn-sm btn-warning multiProcess mr-2" id="change" value="change">修改機場/旅店提貨指定天數</button>
                {{-- <button class="btn btn-sm btn-primary multiExport mr-2" id="excel_DigiwinProduct" value="excel_DigiwinProduct">匯出鼎新</button> --}}
                {{-- <button class="btn btn-sm btn-primary multiExport mr-2" id="excel_DigiwinNo" value="excel_DigiwinNo">鼎新品號廠商表(生效日)</button> --}}
            </div>
        </div>
    </div>
</div>
{{-- 修改最快/最後出貨日Modal --}}
<div id="changeDateModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeDateModalLabel">修改最快出貨日/最後出貨日</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="changeDateForm" action="{{ url('products/multiProcess') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cate" value="changeDate">
                    <div class="row">
                        <div class="col-3 mt-2">
                            <label for="earliestDate">最快出貨日:</label>
                            <input type="date" class="form-control datepicker" id="earliestDate" name="earliestDate" placeholder="格式：2016-06-06" autocomplete="off">
                        </div>
                        <div class="col-3 mt-2">
                            <label for="latestDate">最後出貨日:</label>
                            <input type="date" class="form-control datepicker" id="latestDate" name="latestDate" placeholder="格式：2016-06-06" autocomplete="off">
                        </div>
                        <div class="col-3 mt-2">
                            <label for="trans_start_date">特定轉倉開始日:</label>
                            <input type="date" class="form-control datepicker" id="trans_start_date" name="trans_start_date" placeholder="格式：2016-06-06" autocomplete="off">
                        </div>
                        <div class="col-3 mt-2">
                            <label for="trans_end_date">特定轉倉結束日:</label>
                            <input type="date" class="form-control datepicker" id="trans_end_date" name="trans_end_date" placeholder="格式：2016-06-06" autocomplete="off">
                        </div>
                        <div class="col-2 mt-2">
                            <label >　</label>
                            <div class="input-group">
                                <button type="submit" class="btn btn-md btn-primary btn-block">修改</button>
                            </div>
                        </div>

                    </div>
                </form>
                <div>
                    <span class="text-danger" id="changeStatusNotice">注意! 按下修改按鈕後，所有被選擇的商品最快出貨日與最後出貨日皆會被修改，不填寫任何資料等於清空該欄位。</span>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- 修改提貨貨日Modal --}}
<div id="changeModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeModalLabel">修改機場/旅店提貨日天數</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="changeForm" action="{{ url('products/multiProcess') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cate" value="change">
                    <div class="row">
                        <div class="col-3 mt-2">
                            <label for="airplane_days">機場提貨指定天數:</label>
                            <input type="number" class="form-control " id="airplane_days" name="airplane_days" value="0" placeholder="機場提貨指定天數" autocomplete="off">
                        </div>
                        <div class="col-3 mt-2">
                            <label for="hotel_days">旅店提貨指定天數:</label>
                            <input type="number" class="form-control " id="hotel_days" name="hotel_days" value="0" placeholder="旅店提貨指定天數" autocomplete="off">
                        </div>
                        <div class="col-2 mt-2">
                            <label >　</label>
                            <div class="input-group">
                                <button type="submit" class="btn btn-md btn-primary btn-block">修改</button>
                            </div>
                        </div>

                    </div>
                </form>
                <div>
                    <span class="text-danger" id="changeStatusNotice">注意! 按下修改按鈕後，所有被選擇的商品機場提貨指定天數與旅店提貨指定天數皆會被修改，不填寫任何資料則以0填入該欄位。</span>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- 修改狀態Modal --}}
<div id="changeStatusModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">多筆商品狀態修改</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="changeStatusForm" action="{{ url('products/multiProcess') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cate" value="changeStatus">
                    <div class="form-group">
                        <div class="input-group">
                            <select class="form-control" name="changeStatus">
                                <option value="0">送審中</option>
                                <option value="-1">未送審(草稿)</option>
                                <option value="-3">暫停銷售</option>
                                <option value="-9">已下架</option>
                                <option value="1">上架中</option>
                            </select>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-md btn-primary btn-block">修改</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div>
                    <span class="text-danger" id="changeStatusNotice">注意! 按下修改後會將所有被選擇的商品狀態將會被修改。</span>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- 匯入變價Modal --}}
<div id="importModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">請選擇匯入變價功能檔案</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="importForm" action="{{ url('products/import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="cate" value="changePrice">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" id="filename" name="filename" class="custom-file-input" required autocomplete="off">
                                <label class="custom-file-label" for="filename">瀏覽選擇EXCEL檔案</label>
                            </div>
                            <div class="input-group-append">
                                <button id="importBtn" type="button" class="btn btn-md btn-primary btn-block">上傳</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div>
                    <span class="text-danger" id="importModalNotice">注意! 請選擇正確的檔案並填寫正確的資料格式匯入，否則將造成資料錯誤，若不確定格式，請參考 <a href="./sample/變價匯入格式.xlsx" target="_blank">變價匯入格式範本</a> ，製作正確的檔案。</span>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- 匯入上下架Modal --}}
<div id="importUpDownModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importUpDownModalLabel">請選擇匯入商品上下架檔案</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="showUpDownImportForm" action="{{ url('products/import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="cate" value="changeStatus">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" id="filename2" name="filename" class="custom-file-input" required autocomplete="off">
                                <label class="custom-file-label" for="filename2">瀏覽選擇EXCEL檔案</label>
                            </div>
                            <div class="input-group-append">
                                <button id="importUpDownBtn" type="button" class="btn btn-md btn-primary btn-block">上傳</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div>
                    <span class="text-danger" id="importModalNotice">注意! 請選擇正確的檔案並填寫正確的資料格式匯入，否則將造成資料錯誤，若不確定格式，請參考 <a href="./sample/商品上下架匯入格式.xlsx" target="_blank">商品上下架匯入格式範本</a> ，製作正確的檔案。</span>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- 匯入商品Modal --}}
<div id="importProductModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">請選擇匯入檔案</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="importProductForm" action="{{ url('products/import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="cate" value="addProduct">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" id="filename" name="filename" class="custom-file-input" required autocomplete="off">
                                <label class="custom-file-label" for="filename">瀏覽選擇EXCEL檔案</label>
                            </div>
                            <div class="input-group-append">
                                <button id="importProductBtn" type="button" class="btn btn-md btn-primary btn-block">上傳</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div>
                    <span class="text-danger" id="importModalNotice">注意! 請選擇正確的檔案並填寫正確的資料格式匯入，否則將造成資料錯誤，若不確定格式，請參考 <a href="./sample/變價匯入格式.xlsx" target="_blank">變價匯入格式範本</a> ，製作正確的檔案。</span>
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
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        // date time picker 設定
        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        $('.datepicker').datepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

        $('.timepicker').timepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });

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

        $('.recover-btn').click(function (e) {
            if(confirm('請確認是否要復原這筆資料?')){
                $(this).parents('form').submit();
            };
        });

        $('#showForm').click(function(){
            let text = $('#showForm').html();
            $('#searchForm').toggle();
            text == '使用欄位查詢' ? $('#showForm').html('隱藏欄位查詢') : $('#showForm').html('使用欄位查詢');
        });

        $('#showImportForm').click(function(){
            $('#importModal').modal('show');
        });

        $('#showUpDownModal').click(function(){
            $('#importUpDownModal').modal('show');
        });

        $('#productImportForm').click(function(){
            $('#importProductModal').modal('show');
        });

        $('#importBtn').click(function(){
            let form = $('#importForm');
            $('#importBtn').attr('disabled',true);
            form.submit();
        });

        $('#importUpDownBtn').click(function(){
            let form = $('#showUpDownImportForm');
            $('#importUpDownBtn').attr('disabled',true);
            form.submit();
        });

        $('#importProductBtn').click(function(){
            let form = $('#importProductForm');
            $('#importProductBtn').attr('disabled',true);
            form.submit();
        });

        $('#stockmodify').click(function(){
            let product_model_id = $('input[name=product_model_id]').val();
            let quantity = $('input[name=quantity]').val();
            let safe_quantity = $('input[name=safe_quantity]').val();
            safe_quantity == 0 ? safe_quantity = 1 : '';
            let reason = $('input[name=reason]').val();
            let token = '{{ csrf_token() }}';
            $.ajax({
                type: "post",
                url: 'products/stockmodify',
                data: { product_model_id: product_model_id, quantity: quantity, safe_quantity: safe_quantity, reason: reason, _token: token },
                success: function(data) {
                    if(data['productQtyRecord']){
                        let x = $('.record').length;
                        let dateTime = new Date(data['productQtyRecord']['create_time']).toISOString().slice(0, 10);
                        let timestamp = new Date(data['productQtyRecord']['create_time']).getTime();
                        let count = data['productQtyRecord']['after_quantity'] - data['productQtyRecord']['before_quantity'];
                        let record = '';
                        record += '<tr class="record"><td class="align-middle">'+(x+1)+'</td><td class="align-middle text-right">'+data['productQtyRecord']['before_quantity']+'</td><td class="align-middle text-right">'+count+'</td><td class="align-middle text-right">'+data['productQtyRecord']['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord']['reason']+'</td>';
                        if(data['productQtyRecord']['admin'] != null){
                            record += '<td class="align-middle">iCarry-'+data['productQtyRecord']['admin']+'</td>';
                        }else if(data['productQtyRecord']['vendor'] != null){
                            record += '<td class="align-middle">廠商-'+data['productQtyRecord']['vendor']+'</td>';
                        }else{
                            record += '<td class="align-middle"></td>';
                        }
                        record += '<td class="align-middle">'+dateTime+'</td></tr>';
                        $('#record').prepend(record);
                        $('#quantity_'+product_model_id).html(quantity);
                        $('#safe_quantity_'+product_model_id).html(safe_quantity);
                    }else{
                        $('#safe_quantity_'+product_model_id).html(safe_quantity);
                        $('#myModal').modal('hide');
                    }
                }
            });
        });

        var num_all = $('input[name="chk_box"]').length;
        var num = $('input[name="chk_box"]:checked').length;
        $("#chkallbox_text").text("("+num+"/"+num_all+")");

        $('input[name="chk_box"]').change(function(){
            var num_all = $('input[name="chk_box"]').length;
            var num = $('input[name="chk_box"]:checked').length;
            num > 0 ? $("#selectBox").prop("checked",true) : $("#selectBox").prop("checked",false);
            if(num == num_all){
                $("#chkallbox").prop("checked",true);
                $('#multiProcess').prop("disabled",false);
            }else if(num > 0){
                $("#selectBox").prop("checked",true)
                $('#multiProcess').prop("disabled",false);
            }else if(num == 0){
                $("#chkallbox").prop("checked",false);
                $('#multiProcess').prop("disabled",true);
            }
            $("#chkallbox_text").text("("+num+"/"+num_all+")");
        });

        $('input[name="exportMethod"]').click(function(){
            if($(this).val() == 'allOnPage'){
                $('input[name="chk_box"]').prop("checked",true);
                $('#multiProcess').prop("disabled",false);
                $('#oit').prop("disabled",false);
            }else if($(this).val() == 'selected'){
                $('input[name="chk_box"]').prop("checked",false);
                $('#multiProcess').prop("disabled",true);
                $('#oit').prop("disabled",false);
            }else if($(this).val() == 'byQuery'){
                $('input[name="chk_box"]').prop("checked",false);
                $('#multiProcess').prop("disabled",false);
                $('#oit').prop("disabled",true);
            }else if($(this).val() == 'allData'){
                $('input[name="chk_box"]').prop("checked",false);
                $('#multiProcess').prop("disabled",false);
                $('#oit').prop("disabled",true);
            }else{
                $('#multiProcess').prop("disabled",true);
                $('#oit').prop("disabled",false);
            }
            $('#searchForm').hide();
            $('#showForm').html('使用欄位查詢');
            var num_all = $('input[name="chk_box"]').length;
            var num = $('input[name="chk_box"]:checked').length;
            $("#chkallbox_text").text("("+num+"/"+num_all+")");
        });

        $('.multiExport').click(function (e){
            let form = $('#export');
            let cate = $(this).val().split('_')[0];
            let type = $(this).val().split('_')[1];
            let filename = $(this).html();
            let orderids = $('input[name="chk_box"]:checked').serializeArray().map( item => item.value );
            let exportMethod = $('input[name="exportMethod"]:checked').val();
            if(exportMethod == 'allOnPage' || exportMethod == 'selected'){
                if(orderids.length > 0){
                    for(let i=0;i<orderids.length;i++){
                        let orderId = $('<input type="hidden" class="formappend" name="id['+i+']">').val(orderids[i]);
                        form.append(orderId);
                    }
                }else{
                    alert('尚未選擇商品');
                    return;
                }
            }else if(exportMethod == 'byQuery'){ //by條件
                let sel="";
                $("#shipping_methods>option:selected").each(function(){
                    sel+=","+$(this).val();
                });
                $("#shipping_methods_hidden").val(sel.substring(1));

                sel = "";
                $("#status>option:selected").each(function(){
                    sel+=","+$(this).val();
                });
                $("#status_hidden").val(sel.substring(1));

                let con_val = $('#myForm').serializeArray().map( item => item.value );
                let con_name = $('#myForm').serializeArray().map( item => item.name );
                for(let j=0; j<con_name.length;j++){
                    let tmp = '';
                    tmp = $('<input type="hidden" class="formappend" name="con['+con_name[j]+']" value="'+con_val[j]+'">');
                    form.append(tmp);
                }
            }else if(exportMethod == 'allData'){ //全部資料
            }else{
                return;
            }
            let export_method = $('<input type="hidden" class="formappend" name="method" value="'+exportMethod+'">');
            let export_cate = $('<input type="hidden" class="formappend" name="cate" value="'+cate+'">');
            let export_type = $('<input type="hidden" class="formappend" name="type" value="'+type+'">');
            form.append(export_method);
            form.append(export_cate);
            form.append(export_type);
            form.append( $('<input type="hidden" class="formappend" name="filename" value="'+filename+'">') );
            form.append( $('<input type="hidden" class="formappend" name="model" value="products">') );
            form.submit();
            $('.formappend').remove();
            $('#multiModal').modal('hide');
            // alert(filename+'已開始執行，請過一段時間後到匯出中心下載。');
        });

        $('#multiProcess').click(function(){
            if($('input[name="exportMethod"]:checked').val() == 'select'){
                let num = $('input[name="chk_box"]:checked').length;
                if(num == 0){
                    alert('尚未選擇商品');
                    return;
                }
            }
            $('#multiModal').modal('show');
        });

        $('.multiProcess').click(function(){
            let form = null;
            if($(this).val() == 'changeStatus'){
                $('#multiModal').modal('hide');
                $('#changeModal').modal('hide');
                $('#changeStatusModal').modal('show');
                form = $('#changeStatusForm');
            }else if($(this).val() == 'changeDate'){
                $('#multiModal').modal('hide');
                $('#changeModal').modal('hide');
                $('#changeDateModal').modal('show');
                form = $('#changeDateForm');
            }else if($(this).val() == 'change'){
                $('#multiModal').modal('hide');
                $('#changeDateModal').modal('hide');
                $('#changeModal').modal('show');
                form = $('#changeForm');
            }else{
                return;
            }
            let orderids = $('input[name="chk_box"]:checked').serializeArray().map( item => item.value );
            let exportMethod = $('input[name="exportMethod"]:checked').val();
            if(exportMethod == 'allOnPage' || exportMethod == 'selected'){
                if(orderids.length > 0){
                    for(let i=0;i<orderids.length;i++){
                        let orderId = $('<input type="hidden" class="formappend" name="id['+i+']">').val(orderids[i]);
                        form.append(orderId);
                    }
                }else{
                    alert('尚未選擇商品');
                    return;
                }
            }else if(exportMethod == 'byQuery'){ //by條件
                let sel="";
                $("#shipping_methods>option:selected").each(function(){
                    sel+=","+$(this).val();
                });
                $("#shipping_methods_hidden").val(sel.substring(1));

                sel = "";
                $("#status>option:selected").each(function(){
                    sel+=","+$(this).val();
                });
                $("#status_hidden").val(sel.substring(1));
                let con_val = $('#myForm').serializeArray().map( item => item.value );
                let con_name = $('#myForm').serializeArray().map( item => item.name );
                for(let j=0; j<con_name.length;j++){
                    let tmp = '';
                    tmp = $('<input type="hidden" class="formappend" name="con['+con_name[j]+']" value="'+con_val[j]+'">');
                    form.append(tmp);
                }
            }else if(exportMethod == 'allData'){ //全部資料
            }else{
                return;
            }
        });


    })(jQuery);

    function formSearch(){
        let sel="";
        $("#shipping_methods>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#shipping_methods_hidden").val(sel.substring(1));

        sel = "";
        $("#status>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#status_hidden").val(sel.substring(1));

        if($('#created_at').val() && $('#created_at_end').val() && ($('#created_at').val() > $('#created_at_end').val())){
            alert('上架時間區間的開始時間不能小於結束時間!');
        }else if($('#pass_time').val() && $('#pass_time_end').val() && ($('#pass_time').val() > $('#pass_time_end').val())){
            alert('送審通過區間的開始時間不能小於結束時間!');
        }else{
            $("#myForm").submit();
        }
    }

    function getstockrecord(id){
        $('#result').html(''); //開啟modal前清除搜尋資料
        $('#myModal').modal('show');
        let token = '{{ csrf_token() }}';
        $.ajax({
            type: "post",
            url: 'products/getstockrecord',
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
                    data['productQtyRecord'][i]['reason'] == null ? data['productQtyRecord'][i]['reason'] = '' : '';
                    let x = data['productQtyRecord'].length - i;
                    let dateTime = new Date(data['productQtyRecord'][i]['create_time']).toISOString().slice(0, 10);
                    let timestamp = new Date(data['productQtyRecord'][i]['create_time']).getTime();
                    count = data['productQtyRecord'][i]['after_quantity'] - data['productQtyRecord'][i]['before_quantity'];
                    record += '<tr class="record"><td class="align-middle">'+(x)+'</td><td class="align-middle text-right">'+data['productQtyRecord'][i]['before_quantity']+'</td><td class="align-middle text-right">'+count+'</td><td class="align-middle text-right">'+data['productQtyRecord'][i]['after_quantity']+'</td><td class="align-middle">'+data['productQtyRecord'][i]['reason']+'</td>';
                    if(data['productQtyRecord'][i]['admin'] != null){
                        record += '<td class="align-middle">iCarry-'+data['productQtyRecord'][i]['admin']+'</td>';
                    }else if(data['productQtyRecord'][i]['vendor'] != null){
                        record += '<td class="align-middle">廠商-'+data['productQtyRecord'][i]['vendor']+'</td>';
                    }else{
                        record += '<td class="align-middle"></td>';
                    }
                    record += '<td class="align-middle">'+dateTime+'</td></tr>';
                }
                $('#record').html(record);
                $('input[name=product_model_id]').val(data['productModel']['id']);
                $('input[name=quantity]').val(data['productModel']['quantity']);
                $('input[name=safe_quantity]').val(data['productModel']['safe_quantity']);
                $('input[name=sku_text]').val(data['productModel']['sku']);
            }
        });
    }

    function removeCondition(name){
        let sel="";
        $("#shipping_methods>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#shipping_methods_hidden").val(sel.substring(1));

        sel = "";
        $("#status>option:selected").each(function(){
            sel+=","+$(this).val();
        });
        $("#status_hidden").val(sel.substring(1));

        if(name == 'pass_time' || name == 'created_at'){
            $('input[name="'+name+'"]').val('');
            $('input[name="'+name+'_end"]').val('');
        }else if(name == 'status'){
            $('input[name="'+name+'"]').val('1,0,-1,-2,-3,-9');
        }else if(name == 'shipping_method'){
            $('input[name="'+name+'"]').val('1,2,3,4,5,6');
        }else if(name == 'category_id' || name == 'vendor_id'){
            $('#'+name).empty();
        }else{
            $('input[name="'+name+'"]').val('');
        }
        $("#myForm").submit();
    }
</script>
@endsection
