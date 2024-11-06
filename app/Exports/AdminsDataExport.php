<?php

namespace App\Exports;

use App\Models\GateAdmin as AdminDB;
use App\Models\GateMainmenu as MainmenuDB;
use App\Models\GatePowerAction as PowerActionDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminsDataExport implements FromCollection,WithProperties,ShouldAutoSize,WithStrictNullComparison, WithHeadings, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $admins = AdminDB::with('passChange')->orderBy('id','desc')->get();
        $mainmenus = MainmenuDB::with('submenu')->where([['type',1],['is_on',1]])->orderBy('sort','asc')->get();
        $x = 0;
        foreach($mainmenus as $mainmenu){
            foreach($mainmenu->submenu as $submenu){
                if($submenu->is_on == 1){
                    $subPowerAction = explode(',',$submenu->power_action);
                    $subPower = [];
                    for($k=0;$k<count($subPowerAction);$k++){
                        $y = 0;
                        $powerAction = PowerActionDB::where('code',$subPowerAction[$k])->first();
                        if(!empty($powerAction)){
                            $subPower[$y]['code'] = $submenu->code.$subPowerAction[$k];
                            $subPower[$y]['name'] = $powerAction->name;
                            $y++;
                        }
                    }
                    $submenus[$x]['submenu'] = $submenu;
                    $submenus[$x]['subPower'] = $subPower;
                    $x++;
                }
            }
        }
        $i = 0;
        foreach($admins as $admin){
            $head = [
                $admin->name,
                $admin->account,
                $admin->email,
                $admin->created_at,
                $admin->off_time,
                !empty($admin->passChange) ? $admin->passChange->created_at : null,
                $admin->is_on == 1 ? '啟用' : '停用',
                ''
            ];
            $powers = explode(',',$admin->power);
            for($x=0;$x<count($submenus);$x++){
                $submenu = $submenus[$x]['submenu'];
                $subPower = $submenus[$x]['subPower'];
                $adminPower = null;
                for($j=0;$j<count($powers);$j++){
                    if($submenu->code == $powers[$j]){
                        $adminPower .= '查詢,';
                    }
                    for($k=0;$k<count($subPower);$k++){
                        if($powers[$j] == $subPower[$k]['code']){
                            $adminPower .= $subPower[$k]['name'].',';
                            break;
                        }
                    }
                }
                $power[$x] = rtrim($adminPower,',');
            }
            $data[$i] = array_merge($head,$power);
            $i++;
        }
        return collect($data);
    }

    public function headings(): array
    {
        $mainmenus = MainmenuDB::with('submenu')->where([['type',1],['is_on',1]])->orderBy('sort','asc')->get();
        $power1 = [
            '姓名',
            '帳號',
            '電子郵件',
            '建立日期',
            '停用日期',
            '密碼變更日期',
            '啟用狀態',
            ''
        ];
        foreach($mainmenus as $mainmenu){
            foreach($mainmenu->submenu as $submenu){
                $power2[] = $submenu->name;
            }
        }
        $heading = array_merge($power1,$power2);
        return $heading;
    }

    public function title(): string
    {
        return '後台管理員資料';
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry後台管理-系統管理員資料匯出',
            'description'    => 'iCarry後台管理-系統管理員資料匯出',
            'subject'        => 'iCarry後台管理-系統管理員資料匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
