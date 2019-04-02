<?php
/**
 * Created by PhpStorm.
 * User: wanghaoran
 * Date: 2019-03-29
 * Time: 16:11
 */
include_once('../../metaPHP/include.php');
$metaApi = new phpInterpreter('');

function Mstring($str, $borderStr = "'")
{
    return array(
        'type' => 'string',
        'borderStr' => $borderStr,
        'data' => $str
    );
}

function D($type, $data = null)
{
    if ($data !== null) {
        return array(
            'type' => $type,
            'data' => $data
        );
    } else {
        return array(
            'type' => $type
        );
    }
}

//输出hello World的代码的结构，可以理解为下面的复合数组形式
$metaApi->codeMeta = array(
    'type' => 'window',
    'child' => array(
        array(
            'type' => 'phpBegin'
        ),
        array(
            'type' => 'functionCall',
            'name' => 'include_once',
            'property' => array(
                D('string', 'include.php')
            )
        ),
        array(
            'type' => 'staticFunction',
            'object' => 'kod_web_rewrite',//类名
            'name' => 'init',//函数名
            'property' => array(
                array(
                    'type' => '.',
                    'object1' => array(
                        'type' => 'functionCall',
                        'name' => 'dirname',
                        'property' => array(
                            D('__FILE__')
                        )
                    ),
                    'object2' => D('string', '/rewrite.conf')
                ),
            )
        ),
        array(
            'type' => '=',
            'object1' => D('variable', '$result'),
            'object2' => array(
                'type' => 'staticFunction',
                'object' => 'kod_web_rewrite',//类名
                'name' => 'getPathByUrl',//函数名
                'property' => array(
                    array(
                        'type' => 'functionCall',
                        'name' => 'current',
                        'property' => array(
                            array(
                                'type' => 'functionCall',
                                'name' => 'dirname',
                                'property' => array(
                                    D('string', '?'),
                                    array(
                                        'type' => 'arrayGet',
                                        'object' => D('variable', '$_SERVER'),
                                        'key' => D('string', 'REQUEST_URI')
                                    )
                                )
                            )
                        ),
                    ),
                )
            ),
        ),
        array(
            'type' => 'functionCall',
            'name' => 'header',
            'property' => array(
                D('string', 'Access-Control-Allow-Origin: *')
            )
        ),
        array(
            'type' => 'functionCall',
            'name' => 'header',
            'property' => array(
                D('string', 'Access-Control-Allow-Credentials:true')
            )
        ),
        array(
            'type' => 'functionCall',
            'name' => 'header',
            'property' => array(
                D('string', 'Access-Control-Allow-Methods:OPTIONS, GET, POST, PUT, DELETE')
            )
        ),
        array(
            'type' => 'if',
            'value' => array(
                'type' => '===',
                'object1' => array(
                    'type' => 'arrayGet',
                    'object' => D('variable', '$_SERVER'),
                    'key' => D('string', 'REQUEST_URI')
                ),
                'object2' => D('string', 'OPTIONS')
            ),
            'child' => array(
                array(
                    'type' => 'functionCall',
                    'name' => 'header',
                    'property' => array(
                        D('string', 'Access-Control-Allow-Headers:Content-Type,XFILENAME,XFILECATEGORY,XFILESIZE')
                    )
                ),
                D('exit')
            )
        ),
        array(
            'type' => 'if',
            'value' => array(
                'type' => 'functionCall',
                'name' => 'empty',
                'property' => array(
                    D('variable', '$result')
                ),
            ),
            'child' => array(
                array(
                    'type' => '=',
                    'object1' => D('variable', '$new'),
                    'object2' => array(
                        'type' => 'functionCall',
                        'name' => 'parse_url',
                        'property' => array(
                            array(
                                'type' => 'arrayGet',
                                'object' => D('variable', '$result'),
                                'key' => D('int', '1')
                            )
                        )
                    ),
                ),
                array(
                    'type' => 'functionCall',
                    'name' => 'parse_str',
                    'property' => array(
                        array(
                            'type' => 'arrayGet',
                            'object' => D('variable', '$new'),
                            'key' => D('string', 'query')
                        ),
                        D('variable', '$myArray')
                    )
                ),
                array(
//                    $_GET = array_merge($_GET, $myArray);//后面盖住前面
                    'type' => '=',
                    'object1' => D('variable', '$_GET'),
                    'object2' => array(
                        'type' => 'functionCall',
                        'name' => 'array_merge',
                        'property' => array(
                            D('variable', '$_GET'),
                            D('variable', '$myArray')
                        )
                    ),
                ),
                array(
//                    $_SERVER["SCRIPT_URL"] = $new["path"];
                    'type' => '=',
                    'object1' => array(
                        'type' => 'arrayGet',
                        'object' => D('variable', '$_SERVER'),
                        'key' => D('string', 'SCRIPT_URL')
                    ),
                    'object2' => array(
                        'type' => 'arrayGet',
                        'object' => D('variable', '$new'),
                        'key' => D('string', 'path')
                    ),
                ),
                array(
                    //$_SERVER["SCRIPT_URI"] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER["HTTP_HOST"] . '/' . $new["path"];
                    'type' => '=',
                    'object1' => array(
                        'type' => 'arrayGet',
                        'object' => D('variable', '$_SERVER'),
                        'key' => D('string', 'SCRIPT_URI')
                    ),
                    'object2' => array(
                        'type' => '.',
                        'object1' => array(
                            'type' => 'arrayGet',
                            'object' => D('variable', '$_SERVER'),
                            'key' => D('string', 'REQUEST_SCHEME')
                        ),
                        'object2' => D('string', '://'),
                        'object3' => array(
                            'type' => 'arrayGet',
                            'object' => D('variable', '$_SERVER'),
                            'key' => D('string', 'HTTP_HOST')
                        ),
                        'object4' => D('string', '/'),
                        'object5' => array(
                            'type' => 'arrayGet',
                            'object' => D('variable', '$new'),
                            'key' => D('string', 'path')
                        ),
                    ),
                ),
                array(
                    'type' => 'if',
                    'value' => array(
                        'type' => '!',
                        'value' => array(
                            'type' => 'functionCall',
                            'name' => 'empty',
                            'property' => array(
                                array(
                                    'type' => 'arrayGet',
                                    'object' => D('variable', '$new'),
                                    'key' => D('string', 'query')
                                )
                            ),
                        ),
                    ),
                    'child' => array(
                        //$_SERVER["REQUEST_URI"] = $new["path"] . "?" . $new["query"];
                        array(
                            'type' => '=',
                            'object1' => array(
                                'type' => 'arrayGet',
                                'object' => D('variable', '$_SERVER'),
                                'key' => D('string', 'REQUEST_URI')
                            ),
                            'object2' => array(
                                'type' => '.',
                                'object1' => array(
                                    'type' => 'arrayGet',
                                    'object' => D('variable', '$new'),
                                    'key' => D('string', 'path')
                                ),
                                'object2' => D('string', '?'),
                                'object3' => array(
                                    'type' => 'arrayGet',
                                    'object' => D('variable', '$new'),
                                    'key' => D('string', 'query')
                                ),
                            )
                        )
                    )
                ),
                array(
                    'type' => 'else',
                    'child' => array(
                        array(
                            'type' => '=',
                            'object1' => array(
                                'type' => 'arrayGet',
                                'object' => D('variable', '$_SERVER'),
                                'key' => D('string', 'REQUEST_URI')
                            ),
                            'object2' => array(
                                'type' => 'arrayGet',
                                'object' => D('variable', '$new'),
                                'key' => D('string', 'path')
                            )
                        )
                    )
                ),
                array(
                    'type' => '=',
                    'object1' => array(
                        'type' => 'arrayGet',
                        'object' => D('variable', '$_SERVER'),
                        'key' => D('string', 'SCRIPT_FILENAME')
                    ),
                    'object2' => array(
                        'type' => 'arrayGet',
                        'object' => D('variable', '$new'),
                        'key' => D('string', 'path')
                    )
                ),
                array(
                    'type' => '=',
                    'object1' => array(
                        'type' => 'arrayGet',
                        'object' => D('variable', '$_SERVER'),
                        'key' => D('string', 'SCRIPT_NAME')
                    ),
                    'object2' => array(
                        'type' => 'arrayGet',
                        'object' => D('variable', '$new'),
                        'key' => D('string', 'path')
                    )
                ),
                array(
                    'type' => '=',
                    'object1' => array(
                        'type' => 'arrayGet',
                        'object' => D('variable', '$_SERVER'),
                        'key' => D('string', 'PHP_SELF')
                    ),
                    'object2' => array(
                        'type' => 'arrayGet',
                        'object' => D('variable', '$new'),
                        'key' => D('string', 'path')
                    )
                ),
                array(
                    'type' => 'functionCall',
                    'name' => 'unset',
                    'property' => array(
                        D('variable', '$new')
                    )
                ),
                array(
                    'type' => 'functionCall',
                    'name' => 'chdir',
                    'property' => array(
                        D('string', './app/')
                    )
                ),
                array(
                    'type' => 'if',
                    'value' => array(
                        'type' => 'functionCall',
                        'name' => 'substr',
                        'property' => array(
                            array(
                                'type' => 'arrayGet',
                                'object' => D('variable', '$_SERVER'),
                                'key' => D('string', 'SCRIPT_FILENAME')
                            ),
                            array(
                                'type' => 'functionCall',
                                'name' => 'strlen',
                                'property' => array(
                                    array(
                                        'type' => 'arrayGet',
                                        'object' => D('variable', '$_SERVER'),
                                        'key' => D('string', 'SCRIPT_FILENAME')
                                    )
                                )
                            ),
                        ),
                    ),
                    'child' => array(
                        array(
                            'type' => 'functionCall',
                            'name' => 'include_once',
                            'property' => array(
                                array(
                                    'type' => '.',
                                    'object1' => array(
                                        'type' => 'arrayGet',
                                        'object' => D('variable', '$_SERVER'),
                                        'key' => D('string', 'SCRIPT_FILENAME')
                                    ),
                                    'object2' => D('string', 'index.php')
                                )
                            )
                        )
                    )
                ),
                array(
                    'type' => 'else',
                    'child' => array(
                        array(
                            'type' => 'if',
                            'value' => array(
                                'type' => '===',
                                'object1' => array(
                                    'type' => 'functionCall',
                                    'name' => 'substr',
                                    'property' => array(
                                        array(
                                            'type' => 'arrayGet',
                                            'object' => D('variable', '$_SERVER'),
                                            'key' => D('string', 'SCRIPT_FILENAME')
                                        ),
                                        D('int', 0),
                                        D('int', 1)
                                    )
                                ),
                                'object2' => D('string', '/')
                            ),
                            'child' => array(
                                array(
                                    'type' => 'functionCall',
                                    'name' => 'include_once',
                                    'property' => array(
                                        array(
                                            'type' => '.',
                                            'object1' => D('string', '.'),
                                            'object2' => array(
                                                'type' => 'arrayGet',
                                                'object' => D('variable', '$_SERVER'),
                                                'key' => D('string', 'SCRIPT_FILENAME')
                                            )
                                        )
                                    )
                                )
                            )
                        ),
                        array(
                            'type' => 'else',
                            'child' => array(
                                array(
                                    'type' => 'functionCall',
                                    'name' => 'include_once',
                                    'property' => array(
                                        array(
                                            'type' => 'arrayGet',
                                            'object' => D('variable', '$_SERVER'),
                                            'key' => D('string', 'SCRIPT_FILENAME')
                                        )
                                    )
                                )
                            )
                        )
                    )
                ),
                array(
                    'type' => 'if',
                    'value' => array(
                        'type' => 'functionCall',
                        'name' => 'isset',
                        'property' => array(
                            array(
                                'type' => 'arrayGet',
                                'object' => D('variable', '$result'),
                                'key' => D('int', 2)
                            )
                        )
                    ),
                    'child' => array(
                        array(
                            'type' => 'objectFunction',
                            'object' => array(
                                'type' => 'staticFunction',
                                'object' => 'restApi',
                                'name' => 'getinstance',
                                'property' => array(
                                    array(
                                        'type' => 'arrayGet',
                                        'object' => D('variable', '$result'),
                                        'key' => D('int', 2)
                                    )
                                )
                            ),
                            'name' => 'lastExit'
                        ),

                    )
                ),
                array(
                    'type' => 'else',
                    'child' => array(
                        array(
                            'type' => 'objectFunction',
                            'object' => array(
                                'type' => 'staticFunction',
                                'object' => 'restApi',
                                'name' => 'getinstance',
                            ),
                            'name' => 'lastExit'
                        ),
                    )
                ),
                array(
                    'type' => 'functionCall',
                    'name' => 'unset',
                    'property' => array(
                        D('variable', '$result')
                    )
                ),
            )
        ),
        array(
            'type' => 'else',
            'child' => array(
                array(
                    'type' => 'functionCall',
                    'name' => 'header',
                    'property' => array(
                        D('string', 'HTTP/1.1 404 Not Found')
                    ),
                ),
                array(
                    'type' => 'functionCall',
                    'name' => 'echo',
                    'property' => array(
                        D('string', '404')
                    ),
                ),
                D('exit')
            )
        )
    ),
);
echo $metaApi->getCode();
//126
exit;