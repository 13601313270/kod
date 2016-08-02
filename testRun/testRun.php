<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 14/11/2
 * Time: 下午9:46
 */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
include_once("../include.php");
//=================================2014/11/17日===============================================
//=================================kod_db_mysqlSingle类的getList方法测试================
/*
class subjectHandle extends kod_db_mysqlSingle{
	protected $tableName = "subject";
	public function getAllSubject(){
		$this->sql();
		//return parent::getList(array());//""
		//return parent::getList("");//""

		//return $this->getList("id=0");//select * from subject where id=0
		//return $this->onlyColumn("id,name")->getList("id=0");//select id,name from subject where id=0

		//return parent::getList("select * from subject limit 2");//"select * from subject limit 2"

		//return $this->getList("id>0 limit 2");//"select * from subject where id>0 limit 2"
		//return $this->onlyColumn("id,name")->getList("id>0 limit 2");//select id,name from subject where id>0 limit 2

		//return $this->getList(array(
		//	"select"=>"id,name,isHot",
		//));
		//select id,name,isHot from subject

		//return $this->onlyColumn("id,name")->getList(array(
		//	"select"=>"id,name,isHot",
		//));
		//select id,name from subject

		//return $this->getList(array(
		//	"where"=>"id>0",
		//));
		//select * from subject where id>0

		//return $this->onlyColumn("id,name")->getList(array(
		//	"where"=>"id>0",
		//));
		//select id,name from subject where id>0

		//return $this->getList(array(
		//	"id"=>0,
		//));
		//select * from subject where id=0

		//return $this->onlyColumn("id,name")->getList(array(
		//	"id"=>0,
		//));
		//select id,name from subject where id=0

		//return $this->getList(array(
		//	"where"=>array(
		//		"id"=>1,
		//	),
		//));
		//select * from subject where id=1
		//return $this->onlyColumn("id,name")->getList(array(
		//	"where"=>array(
		//		"id"=>1,
		//	),
		//));
		//select id,name from subject where id=1

		//return $this->getList(array(
		//	"where"=>array(
		//		"id>1",
		//	),
		//));
		//select * from subject where id>1
		//return $this->onlyColumn("id,name")->getList(array(
		//	"where"=>array(
		//		"id>1",
		//	),
		//));
		//select id,name from subject where id>1

		//return $this->getList(array(
		//	"select"=>"name,isHot",
		//	"id"=>"0",
		//));
		//select name,isHot from subject where id=0
		//return $this->onlyColumn("id,name")->getList(array(
		//	"select"=>"name,isHot",
		//	"id"=>"0",
		//));
		//select name from subject where id=0

		//return $this->getList(array(
		//	"select"=>array("name","isHot"),
		//));
		//select name,isHot from subject
		//return $this->onlyColumn("id,name")->getList(array(
		//	"select"=>array("name","isHot"),
		//));
		//select name from subject

		//return $this->getList(array(
		//	"id>0","isHot=1",
		//));
		//select * from subject where id>0 and isHot=1
		//return $this->onlyColumn("id,name")->getList(array(
		//	"id>0","isHot=1",
		//));
		//select id,name from subject where id>0 and isHot=1
	}
}
echo subjectHandle::create()->getAllSubject();exit;
*/
//=================================2014/12/14日===============================================
//=================================测试smarty的植入============================================
/*
class testSmarty extends kod_web_httpObject{
	protected $smartyTpl='testSmarty.tpl';
	public function initSmarty($smartyObject){
		$smartyObject->compile_dir = KOD_DIR_NAME."/smarty/templates_c";
	}
	public function main($id=30,$jj){
		$this->assign("aaa",$id);
	}
}
$temp = new testSmarty();
$temp->run();
/*
/*
class testSmarty extends kod_web_httpObject{
	protected $smartyTpl='testSmart.tpl';
	public function initSmarty($smartyObject){
		$smartyObject->compile_dir = KOD_DIR_NAME."/smarty/templates_c";
	}
	public function main($id=30,$jj){
	}
}
$temp = new testSmarty();
$temp->run();
*/
//【testSmarty】类设置的smartyTpl属性值【testSmart.tpl】，文件在路径【/home/www/kod/smarty/templates_c/】里无法找到
/*
class testSmarty extends kod_web_httpObject{
	protected $smartyTpl='testSmarty.tpl';
	public function initSmarty($smartyObject){
		$smartyObject->compile_dir = KOD_DIR_NAME."/smarty/templates_c2";
	}
	public function main($id=30,$jj){
	}
}
$temp = new testSmarty();
$temp->run();
*/
//无法写入文件 /home/www/kod/smarty/templates_c2/wrt549ea965bb5996_00179678，请检查文件夹路径必须存在，且有写入的权限
/*
class testSmarty extends kod_web_httpObject{
	protected $smartyTpl='testSmarty.tpl';
	//public function initSmarty($smartyObject){
		//$smartyObject->compile_dir = KOD_DIR_NAME."/smarty/templates_c";
	//}
	public function main($id=30,$jj){
	}
}
$temp = new testSmarty();
$temp->run();
*/
//smarty无法对预编译文件进行存储，请尝试在【testSmarty】类中定义一个initSmarty方法，initSmarty第一个参数是关联的smarty的对象，对这个参数传入的smarty对象赋予compile_dir属性值。也可以在kod框架加载文件中配置变量【KOD_SMARTY_COMPILR_DIR】作为默认值
/*
class testSmarty extends kod_web_httpObject{
	public function main($id=30,$jj){
	}
}
$temp = new testSmarty();
$temp->run();
*/
//请给【testSmarty】类设置smartyTpl属性用来指定对应tpl文件

