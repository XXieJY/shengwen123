<?php

// +----------------------------------------------------------------------

// | 浩森PHP框架 [ IeasynetPHP ]

// +----------------------------------------------------------------------

// | 版权所有 2017~2018 北京浩森宇特互联科技有限公司 [ http://www.ieasynet.com ]

// +----------------------------------------------------------------------

// | 官方网站：http://ieasynet.com

// +----------------------------------------------------------------------

// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )

// +----------------------------------------------------------------------

// |  c08129327b9fa0a4bc29b4f070c9877e

// +----------------------------------------------------------------------



namespace app\cms\home;



use app\cms\model\Column as ColumnModel;

use app\cms\model\Document as DocumentModel;

use util\Tree;

use think\Db;

use EasyWeChat\Foundation\Application;
use Doctrine\Common\Cache\RedisCache;

/**
 * 文档控制器
 * @package app\cms\home
 */

class Document extends Common

{

    /**
     * 文档详情页
     * @param null $id 文档id
     * @param string $model 独立模型id
     * @author 拼搏 <378184@qq.com>
     * @return mixed
     */

    public function detail($id = null,$t=null)

    {

		/*登陆验证方法*/

		session_start();
		if(!is_weixin())
        {
        	$chapter=DB::table('ien_chapter')->where('id',$id)->find();
			//限免
			$book=DB::table('ien_book')->where('id',$chapter['bid'])->find();
			if($book['isfree']=1 && $book['free_stime']<=time() && $book['free_etime']>=time())
			{
				$viplog=0;
			}
			else{
				if($chapter['isvip']==1)
				{
					$viplog=1;
				}
				else
				{
					$viplog=0;
				}
			}
        	//非微信端&开启后台微信端
        	//判断登录状态
        	if(empty($_SESSION['wechat_user'])&&$viplog==1){
            $this->redirect('user/login');
        	}

        $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        
		

		if($t!="")

		{

		DB::table('ien_agent')->where('id',$t)->setInc('click');

		}

        //通过地址更新推广ID
       preg_match("/^\w(\d*)/",$_SERVER['HTTP_HOST'],$dlid);
      if(!empty($dlid))
      {
        $agentid=DB::table('ien_agent')->where('uid',$dlid[1])->order('create_time desc')->value('id');
        $user_sxid=DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->find();
        if(!empty($agentid) && $dlid[1]!=$user_sxid['sxid'])
        {
            $datasx=['sxid'=>$dlid[1],'tgid'=>$agentid,'gzopenid'=>""];
            DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->update($datasx);
        }
      }
                //更新读者推广ID
        $urla['1']="";   
        if(strpos($_SESSION['target_url'],"?"))
        {
            $url=explode("?",$_SESSION['target_url']);
            $urla=explode("=", $url[1]);
        }
        if(!empty($urla['1']))
        {
          $sxid=DB::table('ien_agent')->where('id',$urla['1'])->find();
          if(empty($sxid))
          {
            $sxid['uid']=0;
          }
        }
        else{$sxid['uid']=0;}
        if($sxid['uid']!=0 && $sxid['uid']!="" && $urla['1']!="")
        {
            $dataagent=['sxid'=>$sxid['uid'],'tgid'=>$urla['1'],'gzopenid'=>""];
            DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->update($dataagent);
        }
        //更新读者推广ID结束
        

		$yz['id']=$id;

		$info=DB::table('ien_chapter')->where($yz)->find();

		if(empty($info)){

		$this->redirect('index/index');}

		
        $book=Db::table('ien_book')->field('image,title')->where('id', $info['bid'])->find();

		$info['content']= nl2br($info['content']); 



		

        if(!empty($_SESSION['wechat_user'])){
        	//添加阅读历史
           $this->readold($id);
           //验证VIP章节，消费
			$this->isvip($id);
        }

		//判断强制关注章节

		$guanzhu=$this->gzzj($id);

		//判断关注章节跳转

		$isgz=$this->isguanzhu($id,$guanzhu);

		

		//每日签到积分

		//$this->addcore();


        $this->assign('isgz', $isgz);
        $this->assign('book', $book);
        $this->assign('document', $info);
        $this->assign('next', $this->getNext($id));

		//上一张

		$this->assign('prev', $this->getPrev($id));
		if(!empty($_SESSION['wechat_user'])){
           $user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();
			$sxid=0;
		if($user['sxid']==0)
					{
						$sxid=0;
					}
					else
					{
						$sxid=$user['sxid'];
						
					}
			//跳转关注页面
		$this->assign('sxid', $sxid);
        }
        else{
        	$this->assign('sxid', 0);
        }

        return $this->fetch('detail');

        }
        else{



        $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        
		//添加点击数

		/*$urla['1']="";

        if(strpos($_SERVER['REQUEST_URI'],"?"))

        {

            $url=explode("?",$_SERVER['REQUEST_URI']);

            $urla=explode("=", $url[1]);

        }

		if($urla['1']!="")

		{

		DB::table('ien_agent')->where('id',$_GET['t'])->setInc('click');

		}*/

		if($t!="")

		{

		DB::table('ien_agent')->where('id',$t)->setInc('click');

		}


		

        if(empty($_SESSION['wechat_user'])){

            $this->redirect('oauth/oauth');

            //$this-> checklogin();

        }
        //通过地址更新推广ID
      preg_match("/^\w(\d*)/",$_SERVER['HTTP_HOST'],$dlid);
      if(!empty($dlid))
      {
        $agentid=DB::table('ien_agent')->where('uid',$dlid[1])->order('create_time desc')->value('id');
        $user_sxid=DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->find();
        if(!empty($agentid) && $dlid[1]!=$user_sxid['sxid'])
        {
            $datasx=['sxid'=>$dlid[1],'tgid'=>$agentid,'gzopenid'=>""];
            DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->update($datasx);
        }
      }
                //更新读者推广ID
        $urla['1']="";   
        if(strpos($_SESSION['target_url'],"?"))
        {
            $url=explode("?",$_SESSION['target_url']);
            $urla=explode("=", $url[1]);
        }
        if(!empty($urla['1']))
        {
          $sxid=DB::table('ien_agent')->where('id',$urla['1'])->find();
          if(empty($sxid))
          {
            $sxid['uid']=0;
          }
        }
        else{$sxid['uid']=0;}
        if($sxid['uid']!=0 && $sxid['uid']!="" && $urla['1']!="")
        {
            $dataagent=['sxid'=>$sxid['uid'],'tgid'=>$urla['1'],'gzopenid'=>""];
            DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->update($dataagent);
        }
        //更新读者推广ID结束


		$yz['id']=$id;

		$info=DB::table('ien_chapter')->where($yz)->find();

		if(empty($info)){

		$this->redirect('index/index');}

		

        $book=Db::table('ien_book')->field('image,title,cid')->where('id', $info['bid'])->find();

		//$info['content']= nl2br($info['content']);
        $info['content']=str_replace("\n", "</p><p>", str_replace(" ", "", $info['content']));
		//$info['title']= str_replace("\r", '', $info['title']); 

		//$info['title'] = str_replace(array("\r\n", "\r", "\n"), "", $info['title']);  

		//$info['title'] = preg_replace('//s*/', '', $info['title']);
//广告
$adcontent="";
		if($this->ispay($_SESSION['wechat_user']['original']['openid'])){
			//vip
			$ad=DB::table('ien_cms_advert')->where('tagname','document_right')->find();
			if(!empty($ad) && !empty($ad['content']))
			{
			$adcontent='
<div style="position:fixed;bottom: 100px;right: 0px;" id="vip-btn">
<div onclick="$(\'#vip-btn\').hide();" style="padding-top: 25px;text-align: center;"><img width="28px" style="border-radius: 50%;" src="/public/static/cms/img/close.jpg"></div>
'.$ad['content'].'
</div>';

			}
		}
		else{
			if(!empty($ad) && !empty($ad['content']))
			{
			$adcontent='
<div style="position:fixed;bottom: 100px;right: 0px;" id="vip-btn">
'.$ad['content'].'
</div>';

			}

		}

//推荐
		$tjbook=array();
if(!empty($book['cid']))
        {
		$tuijianbook=DB::query('SELECT * FROM `ien_book` where cid='.$book['cid'].' ORDER BY RAND() LIMIT 3');
		foreach ($tuijianbook as $key => $value) {
			$tjcpid=DB::table('ien_chapter')->where('bid',$value['id'])->where('idx',1)->find();
			if(!empty($tjcpid))
			{
				$tjbook[$key]['id']=$tjcpid['id'];
				$tjbook[$key]['img']=$value['image'];
				$tjbook[$key]['title']=DB::query('SELECT title FROM `ien_fodder` WHERE fenlei=0 order by rand() limit 1');
			}
			
		}
}
		$this->assign('tjbook', $tjbook);
		
		$this->assign('ad', $adcontent);
		//添加阅读历史

        $this->readold($id);

		//判断强制关注章节

		$guanzhu=$this->gzzj($id);

		//判断关注章节跳转

		$isgz=$this->isguanzhu($id,$guanzhu);

		//验证VIP章节，消费

		$this->isvip($id);

		//每日签到积分

		//$this->addcore();


        $this->assign('isgz', $isgz);

        $this->assign('book', $book);
       // $this->assign('uid', UID);

        $this->assign('document', $info);

        //$this->assign('breadcrumb', $this->getBreadcrumb($info['cid']));

        //$this->assign('prev', $this->getPrev($id));

        $this->assign('next', $this->getNext($id));

		//上一张

		$this->assign('prev', $this->getPrev($id));

		$user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();
		$sxid=0;

				if($user['sxid']==0)
					{
						$sxid=0;
					}
					else
					{
						$sxid=$user['sxid'];
						
					}
			//跳转关注页面
		$this->assign('sxid', $sxid);


        return $this->fetch('detail');
        }
    }


