<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 16/2/1
 * Time: 下午2:29
 * @method static string set($key,$val)
 * @method static string get($key)
 * @method static string delete($key)
 */
class kod_db_memcache{
	private $memObj;
	public function __construct(){
		$this->memObj = new Memcache();
		static::initServerConnect($this->memObj);
	}
	public static function initServerConnect(&$MemcacheObj){
		$MemcacheObj->connect('localhost', 11211);
	}
	public function __call($function_name,$args){

	}
	public static function __callStatic($function_name,$args){
		$memcache_obj = new Memcache;
		static::initServerConnect($memcache_obj);
		return call_user_func_array(array($memcache_obj,$function_name),$args);
	}

	//自增服务
	public static function adding($key,$numAdd,$function,$step=0){
		$value = static::get($key);
		if($value==false){
			static::set($key,$numAdd);
		}else{
			static::increment($key,$numAdd);
		}
		$value = static::get($key);
		if($step && $value%$step==0){
			$function($value);
			static::set($key,0);
		}
		return $value;
	}
	//return值是null的时候，不进行存储
	/*
	 * @param string $key
	 * @param func $function
	 * @param int $flag
	 * @param int $expire
	 * @param function $funcIsExpire 验证数据是否已经过期,返回false,会走重新生成的逻辑
	 * */
	public static function returnCacheOrSave($key,$function,$flag=0,$expire=0,$funcIsExpire=null){
		$lockValue = 'lock';
		$lockValue2 = 'lock2';
		for($i=0;$i<10;$i++){
			$value = static::get($key);
			if(in_array($value,array($lockValue,$lockValue2))){
				sleep(1);
			}else{
				break;
			}
		}
		if($value==false || ($funcIsExpire!==null && $value!==false && $funcIsExpire($value)==false)){
			//进程
			static::set($key,$lockValue,0,10);
			$weatTime = 10000;//轮训时间1s
			usleep(rand(1,$weatTime));
			if(static::get($key)==$lockValue){
				static::set($key,$lockValue2,0,10);
				$data = $function();
				if($data==null){
					static::delete($key);
					return null;
				}else{
					static::set($key,$data,$flag,$expire);
					return static::get($key);
				}
			}else{
				for($i=0;$i<3;$i++){
					sleep(1);
					$value = static::get($key);
					if(!in_array($value,array(false,$lockValue,$lockValue2))){
						return $value;
					}
				}
			}
		}else{
			return $value;
		}
	}
	static function create(){
		$temp = get_called_class();
		return new $temp();
	}
}
//memcache方法在网址  http://php.net/manual/zh/book.memcache.php  中查看
//快速调取释放类型，每一次都链接，并且断开
/*
kod_db_memcache::set('test',array(
	'obej'=>1,
	'hehe'=>array(1,2,3,5),
),0,20);
print_r(kod_db_memcache::get('test'));
*/
/*
$data = 'hehe';
echo kod_db_memcache::returnCacheOrSave('test',function() use($data){
	return $data;
},0,10);
 * */