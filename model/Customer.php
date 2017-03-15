<?php

use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;

/**
 * 客户检索model
 */

class CustomerModel extends Model {

	/**
	 * 初始化
	 * @param array $param [description]
	 */
	function __construct( $param=[] ) {
		parent::__construct();
		$this->table('customer');
	}
	
	/**
	 * 数据表结构
	 * @see https://laravel.com/docs/5.3/migrations#creating-columns
	 * @return [type] [description]
	 */
	function __schema() {
			
			$this->putColumn( 'id', $this->type('bigInteger', ['unique'=>1]) )
			->putColumn( 'num', $this->type('string', ['length'=>80, 'unique'=>1]) )
			->putColumn( 'company', $this->type('string', ['length'=>200]) )
			->putColumn( 'name', $this->type('string', ['length'=>20]) )
			->putColumn( 'title', $this->type('string', ['length'=>80]) )
			->putColumn( 'mobile', $this->type('string', ['length'=>20]) )
			->putColumn( 'email', $this->type('string', ['length'=>200]) )
			->putColumn( 'address', $this->type('string', ['length'=>200]) )
			->putColumn( 'remark', $this->type('string', ['length'=>200]) )
			->putColumn( 'fulltext', $this->type('mediumText') )
			->putColumn( 'status', $this->type('enum', [
					'enum'=>['active','inactive'],
					'index'=>1,
					]))
		;
		// 设定默认值
		// $schema = $this->db()->getSchemaBuilder();
		// $schema->table( $this->table, function($table){
		// 	$table->enum('status',['active','inactive'])->default('active');
		// });
	}


	// 创建数据
	function create($data){

		// 所有数据汇总存入方便查询(将所有数组转化为字符串存入)
		$data['fulltext'] = implode("",$data);

	
		// 判断编号和id是否为空,如果不存在自动生成
		
		if (empty($data['num'])){

			$data['num'] = $this->generateNum(6);
			
		}

		if (empty($data['id'])){

			$data['id'] = $this->generateId(5);
		}

		// 进行存储如果有错误的return
		return parent::create($data);
	}

	/**
	 * 更新数据
	 * @param  [type] $id   [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	function  __update($id,$data){
		
		return $this->update($id,$data);
	}


	function __clear() {
		
		$this->dropTable();
	}
	

	/**
	 * 
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	function hackPageurl( & $data ) {

		// 对分页链接进行优化
		if ( strpos($data["prev_page_url"], '/?') === 0 ) {
			$data["prev_page_url"] = mb_substr(urldecode($data["prev_page_url"]),2);
		}

		if ( strpos($data["next_page_url"], '/?') === 0 ) {
			$data["next_page_url"] = mb_substr(urldecode($data["next_page_url"]),2);
		}

		return $data;
	}


	function hackPageurlequ( & $data ) {

		// 对分页链接进行优化
		if ( strpos($data["prev_page_url"], '/?=') === 0 ) {
			$data["prev_page_url"] = mb_substr(urldecode($data["prev_page_url"]),3);
		}

		if ( strpos($data["next_page_url"], '/?=') === 0 ) {
			$data["next_page_url"] = mb_substr(urldecode($data["next_page_url"]),3);
		}

		return $data;
	}



	/**
	 *生成id随机数
	 * @return [type] [description]
	 */
	function generateId( $length ) {
    	// 随机数字符集，可任意添加你需要的字符
    	$chars = '1234567890';
	    $num = '';
	    for ( $i = 0; $i < $length; $i++ ) 
	    {
	      $num .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	    }
		return date(time()).$num;
	}

	/**
	 *生成num随机数
	 * @return [type] [description]
	 */
	function generateNum( $length ) 
	{
	   	// 随机数字符集，可任意添加你需要的字符
    	$chars = '1234567890';
	    $num = '';
	    for ( $i = 0; $i < $length; $i++ ) 
	    {
	      $num .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	    }

	    return $num;
	}

	/**
	 * 创建测试数据
	 * @return [type] [description]
	 */
	function testcreate(){
		
		$faker = Utils::faker();
		for( $i=0; $i<36; $i++ ) {
			try {
			$cust = $this->create([
		        'company'=> $faker->company,
		        'name'=> $faker->name,
		        'title' => $faker->jobTitle,
		        'mobile'=> $faker->phoneNumber,
		        'email'=> $faker->email,
		        'address'=> $faker->address,
		        'remark'=> $faker->text(100),
		        'status'=>'active'
		    ]);
			} catch(Excp $e){

		    }
	    }

	}






}