    public function ispay($uid=null){
    	$ispay=DB::table('ien_pay_log')->where('uid',$uid)->where('status',1)->find();
    	if(!empty($ispay))
    	{
    		return true;
    	}
    	else
    	{
    		return false;
    	}
    }


    /**
     * 获取栏目面包屑导航
     * @param int $id 栏目id
     * @author 拼搏 <378184@qq.com>
     */

    private function getBreadcrumb($id)

    {

        $columns = ColumnModel::where('status', 1)->column('id,pid,name,url,target,type');

        foreach ($columns as &$column) {

            if ($column['type'] == 0) {

                $column['url'] = url('cms/column/index', ['id' => $column['id']]);

            }

        }

        return Tree::config(['title' => 'name'])->getParents($columns, $id);

    }



    /**
     * 获取上一篇文档
     * @param int $id 当前文档id
     * @param string $model 独立模型id
     * @author 拼搏 <378184@qq.com>
     * @return array|false|\PDOStatement|string|\think\Model
     */

    private function getPrev($id)

    {

        $cha=DB::table('ien_chapter')->where('id',$id)->find();

		$idx=$cha['idx']-1;

		$map['bid']=$cha['bid'];

		$map['idx']=$idx;

		$doc=DB::table('ien_chapter')->where($map)->find();



        if ($doc) {

            $doc['url'] = url('cms/document/detail', ['id' => $doc['id']]);

        }

		else

		{

			$doc['url'] = url('cms/index/index');

			}



        return $doc;

    }



