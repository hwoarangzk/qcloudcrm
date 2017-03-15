<?php

use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Loader\App as App;

class SourceModel extends Model {

	/**
	 * 初始化
	 * @param array $param [description]
	 */
	function __construct( $param=[] ) {
		parent::__construct();
		$this->table('source');
	}
	


	function __schema() {
		
		$this->putColumn( 'id', $this->type('bigInteger',['unique'=>1]) )
			->putColumn( 'num', $this->type('string', ['length'=>80,'unique'=>1,index=>1]))
			->putColumn( 'title', $this->type('string', ['length'=>80]) )
			->putColumn( 'content', $this->type('text') )
			->putColumn( 'push_at', $this->type('time'))
			->putColumn( 'type', $this->type('string', ['length'=>20]) )
			->putColumn( 'url', $this->type('string', ['length'=>200]) )
			;
		// 设定默认值
		// $schema = $this->db()->getSchemaBuilder();
		// $schema->table( $this->table, function($table){
		// 	$table->enum('type',['sms','mail'])->default('sms');
		// });
	}
	
	// 创建数据
	function create($data){
		
		// 判断编号和id是否为空,如果不存在自动生成
		
		if (empty($data['num'])){

			$data['num'] = $this->generateNum(6);
			
		}

		if (empty($data['id'])){

			$data['id'] = $this->generateId(5);
			
		}

		// 对数据进行上传cdn处理
		try{
		
			$data['content']=$this->uploadImages($data['content']);

		 } catch (Exce $e) {

			echo $e->tojson;
			return;
		}

		$message = parent::create($data);
		// 创建的时候内容存入缓存
		$this->html($message['_id'],$message['content']);

		// 存入之后更新url和内容
		$this->__update($message['_id'],['url'=>App::NR('SourceData','sethtml',['_id'=>$message['_id']]),'content'=>$message['content']]);

		// 进行存储如果有错误的return
		return $message;
	}


	/**
	 * 更新数据
	 * @param  [type] $id   [description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	function  __update($id,$data){

		try{
		
			$data['content'] = $this->uploadImages($data['content']);

		 } catch (Exce $e) {

			echo $e->tojson;
			return;
			
		 }

		return $this->update($id,$data);

	}

	
	/**
	 * 卸载使用
	 * @return [type] [description]
	 */
	function __clear() {
		
		$this->dropTable();

	}
	
	
	/**
	 * 缓存
	 * @param  [type] $cache_name [description]
	 * @param  [type] $content    [description]
	 * @return [type]             [description]
	 */
	function html( $cache_name, $content=null ) {

		$mem = new Mem;
		if ( $content == null ) {
			return $mem->get( $cache_name );
		}

		return $mem->set( $cache_name, $content );
	}


	/**
	 * 提取markdown转化为html 上传cos空间
	 * @param  [type] $markdown [description]
	 * @return [type]           [description]
	 */
	function uploadImages($markdown) {
		$cos = App::M("cos",["bucket"=>"test",
			"appid"=>"1252758974",
			"SecretID"=>"AKIDxoTxGQLIwPhnka5EOOCoFVZS9j8NKbw5",
			"SecretKey"=>"5VfkeLuHSTh8XkmbEfu030lKhsreLxPg",
		]);

		// 正则表达式匹配出全部的符合要求的图片地址
		if (preg_match_all("/[!]{0,1}\[(.*)\]\(([a-zA-Z0-9\/\_\:\.]+)\)/",$markdown,$match)) {
			// 对符合要求的地址做循环($m[1]);
			foreach( $match['2'] as $m ) {
				if (strpos( $m, "myqcloud.com") === false) {
					$resp = $cos->uploadByUrl($m,['filetype'=>$this->get_extension($m)]);
					// 判断如果code==0表示上传成功
					if ( $resp['code'] == 0 ) {
						$markdown = str_replace($m, $resp['data']['source_url'], $markdown);
					}
				}
			}
		}
		return $markdown ;
	}


	/**
	 * 删除方法
	 * @return [type] [description]
	 */
	function __delete($_id) {

		$cos = App::M("cos",["bucket"=>"test",
			"appid"=>"1252758974",
			"SecretID"=>"AKIDxoTxGQLIwPhnka5EOOCoFVZS9j8NKbw5",
			"SecretKey"=>"5VfkeLuHSTh8XkmbEfu030lKhsreLxPg",
		]);


		$markdown = $this->getLine( "where _id=:id", [],['id'=>$_id ] );

		// 匹配文件名字
		if (preg_match_all("/(http:\/\/)?\w+\.(jpg|jpeg|gif|png)/",$markdown['content'],$match)) {
			foreach ($match['0'] as $url) {
				$res =$cos->remove($url);
				if ($res["message"]!="SUCCESS") {
					return false;
				}
			}
		}

		return $this->delete($_id);
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


	// 获取文件类型
	function get_extension($file){
		return substr($file, strrpos($file, '.')+1);
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
		        'title' => $faker->jobTitle,
		        'content'=> 'tuanduimao',
		        'push_at'=> $faker->date('Y-m-d H:i:s'),
		        'type'=> 'sms',
		        'scope'=>'tuanduimao'

		    ]);
			} catch(Excp $e){

		    }
	    }

	}
	
}