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
        return array('type' => $type, 'data' => $data);
    } else {
        return array('type' => $type);
    }
}

$metaApi->codeMeta = array(
    'type' => 'window',
    'child' => array(
        D('phpBegin'),
        array(
            'type' => 'functionCall',
            'name' => 'ini_set',
            'property' => [Mstring('display_errors'), D('int', 1),]
        ),
        array(
            'type' => 'functionCall',
            'name' => 'error_reporting',
            'property' => array(
                array(
                    'type' => '^',
                    'object1' => D('E_ALL'),
                    'object2' => D('E_NOTICE')
                )
            )
        ),
        array(
            'type' => 'functionCall',
            'name' => 'date_default_timezone_set',
            'property' => [Mstring('PRC')]
        ),
    )
);
function getSingleStdin($title, $default)
{
    echo $title;
    if (!empty($default)) {
        echo "(" . $default . ")";
    }
    echo ":";
    $inputVal = trim(fgets(STDIN));
    if ($inputVal === '' && !empty($default)) {
        return $default;
    }
    return $inputVal;
}

$KOD_COMMENT_MYSQLDB_CHARSET = getSingleStdin('数据库编码', 'utf8');
$KOD_MYSQL_SERVER = getSingleStdin('mysql域名', '127.0.0.1');
$KOD_MYSQL_USER = getSingleStdin('mysql账号', 'root');
$KOD_MYSQL_PASSWORD = getSingleStdin('mysql密码', '');


$pdo = new PDO("mysql:host=" . $KOD_MYSQL_SERVER, $KOD_MYSQL_USER, $KOD_MYSQL_PASSWORD, array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "set names " . $KOD_COMMENT_MYSQLDB_CHARSET
));
$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$sth = $pdo->prepare('show databases');
$sth->setFetchMode(PDO::FETCH_ASSOC);
$sth->execute();
$databases = $sth->fetchAll();
if (empty($databases)) {
    exit;
}
foreach ($databases as $k => $v) {
    echo ($k + 1) . ':' . $v['Database'] . "\n";
}
echo "请选择默认数据库(写入数字序号)\n";
$handle = fopen("php://stdin", "r");
$index = fgets($handle);
$dbName = $databases[intval($index) - 1]['Database'];

$KOD_COMMENT_MYSQLDB = $dbName;
if (empty($KOD_COMMENT_MYSQLDB)) {
    exit;
}
$define = array(
    'webDIR' => array(
        'type' => '.',
        'object1' => array(
            'type' => 'functionCall',
            'name' => 'dirname',
            'property' => [D('__FILE__')]
        ),
        'object2' => D('string', '/' . getSingleStdin('业务代码所在文件夹', 'app') . '/'),
    ),
    'KOD_SMARTY_COMPILR_DIR' => array(
        'type' => '.',
        'object1' => array(
            'type' => 'functionCall',
            'name' => 'dirname',
            'property' => [D('__FILE__')]
        ),
        'object2' => Mstring('/' . getSingleStdin('smarty模板缓存所在文件夹', 'smarty_cache')),
    ),
    'KOD_MYSQL_SERVER' => Mstring($KOD_MYSQL_SERVER),
    'KOD_MYSQL_USER' => Mstring($KOD_MYSQL_USER),
    'KOD_MYSQL_PASSWORD' => Mstring($KOD_MYSQL_PASSWORD),
    'KOD_COMMENT_MYSQLDB' => Mstring($KOD_COMMENT_MYSQLDB),
);

if ($KOD_COMMENT_MYSQLDB_CHARSET !== 'utf8') {
    $define['KOD_MEMCACHE_OPEN'] = Mstring($KOD_COMMENT_MYSQLDB_CHARSET);
}
$isOpenMemcache = in_array(getSingleStdin('是否开启memcache', 'yes'), ['Y', 'y', 'yes']);
$define['KOD_MEMCACHE_OPEN'] = D('boolean', $isOpenMemcache);
if ($isOpenMemcache) {
    $define['KOD_MEMCACHE_TYPE'] = D(in_array(
        getSingleStdin('使用更为先进的memcached来取代memcache', 'yes'), ['Y', 'y', 'yes']
    ) ? 'KOD_MEMCACHE_TYPE_MEMCACHED' : 'KOD_MEMCACHE_TYPE_MEMCACHE');

    $define['KOD_MEMCACHE_HOST'] = D('string', getSingleStdin('memcache地址', 'localhost'));
    $define['KOD_MEMCACHE_PORT'] = D('string', getSingleStdin('memcache端口', '11211'));
}

foreach ($define as $k => $item) {
    $metaApi->codeMeta['child'][] = array(
        'type' => 'functionCall',
        'name' => 'define',
        'property' => array(
            D('string', $k),
            $item,
        )
    );
}
$metaApi->codeMeta['child'][] = array(
    'type' => 'function',
    'name' => 'kod_ControlAutoLoad',
    'property' => [D('variable', '$model')],
    'child' => [
        array(
            'type' => '=',
            'object1' => D('variable', '$classAutoLoad'),
            'object2' => D('array'),
        ),
        array(
            'type' => 'if',
            'value' => array(
                'type' => 'functionCall',
                'name' => 'isset',
                'property' => [array(
                    'type' => 'arrayGet',
                    'object' => D('variable', '$classAutoLoad'),
                    'key' => D('variable', '$model'),
                )]
            ),
            'child' => [array(
                'type' => 'functionCall',
                'name' => 'include_once',
                'property' => [array(
                    'type' => 'arrayGet',
                    'object' => D('variable', '$classAutoLoad'),
                    'key' => D('variable', '$model'),
                )]
            )]
        ),
        array(
            'type' => 'elseif',
            'value' => array(
                'type' => '&&',
                'object1' => array(
                    'type' => '===',
                    'object1' => array(
                        'type' => 'functionCall',
                        'name' => 'strpos',
                        'property' => [D('variable', '$model'), Mstring('kod_')]
                    ),
                    'object2' => D('boolean', false)
                ),
                'object2' => array(
                    'type' => '===',
                    'object1' => array(
                        'type' => 'functionCall',
                        'name' => 'strpos',
                        'property' => [D('variable', '$model'), Mstring('Smarty_')]
                    ),
                    'object2' => D('boolean', false)
                ),
            ),
            'child' => [array(
                'type' => 'if',
                'value' => array(
                    'type' => 'functionCall',
                    'name' => 'is_file',
                    'property' => [array(
                        'type' => '.',
                        'object1' => D('__DIR__'),
                        'object2' => array(
                            'type' => '.',
                            'object1' => Mstring('/include/'),
                            'object2' => array(
                                'type' => '.',
                                'object1' => D('variable', '$model'),
                                'object2' => Mstring('.php')
                            )
                        )
                    )]
                ),
                'child' => [array(
                    'type' => 'functionCall',
                    'name' => 'include_once',
                    'property' => [array(
                        'type' => '.',
                        'object1' => D('__DIR__'),
                        'object2' => array(
                            'type' => '.',
                            'object1' => Mstring('/include/'),
                            'object2' => array(
                                'type' => '.',
                                'object1' => D('variable', '$model'),
                                'object2' => Mstring('.php')
                            )
                        )
                    )]
                )]
            )]
        )
    ]
);
echo $metaApi->getCode();
file_put_contents('../../include.php', $metaApi->getCode());
//输出hello World的代码的结构，可以理解为下面的复合数组形式
$metaApi->codeMeta = array(
    'type' => 'window',
    'child' => array(
        D('phpBegin'),
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
file_put_contents('../../index.php', $metaApi->getCode());
exit;