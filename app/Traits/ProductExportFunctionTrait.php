<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Product as ProductDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\ProductUnitName as ProductUnitNameDB;
use App\Models\Country as CountryDB;
use DB;

trait ProductExportFunctionTrait
{
    protected function getProductData($param)
    {
        $products = ProductModelDB::join('products','products.id','product_models.product_id')
            ->join('vendors','vendors.id','products.vendor_id')
            ->join('categories','categories.id','products.category_id');

        if($param['type'] == 'DigiwinIsOn'){
            $products = $products->where('vendors.is_on',1);
        }elseif($param['type'] == 'DigiwinIsOff'){
            $products = $products->where('vendors.is_on',0);
        }

        if($param['method'] == 'allData'){ //全部商品
            //不做任何事情
        }elseif(isset($param['id'])){ //指定選擇的商品 or 目前頁面所有商品
            $products = $products->whereIn('products.id',$param['id']);
        }elseif(isset($param['con'])){ //by條件
            //將進來的資料作參數轉換
            foreach ($param['con'] as $key => $value) {
                $$key = $value;
            }

            //查詢參數
            !empty($status) ? $products = $products->whereIn('products.status',explode(',',$status)) : '';
            if(!empty($shipping_methods)){
                $shipping_methods = ltrim(rtrim($shipping_methods,','),','); //去除左右邊逗點
                $sm = explode(',',$shipping_methods);
                $c = 'products.shipping_methods is null ';
                for($i=0;$i<count($sm);$i++){
                    $c .= " OR FIND_IN_SET('$sm[$i]',products.shipping_methods) ";
                }
                $products = $products->where(function($query)use($c){
                    $query->whereRaw($c);
                });
            }
            !empty($digiwin_no) ? $products = $products->where('product_models.digiwin_no','like',"%$digiwin_no%") : '';
            !empty($sku) ? $products = $products->where('product_models.sku','like',"%$sku%") : '';
            !empty($low_quantity) ? $products = $products->whereRaw('product_models.quantity < product_models.safe_quantity') : '';
            !empty($zero_quantity) ? $products = $products->whereRaw('product_models.quantity <= 0') : '';
            !empty($vendor_id) ? $products = $products->where('products.vendor_id', $vendor_id) : '';
            !empty($category_id) ? $products = $products->where('products.category_id', $category_id) : '';
            !empty($product_name) ? $products = $products->where('products.name', 'like', "%$product_name%") : '';
            !empty($vendor_name) ? $products = $products->where('vendors.name', 'like', "%$vendor_name%") : '';
            !empty($created_at) ? $products = $products->where('products.created_at', '>=', $created_at) : '';
            !empty($created_at_end) ? $products = $products->where('products.created_at', '<=', $created_at_end) : '';
            !empty($pass_time) ? $products = $products->where('products.pass_time', '>=', $pass_time) : '';
            !empty($pass_time_end) ? $products = $products->where('products.pass_time', '<=', $pass_time_end) : '';
        }

        //選擇資料
        $products = $products->select([
            'products.*',
            'vendor_id',
            'vendors.name as vendor_name',
            'vendors.service_fee',
            'categories.name as category_name',
            'unit_name' => ProductUnitNameDB::whereColumn('product_unit_names.id','products.unit_name_id')->select('name')->limit(1),
            DB::raw("(CASE WHEN products.status = -9 THEN '已下架' WHEN products.status = -3 THEN '停售中' WHEN products.status = -2 THEN '審核失敗' WHEN products.status = -1 THEN '未送審' WHEN products.status = 0 THEN '送審中' WHEN products.status = 1 THEN '上架中' WHEN products.status = 2 THEN '送審中' END) as status_name"),
            DB::raw("(CASE WHEN vendors.is_on = 1 THEN '啟用中' ELSE '停用中' END) as vendor_status"),
            DB::raw("(CASE WHEN products.model_type = 1 THEN products.model_name ELSE product_models.name END) as model_name"),
            DB::raw("(CASE WHEN products.deleted_at is not null THEN 'V' WHEN product_models.deleted_at is not null THEN 'V' END) as is_del"),
            'product_models.id as product_model_id',
            'product_models.gtin13',
            'product_models.sku',
            'product_models.digiwin_no',
            'product_models.quantity',
            'product_models.safe_quantity',
        ]);
        $products = $products->withTrashed()->orderBy('products.id','asc')->get();

        return $products;
    }


    protected function makeSku($input){
        if(isset($input['sku'])){
            $output['sku'] = $input['sku'];
        }else{
            //sku的編碼方式 EC 0001 000001
            $output['sku'] = "EC" . str_pad($input['vendor_id'],5,'0',STR_PAD_LEFT) . str_pad($input['product_model_id'],6,'0',STR_PAD_LEFT);
        }

        //digiwin_no的編碼方式
        $digiwinNo="5";
        $countryId = $input['from_country_id'];
        $country = CountryDB::findOrFail($countryId);
        $digiwinNo .= $country->lang; //語言代碼 1:tw, 5:jp
        $digiwinNo .= "A".str_pad($input['vendor_id'],5,"0",STR_PAD_LEFT);

        // 找出product_models與product_id跟vendor_id關聯的總數
        $vendorProductModelCounts = ProductModelDB::where('id','<=',$input['product_model_id'])
            ->whereIn('product_id', ProductDB::where('vendor_id',$input['vendor_id'])->select('id')->get())
            ->count();

        //鼎新編碼原則（包括單品與組合商品）
        if(substr($output['sku'],0,3)=="BOM"){
            $digiwinNo .= "B".str_pad(base_convert($vendorProductModelCounts, 10, 36),3,"0",STR_PAD_LEFT);
        }else{
            $digiwinNo .= str_pad(base_convert($vendorProductModelCounts, 10, 36),4,"0",STR_PAD_LEFT);
        }

        $output['digiwin_no'] = strtoupper($digiwinNo);
        return $output;
    }
}
