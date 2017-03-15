<?php 
use \Tuanduimao\Mem as Mem;
use \Tuanduimao\Excp as Excp;
use \Tuanduimao\Err as Err;
use \Tuanduimao\Conf as Conf;
use \Tuanduimao\Model as Model;
use \Tuanduimao\Utils as Utils;
use \Tuanduimao\Loader\App as App;
use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;
class MonitorModel extends Model {

	function __construct( $param ) {
	    parent::__construct();
	    $this->table('scaling');  // 数据表名称定义为 scaling
	}
	
	// 接收并处理告警邮件
	function recive($arr){

		$arr = ['host'=>$arr['host'],'passwd'=>$arr['passwd'],'user'=>$arr['user']];
		$mailbox = new ImapMailbox('{'.$arr['host'].'/imap/ssl/novalidate-cert}INBOX',$arr['user'],$arr['passwd'], __DIR__);
		$mailsIds = $mailbox->searchMailbox('ALL');
		// 转化为数组
		$mail = get_object_vars($mailbox->getMail(end($mailsIds)));
		$mail = $mail["textHtml"];
	 	return $mail;
	}

	// 解析邮件，分析报警信息
	function parseMail( $mail ){
		$data = []; $message = [];
		// 抓取告警对象
		if ( preg_match("/告警对象：(.+)\|/", $mail, $match)) {

		    $data["object"] = $match[1];
		}

		// 抓取监控指标
		if ( preg_match("/监控指标：(.+)/", $mail, $match)) {
			
			$data["type"] = $match[1];
		}
		
		// 抓取出发条件
		if ( preg_match("/告警内容：(.+)/", $mail, $match ) ) {

		    $data["content"] = $match[1];
		}

		if ( !empty($data["type"]) ) {

		    $message['event'] = json_decode($this->getEvent( $data["type"], $data['content'] ),true);
		}

	
		//通过ip查询内容
		if( !empty($data["object"]) ) {

			//转换查询方法
			$message['way'] = json_decode($message['event']['data'],true);
			// ip查询ip
			// 查询的字段
			// action方法
			//如果执行mysql（数据库）的方法还是其他方法因为查询条件不同
			if ($message['event']['nicident']=='setConn'){
					
				$message['inst'] = $this->getInstid($data["object"],$message['way']);

			}else{

				$message['inst'] = $this->getInst($data["object"],$message['way']);

			}
		}


		// return  array('methond' =>$message["event"]["nicident"],
		// 	'id'=>$message['inst']);
	
	}

	/**
	 * 匹配方法
	 * @return [type] [description]
	 */
	function getEvent($type,$content){
		$Scaling =App::M('Scaling');
		$data = $Scaling->select();
		foreach ($data['data'] as $num => $keyval) {
			//匹配条件
			$message = json_decode($keyval['fulltext'],true);
			// 匹配方法
			preg_match_all('/(?:'.$message['index'].')/', $type, $indexmes);
			preg_match_all('/(?:'.$message['warning'].')/', $content, $warmatch);
			// 如果条件匹配根方法据匹配提交确定出数据库中执行
			if(!empty($indexmes['0'])&&!empty($warmatch['0'])){
				
				    $waydata = array('nicident'=>$keyval['ncident'],'data'=> $keyval['data']);
			}

		}

	

		 return json_encode($waydata);
	}
	
	/**
	 * 通过id获取信息
	 * @return [type] [description]
	 */
	function getInst($ip,$way){

		// 对传进去的值进行处理
		if(preg_match("/\b(?:[0-9]{1,3}\.){3}[0-9]{1,3}\b/",$ip,$content)){
			$Scaling =App::M('Scaling');
			$data = $Scaling->search($content['0'],$way);
			if ($data['code']!='0') {
				echo $data['message'];
			}else{
				
				return $data;
			}
		}
	}

	
	/**
	 * 匹配出mysql主机id
	 * @return [type] [description]
	 */
	function getInstid($ip,$way){
		
		if(preg_match("/^cdb[0-9]{6}/",$ip,$content)) {
			// 匹配的id执行方法
			$Scaling =App::M('Scaling');

			$data = $Scaling->sertotal($content['0']);
		}
	}

}


 ?>