//=================================2014/11/17日===============================================
//=================================kod_db_mysqlSingle类update方法throw报错的测试================
/*
class userTable extends kod_db_mysqlSingle{
	protected $dbName = "user";
	protected $tableName = "user";
}
$userTableHandle = new userTable();
$return = $userTableHandle->sql()->update("1",array(
	"age"=>2,
));
print_r($return);
*/
//=================================2014/11/5日===============================================
//=================================kod_db_mysqlSingle类throw报错的测试========================
//kod_db_mysqlSingle用来定义针对单表的操作，在操作单表的时候比起kod_db_mysqlDB更为简化，不用在自己拼接sql语句。
//kod_db_mysqlSingle是一个抽象类，必须派生出对应表
/*
class userTable extends kod_db_mysqlSingle{
	protected $dbName = "user";
	protected $tableName = "user";
}
$userTableHandle = new userTable();
$result = $userTableHandle->insert(array(
	"username"=>"asdf2",
	"password"=>"ffff",
	"age"=>"20",
));
//向表【user】插入数据时【name】字段为必填字段
*/

/*
$result = $userTableHandle->insert(array(
	"username"=>"asdf1",
	"password"=>"ffff",
	"age"=>"sdf",
));
//向表【user】插入数据时,字段【age】的值【sdf】必须是整形
*/

/*
$result = $userTableHandle->insert(array(
	"username"=>"asdf1",
	"password"=>"ffff",
	"age2"=>"sdf",
));
//向表【user】插入数据时，传入的字段【age2】在表结构中不存在
*/

//插入一条已经存在的asdf5
/*
$result = $userTableHandle->insert(array(
	"username"=>"asdf5",
	"password"=>"ffff",
));
//向表【user】插入数据时，主键【username】值为【asdf5】的数据已经存在
echo $result;exit;
*/

//=================================2014/11/4日===============================================
//=================================kod_db_mysqlDB类runsql方法错误提醒监测======================
//正确的一次请求
//$data = kod_db_mysqlDB::create("user")->runsql("select * from user");
//print_r($data);

//kod_db_mysqlDB::create("user2")->runsql("select * from user");
//数据库【user2】不存在

//kod_db_mysqlDB::create("user")->runsql("select username,password from user2");
//数据库【user】不包含表【user2】

//kod_db_mysqlDB::create("user")->runsql("select username,password3 from user");
//字段【password3】在表【user】中不存在，请检查查询语句【select username,password3 from user】

//kod_db_mysqlDB::create("user")->runsql('delete from user2 where username2="wanghaoran"');
//数据库【user】不包含表【user2】

//kod_db_mysqlDB::create("user")->runsql('delete form user where username2="wanghaoran"');
//'sql语句规范不合法，必须符合【delete from 表名】的格式，检查from是否拼错

//kod_db_mysqlDB::create("user")->runsql('delete from user where username2="wanghaoran"');
//删除语句条件【username2="wanghaoran"】存在问题，请检查查询语句【delete from user where username2="wanghaoran"】的条件部分

//kod_db_mysqlDB::create("user")->runsql('insert into user2 where username2="wanghaoran"');
//数据库【user】不包含表【user2】

//kod_db_mysqlDB::create("user")->runsql('insert into user where username2="wanghaoran"');
//kod_db_mysqlDB::create("user")->runsql('update into from user where username2="wanghaoran"');
//exit;


//=================================2014/11/2日===============================================
//=================================kod_db_mysqlDB类基础功能测试===============================
//测试数据库
//$dbHand = new kod_db_mysqlDB("user");
//$dbHand = new kod_db_mysqlDB();//如果没有写库名称，则使用的是默认数据库，全局变量KOD_COMMENT_MYSQLDB。在kod/include.php定义。

//$dbHand = kod_db_mysqlDB::create("库名称");//也可以使用类方法create来创建。等同于new方法。
//$dbHand = kod_db_mysqlDB::create();//与new方法同理，没有传入库名称则为默认数据库

//create方法提供了更方便的链式操作，更加方便，也不在需要中间变量的定义。
//$dataOfMysql = kod_db_mysqlDB::create("库名称")->runsql("select * from user");

//kod_db_mysqlDB类runsql方法错误提醒监测
//$data = $dbHand->runsql("select username,password from user");

//$data = $dbHand->runsql('delete from user where username="wanghaoran"');
//print_r($data);