    /**
     * 获取下一篇文档
     * @param int $id 当前文档id
     * @param string $model 独立模型id
     * @author 拼搏 <378184@qq.com>
     * @return array|false|\PDOStatement|string|\think\Model
     */

    private function getNext($id)

    {

        $cha=DB::table('ien_chapter')->where('id',$id)->find();

		$idx=$cha['idx']+1;

		$map['bid']=$cha['bid'];

		$map['idx']=$idx;

		$doc=DB::table('ien_chapter')->where($map)->find();



        if ($doc) {

            $doc['url'] = url('cms/document/detail', ['id' => $doc['id']]);

        }

		else

		{

			$doc['url'] = url('cms/index/index');

			}



        return $doc;

    }



    public function addbookmark($id=null)

    {

		session_start();

        $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        if(empty($_SESSION['wechat_user'])){

            $this->redirect('oauth/oauth');

            //$this-> checklogin();

        }

        if($id==''){ return false;}

        $data = ['uid' =>$_SESSION['wechat_user']['original']['openid'],

                 'zid' => $id,

                ];

        Db::table('ien_bookmarks')->insert($data);  

        return true;

    }

	

	

		//添加阅读历史记录

	public function readold($zid=null)

	{

		//查询是否有这本书的记录

		$bid=DB::table('ien_chapter')->where('id',$zid)->find();

		$openid=$_SESSION['wechat_user']['id'];

		$map['bid']=$bid['bid'];

		$map['uid']=$openid;

		//$map['zid']=$zid;

		$res=DB::table('ien_read_log')->where($map)->find();

		//如果有，更新章节和更新时间，如果没有插入记录。

		if($res)

		{

			$data['zid']=$zid;

			$data['update_time']=time();

			Db::table('ien_read_log')->where($map)->update($data);

			

			}

		else{

			$datai['uid']=$openid;

			$datai['zid']=$zid;

			$datai['bid']=$bid['bid'];

			$datai['create_time']=time();

			Db::table('ien_read_log')->insert($datai);

			

		

		}

	}

	

