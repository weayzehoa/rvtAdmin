<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Laravel 8.x 需將所有Controller列出於function中
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminsController;
use App\Http\Controllers\Admin\MainMenusController;
use App\Http\Controllers\Admin\SubMenusController;
use App\Http\Controllers\Admin\SendMailsController;
use App\Http\Controllers\Admin\UploadsController;
use App\Http\Controllers\Admin\IpAddressController;
use App\Http\Controllers\Admin\SMSController;
use App\Http\Controllers\Admin\VendorsController;
use App\Http\Controllers\Admin\VendorShopsController;
use App\Http\Controllers\Admin\VendorAccountsController;
use App\Http\Controllers\Admin\ShippingVendorsController;
use App\Http\Controllers\Admin\ShippingFeesController;
use App\Http\Controllers\Admin\ShippingInfoController;
use App\Http\Controllers\Admin\CountriesController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\ProductImagesController;
use App\Http\Controllers\Admin\ProductUnitNamesController;
use App\Http\Controllers\Admin\ProductPackagesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\OrderItemsController;
use App\Http\Controllers\Admin\OrderShippingsController;
use App\Http\Controllers\Admin\OrderVendorShippingsController;
use App\Http\Controllers\Admin\UnpayOrdersController;
use App\Http\Controllers\Admin\CompanySettingsController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\InvoicesController;
use App\Http\Controllers\Admin\ReferCodesController;
use App\Http\Controllers\Admin\ReceiverBaseController;
use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\CurationsController;
use App\Http\Controllers\Admin\CategoryCurationsController;
use App\Http\Controllers\Admin\CurationImagesController;
use App\Http\Controllers\Admin\CurationVendorsController;
use App\Http\Controllers\Admin\CurationProductsController;
use App\Http\Controllers\Admin\PayMethodsController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\PromoBoxController;
use App\Http\Controllers\Admin\DigiWinController;
use App\Http\Controllers\Admin\ProductModelController;
use App\Http\Controllers\Admin\ExportCenterController;
use App\Http\Controllers\Admin\AdminLoginLogController;
use App\Http\Controllers\Admin\AddressDisableController;
use App\Http\Controllers\Admin\ShortUrlController;
use App\Http\Controllers\Admin\NewFunctionController;
use App\Http\Controllers\Admin\GroupBuyingsController;
use App\Http\Controllers\Admin\SubCategoriesController;
use App\Http\Controllers\Admin\SearchTitleController;
use App\Http\Controllers\Admin\PriceChangeController;
use App\Http\Controllers\Admin\IndexBannerController;

