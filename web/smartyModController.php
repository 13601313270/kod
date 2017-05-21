<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/5/21
 * Time: 下午11:19
 */
abstract class kod_web_smartyModController{
    public $assignValueList = array();
    abstract function init($aData);
    abstract function finish($aData);
    public function assign($k,$v){
        $this->assignValueList[$k] = $v;
    }
}