		//判断当前用户，代理商设置的关注章节	

	public function gzzj($id=null){

		$openid=$_SESSION['wechat_user']['original']['openid'];

		
		//获取用户信息

		$user=DB::table('ien_admin_user')->where('openid',$openid)->find();

		if($user['tgid']!=0){

		//判断是否从推广链接进来

		$agent=DB::table('ien_agent')->where('id',$user['tgid'])->find();

			if($agent){

				//判断代理商是否设置了强制关注的ID

			$agentuser=DB::table('ien_admin_user')->where('id',$agent['uid'])->find();



				if(!empty($agentuser['guanzhu']) && $agentuser['guanzhu']!=0)

				{

					return $agentuser['guanzhu'];

					}

				else{
					
                  	try{
                    $bid=DB::table('ien_chapter')->where('id',$id)->column('bid');
        			$gzid=DB::table('ien_book')->where('id',$bid['0'])->column('gzzj');
                      if(!empty($gzid['0']) && $gzid['0']>0)
                      {
                    	return $gzid['0'];
                      }
                      else
                      {
                        return module_config('agent.agent_guanzhu');
                      }
                    }
                      catch(\Exception $e){
                        return module_config('agent.agent_guanzhu');
                      }
					}	

			

			}

			else{

				try{
                    $bid=DB::table('ien_chapter')->where('id',$id)->column('bid');
        			$gzid=DB::table('ien_book')->where('id',$bid['0'])->column('gzzj');
                      if(!empty($gzid['0']) && $gzid['0']>0)
                      {
                    	return $gzid['0'];
                      }
                      else
                      {
                        return module_config('agent.agent_guanzhu');
                      }
                    }
                      catch(\Exception $e){
                        return module_config('agent.agent_guanzhu');
                      }

				}

			

		}

		else{

			try{
                    $bid=DB::table('ien_chapter')->where('id',$id)->column('bid');
        			$gzid=DB::table('ien_book')->where('id',$bid['0'])->column('gzzj');
                      if(!empty($gzid['0']) && $gzid['0']>0)
                      {
                    	return $gzid['0'];
                      }
                      else
                      {
                        return module_config('agent.agent_guanzhu');
                      }
                    }
                      catch(\Exception $e){
                        return module_config('agent.agent_guanzhu');
                      }

			}

		

		

		

		}

		

		

	//判断是否需要关注

	public function isguanzhu($zid=null,$guanzhu=null)

	{

		$chapter=DB::table('ien_chapter')->where('id',$zid)->find();
		$gz=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();
		$data['forceFollow']="true";
		$data['showFollowPopupOnNext']="false";
		if($gz['isguanzhu']==0)
		{
			$gzms=DB::table('ien_wechat_uconfig')->where('uid',$gz['sxid'])->find();
			if(empty($gzms) || empty($gzms['gzmoshi']) || $gzms['gzmoshi']==0)	
			{		
					$data['forceFollow']="false";
			}
			else{
	          		if($chapter['idx']>=$guanzhu)
					{
						$data['showFollowPopupOnNext']="true";
					}
			}
		}

			$user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();


			if($user['sxid']==0)
					{
						$sxid=0;
					}
			else
					{
						$sxid=$user['sxid'];
					}

			
			//跳转关注页面
			$data['ewm']=$this->erweima($sxid);

			return $data;
			//$this->redirect('document/erweima',['id'=>$sxid]);

			}



	public function erweima($id=null)

	{

		$erweima=DB::table('ien_admin_user')->where('id',$id)->find();

		if(strlen($erweima['ewm'])<5)

		{
			
			$ewm=module_config('agent.agent_qzgzewm');
		}

		else{
			$ewm=$erweima['ewm'];
		}

		//$this->assign('ewm', $ewm);

		return $ewm;

		}

