<?php 

use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Loader\App as App;



// 素材	

class TaskModel extends Model {

	
	/**
	 * 初始化
	 * @param array $param [description]
	 */
	function __construct( $param=[] ) {
		parent::__construct();
		$this->table('task');
	}
	

	/**
	 * 数据表结构
	 * @see https://laravel.com/docs/5.3/migrations#creating-columns
	 * @return [type] [description]
	 */
	function __schema() {

		$this->putColumn( 'id', $this->type('bigInteger', ['unique'=>1]) )
			->putColumn( 'num', $this->type('string', ['length'=>80,'unique'=>1]))
			->putColumn( 'sid', $this->type('string'))
			->putColumn( 'name', $this->type('string', ['length'=>200]))
			->putColumn( 'company', $this->type('string', ['length'=>200]))
			->putColumn( 'scope', $this->type('string', ['length'=>200]))
			->putColumn( 'rscnt', $this->type('bigInteger', ['length'=>20]))
			->putColumn( 'pushedcnt', $this->type('bigInteger', ['length'=>20]))
			->putColumn( 'source_id', $this->type('bigInteger', ['length'=>20,'index'=>'1']))
			->putColumn( 'title', $this->type('string', ['length'=>20]))
			->putColumn( 'content', $this->type('text'))
			->putColumn( 'push_at', $this->type('timestamp'))
			->putColumn( 'type', $this->type('string', ['length'=>10,'index'=>'1']) )
			->putColumn( 'status', $this->type('string', ['length'=>10,'index'=>'1']) )
			;

		// 设定默认值
		//$schema = $this->db()->getSchemaBuilder();
		// $schema->table( $this->table, function($table){
		// 	$table->enum('type',['active','inactive'])->default('sms');
		// 	$table->enum('status',['pending','running','done'])->default('pending');
		// });
	}

	// 删除表用
	function __clear() {
		
		$this->dropTable();
	}
	
	// 更新用表
	function  __update($id,$data){
		
		$Source = App::M("Source");
		try{
		
			$data['content'] = $Source->uploadImages($data['content']);

		} catch (Exce $e) {

			echo $e->tojson;
			return;
			
		}
		return parent::update($id,$data);

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

	// 创建数据
	function create($data){ 


		$Source = App::M("Source");
		// 判断编号和id是否为空,如果不存在自动生成
		if (empty($data['num'])){
			$data['num'] = $this->generateId(6);
		}

		if (empty($data['id'])){
			$data['id'] = $this->generateId(6);
		}

		// 对数据进行上传cdn处理
		try{
		
			$data['content']=$Source->uploadImages($data['content']);

		 } catch (Exce $e) {

			echo $e->tojson;
			return;
		}

		// 对素材进行添加
		try{
		
		 $taskdata =$Source->create($data);

		 } catch (Exce $e) {

			echo $e->tojson;
			return;
		}

		// 设置初始化状态
		$data['status'] = 'pending';
		// 把素材id存入任务表里面
		$data ['sid'] = $taskdata['_id'];
		// 进行存储如果有错误的return
		return parent::create($data);
	}

	/**
	 * 发送信息
	 * @param  [type] $tid [description]
	 * @return [type]      [description]
	 */
	function sendSMS($tid,$type ) {

		// 通过id查询这条任务
		$task= $this->getLine( "where _id=:id", [],['id'=>$tid ] );

		$Customer = App::M('Customer');
		$data= $Customer ->query()
			->orderBy('created_at','asc')
			->where('fulltext', 'like','%'.$task['scope'].'%')
			->get()
			->toArray();

		/**
		 * 设置开始时总数已经已发送数调用setStatus
		 * 实例化sms
		 * 判断发送短信类型1为文本2为语音默认文本
		 */

		$this->setStatus($tid,'0',count($data));
	 	if ($type=='1'){
	 		$stype = "sendvoice";
	 		
	 	}else{

	 		$stype = "send";
	 	}
		//实例化
	 	$cmq = App::M("Cmq");
	 	// 创建队列返回队列名字
	 	$queuename = $cmq->create('sms');
		foreach ($data as $num=> $message) {
			//压入队列
 			$cmq->sendMessage('sms',json_encode(['mobile'=>$message["mobile"],'tid'=>$tid,'count'=>count($data),'type'=>$stype]));
 		}
		
	}
	


	function sendEmail( $tid ) {
		// 通过id查询这条任务
		$task= $this->getLine( "where _id=:id", [],['id'=>$tid ] );
		$Customer = App::M('Customer');
		$data= $Customer ->query()
			->orderBy('created_at','asc')
			->where('fulltext', 'like','%'.$task['scope'].'%')
			->get()
			->toArray();
		$this->setStatus($tid,'0',count($data));
		$cmq = App::M("Cmq");
		$queuename = $cmq->create('mail');
		foreach ($data as $num=> $message) {
			//压入队列
 			$a = $cmq->sendMessage('mail',json_encode(['mail'=>$message["email"],'tid'=>$tid,'count'=>count($data)]));
 		}

	}

	/**
	 * 对发送内容进行处理
	 * @param  [type] $tid [description]
	 * @return [type]      [description]
	 */
	function datamail($tid){
		// 通过id查询这条任务
		$task= $this->getLine( "where _id=:id", [],['id'=>$tid ] );
		$Customer = App::M('Customer');
		$source = App::M('source');
		$data= $Customer ->query()
			->orderBy('created_at','asc')
			->where('fulltext', 'like','%'.$task['scope'].'%')
			->get()
			->toArray();
		// 通过任务栏sid查询素材链接
		$taskdata['url']= $source->getLine( "where _id=:id", [],['id'=>$task['sid']] );
		// 调用composer类转换html
		$parser = new \cebe\markdown\Markdown();
		$content = $parser->parse($task['content']);
		// 发送邮件替{link}
		$content=str_replace("{link}",$taskdata['url']['url'],$content);
		return $content;
	}


	/**
	 * 查询进度
	 * @param  [type] $tid [description]
	 * @return [type]      [description]
	 */
	function getStatus( $tid ) {

		return $this->getLine( "where _id=:id", [],['id'=>$tid ] );
	}


	/**
	 * 设定进度
	 * @param [type] $tid   [description]
	 * @param [type] $curr  [description]
	 * @param [type] $total [description]
	 */
	function setStatus( $tid, $curr, $total=null ) {

		$data = [ 'id'=>$tid, 'pushedcnt'=>$curr ];
		// 如果传入总数不为空设置
		if ( $total != null ) {

			// 客户总量
			// 已经推送的量
			$message = ['rscnt' => $total,'pushedcnt'=>$data['pushedcnt']];	
		}
		return $this->update($tid, $message);
	}



	/**
	 * 优化链接
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
	    for ( $i = 0; $i<$length; $i++ ) 
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
		for( $i=0; $i<5; $i++ ) {
			try {
			$cust = $this->create([
		        'company'=> $faker->company,
		        'name'=> $faker->name,
		        'title' => $faker->jobTitle,
		        'mobile'=> $faker->phoneNumber,
		        'email'=> $faker->email,
		        'address'=> $faker->address,
		        'remark'=> $faker->text(100),
		        'status'=>'pending',
		        'type'=>'sms',
		        'scope'=>'tuanduimao',
		        'push_at'=>date('Y-m-d',time()+100000000)
			]);
			} catch(Excp $e){

		    }
	    }

	}
}


?>