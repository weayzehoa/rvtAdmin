<?php
namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Auth;

use App\Models\GateMainmenu as MainmenuDB;
use App\Models\GateSubmenu as SubmenuDB;
use App\Models\GatePowerAction as PowerActionDB;

class AdminIndexComposer
{
    public function compose(View $view){
        // $mainmenus = MainmenuDB::with('submenu')->where(['type' => 1, 'is_on' => 1])->orderBy('sort','asc')->get();
        $mainmenus = MainmenuDB::with('submenu')->where(['is_on' => 1])->orderBy('sort','asc')->get();
        // $mainmenus = MainmenuDB::with('submenu')->where([['type','!=',2],['is_on',1]])->orderBy('sort','asc')->get();
        $poweractions = PowerActionDB::all();
        if(Auth::user()){
            $view->with('mainmenus', $mainmenus);
            $view->with('poweractions', $poweractions);
        }
    }
}
