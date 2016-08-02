<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 16/6/5
 * Time: 下午9:37
 */
class kod_reProgram_class{
	private $program = '';
	public function init($pach){
		$myfile = fopen($pach, "r") or die("Unable to open file!");
		while(!feof($myfile)) {
			$this->program.=fgets($myfile);
		}
		fclose($myfile);
		echo $this->program;exit;
	}
	public function getAttr($class,$attr){

	}
	public function setAttr($class,$attr){

	}
	private function readFile(){
		$file = fopen("test.txt","r");
	}
}