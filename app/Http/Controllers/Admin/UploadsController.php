<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;
use File;
use Carbon\Carbon;
use Session;
use Spatie\Image\Image;
use ImageOptimizer;


class UploadsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 先經過 middleware 檢查
        $this->middleware('auth:admin');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showUploadForm()
    {
        $menuCode = 'M15S3';
        return view('admin.uploads.UploadForm',compact('menuCode'));
    }

    public function imageUpload(Request $request)
    {
        // 檢查確認是否可以連線AWS S3
        // $s3 = Storage::cloud();
        // dd($s3);

        if($request->hasFile('image')){
            $file = $request->file('image');
            $destPath = 'upload/test';

            if(!file_exists(public_path() . '/' . $destPath)){
                File::makeDirectory(public_path() . '/' . $destPath, 0755, true);
            }

            //原始尺寸及大小
            $ofileSize = filesize($file);
            $fileOd = getimagesize($file);
            $ofileDim = number_format($fileOd[0]).' x '.number_format($fileOd[1]);

            //抓取原始副檔名
            $ext = $file->getClientOriginalExtension();
            //新的檔案名稱
            $newFileName = Carbon::now()->timestamp;
            //新的檔案名稱包含副檔名
            $fileName = $newFileName . '.' . $ext;

            // $ext = $file->getClientOriginalExtension();

            // //檔案名稱修改成目前時間標記加上副檔名
            // $fileName = (Carbon::now()->timestamp) . '.' . $ext;

            //檢查S3目錄是否存在，不存在則建立
            if(!Storage::disk('s3')->has($destPath)){
                Storage::disk('s3')->makeDirectory($destPath);
            }
            //將檔案傳送至 S3
            //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
            Storage::disk('s3')->put("$destPath/$fileName", file_get_contents($file) , 'public');
            //獲取 S3 圖片連結
            $imgUrl = Storage::disk('s3')->url("$destPath/$fileName");

            //將檔案搬至public目錄
            $file->move(public_path() . '/' . $destPath, $fileName);

            $ssTime = microtime(true); //紀錄開始時間
            //使用Spatie/image的套件壓縮檔案
            Image::load($destPath.'/'.$fileName)
            ->width(1280)
            ->height(1024)
            ->save($destPath.'/'.$newFileName.'_Spatie.'.$ext);
            ImageOptimizer::optimize($destPath.'/'.$newFileName.'_Spatie.'.$ext);
            $seTime = microtime(true) - $ssTime;
            Storage::disk('s3')->put($destPath.'/'.$newFileName.'_Spatie.'.$ext, file_get_contents($destPath.'/'.$newFileName.'_Spatie.'.$ext) , 'public');
            $spatieSize = filesize($destPath.'/'.$newFileName.'_Spatie.'.$ext);

            $tsTime = microtime(true);
            //使用tinyjpg.com的API壓縮檔案
            \Tinify\setKey(env("TINIFY_KEY"));
            $source = \Tinify\fromFile($destPath.'/'.$fileName);
            $source->resize([
                "method" => "fit",
                "width" => 1280,
                "height" => 1024
            ])
            ->toFile($destPath.'/'.$newFileName.'_Tinify.'.$ext);
            // ->store([
            //     "service" => "s3",
            //     "aws_access_key_id" => env('AWS_ACCESS_KEY_ID'),
            //     "aws_secret_access_key" => env('AWS_SECRET_ACCESS_KEY'),
            //     "region" => env('AWS_S3_DEFAULT_REGION'),
            //     "headers" => array("Cache-Control" => "max-age=31536000, public"),
            //     "path" => env('AWS_BUCKET').'/'.$destPath.'/'.$newFileName.'_Tinify.'.$ext
            // ]);
            $teTime = microtime(true) - $tsTime;
            Storage::disk('s3')->put($destPath.'/'.$newFileName.'_Tinify.'.$ext, file_get_contents($destPath.'/'.$newFileName.'_Tinify.'.$ext) , 'public');
            $tinifySize = filesize($destPath.'/'.$newFileName.'_Tinify.'.$ext);
            $newFileOd = getimagesize($destPath.'/'.$newFileName.'_Tinify.'.$ext);
            $newFileDim = number_format($newFileOd[0]).' x '.number_format($newFileOd[1]);
        }

        unlink($destPath.'/'.$fileName);
        unlink($destPath.'/'.$newFileName.'_Spatie.'.$ext);
        unlink($destPath.'/'.$newFileName.'_Tinify.'.$ext);

        $message = "檔案上傳成功";
        Session::put('success',$message);

        return view('admin.uploads.UploadForm', compact('imgUrl','ext','ofileSize','ofileDim','newFileDim','seTime','teTime','spatieSize','tinifySize'));
    }


}
