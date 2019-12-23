<?php
/**
 * Created by PhpStorm.
 * User: wanghaoran
 * Date: 2019-12-16
 * Time: 16:29
 */
function dir_size($dir)
{
    $dh = @opendir($dir); // 打开目录，返回一个目录流
    $return = array();
    while ($file = @readdir($dh)) { // 循环读取目录下的文件
        if ($file != '.' and $file != '..') {
            $path = $dir . '/' . $file; // 设置目录，用于含有子目录的情况
            if (is_dir($path)) {
            } elseif (is_file($path)) {
                $filetime[] = date("Y-m-d H:i:s", filemtime($path)); // 获取文件最近修改日期
                $return[$file] = filemtime($path);
            }
        }
    }
    @closedir($dh); // 关闭目录流
    array_multisort($filetime, SORT_DESC, SORT_STRING, $return);//按时间排序
    return $return; // 返回文件
}