		public function payerweima($id=null)

	{

		$erweima=DB::table('ien_admin_user')->where('id',$id)->find();

		if(strlen($erweima['ewm'])<5)

		{
			
			$ewm=module_config('agent.agent_qzgzewm');
		}

		else{
			$ewm=$erweima['ewm'];
		}

		

		$this->assign('ewm', $ewm);

		return $this->fetch('payerweima');

		}


	

	

	//判断是否VIP章节

	public function isvip($zid=null)

	{


		$chapter=DB::table('ien_chapter')->where('id',$zid)->find();
		//限免
		$book=DB::table('ien_book')->where('id',$chapter['bid'])->find();
		if($book['isfree']=1 && $book['free_stime']<=time() && $book['free_etime']>=time())
		{
			return true;
		}

		$user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();

		if($chapter['isvip']==1)

		{
			//判断年费会员
			$user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();

			if($user['isvip']==1 && time()>$user['vipstime'] && time()<=$user['vipetime'])
			{
				$this->readold($zid);
				return true;
			}

			//判断是否消费过

			$map['uid']=$_SESSION['wechat_user']['original']['openid'];

			$map['zid']=$zid;

			$pay=DB::table('ien_consume_log')->where($map)->find();

			$bookscore=DB::table('ien_book')->where('id',$chapter['bid'])->find();
			
			if(empty($bookscore['score']))
			{
				$score=module_config('agent.agent_pay_money');
			}
			else{
				$score=$bookscore['score'];
			}		
			if(!$pay){
				//判断余额是否够金额16

				if(!$user || $user['score']<$score)

				{

				$this->redirect('pay/index');

				//$this->readold($zid);

				//跳转支付充值

				//支付充值成功跳转阅读历史

				}

				else

				{

					//消费积分，保存消费记录，添加阅读记录

					$data['zid']=$zid;

					$data['uid']=$_SESSION['wechat_user']['original']['openid'];

					$data['money']=$score;

					$data['addtime']=time();

					$res=DB::table('ien_consume_log')->insert($data);

					//减少会员积分

					

					//$core=$user['score'] - 21;

					$user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->setDec('score',module_config('agent.agent_pay_money'));

					

					$this->readold($zid);

					//显示文章

					return true;

					}

				}

			else{

				$this->readold($zid);

				return true;

				}

		}

		else{

			//添加阅读记录

			$this->readold($zid);

			return true;

			

			}

	}

