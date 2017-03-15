<?php 


use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Tuan as Tuan;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Loader\App as App;

// 发送接口
class TaskDaemonController extends \Tuanduimao\Loader\Controller {
	
	// 发送信息
	function  sendsms(){

		// 实例化
		$task = App::M('task');

		// 查询
		$data= $task->query()
		->orderBy('created_at','asc')
		->where("push_at",">=",date('Y-m-d',time()))
		->where("status","=","pending")
		->where("type","=","sms")
		->get()
		->toArray();
		// 循环执行
		foreach ($data as $ts ) {
			$task->sendSMS($ts['_id']);
		}
	}

	// 发送邮件
	function   sendmail(){
		// 实例化
		$task = App::M('task');
		// 查询
		$data= $task->query()
		->orderBy('created_at','asc')
		->where("push_at",">=",date('Y-m-d',time()))
		->where("status","=","pending")
		->where("type","=","mail")
		->get()
		->toArray();
		
		// 循环执行
		foreach ($data as $ts ) {

			$task->sendEmail($ts['_id']);

		}
	}


	/**
	 * 队列消费程序控制器（邮件）
	 * @return [type] [description]
	 */
	function mailworker(){
		$receiptHandle = NULL;
		$cmq = App::M("Cmq");
		$Task = App::M("Task");
		$Sms = App::M('Sms');
		// 邮件or短信参数
		$queue_name = $_GET['method'];
		if($queue_name=='mail') {
			$res = $cmq->receiveMessage('mail');
			// 对象转数组
			$message = $cmq->objarray_to_array(json_decode($res["msgBody"]));
			// 更改状态
			$Task->__update($message['tid'],['status'=>'running']);
			// 对邮件的格式进行转换
			$data = $Task->datamail($message['tid']);
			//发送邮件
			//定义发送邮件参数（具体参考model=>sms=>sendemail）
			$arr=['user'=>'maoshun@diancloud.com','content'=>$data,'host'=>'smtp.exmail.qq.com','passwd'=>'Woaini110','guest'=>$message['mail'],'title'=>'gaomaoshun'];
			$Sms->sendemail($arr);
			// 对队列中的数据进行删除
			$cmq->removeMessage('mail',$res['receiptHandle']);
			//查询进度
 			$iddata = $Task->getStatus($message['tid']);
 			//设置进度
 			$Task->setStatus($message['tid'],$iddata['pushedcnt']+'1',$message['count']);
		 	//设置完毕之后查询进度
 			$type = $Task->getStatus($message['tid']);
			//当发送总数和已发送数相等时更改状态发送完毕
			if ($type["pushedcnt"]==$type["rscnt"]) {

				$Task->__update($message['tid'],['status'=>'done']);
			}
		}
	}

	/**
	 * 队列消费程序控制器（短信）
	 * @return [type] [description]
	 */
	function smsworker(){
		$receiptHandle = NULL;
		$cmq = App::M("Cmq");
		$Task = App::M("Task");
		$Sms = App::M('Sms',[
	 	"AppID"=>"1400017564", 
	 	"AppKey"=>"2b9f1e3ef8e81ebb5cf4f2b9d1433fe0",
	 	]);
		// 邮件or短信参数
		$queue_name = $_GET['method'];
		if($queue_name=='sms') {
			$res = $cmq->receiveMessage('sms');	
			// 对象转数组
			$message = $cmq->objarray_to_array(json_decode($res["msgBody"]));
			// 更改状态
			$Task->__update($message['tid'],['status'=>'running']);
			$type = $message['type'];
			// 发送信息
			$test= $Sms->$type($message['mobile'],'尊敬的客户{1}您好！您的{2}会员卡已生效，请在{3}天内激活，感谢合作和支持。');
			// 对队列中的数据进行删除
			$cmq->removeMessage('sms',$res['receiptHandle']);
			//查询进度
 			$iddata = $Task->getStatus($message['tid']);
 			//设置进度
 			$Task->setStatus($message['tid'],$iddata['pushedcnt']+'1',$message['count']);
		 	//设置完毕之后查询进度
 			$type = $Task->getStatus($message['tid']);
			//当发送总数和已发送数相等时更改状态发送完毕
			if ($type["pushedcnt"]==$type["rscnt"]) {

				$Task->__update($message['tid'],['status'=>'done']);
			}
		}
	}


}
?>