//iCarry 後台 用的路由 網址看起來就像 https://admin.localhost/{名稱}
//使用多個網域時須使用name()來將路由區分開, 不然會被後面的網域覆蓋掉.
//檢查IP
Route::middleware(['checkIp'])->group(function () {
    Route::name('admin.')->group(function() {
        Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminLoginController::class, 'login'])->name('login.submit');
        Route::get('otp', [AdminLoginController::class, 'showOtpForm'])->name('otp');
        Route::post('otp', [AdminLoginController::class, 'otp'])->name('otp.submit');
        Route::get('2fa', [AdminLoginController::class, 'show2faForm'])->name('2fa');
        Route::post('2fa', [AdminLoginController::class, 'verify2fa'])->name('2fa.submit');
        Route::get('passwordChange', [AdminLoginController::class, 'showPwdChangeForm'])->name('passwordChange');
        Route::post('passwordChange', [AdminLoginController::class, 'passwordChange'])->name('passwordChange.submit');
        Route::get('logout', [AdminLoginController::class, 'logout'])->name('logout');
        Route::get('', [AdminLoginController::class, 'showLoginForm']);
        Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        //管理員帳號管理功能
        Route::post('admins/unlock/{id}', [AdminsController::class, 'unlock'])->name('admins.unlock');
        Route::post('admins/active/{id}', [AdminsController::class, 'active']);
        Route::get('admins/search', [AdminsController::class, 'search']);
        Route::get('admins/export', [AdminsController::class, 'export'])->name('admins.export');
        Route::get('admins/changePassWord', [AdminsController::class, 'changePassWordForm']);
        Route::post('admins/changePassWord', [AdminsController::class, 'changePassWord'])->name('admins.changePassWord');
        Route::resource('admins', AdminsController::class);
        //後台主選單管理功能
        Route::post('mainmenus/active/{id}', [MainMenusController::class, 'active']);
        Route::post('mainmenus/open/{id}', [MainMenusController::class, 'open']);
        Route::get('mainmenus/sortup/{id}',[MainMenusController::class, 'sortup']);
        Route::get('mainmenus/sortdown/{id}',[MainMenusController::class, 'sortdown']);
        Route::get('mainmenus/submenu/{id}',[MainMenusController::class, 'submenu']);
        Route::resource('mainmenus', MainMenusController::class);
        //後台次選單管理功能
        Route::post('submenus/active/{id}', [SubMenusController::class, 'active']);
        Route::post('submenus/open/{id}', [SubMenusController::class, 'open']);
        Route::get('submenus/sortup/{id}',[SubMenusController::class, 'sortup']);
        Route::get('submenus/sortdown/{id}',[SubMenusController::class, 'sortdown']);
        Route::resource('submenus', SubMenusController::class);
        //後台管理員寄信功能
        Route::get('mails/sendmail',[SendMailsController::class,'adminSendMailForm']);
        Route::post('mails/sendmail',[SendMailsController::class,'sendmail'])->name('sendmail');
        Route::post('mails/sendnote',[SendMailsController::class,'sendnote'])->name('sendnote');
        Route::post('mails/sendqueues',[SendMailsController::class,'sendqueues'])->name('sendqueues');
        //後台管理員上傳功能
        Route::get('uploads',[UploadsController::class,'showUploadForm']);
        //上傳圖檔先優化
        Route::middleware('optimizeImages')->group(function () {
            Route::post('uploads/imageUpload',[UploadsController::class,'imageUpload'])->name('uploads.imageUpload');
            Route::post('vendors/upload/{id}', [VendorsController::class, 'upload'])->name('vendors.upload');
            Route::post('productimages/upload/{id}', [ProductImagesController::class, 'upload'])->name('productimages.upload');
            Route::post('products/upload', [ProductsController::class, 'upload'])->name('products.upload');
        });
        //後台管理員發送簡訊功能
        Route::get('sendSMS',[SMSController::class,'sendSMSForm'])->name('sendSMS');
        Route::POST('sendSMS/send',[SMSController::class,'sendSMS'])->name('sendSMS.send');

        //IP Settings 設定
        Route::post('ipSettings/active/{id}', [IpAddressController::class, 'active']);
        Route::resource('ipSettings', IpAddressController::class);

        //後台商家管理
        Route::get('vendors/export', [VendorsController::class, 'export']);
        Route::post('vendors/active/{id}', [VendorsController::class, 'active']);
        Route::post('vendors/lang/{vendor_id}', [VendorsController::class, 'lang'])->name('vendors.lang');
        Route::resource('vendors', VendorsController::class);
        Route::post('vendorshops/active/{id}', [VendorShopsController::class, 'active']);
        Route::resource('vendorshops', VendorShopsController::class);
        Route::post('vendoraccounts/unlock/{id}', [VendorAccountsController::class, 'unlock'])->name('vendoraccounts.unlock');
        Route::post('vendoraccounts/active/{id}', [VendorAccountsController::class, 'active']);
        Route::resource('vendoraccounts', VendorAccountsController::class);

        //後台物流管理
        Route::get('shippingvendors/sortup/{id}',[ShippingVendorsController::class, 'sortup']);
        Route::get('shippingvendors/sortdown/{id}',[ShippingVendorsController::class, 'sortdown']);
        Route::resource('shippingvendors', ShippingVendorsController::class);

        //後台物流運費設定
        Route::post('shippingfees/active/{id}', [ShippingFeesController::class, 'active']);
        Route::resource('shippingfees', ShippingFeesController::class);

        //渠道出貨資訊
        Route::resource('shippinginfo', ShippingInfoController::class);

        //國家資料設定
        Route::get('countries/sortup/{id}',[CountriesController::class, 'sortup']);
        Route::get('countries/sortdown/{id}',[CountriesController::class, 'sortdown']);
        Route::resource('countries', CountriesController::class);

        //商品管理
        Route::get('products/copy/{id}', [ProductsController::class, 'copy'])->name('product.copy');
        Route::post('products/recover', [ProductsController::class, 'recover'])->name('products.recover');
        Route::post('products/import', [ProductsController::class, 'import'])->name('products.import');
        Route::get('products/export', [ProductsController::class, 'export']);
        Route::post('products/lang/{vendor_id}', [ProductsController::class, 'lang'])->name('products.lang');
        Route::post('products/getGtin13History', [ProductsController::class, 'getGtin13History'])->name('products.getGtin13History');
        Route::post('products/getSubCate', [ProductsController::class, 'getSubCate'])->name('products.getSubCate');
        Route::post('products/getHistory', [ProductsController::class, 'getHistory'])->name('products.getHistory');
        Route::post('products/getlist', [ProductsController::class, 'getList'])->name('products.getlist');
        Route::post('products/delmodel', [ProductsController::class, 'delModel'])->name('products.delmodel');
        Route::post('products/delpackage', [ProductsController::class, 'delPackage'])->name('products.delpackage');
        Route::post('products/dellist', [ProductsController::class, 'delList'])->name('products.dellist');
        Route::post('products/deloldimage', [ProductsController::class, 'deloldimage'])->name('products.deloldimage');
        Route::post('products/getstockrecord', [ProductsController::class, 'getStockRecord'])->name('products.getstockrecord');
        Route::post('products/stockmodify', [ProductsController::class, 'stockModify'])->name('products.stockmodify');
        Route::post('products/multiProcess', [ProductsController::class, 'multiProcess'])->name('products.multiProcess');
        Route::resource('products', ProductsController::class);

        //組合商品
        Route::get('packages/export', [ProductPackagesController::class, 'export']);
        Route::resource('packages', ProductPackagesController::class);

        //商品照片
        Route::get('productimages/sortup/{id}',[ProductImagesController::class, 'sortup']);
        Route::get('productimages/sortdown/{id}',[ProductImagesController::class, 'sortdown']);
        Route::post('productimages/active/{id}', [ProductImagesController::class, 'active']);
        Route::post('productimages/top/{id}', [ProductImagesController::class, 'top'])->name('productimages.top');
        Route::resource('productimages', ProductImagesController::class);

        //單位名稱設定
        Route::get('unitnames/sortup/{id}',[ProductUnitNamesController::class, 'sortup']);
        Route::get('unitnames/sortdown/{id}',[ProductUnitNamesController::class, 'sortdown']);
        Route::resource('unitnames', ProductUnitNamesController::class);

        //使用者管理
        Route::post('users/import', [UsersController::class, 'import']);
        Route::post('users/active/{id}', [UsersController::class, 'active']);
        Route::post('users/mark/{id}', [UsersController::class, 'mark']);
        Route::post('users/addpoints/{id}', [UsersController::class, 'addPoints']);
        Route::post('users/getintro', [UsersController::class, 'getIntro']);
        Route::post('users/sendsms/{id}', [UsersController::class, 'sendSms']);
        Route::resource('users', UsersController::class);

        // //訂單管理
        // Route::post('orders/refund', [OrdersController::class, 'refund'])->name('orders.refund');
        // Route::post('orders/modify', [OrdersController::class, 'modify'])->name('orders.modify');
        // Route::post('orders/export', [OrdersController::class, 'export'])->name('orders.export');
        // Route::post('orders/import', [OrdersController::class, 'import'])->name('orders.import');
        // Route::post('orders/itemmemo', [OrdersController::class, 'itemMemo'])->name('orders.itemmemo');
        // Route::post('orders/itemqtymodify', [OrdersController::class, 'itemQtyModify'])->name('orders.itemqtymodify');
        // Route::post('orders/getlog', [OrdersController::class, 'getLog'])->name('orders.getlog');
        // Route::post('orders/getshippingvendors', [OrdersController::class, 'getShippingVendors'])->name('orders.getshippingvendors');
        // Route::post('orders/getvendors', [OrdersController::class, 'getVendors'])->name('orders.getvendors');
        // Route::get('orders/getExpressData', [OrdersController::class, 'getExpressData'])->name('orders.getexpressdata');
        // Route::resource('orders', OrdersController::class);
        // Route::post('ordershippings/modify', [OrderShippingsController::class, 'modify'])->name('ordershippings.modify');
        // Route::post('ordershippings/export', [OrderShippingsController::class, 'export'])->name('ordershippings.export');
        // Route::resource('ordershippings', OrderShippingsController::class);
        // Route::resource('ordervendorshippings', OrderVendorShippingsController::class);

        // //未付款訂單管理
        // Route::post('unpayorders/modify', [UnpayOrdersController::class, 'modify']);
        // Route::resource('unpayorders', UnpayOrdersController::class);

        //Company Settings 設定
        Route::resource('companysettings', CompanySettingsController::class);

        //System Settings 設定
        Route::resource('systemsettings', SystemSettingsController::class);

        // //發票管理
        // Route::get('invoices/print/{id}', [InvoicesController::class, 'print'])->name('invoices.print');
        // Route::post('invoices/modify', [InvoicesController::class, 'modify']);
        // Route::post('invoices/export', [InvoicesController::class, 'export']);
        // Route::post('invoices/import', [InvoicesController::class, 'import']);
        // Route::resource('invoices', InvoicesController::class);

        //推薦註冊碼設定
        Route::post('refercodes/active/{id}', [ReferCodesController::class, 'active']);
        Route::resource('refercodes', ReferCodesController::class);

        //提貨日設定
        Route::post('receiverbase/search', [ReceiverBaseController::class, 'search'])->name('receiverbase.search');
        Route::resource('receiverbase', ReceiverBaseController::class);

        //商品分類設定
        Route::get('categories/sortup/{id}',[CategoriesController::class, 'sortup']);
        Route::get('categories/sortdown/{id}',[CategoriesController::class, 'sortdown']);
        Route::post('categories/active/{id}', [CategoriesController::class, 'active']);
        Route::post('categories/upload/{id}', [CategoriesController::class, 'upload'])->name('categories.upload');
        Route::post('categories/lang/{category_id}', [CategoriesController::class, 'lang'])->name('categories.lang');
        Route::resource('categories', CategoriesController::class);

        //首頁策展
        Route::get('curations/sortup/{id}',[CurationsController::class, 'sortup']);
        Route::get('curations/sortdown/{id}',[CurationsController::class, 'sortdown']);
        Route::post('curations/sort',[CurationsController::class, 'sort'])->name('curations.sort');
        Route::post('curations/active/{id}', [CurationsController::class, 'active']);
        Route::post('curations/getproducts', [CurationsController::class, 'getProducts']);
        Route::resource('curations', CurationsController::class);

        //分類策展
        Route::get('categorycurations/sortup/{id}',[CategoryCurationsController::class, 'sortup']);
        Route::get('categorycurations/sortdown/{id}',[CategoryCurationsController::class, 'sortdown']);
        Route::post('categorycurations/sort',[CategoryCurationsController::class, 'sort'])->name('categorycurations.sort');
        Route::post('categorycurations/active/{id}', [CategoryCurationsController::class, 'active']);
        Route::post('categorycurations/getproducts', [CategoryCurationsController::class, 'getProducts']);
        Route::resource('categorycurations', CategoryCurationsController::class);

        //策展-圖片資料處理
        Route::get('curationimages/sortup/{id}',[CurationImagesController::class, 'sortup']);
        Route::get('curationimages/sortdown/{id}',[CurationImagesController::class, 'sortdown']);
        Route::resource('curationimages', CurationImagesController::class);

        //策展-Vendor資料處理
        Route::get('curationvendors/sortup/{id}',[CurationVendorsController::class, 'sortup']);
        Route::get('curationvendors/sortdown/{id}',[CurationVendorsController::class, 'sortdown']);
        Route::post('curationvendors/sort',[CurationVendorsController::class, 'sort'])->name('curationvendors.sort');
        Route::resource('curationvendors', CurationVendorsController::class);

        //策展-Product資料處理
        Route::get('curationproducts/sortup/{id}',[CurationProductsController::class, 'sortup']);
        Route::get('curationproducts/sortdown/{id}',[CurationProductsController::class, 'sortdown']);
        Route::post('curationproducts/sort',[CurationProductsController::class, 'sort'])->name('curationproducts.sort');
        Route::resource('curationproducts', CurationProductsController::class);

        //付款方式設定
        Route::post('paymethods/active/{id}', [PayMethodsController::class, 'active']);
        Route::get('paymethods/sortup/{id}',[PayMethodsController::class, 'sortup']);
        Route::get('paymethods/sortdown/{id}',[PayMethodsController::class, 'sortdown']);
        Route::resource('paymethods', PayMethodsController::class);

        //統計資料
        Route::get('usermonthlytotal', [StatisticsController::class, 'userMonthlyTotal']);
        Route::get('orderdailytotal', [StatisticsController::class, 'orderDailyTotal']);
        Route::get('ordermonthlytotal', [StatisticsController::class, 'orderMonthlyTotal']);
        Route::get('orderdailytotalOne', [StatisticsController::class, 'orderdailytotalOne']);
        Route::get('ordermonthlytotalOne', [StatisticsController::class, 'ordermonthlytotalOne']);
        Route::get('ordermonthlyselltotal', [StatisticsController::class, 'orderMonthlySellTotal']);
        Route::get('intervalstatistics', [StatisticsController::class, 'intervalStatistics']);
        Route::get('shippingmonthlytotal', [StatisticsController::class, 'shippingMonthlyTotal']);
        Route::get('productsales', [StatisticsController::class, 'productSales']);
        Route::get('vendorsales', [StatisticsController::class, 'vendorSales']);

        //優惠活動設定
        Route::post('promoboxes/active/{id}', [PromoBoxController::class, 'active']);
        Route::resource('promoboxes', PromoBoxController::class);

        // //鼎新資料處理
        // Route::get('digiwin/vendor', [DigiWinController::class, 'vendor']);
        // Route::get('digiwin/vendorsexport', [DigiWinController::class, 'vendorsExport']);
        // Route::get('digiwin/logistic', [DigiWinController::class, 'logistic']); //物流單號匯入匯出
        // Route::post('digiwin/logisticImport', [DigiWinController::class, 'logisticImport']);
        // Route::get('digiwin/ec2no', [DigiWinController::class, 'ec2no']); //商品貨號轉換
        // Route::post('digiwin/ec2noImport', [DigiWinController::class, 'ec2noImport']);
        // Route::get('digiwin/product293', [DigiWinController::class, 'product293']); //閃購專區對應貨號

        //Product Model
        Route::resource('productModel', ProductModelController::class);

        //匯出中心
        Route::post('export', [ExportCenterController::class, 'export'])->name('export');
        Route::resource('exportcenter', ExportCenterController::class);

        //紀錄中心
        Route::resource('adminLoginLog', AdminLoginLogController::class);

        //無法派送關鍵字管理
        Route::resource('addressDisable', AddressDisableController::class);

        //短網址設定
        Route::resource('shortUrl', ShortUrlController::class);

        //團購設定
        Route::post('groupbuyings/active/{id}', [GroupBuyingsController::class, 'active']);
        Route::post('groupbuyings/getproducts', [GroupBuyingsController::class, 'getProducts']);
        Route::resource('groupbuyings', GroupBuyingsController::class);

        //商品次分類設定
        Route::get('subCategories/sortup/{id}',[SubCategoriesController::class, 'sortup']);
        Route::get('subCategories/sortdown/{id}',[SubCategoriesController::class, 'sortdown']);
        Route::post('subCategories/active/{id}', [SubCategoriesController::class, 'active']);
        Route::resource('subCategories', SubCategoriesController::class);

        //搜尋標題設定
        Route::get('searchtitles/sortup/{id}',[SearchTitleController::class, 'sortup']);
        Route::get('searchtitles/sortdown/{id}',[SearchTitleController::class, 'sortdown']);
        Route::post('searchtitles/active/{id}', [SearchTitleController::class, 'active']);
        Route::resource('searchtitles', SearchTitleController::class);

        //首頁橫幅圖管理
        Route::get('indexBanners/sortup/{id}',[IndexBannerController::class, 'sortup']);
        Route::get('indexBanners/sortdown/{id}',[IndexBannerController::class, 'sortdown']);
        Route::post('indexBanners/active/{id}', [IndexBannerController::class, 'active']);
        Route::resource('indexBanners', IndexBannerController::class);

        //功能開發測試
        Route::post('priceChanges/active/{id}', [PriceChangeController::class, 'active']);
        Route::resource('priceChanges', PriceChangeController::class);

        //功能開發測試
        Route::resource('newFunction', NewFunctionController::class);
    });
});
