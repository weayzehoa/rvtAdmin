<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryProductUnitName as UnitNameDB;
use App\Models\iCarryProductPackage as ProductPackageDB;
use App\Models\iCarryProductPackageList as ProductPackageListDB;
use App\Models\ErpProduct as ErpProductDB;
use App\Models\ACErpProduct as ACErpProductDB;
use App\Models\GateLog as LogDB;
use DB;

class ProductAutoUpdateToErpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //讓job不會timeout, 此設定需用 queue:work 才會優先於預設
    public $timeout = 0;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();

        $products = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
            ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id');

        $products = $products->where($productTable.'.id',$this->id);

        $products = $products->select([
            $productTable.'.*',
            $vendorTable.'.name as vendor_name',
            $vendorTable.'.service_fee',
            $vendorTable.'.digiwin_vendor_no',
            $vendorTable.'.ac_digiwin_vendor_no',
            $productModelTable.'.id as product_model_id',
            $productModelTable.'.name as product_model_name',
            $productModelTable.'.gtin13',
            $productModelTable.'.sku',
            $productModelTable.'.digiwin_no',
            $productModelTable.'.quantity',
            $productModelTable.'.safe_quantity',
            DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as name"),
        ])->get();

        if(count($products) > 0){
            foreach($products as $product){
                !empty($product->digiwin_vendor_no) ? $erpVendorNo = $product->digiwin_vendor_no : $erpVendorNo = 'A'.str_pad($product->vendor_id,5,"0",STR_PAD_LEFT);
                $buyPrice = $serviceFeePercent = 0;
                if(!empty($product->service_fee)){
                    $product->service_fee = str_replace(":}",":0}",$product->service_fee); //補0
                    $serviceFee = json_decode($product->service_fee,true);
                    if(is_array($serviceFee)){
                        foreach($serviceFee as $value){
                            if($value['name']=="iCarry"){
                                $serviceFeePercent = $value['percent'];
                                break;
                            }
                        }
                    }
                }
                if(empty($product->vendor_price) || $product->vendor_price == 0){
                    $buyPrice = $product->price * (100-$serviceFeePercent) / 100;
                }else{
                    $buyPrice = $product->vendor_price;
                }
                $buyPrice = round($buyPrice,4);
                $oldData = null;
                if(!empty($product->digiwin_no) && $product->digiwin_no != ''){
                    $data['db_name'] = 'INVMB';
                    $data['COMPANY'] = 'iCarry';
                    $data['USR_GROUP'] = 'DSC';
                    $data['FLAG'] = 1;
                    $data['MB001'] = $product->digiwin_no;
                    $data['MB002'] = mb_substr($product->name,0,110);
                    $data['MB003'] = mb_substr($product->serving_size,0,110);
                    $data['MB004'] = $product->unit_name;
                    $data['MB005'] = '104';
                    $data['MB010'] = $product->digiwin_no;
                    $data['MB011'] = '0001';
                    $data['MB013'] = $product->gtin13;
                    $data['MB015'] = 'g';
                    $data['MB017'] = 'W01';
                    $data['MB019'] = 'Y';
                    $data['MB020'] = 'N';
                    $data['MB022'] = 'N';
                    $data['MB023'] = $product->storage_life;
                    $data['MB024'] = 0;
                    $data['MB025'] = 'P'; //M 組合商品, P 單一規格, 全部使用P
                    $data['MB026'] = '99';
                    $data['MB032'] = $erpVendorNo;
                    $data['MB046'] = round($buyPrice / 1.05,4);
                    $data['MB049'] = $buyPrice;
                    $data['MB034'] = 'L';
                    $data['MB042'] = 1;
                    $data['MB043'] = 0;
                    $data['MB044'] = 'y';
                    $data['MB148'] = 0;
                    $data['MB150'] = 'N';
                    $data['MB151'] = 0;
                    if(!empty($product->erpProduct)){
                        $data['type'] = '更新';
                        $data['COMPANY'] = 'AC';
                        $data['MODIFIER'] = 'DS';
                        $data['MODI_DATE'] = date('Ymd');
                        $data['MODI_TIME'] = date('H:i:s');
                        $data['MODI_AP'] = 'Gate';
                        $data['MODI_PRID'] = 'INVI02';
                        $oldData = json_encode($product->erpProduct,true);
                        $product->erpProduct->update($data);
                    }else{
                        $data['type'] = '新增';
                        $data['CREATOR'] = 'DS';
                        $data['CREATE_DATE'] = date('Ymd');
                        $data['CREATE_TIME'] = date('H:i:s');
                        $data['COMPANY'] = 'AC';
                        $data['CREATE_AP'] = 'Gate';
                        $data['CREATE_PRID'] = 'INVI02';
                        $data['MB025'] = 'P'; //M 組合商品, P 單一規格, 全部使用P
                        $data['MB006'] = $product->digiwin_product_category;
                        ErpProductDB::create($data);
                    }
                    if(!empty($product->ac_digiwin_vendor_no)){
                        $data['MB032'] = $product->ac_digiwin_vendor_no;
                        $data['MB044'] = 'N';
                        $data['MB005'] = '101';
                        $data['MB006'] = '';
                        if(!empty($product->acErpProduct)){
                            $data['type'] = '更新';
                            $data['MODIFIER'] = 'DS';
                            $data['MODI_DATE'] = date('Ymd');
                            $data['MODI_TIME'] = date('H:i:s');
                            $data['MODI_AP'] = 'Gate';
                            $data['MODI_PRID'] = 'INVI02';
                            $oldData = json_encode($product->acErpProduct,true);
                            $product->acErpProduct->update($data);
                        }else{
                            $data['type'] = '新增';
                            $data['CREATOR'] = 'DS';
                            $data['CREATE_DATE'] = date('Ymd');
                            $data['CREATE_TIME'] = date('H:i:s');
                            $data['CREATE_AP'] = 'Gate';
                            $data['CREATE_PRID'] = 'INVI02';
                            ACErpProductDB::create($data);
                        }
                    }
                    $data['sku'] = $product->sku;
                    $data['digiwin_no'] = $product->digiwin_no;
                    LogDB::create($data);
                    //修正組合品資料
                    if($product->model_type == 3){
                        $dd = collect(json_decode(str_replace('	','',$product->package_data)));
                        if(!empty($dd) && count($dd) > 0){
                            foreach ($dd as $model) {
                                if (!empty($model->bom)) {
                                    $tmp = ProductModelDB::where('sku', $model->bom)->first();
                                    if(isset($model->is_del)){
                                        if($model->is_del == 1){
                                            if(!empty($tmp)){
                                                $pp = ProductPackageDB::where([['product_id',$product->id],['product_model_id',$tmp->id]])->first();
                                                if(!empty($pp)){
                                                    $packageList = ProductPackageListDB::where([['product_package_id',$pp->id],['product_model_id',$tmp->id]])->first();
                                                    if(!empty($packageList)){
                                                        $packageList->delete();
                                                    }
                                                    $pp->delete();
                                                }
                                            }
                                        }else{
                                            if(!empty($tmp)){
                                                $pp = ProductPackageDB::where([['product_id',$product->id],['product_model_id',$tmp->id]])->first();
                                                if(empty($pp)){
                                                    $pp = ProductPackageDB::create([
                                                        'product_id' => $product->id,
                                                        'product_model_id' => $tmp->id,
                                                    ]);
                                                }
                                                if(!empty($model->lists) && count($model->lists) > 0){
                                                    foreach ($model->lists as $li) {
                                                        if (!empty($li->sku)) {
                                                            $tmp = ProductModelDB::where('sku', $li->sku)->first();
                                                            if(!empty($tmp) && $li->quantity > 0){
                                                                $packageList = ProductPackageListDB::where([['product_package_id',$pp->id],['product_model_id',$tmp->id]])->first();
                                                                if(empty($packageList)){
                                                                    //組合商品中包含多個商品
                                                                    ProductPackageListDB::create([
                                                                        'product_package_id' => $pp->id,
                                                                        'product_model_id' => $tmp->id,
                                                                        'quantity' => $li->quantity,
                                                                    ]);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        if(!empty($tmp)){
                                            $pp = ProductPackageDB::where([['product_id',$product->id],['product_model_id',$tmp->id]])->first();
                                            if(empty($pp)){
                                                $pp = ProductPackageDB::create([
                                                    'product_id' => $product->id,
                                                    'product_model_id' => $tmp->id,
                                                ]);
                                            }
                                            if(!empty($model->lists) && count($model->lists) > 0){
                                                foreach ($model->lists as $li) {
                                                    if (!empty($li->sku)) {
                                                        $tmp = ProductModelDB::where('sku', $li->sku)->first();
                                                        if(!empty($tmp) && $li->quantity > 0){
                                                            $packageList = ProductPackageListDB::where([['product_package_id',$pp->id],['product_model_id',$tmp->id]])->first();
                                                            if(empty($packageList)){
                                                                //組合商品中包含多個商品
                                                                ProductPackageListDB::create([
                                                                    'product_package_id' => $pp->id,
                                                                    'product_model_id' => $tmp->id,
                                                                    'quantity' => $li->quantity,
                                                                ]);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
