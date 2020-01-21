<?php 
namespace Labspace\UploadApi\Services\Video;

/**
 * 影片介面
 *
 * @license MIT
 */

interface VideoInterface
{
    
    /**
     * 儲存檔案
     * @param $file_path 儲存絕對路徑
     * @param $show_path 回傳顯示的相對路徑
     * @return string
     */
    public function save($file_path,$show_path);


}
