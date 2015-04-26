<?php
final class display{
	static function create($filePath){
		$temp = get_called_class();
		$temp = new $temp($filePath);
		return $temp;
	}
	private $filePath = "";
	private function __construct($filePath){
		$this->filePath = $filePath;
	}
	public function run($params){

	}
}