@extends('admin.layouts.master')

@section('title', '首頁策展')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        {{-- alert訊息 --}}
        @include('admin.layouts.alert_message')
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-6">
                    <h1 class="m-0 text-dark"><b>首頁策展</b><small> ({{ isset($curation) ? '修改' : '新增' }})</small></h1>
                </div>
                <div class="col-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">後台管理系統</a></li>
                        <li class="breadcrumb-item active"><a href="{{ url('curations') }}">首頁策展</a></li>
                        <li class="breadcrumb-item active">{{ isset($curation) ? '修改' : '新增' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">資料設定</h3>
                </div>
                <div class="card-body">
                    @if(isset($curation))
                    <form id="myform" action="{{ route('admin.curations.update', $curation->id) }}" method="POST">
                        <input type="hidden" name="_method" value="PATCH">
                    @else
                    <form id="myform" action="{{ route('admin.curations.store') }}" method="POST">
                    @endif
                        <input type="hidden" name="category" value="home">
                        @csrf
                        <div class="row">
                            <div class="form-group col-12">
                                <div class="row">
                                    <nav class="w-100">
                                        <div class="nav nav-tabs" id="curation-tab" role="tablist">
                                            <a class="nav-item nav-link active" id="curation-chinese-tab" data-toggle="tab" href="#curation-chinese" role="tab" aria-controls="curation-chinese" aria-selected="true">中文</a>
                                            @for($i=0;$i<count($langs);$i++)
                                            <a class="nav-item nav-link" id="curation-{{ $langs[$i]['code'] }}-tab" data-toggle="tab" href="#curation-{{ $langs[$i]['code'] }}" role="tab" aria-controls="curation-{{ $langs[$i]['code'] }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                            @endfor
                                        </div>
                                    </nav>
                                    <div class="col-12 tab-content p-3" id="nav-tabContent">
                                        <div class="tab-pane fade show active" id="curation-chinese" role="tabpanel" aria-labelledby="curation-chinese-tab">
                                            <div class="row">
                                                <div class="form-group col-8">
                                                    <label for="main_title"><span class="text-red">* </span>主標題</label>
                                                    <input type="text" class="form-control {{ $errors->has('main_title') ? ' is-invalid' : '' }}" id="main_title" name="main_title" value="{{ $curation->main_title ?? '' }}" placeholder="請輸入主標題">
                                                    @if ($errors->has('main_title'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('main_title') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-1">
                                                    <label for="show_main_title">顯示主標題</label>
                                                    <div class="input-group">
                                                        <input type="checkbox" name="show_main_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->show_main_title == 1 ? 'checked' : '' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="form-group col-2">
                                                    <label for="main_title_background">主標題背景顏色</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control {{ $errors->has('main_title_background') ? ' is-invalid' : '' }}" name="main_title_background" value="{{ $curation->main_title_background ?? '' }}" placeholder="EX，rgba(210,117,117,1)">
                                                        <div id="main_title_background" class="col-2 input-group-append form-control align-items-center" style="background-color:{{ isset($curation) ? $curation->main_title_background : ''}}">
                                                            <i class="far fa-hand-point-up"></i>
                                                        </div>
                                                    </div>
                                                    @if ($errors->has('main_title_background'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('main_title_background') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-1">
                                                    <label for="show_main_title_background">顯示顏色</label>
                                                    <div class="input-group">
                                                        <input type="checkbox" name="show_main_title_background" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->show_main_title_background == 1 ? 'checked' : '' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="form-group col-11">
                                                    <label for="sub_title">副標題</label>
                                                    <input type="text" class="form-control {{ $errors->has('sub_title') ? ' is-invalid' : '' }}" id="sub_title" name="sub_title" value="{{ $curation->sub_title ?? '' }}" placeholder="請輸入副標題">
                                                    @if ($errors->has('sub_title'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('sub_title') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-1">
                                                    <label for="show_sub_title">顯示副標題</label>
                                                    <div class="input-group">
                                                        <input type="checkbox" name="show_sub_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->show_sub_title == 1 ? 'checked' : '' : '' }}>
                                                    </div>
                                                </div>
                                                <div class="form-group col-6">
                                                    <label for="caption">頁面說明文案</label>
                                                    <textarea max="255" rows="5" class="caption_content" id="caption" name="caption" placeholder="請輸入說明文案">{{ $curation->caption ?? '' }}</textarea>
                                                    @if ($errors->has('caption'))
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('caption') }}</strong>
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="form-group col-6">
                                                    <div class="form-group">
                                                        <label>文案圖片/Header版型背景圖片</label>
                                                        <div class="input-group">
                                                            <div class="custom-file">
                                                                <input type="file" id="background_image" name="background_image" class="custom-file-input {{ $errors->has('background_image') ? ' is-invalid' : '' }}" accept="image/*">
                                                                <label class="custom-file-label" for="background_image">{{ isset($curation) ? $curation->background_image ?? '瀏覽選擇新圖片' : '瀏覽選擇新圖片' }}</label>
                                                            </div>
                                                            @if(isset($curation) && $curation->background_image)
                                                            <div class="input-group-append">
                                                                <button type="button" class="btn btn-sm bg-danger removeImage" value="background_image"><i class="far fa-trash-alt"></i></button>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        @if ($errors->has('background_image'))
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $errors->first('background_image') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="form-group col-8" id="background_image_div">
                                                        @if(isset($curation) && $curation->background_image)
                                                        <a href="{{ env('AWS_FILE_URL').$curation->background_image }}" data-toggle="lightbox" data-title="{{ $curation->main_title .' 背景圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                                            <img class="col-12 background_image" src="{{ env('AWS_FILE_URL').$curation->background_image }}">
                                                        </a>
                                                        @else
                                                        <img class="col-12 background_image" src="">
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @for($i=0;$i<count($langs);$i++)
                                        <div class="tab-pane fade" id="curation-{{ $langs[$i]['code'] }}" role="tabpanel" aria-labelledby="curation-{{ $langs[$i]['code'] }}-tab">
                                            <div class="row">
                                                <div class="form-group col-6">
                                                    <label>{{ $langs[$i]['name'] }}主標題</label>
                                                    <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][main_title]" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['main_title'] : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}主標題">
                                                </div>
                                                <div class="form-group col-6">
                                                    <label>{{ $langs[$i]['name'] }}副標題</label>
                                                    <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][sub_title]" value="{{ isset($langs[$i]['data']) ? $langs[$i]['data']['sub_title'] : '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}副標題">
                                                </div>
                                                <div class="form-group col-12">
                                                    <label for="caption">策展頁面{{ $langs[$i]['name'] }}說明文案</label>
                                                    <textarea rows="3" class="caption_content" name="langs[{{ $langs[$i]['code'] }}][caption]" placeholder="請輸入{{ $langs[$i]['name'] }}說明文案">{{ isset($langs[$i]['data']) ? $langs[$i]['data']['caption'] : '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        @endfor
                                    </div>
                                    <div class="card-primary card-outline col-12 mb-2"></div>
                                    <div class="form-group col-6">
                                        <label for="type">版型: <span class="text-danger text-sm">(僅品牌、商品、圖片版型適用於目前舊版前台。)</span>@if(!empty($curation)) <span class="text-danger text-sm">(變更版型時，本策展資料將先被強制關閉。)</span>@endif</label>
                                        <div>
                                            @foreach($types as $key => $value)
                                            <div class="icheck-primary d-inline">
                                                {{-- <input type="radio" id="type_{{ $key }}" name="type" value="{{ $key }}" {{ isset($curation) ? $curation->type == $key ? 'checked' : '' :  $key == 'header' ? 'checked' : ''}}  {{ isset($curation) ? 'disabled' : '' }}> --}}
                                                <input type="radio" id="type_{{ $key }}" name="type" value="{{ $key }}" {{ (isset($curation) && $curation->type == $key) || $key == 'header' ? 'checked' : '' }}>
                                                <label for="type_{{ $key }}" class="mr-2">{{ $value }}</label>
                                            </div>
                                            @endforeach
                                            @if(!empty($curation))<br><span class="text-sm text-danger text-bold">請先設定版型內容資料後再重新啟用。變更後會保留之前版型設定，以利將來繼續使用之前的版型設定。</span>@endif
                                        </div>
                                    </div>
                                    <div class="form-group col-4">
                                        <label for="datetime">策展時間區間: (未填寫則期間無限)</label>
                                        <div class="input-group">
                                            <input type="datetime" class="form-control datetimepicker" id="start_time" name="start_time" placeholder="格式：2016-06-06 05:55:00" value="{{ isset($curation) ? $curation->start_time : '' }}" autocomplete="off">
                                            <span class="input-group-addon bg-primary">~</span>
                                            <input type="datetime" class="form-control datetimepicker" id="end_time" name="end_time" placeholder="格式：2018-06-16 15:55:00" value="{{ isset($curation) ? $curation->end_time : '' }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-1">
                                        <label for="is_on">啟用狀態</label>
                                        <div class="input-group">
                                            <input type="checkbox" name="is_on" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->is_on == 1 ? 'checked' : '' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group col-1">
                                        <label for="sort">排序</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="sort" value="{{ isset($curation) ? $curation->sort : 0 }}">
                                        </div>
                                    </div>
                                    <div class="form-group col-6">
                                        <label for="old_url">舊版按鈕連結</label>
                                        <input type="text" class="form-control {{ $errors->has('old_url') ? ' is-invalid' : '' }}" id="old_url" name="old_url" value="{{ $curation->old_url ?? '' }}" placeholder="請輸入連結">
                                        @if ($errors->has('old_url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('old_url') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-4">
                                        <label for="url">新版更多按鈕連結</label>
                                        <input type="text" class="form-control {{ $errors->has('url') ? ' is-invalid' : '' }}" id="url" name="url" value="{{ $curation->url ?? '' }}" placeholder="請輸入連結">
                                        @if ($errors->has('url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('url') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-1">
                                        <label for="show_url">顯示更多按鈕</label>
                                        <div class="input-group">
                                            <input type="checkbox" name="show_url" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->show_url == 1 ? 'checked' : '' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group col-1">
                                        <label for="is_on">連結另開視窗</label>
                                        <div class="input-group">
                                            <input type="checkbox" name="url_open_window" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ isset($curation) ? $curation->url_open_window == 1 ? 'checked' : '' : '' }}>
                                        </div>
                                    </div>
                                    <div class="form-group col-2">
                                        <label for="show_background_type">顯示背景類型</label>
                                        <div class="input-group">
                                            <select class="form-control" name="show_background_type">
                                                <option value="off" {{ isset($curation) ? $curation->show_background_type == 'image' ? 'selected' : '' : 'selected' }}>不顯示</option>
                                                <option value="color" {{ isset($curation) ? $curation->show_background_type == 'color' ? 'selected' : '' : '' }}>背景顏色</option>
                                                <option value="css" {{ isset($curation) ? $curation->show_background_type == 'css' ? 'selected' : '' : '' }}>自行設定CSS</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-3">
                                        <label for="background_color">版型背景顏色</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control {{ $errors->has('background_color') ? ' is-invalid' : '' }}" name="background_color" value="{{ $curation->background_color ?? '' }}" placeholder="顏色代碼，EX: rgba(220,94,94,1)">
                                            <div id="background_color" class="col-2 input-group-append form-control align-items-center" style="background-color:{{ isset($curation) ? $curation->background_color : ''}}">
                                                <i class="far fa-hand-point-up"></i>
                                            </div>
                                        </div>
                                        @if ($errors->has('background_color'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('background_color') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group col-7">
                                        <label for="background_css">自行設定背景CSS</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control {{ $errors->has('background_css') ? ' is-invalid' : '' }}" name="background_css" value="{{ $curation->background_css ?? '' }}" placeholder="請輸入CSS代碼，例如：background-color: #FF00FFFF;">
                                        </div>
                                        @if ($errors->has('background_css'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('background_css') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div id="layoutcolumns" class="form-group col-4" style="display: none">
                                        <label>策展布局設定(欄數):</label>
                                        <div id="columns">
                                            @if(isset($curation))
                                            @if($curation->type != 'block' && $curation->type != 'nowordblock')
                                            @for($i = 2; $i <= 6; $i++)
                                            <div class="icheck-success d-inline">
                                                <input type="radio" id="columns{{ $i }}" name="columns" value="{{ $i }}" {{ (isset($curation) && $curation->columns == $i) || $i==1 ? 'checked' : '' }}>
                                                <label for="columns{{ $i }}" class="mr-2">{{ $i }}欄</label>
                                            </div>
                                            @endfor
                                            @else
                                            @for($i = 4; $i <= 8; $i = $i+4)
                                            <div class="icheck-success d-inline">
                                                <input type="radio" id="columns{{ $i }}" name="columns" value="{{ $i }}" {{ (isset($curation) && $curation->columns == $i) || $i==1 ? 'checked' : '' }}>
                                                <label for="columns{{ $i }}" class="mr-2">{{ $i }}欄</label>
                                            </div>
                                            @endfor
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                    <div id="layoutrows" class="form-group col-3" style="display: none">
                                        <label for="rows">策展布局設定(列數):</label>
                                        <div>
                                            @for($i = 1; $i <= 2; $i++)
                                            <div class="icheck-success d-inline">
                                                <input type="radio" id="rows{{ $i }}" name="rows" value="{{ $i }}" {{ (isset($curation) && $curation->columns == $i) || $i==1 ? 'checked' : '' }}>
                                                <label for="rows{{ $i }}" class="mr-2">{{ $i }}列</label>
                                            </div>
                                            @endfor
                                        </div>
                                    </div>
                                    <div id="oldtextlayout" class="form-group col-3" style="display: none">
                                        <label for="text_layout">舊版圖片文字位置:</label>
                                        <div>
                                            <div class="icheck-success d-inline">
                                                <input type="radio" id="text_layout" name="old_text_layout" value="" {{ isset($curation) ? $curation->old_text_layout == null || $curation->text_layout == '' ? 'checked' : '' : 'checked'}}>
                                                <label for="text_layout" class="mr-2">無</label>
                                            </div>
                                            <div class="icheck-success d-inline">
                                                <input type="radio" id="text_layout1" name="old_text_layout" value="inside" {{ isset($curation) ? $curation->old_text_layout == 'inside' ? 'checked' : '' : ''}}>
                                                <label for="text_layout1" class="mr-2">內部</label>
                                            </div>
                                            <div class="icheck-success d-inline">
                                                <input type="radio" id="text_layout2" name="old_text_layout" value="bottom" {{ isset($curation) ? $curation->old_text_layout == 'bottom' ? 'checked' : '' : ''}}>
                                                <label for="text_layout2" class="mr-2">底部</label>
                                            </div>
                                        </div>
                                    </div>
                                    @if(empty($curation))
                                    <div class="form-group col-12">
                                        <h3 class="text-danger">新增時，請先設定好基本資料並儲存後，才能設定版型內容及語言資料</h3>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-center bg-white">
                            @if(in_array(isset($curation) ? $menuCode.'M' : $menuCode.'N', explode(',',Auth::user()->power)))
                            <button type="submit" class="btn btn-primary">{{ isset($curation) ? '修改' : '新增' }}</button>
                            @endif
                            <a href="{{ url('curations') }}" class="btn btn-info">
                                <span class="text-white"><i class="fas fa-history"></i> 取消</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @if(isset($curation))
            <div id="image" class="type" style="display:none">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">圖片版型資料</h3>
                        <div class="card-tools">
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover table-sm text-sm">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center align-middle">圖片</th>
                                    <th width="15%" class="text-left align-middle">主標題</th>
                                    <th width="15%" class="text-left align-middle">副標題</th>
                                    <th width="10%" class="text-center align-middle">文字位置</th>
                                    <th width="30%" class="text-left align-middle">連結網址</th>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <th width="7%" class="text-center align-middle">排序</th>
                                    @endif
                                    <th width="8%" class="text-center align-middle">修改/刪除</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($curation->images) > 0)
                                @foreach($curation->images as $image)
                                <tr>
                                    <td class="text-center align-middle">
                                        @if(str_replace(env('AWS_FILE_URL'),'',$image->image))
                                        <a href="{{ $image->image }}" data-toggle="lightbox" data-title="{{ $image->main_title .' 圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                        <img height="50" class="image_{{ $image->id }}" src="{{ $image->image }}" alt="">
                                        </a>
                                        @else
                                        <img height="50" class="image_{{ $image->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        @if($image->main_title)
                                        <span>{{ $image->main_title }}</span><br>
                                        @if($image->show_main_title == 1)
                                        <span class="badge badge-primary">顯示</span>
                                        @else
                                        <span class="badge badge-secondary">關閉</span>
                                        @endif
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        @if($image->sub_title)
                                        <span>{{ $image->sub_title }}</span><br>
                                        @if($image->show_sub_title == 1)
                                        <span class="badge badge-primary">顯示</span>
                                        @else
                                        <span class="badge badge-secondary">關閉</span>
                                        @endif
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">{{ $image->text_position == 'bottom' ? '圖片底部' : '圖片內部' }}</td>
                                    <td class="text-left align-middle">
                                        @if($image->url != '#')
                                        <div class="col-12 text-warp">
                                            <a href="{{ $image->url }}" target="_blank">{{ $image->url }}</a>
                                        </div>
                                        @else
                                        {{ $image->url }}
                                        @endif
                                    </td>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <td class="text-center align-middle">
                                        @if($loop->iteration != 1)
                                        <a href="{{ url('curationimages/sortup/' . $image->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                        </a>
                                        @endif
                                        @if($loop->iteration != count($curation->images))
                                        <a href="{{ url('curationimages/sortdown/' . $image->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                        </a>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-primary edit-btn" value="image_{{ $image->row }}_{{ $image->id }}"><i class="fas fa-edit"></i></button>
                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                        <form class="d-inline" action="{{ route('admin.curationimages.destroy', $image->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="image_{{ $image->row }}_{{ $image->id }}" style="display:none">
                                    <td colspan="7">
                                        <form class="curationImageForm_image_{{ $image->id }}" action="{{ route('admin.curationimages.update', $image->id) }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                            <input type="hidden" name="style" value="{{ $image->style }}">
                                            <input type="hidden" name="row" value="1">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-2">
                                                        <div class="text-center">
                                                            @if(str_replace(env('AWS_FILE_URL'),'',$image->image))
                                                            <a href="{{ $image->image }}" data-toggle="lightbox" data-title="{{ $image->main_title .' 圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                                            <img width="100%" class="image_{{ $image->id }}" src="{{ $image->image }}" alt="">
                                                            </a>
                                                            @else
                                                            <img width="100%" class="image_{{ $image->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-10 row">
                                                        <div class="form-group col-5">
                                                            <label>上傳圖檔 自動等比縮小為 750 x 750 (縮小後最大尺寸)</label>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input type="file" id="image_{{ $image->id }}" name="image" class="custom-file-input" accept="image/*">
                                                                    <label class="custom-file-label" for="image_{{ $image->id }}">{{ $image->image ?? '瀏覽選擇新圖片' }}</label>
                                                                </div>
                                                                @if(str_replace(env('AWS_FILE_URL'),'',$image->image))
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-sm bg-danger removeImage" value="image_{{ $image->id }}"><i class="far fa-trash-alt"></i></button>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label>連結網址</label>
                                                            <input type="text" class="form-control" name="url" value="{{ $image->url ?? '' }}" placeholder="請輸入連結">
                                                        </div>
                                                        <div class="form-group col-1">
                                                            <label>連結另開視窗</label>
                                                            <div class="input-group">
                                                                <input type="checkbox" name="url_open_window" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $image->url_open_window == 1 ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label>舊版連結網址</label>
                                                            <input type="text" class="form-control" name="old_url" value="{{ $image->old_url ?? '' }}" placeholder="請輸入連結">
                                                        </div>
                                                        <div class="col-12 row">
                                                            <div class="col-10">
                                                                <div class="row">
                                                                    <nav class="w-100">
                                                                        <div class="nav nav-tabs" id="curationimage-tab" role="tablist">
                                                                            <a class="nav-item nav-link active" id="curationimage-chinese-{{ $image->id }}-tab" data-toggle="tab" href="#curationimage-chinese-{{ $image->id }}" role="tab" aria-controls="curationimage-chinese-{{ $image->id }}" aria-selected="true">中文</a>
                                                                            @for($i=0;$i<count($langs);$i++)
                                                                            <a class="nav-item nav-link" id="curationimage-{{ $langs[$i]['code'] }}-{{ $image->id }}-tab" data-toggle="tab" href="#curationimage-{{ $langs[$i]['code'] }}-{{ $image->id }}" role="tab" aria-controls="curationimage-{{ $langs[$i]['code'] }}-{{ $image->id }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                                            @endfor
                                                                        </div>
                                                                    </nav>
                                                                    <div class="col-12 tab-content" id="nav-tabContent">
                                                                        <div class="tab-pane fade show active" id="curationimage-chinese-{{ $image->id }}" role="tabpanel" aria-labelledby="curationimage-chinese-{{ $image->id }}-tab">
                                                                            <div class="row mt-3">
                                                                                <div class="form-group col-5">
                                                                                    <label>主標題</label>
                                                                                    <input type="text" class="form-control" name="main_title" value="{{ $image->main_title ?? '' }}" placeholder="請輸入主標題">
                                                                                </div>
                                                                                <div class="form-group col-1">
                                                                                    <label>顯示</label>
                                                                                    <div class="input-group">
                                                                                        <input type="checkbox" name="show_main_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $image->show_main_title == 1 ? 'checked' : '' }}>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group col-5">
                                                                                    <label>副標題</label>
                                                                                    <input type="text" class="form-control" name="sub_title" value="{{ $image->sub_title ?? '' }}" placeholder="請輸入副標題">
                                                                                </div>
                                                                                <div class="form-group col-1">
                                                                                    <label>顯示</label>
                                                                                    <div class="input-group">
                                                                                        <input type="checkbox" name="show_sub_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $image->show_sub_title == 1 ? 'checked' : '' }}>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group col-12">
                                                                                    <label>Modal 內容</label>
                                                                                    <textarea class="modal_content" name="modal_content">{{ $image->modal_content }}</textarea>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        @for($i=0;$i<count($langs);$i++)
                                                                        <div class="tab-pane fade" id="curationimage-{{ $langs[$i]['code'] }}-{{ $image->id }}" role="tabpanel" aria-labelledby="curationimage-{{ $langs[$i]['code'] }}-{{ $image->id }}-tab">
                                                                            <div class="row mt-3">
                                                                                <div class="form-group col-6">
                                                                                    <label>{{ $langs[$i]['name'] }}主標題</label>
                                                                                    <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][main_title]" value="{{ $langs[$i]['imagedata'][$image->id]['main_title'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}主標題">
                                                                                </div>
                                                                                <div class="form-group col-6">
                                                                                    <label>{{ $langs[$i]['name'] }}副標題</label>
                                                                                    <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][sub_title]" value="{{ $langs[$i]['imagedata'][$image->id]['sub_title'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}副標題">
                                                                                </div>
                                                                                <div class="form-group col-12">
                                                                                    <label>{{ $langs[$i]['name'] }} Modal 內容</label>
                                                                                    <textarea class="modal_content" name="langs[{{ $langs[$i]['code'] }}][modal_content]">{{ $langs[$i]['imagedata'][$image->id]['modal_content'] ?? '' }}</textarea>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        @endfor
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2">
                                                                <div class="form-group col-12">
                                                                    <label>開啟方式(連結或Modal)</label>
                                                                    <div>
                                                                        <div class="icheck-success d-inline">
                                                                            <input type="radio" id="open_method_url{{ $image->id }}" name="open_method" value="url" {{ $image->open_method == 'url' ? 'checked' : 'checked'}}>
                                                                            <label for="open_method_url{{ $image->id }}" class="mr-2">連結</label>
                                                                        </div>
                                                                        <div class="icheck-success d-inline">
                                                                            <input type="radio" id="open_method_modal{{ $image->id }}" name="open_method" value="modal" {{ $image->open_method == 'modal' ? 'checked' : ''}}>
                                                                            <label for="open_method_modal{{ $image->id }}" class="mr-2">Modal</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group col-12">
                                                                    <label>主副標題文字位置</label>
                                                                    <div>
                                                                        <div class="icheck-success d-inline">
                                                                            <input type="radio" id="text_position_inside{{ $image->id }}" name="text_position" value="inside" {{ $image->text_position == 'inside' ? 'checked' : ''}}>
                                                                            <label for="text_position_inside{{ $image->id }}" class="mr-2">內部</label>
                                                                        </div>
                                                                        <div class="icheck-success d-inline">
                                                                            <input type="radio" id="text_position_bottom{{ $image->id }}" name="text_position" value="bottom" {{ $image->text_position == 'bottom' ? 'checked' : ''}}>
                                                                            <label for="text_position_bottom{{ $image->id }}" class="mr-2">底部</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <button type="submit" class="btn btn-sm btn-primary float-right">修改</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                                @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                <tr>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-sm bg-success edit-btn" value="image_data_add">新增</button></td>
                                    <td colspan="6"></td>
                                </tr>
                                @endif
                                <tr class="image_data_add" style="display:none">
                                    <td colspan="7">
                                        <form class="curationImageForm" action="{{ route('admin.curationimages.store') }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                            <input type="hidden" name="style" value="image">
                                            <input type="hidden" name="row" value="1">
                                            <input type="hidden" name="sort" value="9999">
                                            @csrf
                                            <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-2">
                                                            <div class="text-center">
                                                                <img width="100%" class="add_image" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            </div>
                                                        </div>
                                                        <div class="col-10 row">
                                                            <div class="form-group col-5">
                                                                <label>上傳圖檔 自動等比縮小為 750 x 750 (縮小後最大尺寸)</label>
                                                                <div class="input-group">
                                                                    <div class="custom-file">
                                                                        <input type="file" id="add_image" name="image" class="custom-file-input" accept="image/*" required>
                                                                        <label class="custom-file-label" for="add_image">瀏覽選擇新圖片</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label>連結網址</label>
                                                                <input type="text" class="form-control" name="url" value="" placeholder="請輸入連結">
                                                            </div>
                                                            <div class="form-group col-1">
                                                                <label>連結另開視窗</label>
                                                                <div class="input-group">
                                                                    <input type="checkbox" name="url_open_window" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" checked>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 row">
                                                                <div class="col-10">
                                                                    <div class="row">
                                                                        <nav class="w-100">
                                                                            <div class="nav nav-tabs" id="curationblock-tab" role="tablist">
                                                                                <a class="nav-item nav-link active" id="curationblock-chinese-tab" data-toggle="tab" href="#curationblock-chinese" role="tab" aria-controls="curationblock-chinese" aria-selected="true">中文</a>
                                                                                @for($i=0;$i<count($langs);$i++)
                                                                                <a class="nav-item nav-link" id="curationblock-{{ $langs[$i]['code'] }}-tab" data-toggle="tab" href="#curationblock-{{ $langs[$i]['code'] }}" role="tab" aria-controls="curationblock-{{ $langs[$i]['code'] }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                                                @endfor
                                                                            </div>
                                                                        </nav>
                                                                        <div class="col-12 tab-content p-3" id="nav-tabContent">
                                                                            <div class="tab-pane fade show active" id="curationblock-chinese" role="tabpanel" aria-labelledby="curationblock-chinese-tab">
                                                                                <div class="row">
                                                                                    <div class="form-group col-5">
                                                                                        <label>主標題</label>
                                                                                        <input type="text" class="form-control" name="main_title" value="" placeholder="請輸入主標題">
                                                                                    </div>
                                                                                    <div class="form-group col-1">
                                                                                        <label>顯示</label>
                                                                                        <div class="input-group">
                                                                                            <input type="checkbox" name="show_main_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group col-5">
                                                                                        <label>副標題</label>
                                                                                        <input type="text" class="form-control" name="sub_title" value="" placeholder="請輸入副標題">
                                                                                    </div>
                                                                                    <div class="form-group col-1">
                                                                                        <label>顯示</label>
                                                                                        <div class="input-group">
                                                                                            <input type="checkbox" name="show_sub_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group col-12">
                                                                                        <label>Modal 內容</label>
                                                                                        <textarea class="modal_content" name="modal_content"></textarea>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            @for($i=0;$i<count($langs);$i++)
                                                                            <div class="tab-pane fade" id="curationblock-{{ $langs[$i]['code'] }}" role="tabpanel" aria-labelledby="curationblock-{{ $langs[$i]['code'] }}-tab">
                                                                                <div class="row">
                                                                                    <div class="form-group col-6">
                                                                                        <label>{{ $langs[$i]['name'] }}主標題</label>
                                                                                        <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][main_title]" value="" placeholder="請輸入{{ $langs[$i]['name'] }}主標題">
                                                                                    </div>
                                                                                    <div class="form-group col-6">
                                                                                        <label>{{ $langs[$i]['name'] }}副標題</label>
                                                                                        <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][sub_title]" value="" placeholder="請輸入{{ $langs[$i]['name'] }}副標題">
                                                                                    </div>
                                                                                    <div class="form-group col-12">
                                                                                        <label>{{ $langs[$i]['name'] }} Modal 內容</label>
                                                                                        <textarea class="modal_content" name="langs[{{ $langs[$i]['code'] }}][modal_content]"></textarea>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            @endfor
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-2">
                                                                    <div class="form-group col-12">
                                                                        <label>開啟方式(連結或Modal)</label>
                                                                        <div>
                                                                            <div class="icheck-success d-inline">
                                                                                <input type="radio" id="open_method_url" name="open_method" value="url" checked>
                                                                                <label for="open_method_url" class="mr-2">連結</label>
                                                                            </div>
                                                                            <div class="icheck-success d-inline">
                                                                                <input type="radio" id="open_method_modal" name="open_method" value="modal">
                                                                                <label for="open_method_modal" class="mr-2">Modal</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group col-12">
                                                                        <label>主副標題文字位置</label>
                                                                        <div>
                                                                            <div class="icheck-success d-inline">
                                                                                <input type="radio" id="text_position_inside" name="text_position" value="inside">
                                                                                <label for="text_position_inside" class="mr-2">內部</label>
                                                                            </div>
                                                                            <div class="icheck-success d-inline">
                                                                                <input type="radio" id="text_position_bottom" name="text_position" value="bottom" checked>
                                                                                <label for="text_position_bottom" class="mr-2">底部</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-12">
                                                                <button type="submit" class="btn btn-sm bg-success float-right">送出</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="event" class="type" style="display:none">
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">活動版型資料</h3>
                        <div class="card-tools">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="text-danger text-bold">注意!! 此版型不適用於目前舊版前台。</span>
                        </div>
                        <table class="table table-hover table-sm text-sm">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center align-middle">圖片</th>
                                    <th width="15%" class="text-left align-middle">主標題</th>
                                    <th width="15%" class="text-left align-middle">副標題</th>
                                    <th width="10%" class="text-center align-middle">文字位置</th>
                                    <th width="30%" class="text-left align-middle">連結網址</th>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <th width="7%" class="text-center align-middle">排序</th>
                                    @endif
                                    <th width="8%" class="text-center align-middle">修改/刪除</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($curation->events) > 0)
                                @foreach($curation->events as $event)
                                <tr>
                                    <td class="text-center align-middle">
                                        @if(str_replace(env('AWS_FILE_URL'),'',$event->image))
                                        <a href="{{ $event->image }}" data-toggle="lightbox" data-title="{{ $event->main_title .' 圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                        <img height="50" class="image_{{ $event->id }}" src="{{ $event->image }}" alt="">
                                        </a>
                                        @else
                                        <img height="50" class="image_{{ $event->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        @if($event->main_title)
                                        <span>{{ $event->main_title }}</span><br>
                                        @if($event->show_main_title == 1)
                                        <span class="badge badge-primary">顯示</span>
                                        @else
                                        <span class="badge badge-secondary">關閉</span>
                                        @endif
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        @if($event->sub_title)
                                        <span>{{ $event->sub_title }}</span><br>
                                        @if($event->show_sub_title == 1)
                                        <span class="badge badge-primary">顯示</span>
                                        @else
                                        <span class="badge badge-secondary">關閉</span>
                                        @endif
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">{{ $event->text_position == 'bottom' ? '圖片底部' : '圖片內部' }}</td>
                                    <td class="text-left align-middle">
                                        @if($event->url != '#')
                                        <div class="col-12 text-warp">
                                            <a href="{{ $event->url }}" target="_blank">{{ $event->url }}</a>
                                        </div>
                                        @else
                                        {{ $event->url }}
                                        @endif
                                    </td>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <td class="text-center align-middle">
                                        @if($loop->iteration != 1)
                                        <a href="{{ url('curationimages/sortup/' . $event->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                        </a>
                                        @endif
                                        @if($loop->iteration != count($curation->events))
                                        <a href="{{ url('curationimages/sortdown/' . $event->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                        </a>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-primary edit-btn" value="event_{{ $event->row }}_{{ $event->id }}"><i class="fas fa-edit"></i></button>
                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                        <form class="d-inline" action="{{ route('admin.curationimages.destroy', $event->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="event_{{ $event->row }}_{{ $event->id }}" style="display:none">
                                    <td colspan="7">
                                        <form class="curationImageForm_image_{{ $event->id }}" action="{{ route('admin.curationimages.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                            <input type="hidden" name="style" value="{{ $event->style }}">
                                            <input type="hidden" name="row" value="1">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-2">
                                                        <div class="text-center">
                                                            @if(str_replace(env('AWS_FILE_URL'),'',$event->image))
                                                            <a href="{{ $event->image }}" data-toggle="lightbox" data-title="{{ $event->main_title .' 圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                                            <img width="100%" class="image_{{ $event->id }}" src="{{ $event->image }}" alt="">
                                                            </a>
                                                            @else
                                                            <img width="100%" class="image_{{ $event->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-10 row">
                                                        <div class="form-group col-5">
                                                            <label>上傳圖檔 自動等比縮小為 750 x 750 (縮小後最大尺寸)</label>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input type="file" id="image_{{ $event->id }}" name="image" class="custom-file-input" accept="image/*">
                                                                    <label class="custom-file-label" for="image_{{ $event->id }}">{{ $event->image ?? '瀏覽選擇新圖片' }}</label>
                                                                </div>
                                                                @if(str_replace(env('AWS_FILE_URL'),'',$event->image))
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-sm bg-danger removeImage" value="image_{{ $event->id }}"><i class="far fa-trash-alt"></i></button>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label>連結網址</label>
                                                            <input type="text" class="form-control" name="url" value="{{ $event->url ?? '' }}" placeholder="請輸入連結">
                                                        </div>
                                                        <div class="form-group col-1">
                                                            <label>連結另開視窗</label>
                                                            <div class="input-group">
                                                                <input type="checkbox" name="url_open_window" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $event->url_open_window == 1 ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-9">
                                                            <div class="row">
                                                                <nav class="w-100">
                                                                    <div class="nav nav-tabs" id="curationevent-tab" role="tablist">
                                                                        <a class="nav-item nav-link active" id="curationevent-chinese-{{ $event->id }}-tab" data-toggle="tab" href="#curationevent-chinese-{{ $event->id }}" role="tab" aria-controls="curationevent-chinese-{{ $event->id }}" aria-selected="true">中文</a>
                                                                        @for($i=0;$i<count($langs);$i++)
                                                                        <a class="nav-item nav-link" id="curationevent-{{ $langs[$i]['code'] }}-{{ $event->id }}-tab" data-toggle="tab" href="#curationevent-{{ $langs[$i]['code'] }}-{{ $event->id }}" role="tab" aria-controls="curationevent-{{ $langs[$i]['code'] }}-{{ $event->id }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                                        @endfor
                                                                    </div>
                                                                </nav>
                                                                <div class="col-12 tab-content" id="nav-tabContent">
                                                                    <div class="tab-pane fade show active" id="curationevent-chinese-{{ $event->id }}" role="tabpanel" aria-labelledby="curationevent-chinese-{{ $event->id }}-tab">
                                                                        <div class="row mt-3">
                                                                            <div class="form-group col-5">
                                                                                <label>主標題</label>
                                                                                <input type="text" class="form-control" name="main_title" value="{{ $event->main_title ?? '' }}" placeholder="請輸入主標題">
                                                                            </div>
                                                                            <div class="form-group col-1">
                                                                                <label>顯示</label>
                                                                                <div class="input-group">
                                                                                    <input type="checkbox" name="show_main_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $event->show_main_title == 1 ? 'checked' : '' }}>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group col-5">
                                                                                <label>副標題</label>
                                                                                <input type="text" class="form-control" name="sub_title" value="{{ $event->sub_title ?? '' }}" placeholder="請輸入副標題">
                                                                            </div>
                                                                            <div class="form-group col-1">
                                                                                <label>顯示</label>
                                                                                <div class="input-group">
                                                                                    <input type="checkbox" name="show_sub_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $event->show_sub_title == 1 ? 'checked' : '' }}>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @for($i=0;$i<count($langs);$i++)
                                                                    <div class="tab-pane fade" id="curationevent-{{ $langs[$i]['code'] }}-{{ $event->id }}" role="tabpanel" aria-labelledby="curationevent-{{ $langs[$i]['code'] }}-{{ $event->id }}-tab">
                                                                        <div class="row mt-3">
                                                                            <div class="form-group col-6">
                                                                                <label>{{ $langs[$i]['name'] }}主標題</label>
                                                                                <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][main_title]" value="{{ $langs[$i]['eventdata'][$event->id]['main_title'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}主標題">
                                                                            </div>
                                                                            <div class="form-group col-6">
                                                                                <label>{{ $langs[$i]['name'] }}副標題</label>
                                                                                <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][sub_title]" value="{{ $langs[$i]['eventdata'][$event->id]['sub_title'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}副標題">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <label>主副標題文字位置</label>
                                                            <div>
                                                                <div class="icheck-success d-inline">
                                                                    <input type="radio" id="text_position_inside{{ $event->id }}" name="text_position" value="inside" {{ $event->text_position == 'inside' ? 'checked' : ''}}>
                                                                    <label for="text_position_inside{{ $event->id }}" class="mr-2">內部</label>
                                                                </div>
                                                                <div class="icheck-success d-inline">
                                                                    <input type="radio" id="text_position_bottom{{ $event->id }}" name="text_position" value="bottom" {{ $event->text_position == 'bottom' ? 'checked' : ''}}>
                                                                    <label for="text_position_bottom{{ $event->id }}" class="mr-2">底部</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                                        <div class="form-group col-12">
                                                            <button type="submit" class="btn btn-sm btn-primary float-right">修改</button>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                                @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                <tr>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-sm bg-success edit-btn" value="event_data_add">新增</button></td>
                                    <td colspan="6"></td>
                                </tr>
                                @endif
                                <tr class="event_data_add" style="display:none">
                                    <td colspan="7">
                                        <form class="curationImageForm" action="{{ route('admin.curationimages.store') }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                            <input type="hidden" name="style" value="event">
                                            <input type="hidden" name="row" value="1">
                                            <input type="hidden" name="sort" value="9999">
                                            @csrf
                                            <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-2">
                                                            <div class="text-center">
                                                                <img width="100%" class="add_event_image" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            </div>
                                                        </div>
                                                        <div class="col-10 row">
                                                            <div class="form-group col-5">
                                                                <label>上傳圖檔 自動等比縮小為 750 x 750 (縮小後最大尺寸)</label>
                                                                <div class="input-group">
                                                                    <div class="custom-file">
                                                                        <input type="file" id="add_event_image" name="image" class="custom-file-input" accept="image/*" required>
                                                                        <label class="custom-file-label" for="add_event_image">瀏覽選擇新圖片</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-6">
                                                                <label>連結網址</label>
                                                                <input type="text" class="form-control" name="url" value="" placeholder="請輸入連結">
                                                            </div>
                                                            <div class="form-group col-1">
                                                                <label>連結另開視窗</label>
                                                                <div class="input-group">
                                                                    <input type="checkbox" name="url_open_window" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" checked>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-9">
                                                                <div class="row">
                                                                    <nav class="w-100">
                                                                        <div class="nav nav-tabs" id="curationevent-tab" role="tablist">
                                                                            <a class="nav-item nav-link active" id="curationevent-chinese-tab" data-toggle="tab" href="#curationevent-chinese" role="tab" aria-controls="curationevent-chinese" aria-selected="true">中文</a>
                                                                            @for($i=0;$i<count($langs);$i++)
                                                                            <a class="nav-item nav-link" id="curationevent-{{ $langs[$i]['code'] }}-tab" data-toggle="tab" href="#curationevent-{{ $langs[$i]['code'] }}" role="tab" aria-controls="curationevent-{{ $langs[$i]['code'] }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                                            @endfor
                                                                        </div>
                                                                    </nav>
                                                                    <div class="col-12 tab-content p-3" id="nav-tabContent">
                                                                        <div class="tab-pane fade show active" id="curationevent-chinese" role="tabpanel" aria-labelledby="curationevent-chinese-tab">
                                                                            <div class="row">
                                                                                <div class="form-group col-5">
                                                                                    <label>主標題</label>
                                                                                    <input type="text" class="form-control" name="main_title" value="" placeholder="請輸入主標題">
                                                                                </div>
                                                                                <div class="form-group col-1">
                                                                                    <label>顯示</label>
                                                                                    <div class="input-group">
                                                                                        <input type="checkbox" name="show_main_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group col-5">
                                                                                    <label>副標題</label>
                                                                                    <input type="text" class="form-control" name="sub_title" value="" placeholder="請輸入副標題">
                                                                                </div>
                                                                                <div class="form-group col-1">
                                                                                    <label>顯示</label>
                                                                                    <div class="input-group">
                                                                                        <input type="checkbox" name="show_sub_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        @for($i=0;$i<count($langs);$i++)
                                                                        <div class="tab-pane fade" id="curationevent-{{ $langs[$i]['code'] }}" role="tabpanel" aria-labelledby="curationevent-{{ $langs[$i]['code'] }}-tab">
                                                                            <div class="row">
                                                                                <div class="form-group col-6">
                                                                                    <label>{{ $langs[$i]['name'] }}主標題</label>
                                                                                    <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][main_title]" value="" placeholder="請輸入{{ $langs[$i]['name'] }}主標題">
                                                                                </div>
                                                                                <div class="form-group col-6">
                                                                                    <label>{{ $langs[$i]['name'] }}副標題</label>
                                                                                    <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][sub_title]" value="" placeholder="請輸入{{ $langs[$i]['name'] }}副標題">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        @endfor
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-3">
                                                                <label>主副標題文字位置</label>
                                                                <div>
                                                                    <div class="icheck-success d-inline">
                                                                        <input type="radio" id="event_text_position_inside" name="text_position" value="inside">
                                                                        <label for="event_text_position_inside" class="mr-2">內部</label>
                                                                    </div>
                                                                    <div class="icheck-success d-inline">
                                                                        <input type="radio" id="event_text_position_bottom" name="text_position" value="bottom" checked>
                                                                        <label for="event_text_position_bottom" class="mr-2">底部</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-12">
                                                                <button type="submit" class="btn btn-sm bg-success float-right">送出</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="block" class="type" style="display:none">
                @for($r = 1; $r <= $curation->rows; $r++)
                <div class="card card-info checkrows{{ $r }}" {{ $r == 2 ? 'style="display:none;"' : '' }}>
                    <div class="card-header">
                        <h3 class="card-title">宮格版型 第{{ $r }}列資料</h3>
                        <div class="card-tools">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="text-danger text-bold">注意!! 此版型不適用於目前舊版前台。</span>
                        </div>
                        <table class="table table-hover table-sm text-sm">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center align-middle">圖片</th>
                                    <th width="15%" class="text-left align-middle">主標題</th>
                                    <th width="15%" class="text-left align-middle">副標題</th>
                                    <th width="10%" class="text-center align-middle">文字位置</th>
                                    <th width="30%" class="text-left align-middle">連結網址</th>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <th width="7%" class="text-center align-middle">排序</th>
                                    @endif
                                    <th width="8%" class="text-center align-middle">修改/刪除</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($curation->blocks) > 0)
                                @foreach($curation->blocks as $block)
                                @if($block->row == $r)
                                <tr>
                                    <td class="text-center align-middle">
                                        @if(str_replace(env('AWS_FILE_URL'),'',$block->image))
                                        <a href="{{ $block->image }}" data-toggle="lightbox" data-title="{{ $block->main_title .' 圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                        <img height="50" class="block_image_{{ $block->id }}" src="{{ $block->image }}" alt="">
                                        </a>
                                        @else
                                        <img height="50" class="block_image_{{ $block->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        @if($block->main_title)
                                        <span>{{ $block->main_title }}</span><br>
                                        @if($block->show_main_title == 1)
                                        <span class="badge badge-primary">顯示</span>
                                        @else
                                        <span class="badge badge-secondary">關閉</span>
                                        @endif
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        @if($block->sub_title)
                                        <span>{{ $block->sub_title }}</span><br>
                                        @if($block->show_sub_title == 1)
                                        <span class="badge badge-primary">顯示</span>
                                        @else
                                        <span class="badge badge-secondary">關閉</span>
                                        @endif
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">{{ $block->text_position == 'bottom' ? '圖片底部' : '圖片內部' }}</td>
                                    <td class="text-left align-middle">
                                        @if($block->url != '#')
                                        <div class="col-12 text-warp">
                                            <a href="{{ $block->url }}" target="_blank">{{ $block->url }}</a>
                                        </div>
                                        @else
                                        {{ $block->url }}
                                        @endif
                                    </td>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <td class="text-center align-middle">
                                        @if($block->sort != 1)
                                        <a href="{{ url('curationimages/sortup/' . $block->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                        </a>
                                        @endif
                                        @if($block->sort != $totalBlocks[$r])
                                        <a href="{{ url('curationimages/sortdown/' . $block->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                        </a>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-primary edit-btn" value="block_{{ $block->row }}_{{ $block->id }}"><i class="fas fa-edit"></i></button>
                                        <form class="d-inline" action="{{ route('admin.curationimages.destroy', $block->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="block_{{ $block->row }}_{{ $block->id }}" style="display:none">
                                    <td colspan="7">
                                        <form class="curationImageForm_image_{{ $block->id }}" action="{{ route('admin.curationimages.update', $block->id) }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <input type="hidden" name="curation_id" value="{{ $curation->id }}">
                                            <input type="hidden" name="style" value="{{ $block->style }}">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-2">
                                                        <div class="text-center">
                                                            @if(str_replace(env('AWS_FILE_URL'),'',$block->image))
                                                            <a href="{{ $block->image }}" data-toggle="lightbox" data-title="{{ $block->main_title .' 圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                                            <img width="100%" class="image_{{ $block->id }}" src="{{ $block->image }}" alt="">
                                                            </a>
                                                            @else
                                                            <img width="100%" class="image_{{ $block->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-10 row">
                                                        <div class="form-group col-5">
                                                            <label>上傳圖檔 自動等比縮小為 750 x 750 (縮小後最大尺寸)</label>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input type="file" id="image_{{ $block->id }}" name="image" class="custom-file-input" accept="image/*">
                                                                    <label class="custom-file-label" for="image_{{ $block->id }}">{{ $block->image ?? '瀏覽選擇新圖片' }}</label>
                                                                </div>
                                                                @if(str_replace(env('AWS_FILE_URL'),'',$block->image))
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-sm bg-danger removeImage" value="image_{{ $block->id }}"><i class="far fa-trash-alt"></i></button>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label>連結網址</label>
                                                            <input type="text" class="form-control" name="url" value="{{ $block->url ?? '' }}" placeholder="請輸入連結">
                                                        </div>
                                                        <div class="form-group col-1">
                                                            <label>連結另開視窗</label>
                                                            <div class="input-group">
                                                                <input type="checkbox" name="url_open_window" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $block->url_open_window == 1 ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-9">
                                                            <div class="row">
                                                                <nav class="w-100">
                                                                    <div class="nav nav-tabs" id="curationblock-tab" role="tablist">
                                                                        <a class="nav-item nav-link active" id="curationblock-chinese-{{ $block->id }}-tab" data-toggle="tab" href="#curationblock-chinese-{{ $block->id }}" role="tab" aria-controls="curationblock-chinese-{{ $block->id }}" aria-selected="true">中文</a>
                                                                        @for($i=0;$i<count($langs);$i++)
                                                                        <a class="nav-item nav-link" id="curationblock-{{ $langs[$i]['code'] }}-{{ $block->id }}-tab" data-toggle="tab" href="#curationblock-{{ $langs[$i]['code'] }}-{{ $block->id }}" role="tab" aria-controls="curationblock-{{ $langs[$i]['code'] }}-{{ $block->id }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                                        @endfor
                                                                    </div>
                                                                </nav>
                                                                <div class="col-12 tab-content" id="nav-tabContent">
                                                                    <div class="tab-pane fade show active" id="curationblock-chinese-{{ $block->id }}" role="tabpanel" aria-labelledby="curationblock-chinese-{{ $block->id }}-tab">
                                                                        <div class="row mt-3">
                                                                            <div class="form-group col-5">
                                                                                <label>主標題</label>
                                                                                <input type="text" class="form-control" name="main_title" value="{{ $block->main_title ?? '' }}" placeholder="請輸入主標題">
                                                                            </div>
                                                                            <div class="form-group col-1">
                                                                                <label>顯示</label>
                                                                                <div class="input-group">
                                                                                    <input type="checkbox" name="show_main_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $block->show_main_title == 1 ? 'checked' : '' }}>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group col-5">
                                                                                <label>副標題</label>
                                                                                <input type="text" class="form-control" name="sub_title" value="{{ $block->sub_title ?? '' }}" placeholder="請輸入副標題">
                                                                            </div>
                                                                            <div class="form-group col-1">
                                                                                <label>顯示</label>
                                                                                <div class="input-group">
                                                                                    <input type="checkbox" name="show_sub_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $block->show_sub_title == 1 ? 'checked' : '' }}>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @for($i=0;$i<count($langs);$i++)
                                                                    <div class="tab-pane fade" id="curationblock-{{ $langs[$i]['code'] }}-{{ $block->id }}" role="tabpanel" aria-labelledby="curationblock-{{ $langs[$i]['code'] }}-{{ $block->id }}-tab">
                                                                        <div class="row mt-3">
                                                                            <div class="form-group col-6">
                                                                                <label>{{ $langs[$i]['name'] }}主標題</label>
                                                                                <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][main_title]" value="{{ $langs[$i]['blockdata'][$block->id]['main_title'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}主標題">
                                                                            </div>
                                                                            <div class="form-group col-6">
                                                                                <label>{{ $langs[$i]['name'] }}副標題</label>
                                                                                <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][sub_title]" value="{{ $langs[$i]['blockdata'][$block->id]['sub_title'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}副標題">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <div class="form-group col-12">
                                                                <label>列數位置(依據列數設定)</label>
                                                                <div>
                                                                    <div class="icheck-danger d-inline">
                                                                        <input type="radio" id="block_row1_{{ $block->id }}" name="row" value="1" {{ $block->row == 1 ? 'checked' : '' }}>
                                                                        <label for="block_row1_{{ $block->id }}" class="mr-2">第一列</label>
                                                                    </div>
                                                                    @if($curation->rows == 2)
                                                                    <div class="icheck-danger d-inline">
                                                                        <input type="radio" id="block_row2_{{ $block->id }}" name="row" value="2" {{ $block->row == 2 ? 'checked' : '' }}>
                                                                        <label for="block_row2_{{ $block->id }}" class="mr-2">第二列</label>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-12">
                                                                <label>主副標題文字位置</label>
                                                                <div>
                                                                    <div class="icheck-success d-inline">
                                                                        <input type="radio" id="text_position_inside{{ $block->id }}" name="text_position" value="inside" {{ $block->text_position == 'inside' ? 'checked' : ''}}>
                                                                        <label for="text_position_inside{{ $block->id }}" class="mr-2">內部</label>
                                                                    </div>
                                                                    <div class="icheck-success d-inline">
                                                                        <input type="radio" id="text_position_bottom{{ $block->id }}" name="text_position" value="bottom" {{ $block->text_position == 'bottom' ? 'checked' : ''}}>
                                                                        <label for="text_position_bottom{{ $block->id }}" class="mr-2">底部</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                                        <div class="form-group col-12">
                                                            <button type="submit" class="btn btn-sm btn-primary float-right">修改</button>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                                @endif
                                <tr>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-sm bg-success edit-btn" value="block_{{ $r }}_add">新增</button></td>
                                    <td colspan="6"></td>
                                </tr>
                                <tr class="block_{{ $r }}_add" style="display:none">
                                    <td colspan="7">
                                        <form class="curationImageForm" action="{{ route('admin.curationimages.store') }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="curation_id" value="{{ $curation->id }}">
                                            <input type="hidden" name="style" value="block">
                                            <input type="hidden" name="sort" value="9999">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-2">
                                                        <div class="text-center">
                                                            <img width="100%" class="block{{ $r }}_image" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col-10 row">
                                                        <div class="form-group col-5">
                                                            <label>上傳圖檔 自動等比縮小為 750 x 750 (縮小後最大尺寸)</label>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input type="file" id="block{{ $r }}_image" name="image" class="custom-file-input" accept="image/*" required>
                                                                    <label class="custom-file-label" for="block{{ $r }}_image">瀏覽選擇新圖片</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label>連結網址</label>
                                                            <input type="text" class="form-control" name="url" value="" placeholder="請輸入連結">
                                                        </div>
                                                        <div class="form-group col-1">
                                                            <label>連結另開視窗</label>
                                                            <div class="input-group">
                                                                <input type="checkbox" name="url_open_window" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" checked>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-9">
                                                            <div class="row">
                                                                <nav class="w-100">
                                                                    <div class="nav nav-tabs" id="curationblock-tab-{{ $r }}" role="tablist">
                                                                        <a class="nav-item nav-link active" id="curationblock-chinese-tab-{{ $r }}" data-toggle="tab" href="#curationblock-chinese" role="tab" aria-controls="curationblock-chinese-{{ $r }}" aria-selected="true">中文</a>
                                                                        @for($i=0;$i<count($langs);$i++)
                                                                        <a class="nav-item nav-link" id="curationblock-{{ $langs[$i]['code'] }}-tab-{{ $r }}" data-toggle="tab" href="#curationblock-{{ $langs[$i]['code'] }}-{{ $r }}" role="tab" aria-controls="curationblock-{{ $langs[$i]['code'] }}-{{ $r }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                                        @endfor
                                                                    </div>
                                                                </nav>
                                                                <div class="col-12 tab-content p-3" id="nav-tabContent">
                                                                    <div class="tab-pane fade show active" id="curationblock-chinese-{{ $r }}" role="tabpanel" aria-labelledby="curationblock-chinese-tab">
                                                                        <div class="row">
                                                                            <div class="form-group col-5">
                                                                                <label>主標題</label>
                                                                                <input type="text" class="form-control" name="main_title" value="" placeholder="請輸入主標題">
                                                                            </div>
                                                                            <div class="form-group col-1">
                                                                                <label>顯示</label>
                                                                                <div class="input-group">
                                                                                    <input type="checkbox" name="show_main_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary">
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group col-5">
                                                                                <label>副標題</label>
                                                                                <input type="text" class="form-control" name="sub_title" value="" placeholder="請輸入副標題">
                                                                            </div>
                                                                            <div class="form-group col-1">
                                                                                <label>顯示</label>
                                                                                <div class="input-group">
                                                                                    <input type="checkbox" name="show_sub_title" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @for($i=0;$i<count($langs);$i++)
                                                                    <div class="tab-pane fade" id="curationblock-{{ $langs[$i]['code'] }}-{{ $r }}" role="tabpanel" aria-labelledby="curationblock-{{ $langs[$i]['code'] }}-tab-{{ $r }}">
                                                                        <div class="row">
                                                                            <div class="form-group col-6">
                                                                                <label>{{ $langs[$i]['name'] }}主標題</label>
                                                                                <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][main_title]" value="" placeholder="請輸入{{ $langs[$i]['name'] }}主標題">
                                                                            </div>
                                                                            <div class="form-group col-6">
                                                                                <label>{{ $langs[$i]['name'] }}副標題</label>
                                                                                <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][sub_title]" value="" placeholder="請輸入{{ $langs[$i]['name'] }}副標題">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-3">
                                                            <div class="form-group col-12">
                                                                <label>列數位置</label>
                                                                <div>
                                                                    <div class="icheck-danger d-inline">
                                                                        <input type="radio" id="block_row{{ $r }}_1" name="row" value="1" {{ $r == 1 ? 'checked' : '' }}>
                                                                        <label for="block_row{{ $r }}_1" class="mr-2">第一列</label>
                                                                    </div>
                                                                    <div class="icheck-danger d-inline">
                                                                        <input type="radio" id="block_row{{ $r }}_2" name="row" value="2" {{ $r == 2 ? 'checked' : '' }}>
                                                                        <label for="block_row{{ $r }}_2" class="mr-2">第二列</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group col-12">
                                                                <label>主副標題文字位置</label>
                                                                <div>
                                                                    <div class="icheck-success d-inline">
                                                                        <input type="radio" id="block_{{ $r }}_text_position_inside" name="text_position" value="inside">
                                                                        <label for="block_{{ $r }}_text_position_inside" class="mr-2">內部</label>
                                                                    </div>
                                                                    <div class="icheck-success d-inline">
                                                                        <input type="radio" id="block_{{ $r }}_text_position_bottom" name="text_position" value="bottom" checked>
                                                                        <label for="block_{{ $r }}_text_position_bottom" class="mr-2">底部</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <button type="submit" class="btn btn-sm bg-success float-right mr-2">送出</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endfor
            </div>
            <div id="nowordblock" class="type" style="display:none">
                @for($r = 1; $r <= $curation->rows; $r++)
                <div class="card card-info checkrows{{ $r }}" {{ $r == 2 ? 'style="display:none;"' : '' }}>
                    <div class="card-header">
                        <h3 class="card-title">宮格(無字)版型 第{{ $r }}列資料</h3>
                        <div class="card-tools">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="text-danger text-bold">注意!! 此版型不適用於目前舊版前台。</span>
                        </div>
                        <table class="table table-hover table-sm text-sm">
                            <thead>
                                <tr>
                                    <th width="20%" class="text-center align-middle">圖片</th>
                                    <th width="50%" class="text-left align-middle">連結網址</th>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <th width="10%" class="text-center align-middle">排序</th>
                                    @endif
                                    <th width="10%" class="text-center align-middle">修改/刪除</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($curation->noWordBlocks) > 0)
                                @foreach($curation->noWordBlocks as $noWordBlock)
                                @if($noWordBlock->row == $r)
                                <tr>
                                    <td class="text-center align-middle">
                                        @if(str_replace(env('AWS_FILE_URL'),'',$noWordBlock->image))
                                        <a href="{{ $noWordBlock->image }}" data-toggle="lightbox" data-title="{{ '圖片 '.$r.'-'.$loop->iteration }}" data-gallery="gallery" data-max-width="1440">
                                        <img height="50" class="nwblock_image_{{ $noWordBlock->id }}" src="{{ $noWordBlock->image }}" alt="">
                                        </a>
                                        @else
                                        <img height="50" class="nwblock_image_{{ $noWordBlock->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        @if($noWordBlock->url != '#')
                                        <div class="col-12 text-warp">
                                            <a href="{{ $noWordBlock->url }}" target="_blank">{{ $noWordBlock->url }}</a>
                                        </div>
                                        @else
                                        {{ $noWordBlock->url }}
                                        @endif
                                    </td>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <td class="text-center align-middle">
                                        @if($noWordBlock->sort != 1)
                                        <a href="{{ url('curationimages/sortup/' . $noWordBlock->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                        </a>
                                        @endif
                                        @if($noWordBlock->sort != $totalNoWordBlocks[$r])
                                        <a href="{{ url('curationimages/sortdown/' . $noWordBlock->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                        </a>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-primary edit-btn" value="nwblock_{{ $noWordBlock->row }}_{{ $noWordBlock->id }}"><i class="fas fa-edit"></i></button>
                                        <form class="d-inline" action="{{ route('admin.curationimages.destroy', $noWordBlock->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr class="nwblock_{{ $noWordBlock->row }}_{{ $noWordBlock->id }}" style="display:none">
                                    <td colspan="7">
                                        <form class="curationImageForm_image_{{ $noWordBlock->id }}" action="{{ route('admin.curationimages.update', $noWordBlock->id) }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <input type="hidden" name="curation_id" value="{{ $curation->id }}">
                                            <input type="hidden" name="style" value="{{ $noWordBlock->style }}">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-3">
                                                        <div class="text-center">
                                                            @if(str_replace(env('AWS_FILE_URL'),'',$noWordBlock->image))
                                                            <a href="{{ $noWordBlock->image }}" data-toggle="lightbox" data-title="{{ '圖片 '.$r.'-'.$loop->iteration }}" data-gallery="gallery" data-max-width="1440">
                                                            <img width="100%" class="nwimage_{{ $noWordBlock->id }}" src="{{ $noWordBlock->image }}" alt="">
                                                            </a>
                                                            @else
                                                            <img width="100%" class="nwimage_{{ $noWordBlock->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-9 row">
                                                        <div class="form-group col-10">
                                                            <label>連結網址</label>
                                                            <input type="text" class="form-control" name="url" value="{{ $noWordBlock->url ?? '' }}" placeholder="請輸入連結">
                                                        </div>
                                                        <div class="form-group col-2">
                                                            <label>連結另開視窗</label>
                                                            <div class="input-group">
                                                                <input type="checkbox" name="url_open_window" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" {{ $noWordBlock->url_open_window == 1 ? 'checked' : '' }}>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label>上傳圖檔 自動等比縮小為 750 x 750 (縮小後最大尺寸)</label>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input type="file" id="nwimage_{{ $noWordBlock->id }}" name="image" class="custom-file-input" accept="image/*">
                                                                    <label class="custom-file-label" for="nwimage_{{ $noWordBlock->id }}">{{ $noWordBlock->image ?? '瀏覽選擇新圖片' }}</label>
                                                                </div>
                                                                @if($noWordBlock->image)
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-sm bg-danger removeImage" value="image_{{ $noWordBlock->id }}"><i class="far fa-trash-alt"></i></button>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-4">
                                                            <div class="form-group col-12">
                                                                <label>列數位置(依據列數設定)</label>
                                                                <div>
                                                                    <div class="icheck-danger d-inline">
                                                                        <input type="radio" id="nwblock_row1_{{ $noWordBlock->id }}" name="row" value="1" {{ $noWordBlock->row == 1 ? 'checked' : '' }}>
                                                                        <label for="nwblock_row1_{{ $noWordBlock->id }}" class="mr-2">第一列</label>
                                                                    </div>
                                                                    @if($curation->rows == 2)
                                                                    <div class="icheck-danger d-inline">
                                                                        <input type="radio" id="nwblock_row2_{{ $noWordBlock->id }}" name="row" value="2" {{ $noWordBlock->row == 2 ? 'checked' : '' }}>
                                                                        <label for="nwblock_row2_{{ $noWordBlock->id }}" class="mr-2">第二列</label>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                                        <div class="form-group col-2">
                                                            <label>　</label>
                                                            <div>
                                                                <button type="submit" class="btn btn-sm btn-primary float-right">修改</button>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                                @endif
                                <tr>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-sm bg-success edit-btn" value="nwblock_{{ $r }}_add">新增</button></td>
                                    <td colspan="6"></td>
                                </tr>
                                <tr class="nwblock_{{ $r }}_add" style="display:none">
                                    <td colspan="7">
                                        <form class="curationImageForm" action="{{ route('admin.curationimages.store') }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="curation_id" value="{{ $curation->id }}">
                                            <input type="hidden" name="style" value="nowordblock">
                                            <input type="hidden" name="sort" value="9999">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-3">
                                                        <div class="text-center">
                                                            <img width="100%" class="nwblock{{ $r }}_image" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col-9 row">
                                                        <div class="form-group col-10">
                                                            <label>連結網址</label>
                                                            <input type="text" class="form-control" name="url" value="" placeholder="請輸入連結">
                                                        </div>
                                                        <div class="form-group col-2">
                                                            <label>連結另開視窗</label>
                                                            <div class="input-group">
                                                                <input type="checkbox" name="url_open_window" value="1" data-bootstrap-switch data-on-text="開" data-off-text="關" data-off-color="secondary" data-on-color="primary" checked>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-6">
                                                            <label>上傳圖檔 自動等比縮小為 750 x 750 (縮小後最大尺寸)</label>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input type="file" id="nwblock{{ $r }}_image" name="image" class="custom-file-input" accept="image/*" required>
                                                                    <label class="custom-file-label" for="nwblock{{ $r }}_image">瀏覽選擇新圖片</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-4">
                                                            <label>列數位置</label>
                                                            <div>
                                                                <div class="icheck-danger d-inline">
                                                                    <input type="radio" id="nwblock_row{{ $r }}_1" name="row" value="1" {{ $r == 1 ? 'checked' : '' }}>
                                                                    <label for="block_row{{ $r }}_1" class="mr-2">第一列</label>
                                                                </div>
                                                                <div class="icheck-danger d-inline">
                                                                    <input type="radio" id="nwblock_row{{ $r }}_2" name="row" value="2" {{ $r == 2 ? 'checked' : '' }}>
                                                                    <label for="block_row{{ $r }}_2" class="mr-2">第二列</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-2">
                                                            <label>　</label>
                                                            <div>
                                                                <button type="submit" class="btn btn-sm bg-success float-right mr-2">送出</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endfor
            </div>
            <div id="vendor" class="type" style="display:none">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">品牌版型資料</h3>
                        <div class="card-tools">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="text-danger text-bold">注意!! 目前前台不會顯示各語簡介文字，若需要簡介文字請於商家管理做設定。</span>
                        </div>
                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                        <button type="button" class="btn btn-sm bg-success edit-btn mr-2" value="vendor_data_add">{{ $curation->vendors ? '選擇' : '新增' }}</button>
                        <button type="button" class="btn btn-sm bg-success sort-btn" value="vendor"><span id="vendor_sort_text">手動排序</span></button>
                        @endif
                        <div class="vendor_data_add" style="display:none">
                            <div class="card-primary card-outline mt-2"></div>
                            <form class="curationVendorForm" action="{{ route('admin.curationvendors.store') }}" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-12">
                                            <div class="row">
                                                <div class="col-5">
                                                    <label>未選擇商家</label>
                                                    <select id="vendorSelect" class="form-control " size="10" multiple="multiple" data-dropdown-css-class="select2-primary">
                                                        @foreach($unSelectVendors as $unSelectVendor)
                                                        <option value="{{ $unSelectVendor->id }}">{{ $unSelectVendor->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-1">
                                                    <div>
                                                        <label>　</label><br><br><br>
                                                        <button type="button" id="vendorSelect_rightAll" class="btn btn-secondary btn-block"><i class="fas fa-angle-double-right"></i></button>
                                                        <button type="button" id="vendorSelect_rightSelected" class="btn btn-primary btn-block"><i class="fas fa-caret-right"></i></button>
                                                        <button type="button" id="vendorSelect_leftSelected" class="btn btn-primary btn-block"><i class="fas fa-caret-left"></i></button>
                                                        <button type="button" id="vendorSelect_leftAll" class="btn btn-secondary btn-block"><i class="fas fa-angle-double-left"></i></button>
                                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                                        <button type="submit" class="btn btn-success btn-block">儲存</button>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-5">
                                                    <label>已選擇商家</label>
                                                    <select name="vendor_id[]" id="vendorSelect_to" class="form-control" size="12" multiple="multiple" placeholder="已選擇商家...">
                                                        @if($curation->vendors)
                                                        @foreach($curation->vendors as $vendor)
                                                        <option value="{{ $vendor->vendor_id }}">{{ $vendor->name }}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-1 align-items-middle">
                                                    <label>　</label><br><br><br><br>
                                                    <div class="col-12">
                                                        <button type="button" id="vendorSelect_move_up" class="btn btn-primary"><i class="fas fa-caret-up"></i></button>
                                                    </div>
                                                    <br><br><br><br>
                                                    <div class="col-12">
                                                        <button type="button" id="vendorSelect_move_down" class="btn btn-primary"><i class="fas fa-caret-down"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 text-danger">只有已啟用的商家才會顯示在選擇框中。</div>
                                    </div>
                                </div>
                            </form>
                            <div class="card-primary card-outline mb-2"></div>
                        </div>
                        <table class="table table-hover table-sm text-sm">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center align-middle">順序</th>
                                    <th width="15%" class="text-left align-middle">商家名稱</th>
                                    <th width="15%" class="text-left align-middle">Logo</th>
                                    <th width="15%" class="text-left align-middle">背景圖</th>
                                    <th width="25%" class="text-left align-middle">簡介文字</th>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <th width="10%" class="text-center align-middle">排序</th>
                                    @endif
                                    <th width="10%" class="text-center align-middle">修改/刪除</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($curation->vendors) > 0)
                                @foreach($curation->vendors as $vendor)
                                <tr>
                                    <td class="text-center align-middle">
                                        <input type="number" class="form-control text-center vendor-sort" name="{{ $vendor->id }}" value="{{ $vendor->sort }}" readonly>
                                    </td>
                                    <td class="text-left align-middle">
                                        {{ $vendor->name }}
                                    </td>
                                    <td class="text-left align-middle">
                                        @if(str_replace(env('AWS_FILE_URL'),'',$vendor->img_logo))
                                        <a href="{{ $vendor->img_logo }}" data-toggle="lightbox" data-title="{{ $vendor->name .' Logo 圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                        <img height="50" class="vendor_image_{{ $vendor->id }}" src="{{ $vendor->img_logo }}" alt="">
                                        </a>
                                        @else
                                        <img height="50" class="vendor_image_{{ $vendor->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        @if(str_replace(env('AWS_FILE_URL'),'',$vendor->img_cover))
                                        <a href="{{ $vendor->img_cover }}" data-toggle="lightbox" data-title="{{ $vendor->name .' 背景圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                        <img height="50" class="vendor_image_{{ $vendor->id }}" src="{{ $vendor->img_cover }}" alt="">
                                        </a>
                                        @else
                                        <img height="50" class="vendor_image_{{ $vendor->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        {{ $vendor->curation }}
                                    </td>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <td class="text-center align-middle">
                                        @if($loop->iteration != 1)
                                        <a href="{{ url('curationvendors/sortup/' . $vendor->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                        </a>
                                        @endif
                                        @if($loop->iteration != count($curation->vendors))
                                        <a href="{{ url('curationvendors/sortdown/' . $vendor->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                        </a>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-primary edit-btn" value="vendor_{{ $vendor->id }}"><i class="fas fa-edit"></i></button>
                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                        <form class="d-inline" action="{{ route('admin.curationvendors.destroy', $vendor->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="vendor_{{ $vendor->id }}" style="display:none">
                                    <td colspan="7">
                                        <form class="curationVendorForm_vendor_{{ $vendor->id }}" action="{{ route('admin.curationvendors.update', $vendor->id) }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <div class="text-center">
                                                            @if(str_replace(env('AWS_FILE_URL'),'',$vendor->img_logo))
                                                            <a href="{{ $vendor->img_logo }}" data-toggle="lightbox" data-title="{{ $vendor->name .' LOGO圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                                            <img height="200" class="vendor_image_logo_{{ $vendor->id }}" src="{{ $vendor->img_logo }}" alt="">
                                                            </a>
                                                            @else
                                                            <img height="200" class="vendor_image_logo_{{ $vendor->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label>上傳 Logo 圖檔 自動等比縮小為 540 x 360</label>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input type="file" id="vendor_image_logo_{{ $vendor->id }}" name="img_logo" class="custom-file-input" accept="image/*">
                                                                    <label class="custom-file-label" for="vendor_image_logo_{{ $vendor->id }}">{{ $vendor->img_logo ?? '瀏覽選擇新圖片' }}</label>
                                                                </div>
                                                                @if(str_replace(env('AWS_FILE_URL'),'',$vendor->img_logo))
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-sm bg-danger removeImage" value="imglogo_{{ $vendor->id }}"><i class="far fa-trash-alt"></i></button>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="text-center">
                                                            @if(str_replace(env('AWS_FILE_URL'),'',$vendor->img_cover))
                                                            <a href="{{ $vendor->img_cover }}" data-toggle="lightbox" data-title="{{ $vendor->name .' 背景圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                                            <img height="200" class="vendor_image_cover_{{ $vendor->id }}" src="{{ $vendor->img_cover }}" alt="">
                                                            </a>
                                                            @else
                                                            <img height="200" class="vendor_image_cover_{{ $vendor->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            @endif
                                                        </div>
                                                        <div class="form-group col-12">
                                                            <label>上傳 背景 圖檔 自動等比縮小為 1440 x 760</label>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input type="file" id="vendor_image_cover_{{ $vendor->id }}" name="img_cover" class="custom-file-input" accept="image/*">
                                                                    <label class="custom-file-label" for="vendor_image_cover_{{ $vendor->id }}">{{ $vendor->img_cover ?? '瀏覽選擇新圖片' }}</label>
                                                                </div>
                                                                @if(str_replace(env('AWS_FILE_URL'),'',$vendor->img_cover))
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-sm bg-danger removeImage" value="imgcover_{{ $vendor->id }}"><i class="far fa-trash-alt"></i></button>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-4">
                                                        <div class="row">
                                                            <nav class="w-100">
                                                                <div class="nav nav-tabs" id="curationvendor-tab" role="tablist">
                                                                    <a class="nav-item nav-link active" id="curationvendor-chinese-{{ $vendor->id }}-tab" data-toggle="tab" href="#curationvendor-chinese-{{ $vendor->id }}" role="tab" aria-controls="curationvendor-chinese-{{ $vendor->id }}" aria-selected="true">中文</a>
                                                                    @for($i=0;$i<count($langs);$i++)
                                                                    <a class="nav-item nav-link" id="curationvendor-{{ $langs[$i]['code'] }}-{{ $vendor->id }}-tab" data-toggle="tab" href="#curationvendor-{{ $langs[$i]['code'] }}-{{ $vendor->id }}" role="tab" aria-controls="curationvendor-{{ $langs[$i]['code'] }}-{{ $vendor->id }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                                    @endfor
                                                                </div>
                                                            </nav>
                                                            <div class="col-12 tab-content" id="nav-tabContent">
                                                                <div class="tab-pane fade show active" id="curationvendor-chinese-{{ $vendor->id }}" role="tabpanel" aria-labelledby="curationvendor-chinese-{{ $vendor->id }}-tab">
                                                                    <div class="row mt-3">
                                                                        <div class="form-group col-12">
                                                                            <label>簡介文字</label>
                                                                            <input type="text" class="form-control" name="curation" value="{{ $vendor->curation ?? '' }}" placeholder="請輸入簡介文字">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @for($i=0;$i<count($langs);$i++)
                                                                <div class="tab-pane fade" id="curationvendor-{{ $langs[$i]['code'] }}-{{ $vendor->id }}" role="tabpanel" aria-labelledby="curationvendor-{{ $langs[$i]['code'] }}-{{ $vendor->id }}-tab">
                                                                    <div class="row mt-3">
                                                                        <div class="form-group col-12">
                                                                            <label>{{ $langs[$i]['name'] }}簡介文字</label>
                                                                            <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][curation]" value="{{ $langs[$i]['vendordata'][$vendor->id]['curation'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}簡介文字">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                                    <div class="form-group col-12">
                                                        <button type="submit" class="btn btn-sm btn-primary float-right">修改</button>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-left align-middle">
                                        <h3>尚未選擇資料</h3>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="product" class="type" style="display:none">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">產品版型資料</h3>
                        <div class="card-tools">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="text-danger text-bold">注意!! 目前前台僅適用順序功能，其餘商品圖片、商品名稱及語言上下標文字皆不會顯示。</span>
                        </div>
                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                        <button type="button" class="btn btn-sm bg-success edit-btn mr-2" value="product_data_add">{{ $curation->vendors ? '選擇' : '新增' }}</button>
                        <button type="button" class="btn btn-sm bg-success sort-btn" value="product"><span id="product_sort_text">手動排序</span></button>
                        @endif
                        <div class="product_data_add" style="display:none">
                            <div class="card-primary card-outline mt-2"></div>
                            <form class="curationProductForm" action="{{ route('admin.curationproducts.store') }}" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-12">
                                            <div class="row">
                                            <div class="form-group col-2">
                                                <label for="">使用產品類別搜尋產品</label>
                                                <select id="selectByCategory" class="form-control">
                                                    <option value="">產品類別</option>
                                                    @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-3">
                                                <label for="">使用商家名稱搜尋產品</label>
                                                {{-- <select id="selectByVendor" class="form-control select2bs4 select2-primary" data-dropdown-css-class="select2-primary" > --}}
                                                <select id="selectByVendor" class="form-control">
                                                    <option value="">商家名稱</option>
                                                    @foreach($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-3">
                                                <label for="">使用關鍵字搜尋產品</label>
                                                <div class="input-group">
                                                    <input class="form-control form-control-navbar" type="search" id="keyword" name="keyword" value="" placeholder="輸入商家或產品名稱搜尋..." aria-label="Search">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-info search-btn" type="button">
                                                            <i class="fas fa-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="form-group col-12">
                                            <div class="row">
                                                <div class="col-5">
                                                    <label>產品列表</label>
                                                    <select id="productSelect" class="form-control" size="12" multiple="multiple">
                                                    </select>
                                                </div>
                                                <div class="col-1">
                                                    <div>
                                                        <label>　</label><br><br><br>
                                                        <button type="button" id="productSelect_rightAll" class="btn btn-secondary btn-block"><i class="fas fa-angle-double-right"></i></button>
                                                        <button type="button" id="productSelect_rightSelected" class="btn btn-primary btn-block"><i class="fas fa-caret-right"></i></button>
                                                        <button type="button" id="productSelect_leftSelected" class="btn btn-primary btn-block"><i class="fas fa-caret-left"></i></button>
                                                        <button type="button" id="productSelect_leftAll" class="btn btn-secondary btn-block"><i class="fas fa-angle-double-left"></i></button>
                                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                                        <button type="submit" class="btn btn-success btn-block">儲存</button>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-5">
                                                    <label>已選擇產品</label>
                                                    <select name="product_id[]" id="productSelect_to" class="form-control" size="12" multiple="multiple">
                                                        @if($curation->products)
                                                        @foreach($curation->products as $product)
                                                        <option value="{{ $product->product_id }}">{{ $product->name }}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="col-1 align-items-middle">
                                                    <label>　</label><br><br><br><br>
                                                    <div class="col-12">
                                                        <button type="button" id="productSelect_move_up" class="btn btn-primary"><i class="fas fa-caret-up"></i></button>
                                                    </div>
                                                    <br><br><br><br>
                                                    <div class="col-12">
                                                        <button type="button" id="productSelect_move_down" class="btn btn-primary"><i class="fas fa-caret-down"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 text-danger">只有已上架及下架中的產品才會顯示在前台與選擇框中。按下儲存後，未上架的產品資料將被清除。</div>
                                    </div>
                                </div>
                            </form>
                            <div class="card-primary card-outline mb-2"></div>
                        </div>
                        <table class="table table-hover table-sm text-sm">
                            <thead>
                                <tr>
                                    <th width="5%" class="text-center align-middle">順序</th>
                                    <th width="15%" class="text-center align-middle">商品圖</th>
                                    <th width="30%" class="text-left align-middle">商品名稱</th>
                                    <th width="15%" class="text-left align-middle">上標文字</th>
                                    <th width="15%" class="text-left align-middle">下標文字</th>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <th width="10%" class="text-center align-middle">排序</th>
                                    @endif
                                    <th width="10%" class="text-center align-middle">修改/刪除</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($curation->products) > 0)
                                @foreach($curation->products as $product)
                                <tr>
                                    <td class="text-center align-middle">
                                        <input type="number" class="form-control text-center product-sort" name="{{ $product->id }}" value="{{ $product->sort }}" readonly>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($product->image)
                                        <a href="{{ env('AWS_FILE_URL').$product->image }}" data-toggle="lightbox" data-title="{{ $product->name .' 商品圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                        <img height="50" class="product_image_{{ $product->id }}" src="{{ env('AWS_FILE_URL').$product->image }}" alt="">
                                        </a>
                                        @else
                                        <img height="50" class="product_image_{{ $product->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                        @endif
                                    </td>
                                    <td class="text-left align-middle">
                                        {{ $product->name }}
                                    </td>
                                    <td class="text-left align-middle">
                                        {{ $product->curation_text_top }}
                                    </td>
                                    <td class="text-left align-middle">
                                        {{ $product->curation_text_bottom }}
                                    </td>
                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                    <td class="text-center align-middle">
                                        @if($loop->iteration != 1)
                                        <a href="{{ url('curationproducts/sortup/' . $product->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-up text-lg"></i>
                                        </a>
                                        @endif
                                        @if($loop->iteration != count($curation->products))
                                        <a href="{{ url('curationproducts/sortdown/' . $product->id) }}" class="text-navy">
                                            <i class="fas fa-arrow-alt-circle-down text-lg"></i>
                                        </a>
                                        @endif
                                    </td>
                                    @endif
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-primary edit-btn" value="product_{{ $product->id }}"><i class="fas fa-edit"></i></button>
                                        @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                        <form class="d-inline" action="{{ route('admin.curationproducts.destroy', $product->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="product_{{ $product->id }}" style="display:none">
                                    <td colspan="7">
                                        <form class="curationProductForm_product_{{ $product->id }}" action="{{ route('admin.curationproducts.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-3">
                                                        <div class="text-center">
                                                            @if($product->image)
                                                            <a href="{{ $product->image }}" data-toggle="lightbox" data-title="{{ $product->name .' 商品圖片 ' }}" data-gallery="gallery" data-max-width="1440">
                                                                <img height="200" class="product_image_{{ $product->id }}" src="{{ $product->image }}" alt="">
                                                            </a>
                                                            <label>顯示商品第一張照片，不提供修改，修改請至<a href="{{ url('products/'.$product->product_id.'#product-image' ) }}">本商品</a>頁面</label>
                                                            @else
                                                            <a href="{{ url('products/'.$product->product_id.'#product-image' ) }}">
                                                                <img height="200" class="product_image_{{ $product->id }}" src="{{ asset('img/sample_upload.png') }}" alt="">
                                                            </a>
                                                            <label>此商品尚未上傳任何圖片，請至<a href="{{ url('products/'.$product->product_id.'#product-image' ) }}">本商品</a>頁面上傳</label>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-8">
                                                        <div class="row">
                                                            <nav class="w-100">
                                                                <div class="nav nav-tabs" id="curationproduct-tab" role="tablist">
                                                                    <a class="nav-item nav-link active" id="curationproduct-chinese-{{ $product->id }}-tab" data-toggle="tab" href="#curationproduct-chinese-{{ $product->id }}" role="tab" aria-controls="curationproduct-chinese-{{ $product->id }}" aria-selected="true">中文</a>
                                                                    @for($i=0;$i<count($langs);$i++)
                                                                    <a class="nav-item nav-link" id="curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}-tab" data-toggle="tab" href="#curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}" role="tab" aria-controls="curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}" aria-selected="false">{{ $langs[$i]['name'] }}</a>
                                                                    @endfor
                                                                </div>
                                                            </nav>
                                                            <div class="col-12 tab-content" id="nav-tabContent">
                                                                <div class="tab-pane fade show active" id="curationproduct-chinese-{{ $product->id }}" role="tabpanel" aria-labelledby="curationproduct-chinese-{{ $product->id }}-tab">
                                                                    <div class="row mt-3">
                                                                        <div class="form-group col-6">
                                                                            <label>上標文字</label>
                                                                            <input type="text" class="form-control" name="curation_text_top" value="{{ $product->curation_text_top ?? '' }}" placeholder="請輸入上標文字">
                                                                        </div>
                                                                        <div class="form-group col-6">
                                                                            <label>下標文字</label>
                                                                            <input type="text" class="form-control" name="curation_text_bottom" value="{{ $product->curation_text_bottom ?? '' }}" placeholder="請輸入下標文字">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @for($i=0;$i<count($langs);$i++)
                                                                <div class="tab-pane fade" id="curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}" role="tabpanel" aria-labelledby="curationproduct-{{ $langs[$i]['code'] }}-{{ $product->id }}-tab">
                                                                    <div class="row mt-3">
                                                                        <div class="form-group col-6">
                                                                            <label>{{ $langs[$i]['name'] }}上標文字</label>
                                                                            <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][curation_text_top]" value="{{ $langs[$i]['productdata'][$product->id]['curation_text_top'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}上標文字">
                                                                        </div>
                                                                        <div class="form-group col-6">
                                                                            <label>{{ $langs[$i]['name'] }}下標文字</label>
                                                                            <input type="text" class="form-control" name="langs[{{ $langs[$i]['code'] }}][curation_text_bottom]" value="{{ $langs[$i]['productdata'][$product->id]['curation_text_bottom'] ?? '' }}" placeholder="請輸入{{ $langs[$i]['name'] }}下標文字">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(in_array($menuCode.'M',explode(',',Auth::user()->power)))
                                                    <div class="form-group col-1">
                                                        <button type="submit" class="btn btn-sm btn-primary float-right">修改</button>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="7" class="text-left align-middle">
                                        <h3>尚未選擇資料</h3>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
    <form id="myvendorform" action="{{ route('admin.curationvendors.sort') }}" method="POST" class="form-inline" role="search">
        <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
        @csrf
    </form>
    <form id="myproductform" action="{{ route('admin.curationproducts.sort') }}" method="POST" class="form-inline" role="search">
        <input type="hidden" name="curation_id" value="{{ isset($curation) ? $curation->id : '' }}">
        @csrf
    </form>
</div>
@endsection

@section('css')
{{-- iCheck for checkboxes and radio inputs --}}
<link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
{{-- 時分秒日曆 --}}
<link rel="stylesheet" href="{{ asset('vendor/jquery-ui/themes/base/jquery-ui.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.css') }}">
{{-- Ekko Lightbox --}}
<link rel="stylesheet" href="{{ asset('vendor/ekko-lightbox/dist/ekko-lightbox.css') }}">
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('vendor/select2/dist/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css') }}">
@endsection

@section('script')
{{-- Bootstrap Switch --}}
<script src="{{ asset('vendor/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
{{-- Jquery Validation Plugin --}}
<script src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>
{{-- 時分秒日曆 --}}
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/jquery-ui-timepicker-addon.min.js') }}"></script>
<script src="{{ asset('vendor/jqueryui-timepicker-addon/dist/i18n/jquery-ui-timepicker-zh-TW.js') }}"></script>
{{-- 顏色選擇器 --}}
<script src="{{ asset('vendor/vanilla-picker/dist/vanilla-picker.min.js') }}"></script>
{{-- Ekko Lightbox --}}
<script src="{{ asset('vendor/ekko-lightbox/dist/ekko-lightbox.min.js') }}"></script>
{{-- multiselect --}}
<script src="{{ asset('vendor/multiselect/dist/js/multiselect.min.js') }}"></script>
{{-- Select2 --}}
<script src="{{ asset('vendor/select2/dist/js/select2.full.min.js') }}"></script>
{{-- Ckeditor 4.x --}}
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
@endsection

@section('JsValidator')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CurationsRequest', '#myform'); !!}
{!! JsValidator::formRequest('App\Http\Requests\Admin\CurationsLangRequest', '.myform_lang'); !!}
@endsection

@section('CustomScript')
<script>
    (function($) {
        "use strict";

        // CKEDITOR.replaceAll('modal_content');
        CKEDITOR.replaceAll( function( textarea, config ) {
            config.extraPlugins = 'wordcount,notification';
            config.wordcount = {
                // Whether or not you Show Remaining Count (if Maximum Word/Char/Paragraphs Count is set)
                showRemaining: false,

                // Whether or not you want to show the Paragraphs Count
                showParagraphs: false,

                // Whether or not you want to show the Word Count
                showWordCount: false,

                // Whether or not you want to show the Char Count
                showCharCount: true,

                // Whether or not you want to Count Bytes as Characters (needed for Multibyte languages such as Korean and Chinese)
                countBytesAsChars: true,

                // Whether or not you want to count Spaces as Chars
                countSpacesAsChars: true,

                // Whether or not to include Html chars in the Char Count
                countHTML: true,

                // Whether or not to prevent entering new Content when limit is reached.
                hardLimit: true,

                // Whether or not to to Warn only When limit is reached. Otherwise content above the limit will be deleted on paste or entering
                warnOnLimitOnly: false,

                // Maximum allowed Word Count, -1 is default for unlimited
                maxWordCount: -1,

                // Maximum allowed Char Count, -1 is default for unlimited
                maxCharCount: 255,
                };
            if (textarea.className === "modal_content") {
                config.height = '30em';
                // config.customConfig = "/js/ckeditor-config.js";
                return true;
            }else if (textarea.className === "caption_content") {
                config.height = '17em';
                return true;
            }
            return false; //非上面判斷則關閉
        } );

        var parent = document.querySelector('#main_title_background');
        var picker = new Picker({
            popup: 'left',
            parent: parent,
            color: '#FFFFFFFF',
            onDone: function(color) {
                parent.style.background = color.rgbaString;
                $("input[name=main_title_background]").val(color.rgbaString);
            },
        });

        var parent2 = document.querySelector('#background_color');
        var picker2 = new Picker({
            popup: 'top',
            parent: parent2,
            color: '#FFFFFFFF',
            onDone: function(color) {
                parent2.style.background = color.rgbaString;
                $("input[name=background_color]").val(color.rgbaString);
            },
        });

        let checktype = $('input[name=type]:checked').val();
        let checkrows = $('input[name=rows]:checked').val();
        $('#'+checktype).show();
        if(checktype == 'header'){
            $('#layoutcolumns').hide();
            $('#oldtextlayout').hide();
        }else{
            $('#layoutcolumns').show();
        }
        if(checktype == 'block' || checktype == 'nowordblock'){
            $('#oldtextlayout').hide();
            $('#layoutrows').show();
            if(checkrows==2){
                $('.checkrows2').show();
            }else{
                $('.checkrows2').hide();
            }
        }else{
            if(checktype == 'image'){
                $('#oldtextlayout').show();
            }else{
                $('#oldtextlayout').hide();
            }
            $('#layoutrows').hide();
        }

        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $('.datetimepicker').datetimepicker({
            timeFormat: "HH:mm:ss",
            dateFormat: "yy-mm-dd",
        });
        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
        $('.delete-btn').click(function (e) {
            if(confirm('請確認是否要刪除這筆資料?')){
                $(this).parents('form').submit();
            };
        });
        $('.edit-btn').click(function(e){
            $('.'+$(this).val()).toggle();
            if($(this).html() == '新增'){
                $(this).html('取消');
            }else if($(this).html() == '選擇'){
                $(this).html('取消');
            }else if($(this).html() == '取消'){
                $(this).html('新增');
            }
        });
        $('input[name=rows]').change(function(){
            if($(this).val() == 2){
                $('.checkrows2').show();
            }else{
                $('.checkrows2').hide();
            }
        });
        $('input[name=type]').change(function(){
            $('.type').hide();
            $('#'+$(this).val()).show();
            if($(this).val() == 'header'){
                $('#layoutcolumns').hide();
            }else{
                $('#layoutcolumns').show();
            }
            if($(this).val() == 'image'){
                $('#oldtextlayout').show();
            }else{
                $('#oldtextlayout').hide();
            }
            if($(this).val() != 'block' && $(this).val() != 'nowordblock'){
                $('#layoutrows').hide();
                let html = '';
                let checked = '';
                let columns = '{{ isset($curation) ? $curation->columns : 4}}';
                for(let i=2;i<=6;i++){
                    columns == i ? checked = 'checked' : checked = '';
                    html += '<div class="icheck-success d-inline"><input type="radio" id="columns'+i+'" name="columns" value="'+i+'" '+checked+'><label for="columns'+i+'" class="mr-2">'+i+'欄</label></div>';
                }
                $('#columns').html(html);
            }else{
                $('#layoutrows').show();
                let html = '';
                let checked = '';
                let columns = '{{ isset($curation) ? $curation->columns : 4}}';
                for(let i=4; i<=8; i=i+4){
                    columns == i ? checked = 'checked' : checked = '';
                    html += '<div class="icheck-success d-inline"><input type="radio" id="columns'+i+'" name="columns" value="'+i+'" '+checked+'><label for="columns'+i+'" class="mr-2">'+i+'欄</label></div>';
                }
                $('#columns').html(html);
            }
        });

        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox({
                alwaysShowClose: true
            });
        });

        $(document).ready(function($) {
            $('#vendorSelect').multiselect({
                sort: false,
                search: {
                    left: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，不需要按Enter即可查詢" />',
                    right: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，不需要按Enter即可查詢" />',
                },
                fireSearch: function(value) {
                    return value.length > 0;
                }
            });
            $('#productSelect').multiselect({
                sort: false,
                search: {
                    left: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，查詢下方產品，不需要按Enter即可查詢" />',
                    right: '<input type="text" name="q" class="form-control" placeholder="輸入關鍵字，查詢下方產品，不需要按Enter即可查詢" />',
                },
                fireSearch: function(value) {
                    return value.length > 0;
                }
            });
        });

        $('.removeBackgroundImage').click(function(){
            if(confirm('確定要移除圖片？\n請注意，該圖片尚未真正被移除，須按修改按鈕後才會真正被移除。')){
                let form = $('#myform');
                form.append($('<input type="hidden" name="background_image" value="">'));
                $('#background_image_div').remove();
                $(this).parent().remove();
                $('label[for=background_image]').html('瀏覽選擇新圖片');
            }
        });

        $('.removeImage').click(function(){
            if(confirm('確定要移除圖片？\n請注意，該圖片尚未真正被移除，須按修改按鈕後才會真正被移除。')){
                var name = $(this).val().split('_')[0];
                var id = $(this).val().split('_')[1];
                if($(this).val() == 'background_image'){
                    var form = $('#myform');
                    form.append($('<input type="hidden" name="background_image" value="">'));
                    $('#background_image_div').remove();
                    $(this).parent().remove();
                    $('label[for=background_image]').html('瀏覽選擇新圖片');
                }else if(name == 'image'){
                    var form = $('.curationImageForm_image_'+id);
                    form.append($('<input type="hidden" name="image" value="">'));
                    $('.image_' + id).attr('src', '{{ asset("img/sample_upload.png") }}');
                    $(this).parent().remove();
                    $('label[for=image_'+id+']').html('瀏覽選擇新圖片');
                }else if(name == 'imglogo' || name == 'imgcover'){
                    var form = $('.curationVendorForm_vendor_'+id);
                    if(name == 'imglogo'){
                        form.append($('<input type="hidden" name="img_logo" value="">'));
                        $('.vendor_image_logo_'+id).attr('src', '{{ asset("img/sample_upload.png") }}');
                        $('label[for=vendor_image_logo_'+id+']').html('瀏覽選擇新圖片');
                    }else if(name == 'imgcover'){
                        form.append($('<input type="hidden" name="img_cover" value="">'));
                        $('.vendor_image_cover_'+id).attr('src', '{{ asset("img/sample_upload.png") }}');
                        $('label[for=vendor_image_cover_'+id+']').html('瀏覽選擇新圖片');
                    }
                }
            }
        });

        $('.sort-btn').click(function(){
            if($(this).val() == 'vendor'){
                $('.vendor-sort').prop('readonly',false);
                $(this).val('vendor-submit');
                $('#vendor_sort_text').html('儲存排序');
            }else if($(this).val() == 'product'){
                $('.product-sort').prop('readonly',false);
                $(this).val('product-submit');
                $('#product_sort_text').html('儲存排序');
            }else if($(this).val() == 'vendor-submit' || $(this).val() == 'product-submit'){
                if($(this).val() == 'vendor-submit'){
                    var form = $('#myvendorform');
                    var ids = $('.vendor-sort').serializeArray().map( item => item.name );
                    var sorts = $('.vendor-sort').serializeArray().map( item => item.value );
                }else if($(this).val() == 'product-submit'){
                    var form = $('#myproductform');
                    var ids = $('.product-sort').serializeArray().map( item => item.name );
                    var sorts = $('.product-sort').serializeArray().map( item => item.value );
                }
                for(let j=0; j<ids.length;j++){
                    var tmp = '';
                    var tmp2 = '';
                    tmp = $('<input type="hidden" class="formappend" name="id['+j+']" value="'+ids[j]+'">');
                    tmp2 = $('<input type="hidden" class="formappend" name="sort['+j+']" value="'+sorts[j]+'">');
                    form.append(tmp);
                    form.append(tmp2);
                }
                form.submit();
            }
        });

        $('#selectByCategory').change(function(){
            $('input[name=keyword]').val('');
            $('#selectByVendor').find('option:not(:first)').prop('selected',false);
            if($(this).val()){
                search($(this).val(),'');
            }
        });

        $('#selectByVendor').change(function(){
            $('#selectByCategory').find('option:not(:first)').prop('selected',false);
            $('input[name=keyword]').val('');
            if($(this).val()){
                search('','',$(this).val());
            }
        });

        $('.search-btn').click(function(){
            $('#selectByVendor').find('option:not(:first)').prop('selected',false);
            $('#selectByCategory').find('option:not(:first)').prop('selected',false);
            var keyword = $('#keyword').val();
            if(keyword){
                search('',keyword,'');
            }
        });
        $('input[name=keyword]').keyup(function(){
            // $('#selectByVendor').val(null).trigger('selected');
            $('#selectByVendor').find('option:not(:first)').prop('selected',false);
            $('#selectByCategory').find('option:not(:first)').prop('selected',false);
            if($(this).val()){
                search('',$(this).val(),'');
            }
        });

    })(jQuery);

    $('input[type=file]').change(function(x) {
        // name = this.name;
        name = this.id;
        oldimg = '{{ asset("img/sample_upload.png") }}';
        file = x.currentTarget.files;
        if (file.length >= 1) {
            // filename = checkMyImage(file);
            filename = file[0].name; //不檢查檔案直接找出檔名
            if (filename) {
                readURL(this, '.' + name);
                $('label[for=' + name + ']').html(filename);
                alert('請注意，此圖片尚未實際上傳到伺服器，待按下新增或修改按鈕才會實際上傳到伺服器。');
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

    function search(category,keyword,vendor){
        let token = '{{ csrf_token() }}';
        // let id = '{{ isset($curation) ? $curation->id : '' }}';
        let selected = $('#productSelect_to').find('option');
        let ids = [];
        for(let x=0;x<selected.length;x++){
            ids[x] = selected[x].value;
        }
        $.ajax({
            type: "post",
            url: 'getproducts',
            data: {ids: ids, category: category, keyword: keyword, vendor: vendor, _token: token },
            success: function(data) {
                var options = '';
                for(let i=0;i<data.length;i++){
                    if(data[i]['status'] == -9){
                        options +='<option value="'+data[i]['id']+'" class="text-danger text-bold bg-warning"> (已下架)'+data[i]['name']+'</option>';
                    }else{
                        options +='<option value="'+data[i]['id']+'">'+data[i]['name']+'</option>';
                    }
                }
                $('#productSelect').html(options);
            }
        });
    }

</script>
@endsection
