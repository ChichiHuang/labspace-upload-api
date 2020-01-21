<?php 
namespace Labspace\UploadApi\Services\Image;

/**
 * 圖片介面
 *
 * @license MIT
 */

interface ImageInterface
{
    
    /**
     * 儲存檔案
     * @param $file_path 儲存絕對路徑
     * @param $show_path 回傳顯示的相對路徑
     * @param $width 寬度
     * @return string
     */
    public function save($file_path,$show_path,$width);


}
