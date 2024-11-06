@extends('admin.layouts.master')

@section('title', '機台管理')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>機台管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('acpaymachines') }}">機台管理</a></li>
                        <li class="breadcrumb-item active">{{ isset($machine) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        @if(isset($machine))
        <form class="myform" action="{{ route('admin.acpaymachines.update', $machine->id) }}" method="POST">
            <input type="hidden" name="_method" value="PATCH">
        @else
        <form class="myform" action="{{ route('admin.acpaymachines.store') }}" method="POST">
        @endif
            @csrf
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ isset($machine) ? "C".str_pad($machine->id,5,'0',STR_PAD_LEFT) : '' }} 商家資料</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-6">
                                    <label for="name"><span class="text-red">* </span>店名</label>
                                    <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" id="name" name="name" value="{{ $machine->name ?? old('name') }}" placeholder="輸入廠商名稱">
                                    @if ($errors->has('name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group col-6">
                                    <label for="company">公司名稱</label>
                                    <input type="text" class="form-control" id="company" value="{{ isset($machine) ? $machine->vendor->company ?? '' : '' }}" placeholder="輸入公司名稱" readonly>
                                </div>
                                <div class="form-group col-6">
                                    <label for="boss">負責人</label>
                                    <input type="text" class="form-control" id="boss" value="{{ isset($machine) ? $machine->vendor->boss ?? '' : '' }}" placeholder="輸入負責人" readonly>
                                </div>
                                <div class="form-group col-6">
                                    <label for="vat_number">公司統一編號</label>
                                    <input type="text" class="form-control" id="vat_number" value="{{ isset($machine) ? $machine->vendor->vat_number ?? '' : '' }}" placeholder="輸入公司統一編號" readonly>
                                </div>
                                <div class="form-group col-6">
                                    <label for="contact_person"><span class="text-danger">* </span>聯絡人</label>
                                    <input type="text" class="form-control {{ $errors->has('contact_person') ? ' is-invalid' : '' }}" id="contact_person" name="contact_person" value="{{ isset($machine) ? $machine->contact_person ?? old('contact_person') : '' }}" placeholder="輸入聯絡人">
                                    @if ($errors->has('contact_person'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('contact_person') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group col-6">
                                    <label for="email"><span class="text-danger">* </span>Email</label>
                                    <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" id="email" name="email" value="{{ isset($machine) ? $machine->email ?? old('email') : '' }}" placeholder="輸入email">
                                    @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group col-6">
                                    <label for="tel"><span class="text-danger">* </span>門市電話</label>
                                    <input type="text" class="form-control {{ $errors->has('tel') ? ' is-invalid' : '' }}" id="tel" name="tel" value="{{ $machine->tel ?? old('tel') }}" placeholder="輸入門市電話">
                                    @if ($errors->has('tel'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('tel') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group col-6">
                                    <label for="fax">門市傳真</label>
                                    <input type="text" class="form-control {{ $errors->has('fax') ? ' is-invalid' : '' }}" id="fax" name="fax" value="{{ $machine->fax ?? old('fax') }}" placeholder="輸入門市傳真">
                                    @if ($errors->has('fax'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('fax') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                <div class="form-group col-12">
                                    <label for="address"><span class="text-danger">* </span>門市地址</label>
                                    <input type="text" class="form-control {{ $errors->has('address') ? ' is-invalid' : '' }}" id="address" name="address" value="{{ $machine->address ?? old('address') }}" placeholder="輸入門市地址">
                                    @if ($errors->has('address'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ isset($machine) ? "C".str_pad($machine->id,5,'0',STR_PAD_LEFT) : '' }} MPOS 資料</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                            <div class="form-group col-6">
                                <label for="vendor_id"><span class="text-danger">* </span>商家 <span class="text-danger text-sm">設定後不可更改</span></label>
                                @if(isset($machine))
                                <input type="hidden" name="vendor_id" value="{{ $machine->vendor->id }}">
                                <input type="text" class="form-control" value="{{ $machine->vendor->name }}" readonly>
                                @else
                                <select class="form-control select2bs4 select2-primary {{ $errors->has('vendor_id') ? ' is-invalid' : '' }}" data-dropdown-css-class="select2-primary" id="vendor_id" name="vendor_id" {{ isset($machine) ? 'disabled' : ''  }}>
                                    <option value="">請選擇商家</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ isset($machine) && $vendor->id == $machine->vendor_id ? 'selected' : '' }}>{{ $vendor->name }}{{ $vendor->is_on == 0 ? '(停用)' : '' }}</option>
                                    @endforeach
                                </select>
                                @endif
                                @if ($errors->has('vendor_id'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('vendor_id') }}</strong>
                                </span>
                                @endif
                            </div>
                            <div class="form-group col-6">
                                <label for="vendor_account_id"><span class="text-danger">* </span>機台帳號 <span class="text-danger text-sm">設定後不可更改</span></label>
                                @if(isset($machine))
                                <input type="hidden" name="vendor_account_id" value="{{ $machine->account->id }}">
                                <input type="text" class="form-control" value="{{ $machine->account->account }}" readonly>
                                @else
                                <select class="form-control {{ $errors->has('vendor_account_id') ? ' is-invalid' : '' }}" data-dropdown-css-class="select2-primary" id="vendor_account_id" name="vendor_account_id" {{ isset($machine) ? 'disabled' : ''  }}>
                                    <option value="">請先選擇商家</option>
                                    @if(!empty($accounts))
                                    @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ isset($machine) && $account->id == $machine->vendor_account_id ? 'selected' : '' }}>{{ $account->account }}{{ $account->id }}</option>
                                    @endforeach
                                    @endif
                                </select>
                                @endif
                                @if ($errors->has('vendor_account_id'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('vendor_account_id') }}</strong>
                                </span>
                                @endif
                            </div>
                            <div class="form-group col-12">
                                <label for="vendor_account_id">物流方式</label>
                                @for($i=0;$i<count($shippings);$i++)
                                <div class="input-group">
                                    <div class="icheck-primary d-inline mr-2">
                                        <input type="checkbox" id="checkbox_{{ $i + 1 }}" name="{{ $shippings[$i]['shipping'] }}" {{ isset($machine) && $machine->{$shippings[$i]['shipping']} == 1 ? 'checked' : '' }}>
                                        <label for="checkbox_{{ $i + 1 }}">{{ $shippings[$i]['name'] }}</label>
                                    </div>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">每箱運費</span>
                                    </div>
                                    <input type="text" class="form-control" name="{{ $shippings[$i]['box'] }}" value="{{ isset($machine) && $machine->{$shippings[$i]['box']} > 0 ? $machine->{$shippings[$i]['box']} : 0 }}">
                                    <div class="input-group-append">
                                      <div class="input-group-text">基本費</div>
                                    </div>
                                    <input type="text" class="form-control" name="{{ $shippings[$i]['base'] }}" value="{{ isset($machine) && $machine->{$shippings[$i]['base']} > 0 ? $machine->{$shippings[$i]['base']} : 0 }}">
                                </div>
                                @endfor
                            </div>
                            <div class="form-group col-4">
                                <label for="free_shipping">開啟店家免運選項</label>
                                <div class="input-group">
                                    <input type="checkbox" name="free_shipping" value="1" data-bootstrap-switch data-on-text="開啟" data-off-text="關閉" data-off-color="secondary" data-on-color="primary" {{ isset($machine) ? $machine->free_shipping == 1 ? 'checked' : '' : '' }}>
                                </div>
                            </div>
                            <div class="form-group col-4">
                                <label for="can_cancel">開啟交易取消功能</label>
                                <div class="input-group">
                                    <input type="checkbox" name="can_cancel" value="1" data-bootstrap-switch data-on-text="開啟" data-off-text="關閉" data-off-color="secondary" data-on-color="primary" {{ isset($machine) ? $machine->can_cancel == 1 ? 'checked' : '' : '' }}>
                                </div>
                            </div>
                            <div class="form-group col-4">
                                <label for="can_return">開啟退貨功能</label>
                                <div class="input-group">
                                    <input type="checkbox" name="can_return" value="1" data-bootstrap-switch data-on-text="開啟" data-off-text="關閉" data-off-color="secondary" data-on-color="primary" {{ isset($machine) ? $machine->can_return == 1 ? 'checked' : '' : '' }}>
                                </div>
                            </div>
                            <div class="form-group col-12">
                                <label for="vendor_account_id">金流方式</label>
                                <div class="row">
                                    <div class="input-group col-6">
                                        <div class="icheck-primary d-inline mr-2">
                                            <input type="checkbox" id="card_paying" name="card_paying" {{ isset($machine) && $machine->card_paying == 1 ? 'checked' : '' }}>
                                            <label for="card_paying">信用卡</label>
                                        </div>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">抽成</span>
                                        </div>
                                        <input type="text" class="form-control" name="card_draw" value="{{ isset($machine) && $machine->card_draw > 0 ? $machine->card_draw : 0 }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">％</span>
                                        </div>
                                    </div>
                                    <div class="input-group col-6">
                                        <div class="icheck-primary d-inline mr-2">
                                            <input type="checkbox" id="alipay_paying" name="alipay_paying"  {{ isset($machine) && $machine->card_paying == 1 ? 'checked' : '' }}>
                                            <label for="alipay_paying">支付寶</label>
                                        </div>
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">抽成</span>
                                        </div>
                                        <input type="text" class="form-control" name="alipay_draw" value="{{ isset($machine) && $machine->alipay_draw > 0 ? $machine->alipay_draw : 0 }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">％</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-6">
                                <label for="bank"><span class="text-danger">* </span>收款行</label>
                                <select class="form-control" data-dropdown-css-class="select2-primary" id="bank" name="bank">
                                    <option value="環匯" {{ isset($machine) && $machine->bank == '環匯' ? 'selected' : '' }}>環匯</option>
                                    <option value="台新" {{ isset($machine) && $machine->bank == '台新' ? 'selected' : '' }}>台新</option>
                                </select>
                            </div>
                            <div class="form-group col-3">
                                <label for="is_on">狀態</label>
                                <div class="input-group">
                                    <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="啟用" data-off-text="停用" data-off-color="secondary" data-on-color="primary" {{ isset($machine) ? $machine->is_on == 1 ? 'checked' : '' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center">
            @if(in_array(isset($machine) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
            <button type="submit" class="btn btn-primary">{{ isset($machine) ? '修改' : '新增' }}</button>
            @endif
            <a href="{{ route('admin.acpaymachines.index') }}" class="btn btn-info">
                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
            </a>
        </div>
    </form>
    </section>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\MachineListRequest', '.myform'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });

        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $('#vendor_id').change(function(){
            let id = $(this).val();
            alert(id);
            let token = '{{ csrf_token() }}';
            let html = '<option value="">請先選擇商家</option>';
            if(id){
                $.ajax({
                    type: "post",
                    url: 'getvendor',
                    data: { id: id, _token: token },
                    success: function(data) {
                        console.log(data);
                        html = '<option value="">該商家查無可用機台帳號</option>';
                        if(data){
                            $('#company').val(data['company']);
                            $('#boss').val(data['boss']);
                            $('#vat_number').val(data['vat_number']);
                            if(data['accounts'].length > 0){
                                let accounts = data['accounts'];
                                html = '<option value="">請選擇帳號</option>';
                                for(let i=0; i<accounts.length; i++){
                                    html += '<option value="'+accounts[i]['id']+'">'+accounts[i]['account']+'</option>';
                                }
                            }
                        }
                        $('#vendor_account_id').html(html);
                    }
                });
            }
            $('#vendor_account_id').html(html);
        });
    })(jQuery);
</script>
@endsection
