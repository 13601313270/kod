<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 15/5/11
 * Time: 下午10:07
 */
abstract class kod_web_mysqlAdmin extends kod_web_httpObject{
	protected $tableName;
	protected $dbHandle;
	protected $pagePer = 10;
	protected $dbColumn = array();
	protected $deleteOpenAllPageData = false;//是否支持删除全部分页
	protected $updateOpenAllPageData = false;//是否支持批量替换
	/**
	 * getMysqlDbHandle
	 * 返回数据库操作句柄
	 *
	 * @access public
	 * @since 1.0
	 * @return kod_db_mysqlSingle
	 */
	abstract function getMysqlDbHandle();
	public function getAdminHtml($option){
		if(empty($option)){
			$dbHandle = $this->getMysqlDbHandle();
			$tableInfo = $dbHandle->showCreateTable();
			$tableInfo = $tableInfo["Create Table"];
			preg_match("/CREATE TABLE `\S+` \(\n((.+,?\n)+)\)/",$tableInfo,$match);
			$tableInfo = $match[1];
			$tableInfo = explode(",\n",$tableInfo);
			print_r($tableInfo);
			$option = array();
			foreach($tableInfo as $k=>$v){
				if(preg_match("/`(\S+)` (int|smallint|varchar|tinyint|char)\((\d+)\)( NOT NULL| DEFAULT NULL)?( DEFAULT '(\S+)'| AUTO_INCREMENT)?( COMMENT '(\S+)')?/",$v,$match)){
					$option[$match[1]] = array(
						"dataType"=>$match[2],
						"maxLength"=>$match[3],
						"notNull"=>!empty($match[4]),
						"title"=>empty($match[8])?$match[1]:$match[8],
					);
					if(!empty($match[5]) && $match[5]==" AUTO_INCREMENT"){
						$option[$match[1]]["AUTO_INCREMENT"] = true;
					}
				}elseif(preg_match("/`(\S+)` (text|date)( NOT NULL| DEFAULT NULL)?( DEFAULT '(\S+)'| AUTO_INCREMENT)?( COMMENT '(\S+)')?/",$v,$match)){
					$option[$match[1]] = array(
						'dataType'=>$match[2],
						'notNull'=>!empty($match[3]),
						'title'=>empty($match[7])?$match[1]:$match[7],
					);
				}elseif(  preg_match("/`(\S+)` timestamp( NOT NULL| DEFAULT NULL)( DEFAULT CURRENT_TIMESTAMP)?( ON UPDATE CURRENT_TIMESTAMP)?( COMMENT '(\S+)')?/",$v,$match)  ){
					$option[$match[1]] = array(
						"dataType"=>'date',
						"notNull"=>!empty($match[2]),
						"title"=>"",
					);
				}
			}
			echo 'getAdminHtml方法传入的$option参数变量不能为空，请尝试传入下面的变量'."<br/>";
			echo "array(<br/>";
			$tab = '&nbsp;&nbsp;&nbsp;&nbsp;';
			foreach($option as $k=>$v){
				if(in_array(strtoupper($k),array("ADD","ALL","ALTER","ANALYZE","AND","AS","ASC","ASENSITIVE","BEFORE","BETWEEN","BIGINT","BINARY","BLOB",
					"BOTH","BY","CALL","CASCADE","CASE","CHANGE","CHAR","CHARACTER","CHECK","COLLATE","COLUMN","CONDITION","CONNECTION","CONSTRAINT",
					"CONTINUE","CONVERT","CREATE","CROSS","CURRENT_DATE","CURRENT_TIME","CURRENT_TIMESTAMP","CURRENT_USER","CURSOR","DATABASE",
					"DATABASES","DAY_HOUR","DAY_MICROSECOND","DAY_MINUTE","DAY_SECOND","DEC","DECIMAL","DECLARE","DEFAULT","DELAYED","DELETE",
					"DESC","DESCRIBE","DETERMINISTIC","DISTINCT","DISTINCTROW","DIV","DOUBLE","DROP","DUAL","EACH","ELSE","ELSEIF","ENCLOSED",
					"ESCAPED","EXISTS","EXIT","EXPLAIN","FALSE","FETCH","FLOAT","FLOAT4","FLOAT8","FOR","FORCE","FOREIGN","FROM","FULLTEXT","GOTO",
					"GRANT","GROUP","HAVING","HIGH_PRIORITY","HOUR_MICROSECOND","HOUR_MINUTE","HOUR_SECOND","IF","IGNORE","IN","INDEX","INFILE",
					"INNER","INOUT","INSENSITIVE","INSERT","INT","INT1","INT2","INT3","INT4","INT8","INTEGER","INTERVAL","INTO","IS","ITERATE",
					"JOIN","KEY","KEYS","KILL","LABEL","LEADING","LEAVE","LEFT","LIKE","LIMIT","LINEAR","LINES","LOAD","LOCALTIME","LOCALTIMESTAMP",
					"LOCK","LONG","LONGBLOB","LONGTEXT","LOOP","LOW_PRIORITY","MATCH","MEDIUMBLOB","MEDIUMINT","MEDIUMTEXT","MIDDLEINT",
					"MINUTE_MICROSECOND","MINUTE_SECOND","MOD","MODIFIES","NATURAL","NOT","NO_WRITE_TO_BINLOG","NULL","NUMERIC","ON","OPTIMIZE",
					"OPTION","OPTIONALLY","OR","ORDER","OUT","OUTER","OUTFILE","PRECISION","PRIMARY","PROCEDURE","PURGE","RAID0","RANGE","READ",
					"READS","REAL","REFERENCES","REGEXP","RELEASE","RENAME","REPEAT","REPLACE","REQUIRE","RESTRICT","RETURN","REVOKE","RIGHT","RLIKE",
					"SCHEMA","SCHEMAS","SECOND_MICROSECOND","SELECT","SENSITIVE","SEPARATOR","SET","SHOW","SMALLINT","SPATIAL","SPECIFIC","SQL",
					"SQLEXCEPTION","SQLSTATE","SQLWARNING","SQL_BIG_RESULT","SQL_CALC_FOUND_ROWS","SQL_SMALL_RESULT","SSL","STARTING","STRAIGHT_JOIN",
					"TABLE","TERMINATED","THEN","TINYBLOB","TINYINT","TINYTEXT","TO","TRAILING","TRIGGER","TRUE","UNDO","UNION","UNIQUE","UNLOCK",
					"UNSIGNED","UPDATE","USAGE","USE","USING","UTC_DATE","UTC_TIME","UTC_TIMESTAMP","VALUES","VARBINARY","VARCHAR","VARCHARACTER",
					"VARYING","WHEN","WHERE","WHILE","WITH","WRITE","X509","XOR","YEAR_MONTH","ZEROFILL"))){
					throw new Exception("表".$this->tableName."中使用了关键词【".$k."】作为字段，在kod系统中是禁止的");
				}
				echo $tab."'".$k."'=>array("."<br/>";
				echo $tab.$tab."'title'=>'".$v['title']."',<br/>";
				echo $tab.$tab."'dataType'=>'".$v['dataType']."',<br/>";
				if(!in_array($v["dataType"],array('int','tinyint','text','date'))){
					echo $tab.$tab."'maxLength'=>".$v['maxLength'].",<br/>";
				}
				echo $tab.$tab."'notNull'=>".($v['notNull']?'true':'false').",<br/>";
				if(isset($v['AUTO_INCREMENT'])){
					echo $tab.$tab."'AUTO_INCREMENT'=>".($v['AUTO_INCREMENT']?'true':'false').",<br/>";
				}
				echo $tab.'),'.'<br/>';
			}
			echo ")";
			exit;
		}else{
			$smarty = new Smarty();
			$allColumnDataType = array();
			foreach($option as $k=>$v){
				//对配置的语法糖进行处理
				if($v['listsearch'] && is_string($v['listsearch'])){
					$option[$k]['listsearch'] = array(
						array(
							'match'=>$v['listsearch']
						),
					);
				}elseif($v['listsearch']['match']){//就一个的情况下,允许不用建二维数组,而直接定义match
					$option[$k]['listsearch'] = array(
						$v['listsearch']
					);
				}else{
					if(count($v['listsearch'])>0) {
						foreach ($v['listsearch'] as $kk => $vv) {
							if (is_string($vv)) {
								$option[$k]['listsearch'][$kk] = array(
										'match' => $vv,
								);
							}
						}
					}
				}
				if(is_string($v['dataType'])){
					$allColumnDataType[$v['dataType']] = true;
				}
			}
			$allColumnDataType = array_keys($allColumnDataType);
			$smarty->assign('deleteOpenAllPageData',$this->deleteOpenAllPageData);
			$smarty->assign('allColumnDataType',$allColumnDataType);
			$smarty->assign('column',$option);
			$getMysqlDbHandle = $this->getMysqlDbHandle();
			$smarty->assign('column',$option);
			$smarty->assign('key',$getMysqlDbHandle->getKeyColumnName());

			$smarty->assign('perPage',$this->pagePer);
			$this->initSmarty($smarty);
			$adminhtml = $smarty->fetch(KOD_DIR_NAME.'/web/mysqlAdmin.tpl');
			return $adminhtml;
		}
	}
	public function getList($page=0,$searchArr=array()){
		$searchArr = array(
			'where'=>array_values($searchArr)
		);
		$dbHandle = $this->getMysqlDbHandle();
		/*
		$searchArr = array(
			'where'=>'id>0'
		);
		*/
		//获取总数
		$countSearchArr = $searchArr;
		$countSearchArr['select'] = 'count(*) as count';
		$dataCount = $dbHandle->getList($countSearchArr);

		//获取数据
		$select = array();
		foreach($this->dbColumn as $key=>$column){
			if($column['listShowType']!='hidden' || $key==$dbHandle->getKeyColumnName()){
				$select[] = $key;
			}
		}
		$searchArr['select'] = implode(',',$select);
		$searchArr['limit'] = ($page*$this->pagePer).','.$this->pagePer;
		$dataList = $dbHandle->getList($searchArr);
		$dataHtml = [];
		foreach($dataList as $k=>$data){
			foreach($data as $columnKey=>$columnVal){
				if($this->dbColumn[$columnKey]['dataList']['type']=='richHtml'){
					if(isset($this->dbColumn[$columnKey]['dataList']['function'])){
						$method = $this->dbColumn[$columnKey]['dataList']['function'];
						$dataHtml[$k][$columnKey] = $this->$method($data);
					}else{
						echo "表结构属性的mdd字段设置已经设置为通过function来生成列表展示，请设置调用的函数";exit;
					}
				}
			}
		}
		foreach($dataList as $k=>$v){
            foreach($this->dbColumn as $key=>$config){
                if($config['dataList']['type']=='function'){
                    $funcName = $config['dataList']['function'];
                    $dataList[$k][$key] = $this->$funcName($v[$key]);
                }
            }
        }
		echo json_encode(array(
			'data'=>$dataList,
			'dataHtml'=>$dataHtml,
			'dataCount'=>intval($dataCount[0]['count']),
		));exit;
	}
	public function insertData($data){
		$dbHandle = $this->getMysqlDbHandle();
		$data = $dbHandle->insert($data);
		echo $data;exit;
	}
	public function getOneData($id){
		$dbHandle = $this->getMysqlDbHandle();
		echo json_encode($dbHandle->getByKey($id));exit;
	}
	public function update($id,$data){

		$dbHandle = $this->getMysqlDbHandle();
		$key = $dbHandle->getKeyColumnName();
		if($this->dbColumn[$key]['dataType']=='int'){
			echo $dbHandle->update(array($key=>intval($id)),$data);exit;
		}else{
			echo $dbHandle->update(array($key=>$id),$data);exit;
		}
	}
	public function deleteData($id){
		$dbHandle = $this->getMysqlDbHandle();
		echo $dbHandle->deleteById($id);exit;
	}
	public function getCountByWhere($where){
		$searchArr = array(
			'where'=>array_values($where)
		);
		$data = $this->getMysqlDbHandle()->onlyColumn('count(*) as count')->getList($searchArr);
		echo $data[0]['count'];exit;
	}
	public function deleteAllPageData($where){
		if($this->deleteOpenAllPageData===true){
			$dbHandle = $this->getMysqlDbHandle();
			$searchArr = array(
				'where'=>array_values($where)
			);
			echo $dbHandle->delete($searchArr);exit;
		}else{
			return false;
		}
	}
	public function deleteSomeData($id){
		$dbHandle = $this->getMysqlDbHandle();
		$result = $dbHandle->deleteByIds($id);
		echo json_encode($result);exit;
	}
	public function updateAll($where,$column,$runType,$replaceType,$searchText,$replaceText){
		$dbHandle = $this->getMysqlDbHandle();
		if($runType=='keys'){
			if($replaceType=='replacePart'){
				$replaceCount = 0;
				$data = $dbHandle->onlyColumn(array($dbHandle->getKeyColumnName(),$column))->getByKeys($where[$dbHandle->getKeyColumnName()]);
				foreach($data as $item){
					if(strpos($item[$column],$searchText)>-1){
						$replaceArr = array();
						$replaceArr[$column] = str_replace($searchText,$replaceText,$item[$column]);
						if($dbHandle->update($dbHandle->getKeyColumnName().'="'.$item[$dbHandle->getKeyColumnName()].'"' , $replaceArr)){
							$replaceCount++;
						}
					}
				}
				echo $replaceCount."条被替换";exit;
			}
		}else{
			$searchArr = array(
				'where'=>array_values($where)
			);
			print_r($searchArr);exit;
			$temp = $dbHandle->onlyColumn('count(*) as count')->getList($searchArr);
			print_r($temp);
			print_r($searchArr);
		}

		exit;
		echo $dbHandle->sql()->getList(array(
			'keyWord'=>array('写人的作文','写人的作文'),
		));

		exit;
	}
}

/*demo*/
/*
final class temp extends oldArticle{
	final public function getList($arr){
		return parent::getList($arr);
	}
	final public function insert($params,$mysql_insert_id = false){
		return parent::insert($params,false);
	}
	final public function update($where,$params){
		return parent::update($where,$params);
	}
	final public function deleteById($id){
		return parent::deleteById($id);
	}
}
class a extends kod_web_mysqlAdmin{
	public function getMysqlDbHandle(){
		return new temp();
	}
	protected $smartyTpl = "baiduBack.tpl";
	protected $dbColumn = array();
	public function main(){
		$adminHtml = $this->getAdminHtml($this->dbColumn);
		$this->assign("adminHtml",$adminHtml);
	}
}
$abc = new a();
$abc->run();
*/