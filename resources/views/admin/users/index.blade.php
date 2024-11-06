@extends('admin.layouts.master')

@section('title', '使用者管理')

@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            {{-- alert訊息 --}}
            @include('admin.layouts.alert_message')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><b>使用者管理</b></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('users') }}">使用者管理</a></li>
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
                                總會員數： <span class="badge badge-primary mr-5">{{ !empty($users) ? number_format($users->total()) : 0 }}</span>
                                @if(in_array($menuCode.'M', explode(',',Auth::user()->power)))
                                <button id="orderImport" class="btn btn-sm btn-warning">匯入購物金</button>
                                @endif
                                <div class="input-group">
                                    <div class="input-group-append">
                                        {{-- @if(in_array($menuCode['Vendors'].'N',explode(',',Auth::user()->power)))
                                        <a href="{{ route('admin.vendors.create') }}" class="btn-sm btn-primary mr-2"><i class="fas fa-plus mr-1"></i>新增</a>
                                        @endif
                                        @if(in_array($menuCode['Vendors'].'EX',explode(',',Auth::user()->power)))
                                        <a href="{{ url('vendors/export') }}" class="btn-sm btn-info"><i class="fas fa-file-download mr-1"></i>匯出全部</a>
                                        @endif --}}
                                    </div>
                                </div>
                            </div>
                            <div class="float-right">
                                <form action="{{ url('users') }}" method="GET" class="form-inline" role="search">
                                    <div class="form-group-sm">
                                        選擇：
                                        <select class="form-control form-control-sm" name="list" onchange="submit(this)">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                            <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                        </select>
                                        <select class="form-control form-control-sm" name="status" onchange="submit(this)">
                                            <option value="" {{ isset($status) && $status == '' ? 'selected' : '' }}>全部</option>
                                            <option value="1" {{ isset($status) && $status == 1 ? 'selected' : '' }}>啟用</option>
                                            <option value="0" {{ isset($status) && $status == 0 ? 'selected' : '' }}>未驗證</option>
                                            <option value="-1" {{ isset($status) && $status == -1 ? 'selected' : '' }}>停用</option>
                                        </select>
                                        <input type="number" step="1" class="form-control form-control-sm" name="mobile" value="{{ isset($mobile) ? $mobile : '' }}" placeholder="電話號碼">
                                        <input type="number" step="1" class="form-control form-control-sm" name="user_id" value="{{ isset($user_id) ? $user_id : '' }}" placeholder="使用者id">
                                        <input type="search" class="form-control form-control-sm" name="search" value="{{ isset($search) ? $search : '' }}" placeholder="搜尋姓名、地址" title="輸入關鍵字搜尋姓名、地址" aria-label="Search">
                                        <button type="submit" class="btn btn-sm btn-info">
                                            <i class="fas fa-search"></i>
                                            搜尋
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            {{-- 文字不斷行 table中加上 class="text-nowrap" --}}
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="4%">標記</th>
                                        <th class="text-center" width="7%">使用者ID</th>
                                        <th class="text-left" width="8%">姓名</th>
                                        <th class="text-center" width="4%">國碼</th>
                                        <th class="text-left" width="7%">電話</th>
                                        <th class="text-left" width="15%">E-Mail</th>
                                        <th class="text-left" width="20%">地址</th>
                                        <th class="text-center" width="5%">推薦人id</th>
                                        <th class="text-center" width="5%">驗證碼</th>
                                        <th class="text-center" width="10%">註冊日期</th>
                                        <th class="text-center" width="5%">購物金</th>
                                        <th class="text-center" width="5%">狀態</th>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <th class="text-center" width="5%">啟用</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                    <tr>
                                        <td class="text-center align-middle text-sm">
                                            @if($user->is_mark == 1)
                                            <form action="{{ url('users/mark/' . $user->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="is_mark" value="{{ $user->is_mark == 1 ? 0 : 1 }}">
                                                <button type="submit" class="btn"><i class="fas fa-star"></i></button>
                                            </form>
                                            @else
                                            <form action="{{ url('users/mark/' . $user->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="is_mark" value="{{ $user->is_mark == 1 ? 0 : 1 }}">
                                                <button type="submit" class="btn"><i class="far fa-star"></i></button>
                                            </form>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            <a href="{{ route('admin.users.show', $user->id ) }}">{{ $user->id }}</a>
                                        </td>
                                        <td class="text-left align-middle text-sm">{{ $user->name }}</td>
                                        <td class="text-center align-middle text-sm">{{ $user->nation }}</td>
                                        <td class="text-left align-middle text-sm textblur-black">{{ $user->mobile }}</td>
                                        <td class="text-left align-middle text-sm textblur-black">{{ $user->email }}</td>
                                        <td class="text-left align-middle text-sm textblur-black">{{ $user->address }}</td>
                                        <td class="text-center align-middle text-sm">
                                            @if($user->refer_id > 0)
                                            {{-- <a href="{{ route('admin.users.show', $user->refer_id ) }}"> --}}
                                                <span class="badge badge-primary" style="cursor:pointer" onclick="intro({{ $user->refer_id }})">{{ $user->refer_id }}</span>
                                            {{-- </a> --}}
                                            @endif
                                        </td>
                                        <td class="text-center align-middle text-sm">{{ $user->verify_code }}</td>
                                        <td class="text-center align-middle text-sm">{{ $user->create_time }}</td>
                                        <td class="text-center align-middle text-sm">
                                            <span class="badge badge-secondary">
                                                {{ $user->points }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle text-sm">
                                            @if($user->status == -1)
                                            <span class="text-danger">停用</span>
                                            @elseif($user->status == 0)
                                            註冊
                                            @elseif($user->status == 1)
                                            <span class="text-primary">已驗證</span>
                                            @endif
                                        </td>
                                        @if(in_array($menuCode.'O',explode(',',Auth::user()->power)))
                                        <td class="text-center align-middle">
                                            @if($user->status == -1)
                                            <form action="{{ url('users/active/' . $user->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="status" value="{{ $user->status == 1 ? -1 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($user) ? $user->status == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                            @elseif($user->status == 1)
                                            <form action="{{ url('users/active/' . $user->id) }}" method="POST">
                                                @csrf
                                                <input type="checkbox" name="status" value="{{ $user->status == 1 ? -1 : 1 }}" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($user) ? $user->status == 1 ? 'checked' : '' : '' }}>
                                            </form>
                                            @else
                                            未驗證
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
                                <div class="form-group">
                                    <form action="{{ url('users') }}" method="GET" class="form-inline" role="search">
                                        {{-- <input type="hidden" name="is_on" value="{{ $is_on ?? '' }}"> --}}
                                        <select class="form-control" name="list" onchange="submit(this)">
                                            <option value="50" {{ $list == 50 ? 'selected' : '' }}>每頁 50 筆</option>
                                            <option value="100" {{ $list == 100 ? 'selected' : '' }}>每頁 100 筆</option>
                                            <option value="300" {{ $list == 300 ? 'selected' : '' }}>每頁 300 筆</option>
                                            <option value="500" {{ $list == 500 ? 'selected' : '' }}>每頁 500 筆</option>
                                        </select>
                                        <input type="hidden" name="keyword" value="{{ $keyword ?? '' }}">
                                    </form>
                                </div>
                            </div>
                            <div class="float-right">
                                @if(isset($appends))
                                {{ $users->appends($appends)->render() }}
                                @else
                                {{ $users->render() }}
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

@section('modal')
{{-- 推薦人視窗 --}}
<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="width:90%">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-left" width="20%">使用者id</th>
                            <th class="text-left" width="20%">姓名</th>
                            <th class="text-left" width="20%">電話</th>
                            <th class="text-left" width="20%">email</th>
                            <th class="text-left" width="10%">購物金</th>
                            <th class="text-left" width="10%">推薦人</th>
                        </tr>
                    </thead>
                    <tbody id="data"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">關閉</button>
            </div>
        </div>
    </div>
</div>

{{-- 匯入購物金 Modal --}}
<div id="importModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">請選擇匯入檔案</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form  id="importForm" action="{{ url('users/import') }}" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="cate" value="users">
                    <input type="hidden" name="type" value="購物金">
                    @csrf
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
                    <span class="text-danger">注意! 請選擇正確的匯入檔案，否則將造成資料錯誤。<a href="{{ 'https://'.env('ADMIN_DOMAIN').'/sample/匯入購物金範例.xlsx' }}" class="mb-3 mr-3" target="_blank">匯入購物金範例</a></span>
                </div>
            </div>
        </div>
    </div>
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

        $('#orderImport').click(function(){
            $('#importModal').modal('show');
        });

        $('#importBtn').click(function(){
            let form = $('#importForm');
            $('#importBtn').attr('disabled',true);
            form.submit();
            $('#importModal').modal('hide');
            $('#importBtn').attr('disabled',false);
            $('#filename').val('');
            $('.custom-file-label').html('瀏覽選擇EXCEL檔案');
        });

        $('input[type=file]').change(function(x) {
            let name = this.name;
            let file = x.currentTarget.files;
            let filename = file[0].name; //不檢查檔案直接找出檔名
            if (file.length >= 1) {
                if (filename) {
                    $('label[for=' + name + ']').html(filename);
                } else {
                    $(this).val('');
                    $('label[for=' + name + ']').html('瀏覽選擇EXCEL檔案');
                }
            } else {
                $(this).val('');
                $('label[for=' + name + ']').html('瀏覽選擇EXCEL檔案');
            }
        });
    })(jQuery);

    function intro(id){
        let token = '{{ csrf_token() }}';
        $.ajax({
            type: "post",
            url: 'users/getintro',
            data: { id: id, _token: token },
            success: function(data) {
                let result = '';
                result = result + '<tr class="bg-gray"><td><a href="users/'+data['user']['id']+'">'+data['user']['id']+'</a></td><td>'+data['user']['name']+'</td><td>'+data['user']['mobile']+'</td><td>'+data['user']['email']+'</td><td>'+data['user']['points']+'</td><td><span class="badge badge-danger" style="cursor:pointer" onclick="intro('+data['user']['refer_id']+')">'+data['user']['refer_id']+'</span></td></tr>'
                for(i=0;i<data['intros'].length;i++){
                    result = result + '<tr><td><a href="users/'+data['intros'][i]['id']+'">'+data['intros'][i]['id']+'</a></td><td>'+data['intros'][i]['name']+'</td><td>'+data['intros'][i]['mobile']+'</td><td>'+data['intros'][i]['email']+'</td><td>'+data['intros'][i]['points']+'</td><td><span class="badge badge-primary" style="cursor:pointer" onclick="intro('+data['intros'][i]['refer_id']+')">'+data['intros'][i]['refer_id']+'</span></td></tr>'
                }
                $('#myModal').modal('show');
                $('#ModalLabel').html('推薦人 ID:'+ data['user']['id'] +' 總共推薦 '+ data['total'] +' 朋友');
                $('#data').html(result);
            }
        });
    }

    $('.modal').on('shown.bs.modal', function(){
        var margin_vertical = parseInt( $(this).find('.modal-dialog').css('margin-top') ) + parseInt( $(this).find('.modal-dialog').css('margin-bottom') ) || 0;
        var height_header   = parseInt( $(this).find('.modal-header').css('height') ) || 0;
        var height_footer   = parseInt( $(this).find('.modal-footer').css('height') ) || 0;
        var height_body     = ( window.innerHeight - height_header - height_footer - margin_vertical - 10 ) + 'px';
        $(this).find('.modal-body').css('max-height', height_body).css('overflow', 'auto');
    });
</script>
@endsection
