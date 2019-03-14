<?php

/**
 * 数组工具集,不依赖任何其他类的算法糖
 * User: mfw
 * Date: 16/7/20
 * Time: 上午7:49
 */
abstract class kod_tool_lifeCycle
{
    private $events = array();
    protected $stage = [];
    private $isBreakStage = false;
    private $isBreakAll = false;

    public function bind(string $type, $actionObj)
    {
        if ($this->events[$type] === null) {
            $this->events[$type] = array();
        }
        $this->events[$type][] = $actionObj;
    }

    public function actionStage($type, $initData)
    {
        $stepData = $initData;
        if ($this->events[$type]) {
            foreach ($this->events[$type] as $item) {
                $stepData = $item($stepData);
                if ($this->isBreakStage || $this->isBreakAll) {
                    return $stepData;
                }
            }
        }
        return $stepData;
    }

    public function breakStage()
    {
        $this->isBreakStage = true;
    }

    public function breakAll()
    {
        $this->isBreakAll = true;
    }

    public function action()
    {
        $step = array();
        foreach ($this->stage as $stageName) {
            $step = $this->actionStage($stageName, $step);
            if ($this->isBreakAll) {
                return $step;
            }
        }
        return $step;
    }
}
/*
 * demo

class live extends kod_tool_lifeCycle
{
    // array 准备缓解
    // sql 处理环节
    // data 处理环节
    public $stage = ['begin', 'center', 'end'];

    public function action($initData)
    {
        $step = $this->actionStage('begin', $initData);
        // 进入下一个阶段前，对step进行加工
        $step = $this->actionStage('center', $step);
        return $step;
    }
}

$a = new live();
$a->bind('center', function ($arr) {
    return 888;
});
$a->bind('begin', function ($arr) {
    return 1;
});
$a->bind('begin', function ($arr) {
    return $arr + 1;
});
var_dump($a->action(array()));
exit;
*/