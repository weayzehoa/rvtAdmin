<?php
namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Auth;

use App\Models\Mainmenu as MainmenuDB;
use App\Models\Submenu as SubmenuDB;
use App\Models\PowerAction as PowerActionDB;
use App\Models\ServiceMessage as ServiceMessageDB;


class GateIndexComposer
{
    public function compose(View $view){
        $mainmenus = MainmenuDB::with('submenu')->where(['type' => 3, 'is_on' => 1])->orderBy('sort','asc')->get();
        $poweractions = PowerActionDB::all();
        if(Auth::user()){
            $view->with('mainmenus', $mainmenus);
            $view->with('poweractions', $poweractions);
        }
    }
}
