<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryShortUrl as ShortUrlDB;
use App\Http\Requests\Admin\ShortUrlRequest;

class ShortUrlController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menuCode = 'M7S6';
        $appends = $compact = [];
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }
        //找出資料
        $shortUrls = new ShortUrlDB;
        if(isset($keyword)){
            $shortUrls = $shortUrls->where(function($query)use($keyword){
                $query = $query->where('code','like',"%$keyword%")->orWhere('url','like',"%$keyword%")->orWhere('memo','like',"%$keyword%");
            });
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }
        $shortUrls = $shortUrls->orderBy('create_time','desc')->paginate($list);
        $compact = array_merge($compact, ['menuCode','shortUrls','appends']);
        return view('admin.curations.shorturl_index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M7S6';
        $code = null;
        $word = 'abcdefghijklmnopqrstuvwxyz0123456789';//字典檔 你可以將 數字 0 1 及字母 O L 排除
        $len = strlen($word);//取得字典檔長度
        for($i = 0; $i < 4; $i++){ //總共取 幾次
            if(rand(1,2)%2==1){
                $code .= $word[rand() % $len];//隨機取得一個字元
            }else{
                $code .= strtoupper($word[rand() % $len]);//隨機取得一個字元
            }
        }
        return view('admin.curations.shorturl_show', compact(['menuCode','code']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShortUrlRequest $request)
    {
        $data = $request->all();
        $shortUrl = ShortUrlDB::create($data);
        return redirect()->route('admin.shortUrl.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M7S6';
        $shortUrl = ShortUrlDB::findOrFail($id);
        return view('admin.curations.shorturl_show', compact(['menuCode','shortUrl']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ShortUrlRequest $request, $id)
    {
        $data = $request->all();
        $shortUrl = ShortUrlDB::findOrFail($id);
        $shortUrl->update($data);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
