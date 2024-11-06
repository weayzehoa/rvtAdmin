<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarrySearchTitle as SearchTitleDB;
use App\Http\Requests\Admin\SearchTitleRequest;

class SearchTitleController extends Controller
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
        $menuCode = 'M7S7';
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
        $searchTitles = SearchTitleDB::orderBy('sort_id','asc');
        if (isset($keyword) && $keyword) {
            $searchTitles = $searchTitles->where('title', 'like', "%$keyword%");
        }
        if (!isset($list)) {
            $list = 15;
            $compact = array_merge($compact, ['list']);
        }
        $searchTitles = $searchTitles->paginate($list);
        $compact = array_merge($compact, ['menuCode','searchTitles','appends']);
        return view('admin.searchtitles.index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M7S7';
        return view('admin.searchtitles.show',compact(['menuCode']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SearchTitleRequest $request)
    {
        $data = $request->all();
        $searchTitle = SearchTitleDB::create($data);
        return redirect()->route('admin.searchtitles.show', $searchTitle->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M7S7';
        $searchTitle = SearchTitleDB::findOrFail($id);
        return view('admin.searchtitles.show', compact(['searchTitle','menuCode']));
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
    public function update(Request $request, $id)
    {
        $data = $request->all();
        isset($data['is_on']) ? '' : $data['is_on'] = 0;
        $searchTitle = SearchTitleDB::findOrFail($id);
        $searchTitle->update($data);
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
    /*
        啟用或禁用
    */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        SearchTitleDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }
        /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $search = SearchTitleDB::findOrFail($id);
        $up = ($search->sort_id) - 1.5;
        $search->fill(['sort_id' => $up]);
        $search->save();

        $searchs = SearchTitleDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($searchs as $search) {
            $id = $search->id;
            SearchTitleDB::where('id', $id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->back();
    }
    /*
        向下排序
    */
    public function sortdown(Request $request)
    {
        $id = $request->id;
        $search = SearchTitleDB::findOrFail($id);
        $up = ($search->sort_id) + 1.5;
        $search->fill(['sort_id' => $up]);
        $search->save();

        $searchs = SearchTitleDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($searchs as $search) {
            $id = $search->id;
            SearchTitleDB::where('id', $id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->back();
    }
}
