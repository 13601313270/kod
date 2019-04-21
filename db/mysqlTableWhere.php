<?php

/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 16/5/9
 * Time: 下午8:10
 */

function kod_and()
{
    $return = array(
        'and' => array()
    );
    foreach (func_get_args() as $arr) {
        if ($arr['or'] || $arr['and']) {
            $return['and'][] = $arr;
        } else {
            foreach ($arr as $k => $v) {
                $list = explode(' ', $k);
                if (in_array($list[1], array('like'))) {
                    $return['and'][] = [$list[0], $list[1], $v];
                } else if (in_array(substr($k, -2), array('>=', '<=', '<>'))) {
                    $return['and'][] = [substr($k, 0, -2), substr($k, -2), $v];
                } else if (in_array(substr($k, -1), array('>', '<'))) {
                    $return['and'][] = [substr($k, 0, -1), substr($k, -1), $v];
                } else {
                    $return['and'][] = [$k, '=', $v];
                }
            }
        }

    }
    return $return;
}

function kod_or()
{
    $return = array(
        'or' => array()
    );
    foreach (func_get_args() as $arr) {
        if ($arr['or'] || $arr['and']) {
            $return['or'][] = $arr;
        } else {
            foreach ($arr as $k => $v) {
                $list = explode(' ', $k);
                if (in_array($list[1], array('like'))) {
                    $return['or'][] = [$list[0], $list[1], $v];
                } else if (in_array(substr($k, -2), array('>=', '<=', '<>'))) {
                    $return['or'][] = [substr($k, 0, -2), substr($k, -2), $v];
                } else if (in_array(substr($k, -1), array('>', '<'))) {
                    $return['or'][] = [substr($k, 0, -1), substr($k, -1), $v];
                } else {
                    $return['or'][] = [$k, '=', $v];
                }
            }
        }
    }
    return $return;
}
