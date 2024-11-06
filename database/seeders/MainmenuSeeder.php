<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mainmenu as MainmenuDB;
use App\Models\Submenu as SubmenuDB;

class MainmenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mainmenu = [
            //admin後台
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-cogs"></i>', 'name' => '系統管理', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-store"></i>', 'name' => '商家管理', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-truck"></i>', 'name' => '物流管理', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fab fa-product-hunt"></i>', 'name' => '商品管理', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-users"></i>', 'name' => '使用者管理', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-shopping-cart"></i>', 'name' => '訂單管理', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-ad"></i>', 'name' => '行銷策展', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-bar"></i>', 'name' => '統計資料', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-cash-register"></i>', 'name' => 'ACPay 管理', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-file-export"></i>', 'name' => '鼎新資料處理', 'url' => ''],
            ['is_on' => 1, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-clipboard-list"></i>', 'name' => '紀錄中心', 'url' => ''],
            ['is_on' => 0, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '預留一', 'url' => ''],
            ['is_on' => 0, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '預留二', 'url' => ''],
            ['is_on' => 0, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '預留三', 'url' => ''],
            ['is_on' => 0, 'type' => 1, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '功能測試', 'url' => ''],
            //商家後台
            ['is_on' => 1, 'type' => 2, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-store"></i>', 'name' => '商家管理', 'url' => ''],
            ['is_on' => 1, 'type' => 2, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fab fa-product-hunt"></i>', 'name' => '商品管理', 'url' => ''],
            ['is_on' => 1, 'type' => 2, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-shopping-cart"></i>', 'name' => '訂單管理', 'url' => ''],
            ['is_on' => 1, 'type' => 2, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-cash-register"></i>', 'name' => 'iCarryGo管理', 'url' => ''],
            ['is_on' => 0, 'type' => 2, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '商家後台預留', 'url' => ''],
            ['is_on' => 0, 'type' => 2, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '商家後台預留', 'url' => ''],
            ['is_on' => 0, 'type' => 2, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '商家後台預留', 'url' => ''],
            //admin後台
            ['is_on' => 1, 'type' => 1, 'url_type' => 2, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-file-export"></i>', 'name' => '匯出中心', 'url' => '../exportcenter'],
            ['is_on' => 0, 'type' => 1, 'url_type' => 2, 'open_window' => 1, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '舊版後台網站', 'url' => 'https://admin.icarry.me'],
            ['is_on' => 0, 'type' => 1, 'url_type' => 2, 'open_window' => 1, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '舊版商家後台', 'url' => 'https://vendor.icarry.me'],
            //中繼系統
            ['is_on' => 1, 'type' => 3, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-cogs"></i>', 'name' => '系統管理', 'url' => ''],
            ['is_on' => 1, 'type' => 3, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '主選單一', 'url' => ''],
            ['is_on' => 1, 'type' => 3, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '主選單二', 'url' => ''],
            ['is_on' => 1, 'type' => 3, 'url_type' => 0, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '主選單三', 'url' => ''],
        ];
        $submenu = [
            [ //系統管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O', 'fa5icon' => '<i class="nav-icon fas fa-users"></i>', 'name' => '管理員帳號管理', 'url' => 'admins'],
                ['is_on' => 0, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O,S', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '後台選單管理', 'url' => 'mainmenus'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O,S', 'fa5icon' => '<i class="nav-icon fas fa-globe-americas"></i>', 'name' => '國家資料設定', 'url' => 'countries'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'M', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '公司資料設定', 'url' => 'companysettings'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'M', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '系統參數設定', 'url' => 'systemsettings'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'M', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '提貨日設定', 'url' => 'receiverbase'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O,S', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '付款方式設定', 'url' => 'paymethods'],
            ],
            [ //商家管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O,EX', 'fa5icon' => '<i class="nav-icon fas fa-list"></i>', 'name' => '商家列表管理', 'url' => 'vendors'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O', 'fa5icon' => '<i class="nav-icon fas fa-store-slash"></i>', 'name' => '商家分店列表', 'url' => 'vendorshops'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O,T', 'fa5icon' => '<i class="nav-icon fas fa-users"></i>', 'name' => '商家帳號列表', 'url' => 'vendoraccounts'],
            ],
            [ //物流管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,S', 'fa5icon' => '<i class="nav-icon fas fa-list-ul"></i>', 'name' => '物流廠商管理', 'url' => 'shippingvendors'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O', 'fa5icon' => '<i class="nav-icon fas fa-shipping-fast"></i>', 'name' => '物流運費設定', 'url' => 'shippingfees'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-shipping-fast"></i>', 'name' => '渠道出貨資訊', 'url' => 'shippinginfo'],
            ],
            [ //商品管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,EX,SM', 'fa5icon' => '<i class="nav-icon fas fa-list-ol"></i>', 'name' => '商品管理', 'url' => 'products'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'EX', 'fa5icon' => '<i class="nav-icon fas fa-archive"></i>', 'name' => '組合商品', 'url' => 'packages'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,S', 'fa5icon' => '<i class="nav-icon fas fa-underline"></i>', 'name' => '單位名稱設定', 'url' => 'unitnames'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O,S', 'fa5icon' => '<i class="nav-icon fab fa-buromobelexperte"></i>', 'name' => '商品分類設定', 'url' => 'categories'],
            ],
            [ //使用者管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'M,O,P,SMS,SMM', 'fa5icon' => '<i class="nav-icon fas fa-users"></i>', 'name' => '使用者管理', 'url' => 'users'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 1, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-comments"></i>', 'name' => '客服訊息平台', 'url' => 'https://app.crisp.chat/initiate/login/'],
            ],
            [ //訂單管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'M,EX,IM,PR,MK,CO,RM', 'fa5icon' => '<i class="nav-icon fas fa-cart-arrow-down"></i>', 'name' => '訂單管理', 'url' => 'orders'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'MK,CO', 'fa5icon' => '<i class="nav-icon fas fa-cart-arrow-down"></i>', 'name' => '未付款訂單', 'url' => 'unpayorders'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'NE,DE,M,EX,IM,PR', 'fa5icon' => '<i class="nav-icon far fa-check-square"></i>', 'name' => '發票開立管理', 'url' => 'invoices'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'M,EX', 'fa5icon' => '<i class="nav-icon far fa-check-square"></i>', 'name' => '物流單管理', 'url' => 'ordershippings'],
            ],
            [ //行銷策展
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O,S', 'fa5icon' => '<i class="nav-icon fas fa-ad"></i>', 'name' => '首頁策展', 'url' => 'curations'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O,S', 'fa5icon' => '<i class="nav-icon fas fa-ad"></i>', 'name' => '分類策展', 'url' => 'categorycurations'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O', 'fa5icon' => '<i class="nav-icon fas fa-bullhorn"></i>', 'name' => '推薦註冊碼設定', 'url' => 'refercodes'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O', 'fa5icon' => '<i class="nav-icon fas fa-bullhorn"></i>', 'name' => '優惠活動設定', 'url' => 'promoboxes'],
                // ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O', 'fa5icon' => '<i class="nav-icon fas fa-bullhorn"></i>', 'name' => '促銷代碼設定', 'url' => 'promocodes'],
            ],
            [ //統計資料
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-bar"></i>', 'name' => '註冊人數統計', 'url' => 'usermonthlytotal'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-bar"></i>', 'name' => '訂單每日統計', 'url' => 'orderdailytotal'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-bar"></i>', 'name' => '訂單每月統計', 'url' => 'ordermonthlytotal'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-bar"></i>', 'name' => '訂單區間統計', 'url' => 'intervalstatistics'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-bar"></i>', 'name' => '訂單物流統計', 'url' => 'shippingmonthlytotal'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-bar"></i>', 'name' => '商品銷量統計', 'url' => 'productsales'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-bar"></i>', 'name' => '商家銷量統計', 'url' => 'vendorsales'],
                // ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-bar"></i>', 'name' => '促銷活動銷量統計', 'url' => 'promostatistics'],
            ],
            [ //ACPay管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,M,O,EX', 'fa5icon' => '<i class="nav-icon fas fa-cash-register"></i>', 'name' => '機台管理', 'url' => 'acpaymachines'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'EX', 'fa5icon' => '<i class="nav-icon far fa-list-alt"></i>', 'name' => '帳務管理', 'url' => 'acpayaccounting'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'M,EX,MK', 'fa5icon' => '<i class="nav-icon fas fa-cart-arrow-down"></i>', 'name' => '訂單管理', 'url' => 'acpayorders'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-chart-line"></i>', 'name' => '出貨統計', 'url' => 'acpaystatistics'],
            ],
            [ //鼎新資料處理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-file-export"></i>', 'name' => '供應商匯出', 'url' => 'digiwin/vendor'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-file-import"></i>', 'name' => '物流單號匯入匯出', 'url' => 'digiwin/logistic'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-exchange-alt"></i>', 'name' => '商品貨號轉換', 'url' => 'digiwin/ec2no'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-clipboard-list"></i>', 'name' => '閃購專區對應貨號', 'url' => 'digiwin/product293'],
            ],
            [ //紀錄中心
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-clipboard-list"></i>', 'name' => '管理者登入登出紀錄', 'url' => 'adminLoginLog'],
            ],
            [
                //預留一
            ],
            [
                //預留二
            ],
            [
                //預留三
            ],
            [ //功能測試
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'E', 'fa5icon' => '<i class="nav-icon fas fa-mail-bulk"></i>', 'name' => '發信功能測試', 'url' => 'mails/sendmail'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'E', 'fa5icon' => '<i class="nav-icon fas fa-sms"></i>', 'name' => '簡訊功能測試', 'url' => 'sms'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'E', 'fa5icon' => '<i class="nav-icon fas fa-cloud-upload-alt"></i>', 'name' => '上傳功能測試', 'url' => 'uploads'],
            ],
            [ //商家後台商家管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O,EX', 'fa5icon' => '<i class="nav-icon fas fa-list"></i>', 'name' => '商家資料管理', 'url' => 'profile'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O', 'fa5icon' => '<i class="nav-icon fas fa-store-slash"></i>', 'name' => '商家分店管理', 'url' => 'shop'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O,T', 'fa5icon' => '<i class="nav-icon fas fa-users"></i>', 'name' => '商家帳號管理', 'url' => 'account'],
            ],
            [ //商家後台商品管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,EX', 'fa5icon' => '<i class="nav-icon fas fa-list-ol"></i>', 'name' => '商品管理', 'url' => 'product'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'EX', 'fa5icon' => '<i class="nav-icon fas fa-archive"></i>', 'name' => '組合商品', 'url' => 'package'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'EX', 'fa5icon' => '<i class="nav-icon fas fa-ad"></i>', 'name' => '行銷策展', 'url' => 'curation'],
            ],
            [ //商家後台訂單管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'M,EX,IM,PR,MK,CO,RM,PP', 'fa5icon' => '<i class="nav-icon fas fa-cart-arrow-down"></i>', 'name' => '待出貨訂單', 'url' => 'waittingShipping'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'MK,CO', 'fa5icon' => '<i class="nav-icon fas fa-cart-arrow-down"></i>', 'name' => '已出貨訂單', 'url' => 'finishedOrder'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'NE,DE,M,EX,IM,PR', 'fa5icon' => '<i class="nav-icon far fa-check-square"></i>', 'name' => '已取消訂單', 'url' => 'canceledOrder'],
            ],
            [ //商家後台 iCarryGo 管理
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M', 'fa5icon' => '<i class="nav-icon fas fa-users"></i>', 'name' => '機台管理', 'url' => 'machine'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M', 'fa5icon' => '<i class="nav-icon fas fa-cart-arrow-down"></i>', 'name' => '訂單管理', 'url' => 'acpayOrder'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M', 'fa5icon' => '<i class="nav-icon far fa-list-alt"></i>', 'name' => '帳務管理', 'url' => 'accounting'],
            ],
            [],
            [],
            [],
            [],
            [],
            [],
            [ //中繼後台
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O', 'fa5icon' => '<i class="nav-icon fas fa-users"></i>', 'name' => '管理員帳號管理', 'url' => 'admins'],
                ['is_on' => 0, 'url_type' => 1, 'open_window' => 0, 'power_action' => 'N,D,M,O,S', 'fa5icon' => '<i class="nav-icon fas fa-tools"></i>', 'name' => '後台選單管理', 'url' => 'mainmenus'],
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-clipboard-list"></i>', 'name' => '管理者登入登出紀錄', 'url' => 'adminLoginLog'],
            ],
            [ //中繼後台
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-users"></i>', 'name' => '次選單二之一', 'url' => ''],
            ],
            [ //中繼後台
                ['is_on' => 1, 'url_type' => 1, 'open_window' => 0, 'power_action' => '', 'fa5icon' => '<i class="nav-icon fas fa-users"></i>', 'name' => '次選單三之一', 'url' => ''],
            ],
        ];

        if (env('DB_MIGRATE_MAINMENUS')) {
            $s1 = 0;
            $s2 = 0;
            for ($i=0;$i<count($mainmenu);$i++) {
                if($mainmenu[$i]['type'] == 1){
                    $s1++;
                    $sort = $s1;
                }else{
                    $s2++;
                    $sort = $s2;
                }
                MainmenuDB::create([
                    'type' => $mainmenu[$i]['type'],
                    'code' => 'M'.($i+1).'S0',
                    'name' => $mainmenu[$i]['name'],
                    'fa5icon' => $mainmenu[$i]['fa5icon'],
                    'power_action' => $mainmenu[$i]['power_action'],
                    'url' => $mainmenu[$i]['url'],
                    'url_type' => $mainmenu[$i]['url_type'],
                    'open_window' => $mainmenu[$i]['open_window'],
                    'is_on' => $mainmenu[$i]['is_on'],
                    'sort' => $sort,
                ]);
                if ($mainmenu[$i]['url_type']==0) {
                    if(!empty($submenu[$i])){
                        for ($j=0;$j<count($submenu[$i]);$j++) {
                            if (env('DB_MIGRATE_SUBMENUS')) {
                                SubmenuDB::create([
                                    'mainmenu_id' => $i+1,
                                    'code' => 'M'.($i+1).'S'.($j+1),
                                    'name' => $submenu[$i][$j]['name'],
                                    'fa5icon' => $submenu[$i][$j]['fa5icon'],
                                    'power_action' => $submenu[$i][$j]['power_action'],
                                    'url' => $submenu[$i][$j]['url'],
                                    'url_type' => $submenu[$i][$j]['url_type'],
                                    'open_window' => $submenu[$i][$j]['open_window'],
                                    'is_on' => $submenu[$i][$j]['is_on'],
                                    'sort' => $j+1,
                                ]);
                            }
                        }
                    }
                }
            }
            echo "後台選單建立完成\n";
        }
    }
}
