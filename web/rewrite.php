<?php

/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 15/7/5
 * Time: 下午4:39
 */
final class kod_web_rewrite extends kod_tool_config
{
    public static function getArrByStr($str)
    {
        $splitStr = '/';
        $arr = explode($splitStr, $str);
        return $arr;
    }

    public static function getPageContent($confPath)
    {
        $file = fopen($confPath, "r") or exit("Unable to open file!");
        $lineList = array();
        $before = array();
        while (!feof($file)) {
            $oneLine = fgets($file);
            if (substr($oneLine, 0, 1) === '#' || preg_match('/^\s+$/', $oneLine)) {
                continue;
            }
            // 删除最后的换行
            $oneLine = preg_replace('/\n$/', "", $oneLine);
            // 计算锁进长度
            $oneLine = preg_replace('/    /', "\t", $oneLine);
            // 计算锁进位数
            $isHasTab = preg_match('/^\t+/', $oneLine, $match);
            $oneLine = preg_replace('/^\t+/', "", $oneLine);
            $oneLine = preg_replace('/\s+/', " ", $oneLine);

            $oneArr = explode(" ", $oneLine);
            if (count($oneArr) == 0) {
                continue;
            } else {
                if (count($oneArr) == 1) {// 是目录
                    if ($isHasTab) {
                        $before[] = $oneArr[0];
                    } else {
                        $before = array($oneArr[0]);
                    }
                    continue;
                } else {// 不是目录
                    if ($isHasTab) {
                        // 拼接上path
                        $before = array_slice($before, 0, strlen($match[0]));
                    } else {
                        // path清空
                        $before = array();
                    }
                    $oneArr[0] = implode('', $before) . $oneArr[0];
                    $lineList[] = $oneArr;
                }
            }
        }
        fclose($file);
        return $lineList;
    }
}
/*一组测试用例


# api首页
/                   /index.php
# 登陆

/login              /login.php
/userPortrait
    /list           /userPortrait.php?action=list
    /init           /userPortrait.php

# api首页
/mdd
    /list/(\d+)     /mdd.php?action=list&page=$1
    /info/(\d+)     /mdd.php?action=info&mddid=$1
    /myMatch        /mdd.php?action=myMatch
    /video/(\d+)    /video.php?action=list&mddid=$1
    /follow/(\d+)   /follow.php?type=mdd&id=$1
    /article/(\d+)  /article.php?mddid=$1    mddType
/article
    /detail/(\d+)   /article.php?action=detail&id=$1
    /myMatch        /article.php?action=myMatch
    /pv/(\d+)       /article.php?action=pv&id=$1
    /follow/(\d+)   /follow.php?type=article&id=$1

/service
    /list           /service.php
/common/conf        /conf.php
/video
    /pv/(\d+)       /video.php?action=pv&id=$1
/myFollow           /follow.php?type=myFollow

*/