	//发送签到消息
	public function checktempay($openid=null){
      if ($openid === 0) $this->error('参数错误');
      //查用户
      $user=DB::table('ien_admin_user')->where('openid',$openid)->find();
      //查上线是否开启通知
      $userdl=DB::table('ien_wechat_uconfig')->where('uid',$user['sxid'])->where('isopen',"on")->where('ispay',"on")->find();
      if(empty($userdl) || empty($userdl['addcore']) || empty($userdl['cordid']))
      {
        //没开或者内容没有。结束
        return true;
      }
      try{
      $config = [
                        /**
                         * Debug 模式，bool 值：true/false
                         *
                         * 当值为 false 时，所有的日志都不会记录
                         */
                        'debug' => true,
                        /**
                         * 账号基本信息，请从微信公众平台/开放平台获取
                         */
                        'app_id' => $userdl['appid'],         // AppID
                        'secret' => $userdl['appsecret'],     // AppSecret
                        'token' => $userdl['token'],          // Token
                        'aes_key' => $userdl['encodingaeskey'],                    // EncodingAESKey，安全模式下请一定要填写！！！
                        'wechat_name' => $userdl['name'],
                        'wechat_id' => $userdl['gid'],
                        'wechat_number' =>  $userdl['wxh'],
                        'wechat_type' => 1,
                        /**
                        * 缓存
                        */
                         //'cache'   => $cacheDriver,
                        /**
                         * 日志配置
                         *
                         * level: 日志级别, 可选为：
                         *         debug/info/notice/warning/error/critical/alert/emergency
                         * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
                         * file：日志文件位置(绝对路径!!!)，要求可写权限
                         */
                        'log' => [
                            'level' => 'debug',
                            'permission' => 0777,
                            'file' => './runtime/log/wechat/easywechat.log',
                        ],

                        /**
                         * Guzzle 全局设置
                         *
                         * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
                         */
                        'guzzle' => [
                            'timeout' => 3.0, // 超时时间（秒）
                            //'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
                        ],
                    ];
                    $cacheDriver = new RedisCache();
                    // 创建 redis 实例
                    $redis = new \Redis();
                    $redis->connect('localhost', 6379);
                    $cacheDriver->setRedis($redis);  
                    //如果上线是管理员的话,用平台服务号
                    if($user['sxid']==1 || $user['sxid']==0)
                    {
                    $config2 = module_config('wechat');
                    }
                    $config2['cache']=$cacheDriver;
                    $config = array_merge($config, $config2);

                        
                        $app = new Application($config);
                        $notice = $app->notice;
                        $userId = $openid;
                        $templateId = $userdl['cordid'];

                        $old=DB::table('ien_read_log')->where('uid',$openid)->order('update_time desc')->find();
                        if(empty($old))
                        {
                           $url = 'http://'.preg_replace("/\{\d\}/", $user['sxid'], module_config('agent.agent_tuiguangurl')).url('index/index');
                          }
                        else{
                           $url = 'http://'.preg_replace("/\{\d\}/", $user['sxid'], module_config('agent.agent_tuiguangurl')).url('document/detail',['id'=>$old['zid']]);
                          }

                        $tempinfoatt=preg_replace('/{nickname}/',$user['nickname'],$userdl['addcore']);
                        $tempinfoatt=preg_replace('/{score}/',$user['score'],$tempinfoatt);
                        $data = json_decode($tempinfoatt,true);  

                        $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
                        }
                        catch(\Exception $e){
                         echo "error" . PHP_EOL;
                         }     




    }
	

	//每日首次登陆赠送积分

	public function addcore($usecenter=null){

		$cur_date = strtotime(date('Y-m-d'));
		//如果会员中心过来的开启session;

		if($usecenter==1)
		{session_start();}

		//$map['create_time']=$cur_date;

		$map['uid']=$_SESSION['wechat_user']['original']['openid'];

		$map['type']=0;

		$addlog=DB::table('ien_pay_log')->where($map)->whereTime('addtime', 'today')->find();
		
		if(!$addlog)

		{

			$data['uid']=$_SESSION['wechat_user']['original']['openid'];

			$data['addtime']=time();

			$data['type']=0;

			//$data['money']=50;

			//验证必须关注

			$u=DB::table('ien_admin_user')->where('openid',$data['uid'])->find();

			if($u['isguanzhu']==1)

			{

			$add=DB::table('ien_pay_log')->insert($data);

			$user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();

			$user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->setInc('score',50);

			$data['data']="50";

			$data['status']=1;
			$this->checktempay($_SESSION['wechat_user']['original']['openid']);
			return json($data);

			}

			else{
				header("status: 400 Bad Request");

				$data['status']=0;

				$data['code']="already_checked_in";

				return json($data);

				}

			

			}

		header("status: 400 Bad Request");
		
		$data['status']=0;

		$data['code']="already_checked_in";

		return json($data);

		}
		//添加打赏记录
	public function tips($gift_id=null,$novel_id=null){
		session_start();
		if($gift_id==1)
		{
			$money=100;
		}
		if($gift_id==2)
		{
			$money=388;
		}
		if($gift_id==3)
		{
			$money=588;
		}
		if($gift_id==4)
		{
			$money=888;
		}
		$book=DB::table('ien_chapter')->where('id',$novel_id)->find();
		if(!empty($book))
		{
			$ucore=DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->find();

			if($ucore['score']>$money)
			{
				DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->setDec('score',$money);
				DB::table('ien_book')->where('id', $book['bid'])->setInc('tips',$money);
				$data['money']=$money;
				$data['cid']=$novel_id;
				$data['bid']=$book['bid'];
				$data['addtime']=time();
				$data['openid']=$_SESSION['wechat_user']['original']['openid'];
				DB::table('ien_tips')->insert($data);
				return true;
			}
			else{
				header("status: 400 Bad Request");
				$payload=['status'=> 0, 'message'=> "余额不足", 'code'=> 9999];
				return $payload;
			}
		}




	}

	

	

	

}