@extends('admin.layouts.master')

@section('title', '物流運費設定')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>物流運費設定</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('shippingfees') }}">物流運費設定</a></li>
                        <li class="breadcrumb-item active">{{ isset($fee) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ $fee->name ?? '' }} 物流設定資料</h3>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="chinese">
                                    @if(isset($fee))
                                    <form class="myform" action="{{ route('admin.shippingfees.update', $fee->id) }}" method="POST">
                                        <input type="hidden" name="_method" value="PATCH">
                                    @else
                                    <form class="myform" action="{{ route('admin.shippingfees.store') }}" method="POST">
                                    @endif
                                        @csrf
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="form-group col-4">
                                                        <label for="from"><span class="text-red">* </span>寄送地(產地)</label>
                                                        <select class="form-control" id="product_sold_country" name="product_sold_country">
                                                            <option value="">選擇寄送地(產地)</option>
                                                            @foreach($origins as $origin)
                                                            <option value="{{ $origin->product_sold_country }}" {{ isset($fee) ? $origin->product_sold_country == $fee->product_sold_country ? 'selected' : '' : '' }}>{{ $origin->product_sold_country }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('product_sold_country'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('product_sold_country') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-4">
                                                        <label for="is_local"><span class="text-red">* </span>選擇目的地</label>
                                                        <select class="form-control" id="shipping_methods" name="shipping_methods">
                                                            <option value="">選擇目的地</option>
                                                            <option value="當地機場" {{ isset($fee) && $fee->shipping_methods == '當地機場' ? 'selected' : '' }}>當地機場</option>
                                                            <option value="當地地址" {{ isset($fee) && $fee->shipping_methods == '當地地址' ? 'selected' : '' }}>當地地址</option>
                                                            <option value="當地旅店" {{ isset($fee) && $fee->shipping_methods == '當地旅店' ? 'selected' : '' }}>當地旅店</option>
                                                            @foreach($countries as $country)
                                                            <option value="{{ $country->name }}" {{ isset($fee) ? $country->name == $fee->shipping_methods ? 'selected' : '' : '' }}>{{ $country->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @if ($errors->has('shipping_methods'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('shipping_methods') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-4">
                                                        <label for="shipping_type"><span class="text-red">* </span>計價方式</label>
                                                        <select class="form-control {{ $errors->has('shipping_type') ? ' is-invalid' : '' }}" id="shipping_type" name="shipping_type">
                                                            <option value="">選擇計價方式</option>
                                                            <option value="base" {{ isset($fee) && $fee->shipping_type == 'base' ? 'selected' : '' }}>固定計價</option>
                                                            <option value="kg" {{ isset($fee) && $fee->shipping_type == 'kg' ? 'selected' : '' }}>每公斤</option>
                                                        </select>
                                                        @if ($errors->has('shipping_type'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('shipping_type') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-4">
                                                        <label for="price"><span class="text-red">* </span>費用(NT)</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                            </div>
                                                            @if(isset($fee))
                                                            @if($fee->shipping_type == 'base')
                                                            <input type="number" class="form-control {{ $errors->has('price') ? ' is-invalid' : '' }}" id="price" name="price" value="{{ $fee->shipping_base_price }}" placeholder="輸入費用價格，台幣計價">
                                                            @elseif($fee->shipping_type == 'kg')
                                                            <input type="number" class="form-control {{ $errors->has('price') ? ' is-invalid' : '' }}" id="price" name="price" value="{{ $fee->shipping_kg_price }}" placeholder="輸入費用價格，台幣計價">
                                                            @endif
                                                            @else
                                                            <input type="number" class="form-control {{ $errors->has('price') ? ' is-invalid' : '' }}" id="price" name="price" value="" placeholder="輸入費用價格，台幣計價">
                                                            @endif
                                                            @if ($errors->has('price'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('price') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-4">
                                                        <label for="free_shipping"><span class="text-red">* </span>免運門檻 <a href="javascript:$('#free_shipping').val(0);void(0);" class=" btn-link"> (免運) </a> <a href="javascript:$('#free_shipping').val(99999999);void(0);" class="btn-link">(不提供)</a></label>
                                                        <input type="number" class="form-control {{ $errors->has('free_shipping') ? ' is-invalid' : '' }}" id="free_shipping" name="free_shipping" value="{{ $fee->free_shipping ?? old('free_shipping') ?? '' }}" placeholder="設定免運門檻">
                                                        @if ($errors->has('free_shipping'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('free_shipping') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-4">
                                                        <label for="tax_rate">跨境稅率(%)</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                                            </div>
                                                            <input type="text" class="form-control {{ $errors->has('tax_rate') ? ' is-invalid' : '' }}" id="tax_rate" name="tax_rate" value="{{ isset($fee) ? $fee->tax_rate == 0 ? 0 : $fee->tax_rate : 0 }}" placeholder="輸入跨境稅率">
                                                            <div class="input-group-append">
                                                                <div class="input-group-text"><i class="fas fa-percent"></i></div>
                                                            </div>
                                                            @if ($errors->has('tax_rate'))
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $errors->first('tax_rate') }}</strong>
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="description_tw">運費說明(中文)</label>
                                                        <textarea rows="5" class="form-control {{ $errors->has('description_tw') ? ' is-invalid' : '' }}" id="description_tw" name="description_tw" placeholder="輸入說明">{{ $fee->description_tw ?? old('description_tw') }}</textarea>
                                                        @if ($errors->has('description_tw'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('description_tw') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="description_en">運費說明(英文)</label>
                                                        <textarea rows="5" class="form-control {{ $errors->has('description_en') ? ' is-invalid' : '' }}" id="description_en" name="description_en" placeholder="輸入英文說明">{{ $fee->description_en ?? old('description_en') }}</textarea>
                                                        @if ($errors->has('description_en'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('description_en') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="fill_vendor_earliest_delivery_date_tw">代入廠商最快出貨日(中文)</label>
                                                        <textarea rows="5" class="form-control {{ $errors->has('fill_vendor_earliest_delivery_date_tw') ? ' is-invalid' : '' }}" id="fill_vendor_earliest_delivery_date_tw" name="fill_vendor_earliest_delivery_date_tw" placeholder="輸入說明">{{ $fee->fill_vendor_earliest_delivery_date_tw ?? old('fill_vendor_earliest_delivery_date_tw') }}</textarea>
                                                        @if ($errors->has('fill_vendor_earliest_delivery_date_tw'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('fill_vendor_earliest_delivery_date_tw') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="fill_vendor_earliest_delivery_date_en">代入廠商最快出貨日(英文)</label>
                                                        <textarea rows="5" class="form-control {{ $errors->has('fill_vendor_earliest_delivery_date_en') ? ' is-invalid' : '' }}" id="fill_vendor_earliest_delivery_date_en" name="fill_vendor_earliest_delivery_date_en" placeholder="輸入英文說明">{{ $fee->fill_vendor_earliest_delivery_date_en ?? old('fill_vendor_earliest_delivery_date_en') }}</textarea>
                                                        @if ($errors->has('fill_vendor_earliest_delivery_date_en'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('fill_vendor_earliest_delivery_date_en') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-6">
                                                        <label for="is_on">狀態</label>
                                                        <div class="input-group">
                                                            <input class="form-control" type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="secondary" data-on-color="primary" {{ isset($fee) ? $fee->is_on == 1 ? 'checked' : '' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center bg-white">
                                            @if(in_array(isset($fee) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                                            <button type="submit" class="btn btn-primary">{{ isset($fee) ? '修改' : '新增' }}</button>
                                            @endif
                                            <a href="{{ route('admin.shippingfees.index') }}" class="btn btn-info">
                                                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                                            </a>
                                        </div>
                                    </form>
                                </div>
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
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\ShippingFeesRequest', '.myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('select[name=is_local]').change(function(){
            var is_local = $(this).val();
            if(is_local == 1){
                let from = $('select[name=from]').val();
                $('select[name=to]').val(from);
                $('#to').addClass('d-none');
                $('#shipping_local_id').removeClass('d-none');
            }else{
                $('select[name=to]').val('');
                $('select[name=shipping_local_id]').val('');
                $('#shipping_local_id').addClass('d-none');
                $('#to').removeClass('d-none');
            }
        });
    })(jQuery);
</script>
@endsection
