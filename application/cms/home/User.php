<?php

// +----------------------------------------------------------------------

// | 浩森PHP框架 [ IeasynetPHP ]

// +----------------------------------------------------------------------

// | 版权所有 2017~2018 北京浩森宇特互联科技有限公司 [ http://www.ieasynet.com ]

// +----------------------------------------------------------------------

// | 官方网站：http://ieasynet.com

// +----------------------------------------------------------------------

// | e3167d238c902311b5d6311ea6e68420

// +----------------------------------------------------------------------

// | 作者: 拼搏 <378184@qq.com>

// +----------------------------------------------------------------------



namespace app\cms\home;
use think\Db;
use util\Tree;
use EasyWeChat\Foundation\Application;
use Doctrine\Common\Cache\RedisCache;
use think\helper\Hash;
use think\Session;
/**
 * 前台首页控制器
 * @package app\cms\admin
 */

class User extends Common

{

    /**
     * 首页
     * @author 拼搏 <378184@qq.com>
     * @return mixed
     */

    public function login(){


    	return $this->fetch('login');

    }

    public function phone(){
    	session_start();
    	if ($this->request->isPost()) {
            // 获取post数据
            $data = $this->request->post();
            $captcha = $_POST['captcha'];
	        if(!captcha_check($captcha, '', config('captcha'))){
	            //验证失败
	            $res['err']="验证码不正确";
	            return $res;
	        };
	        $password=input('post.pass/s');
	        $username=input('post.user/s');
	        if($user=DB::table('ien_admin_user')->where('username',$username)->find())
	        	{
	        		if(!Hash::check((string)$password, $user['password']))
	        			{
	        				$res['err']="帐号或密码不正确！";
	            			return $res;
	        			}
	        		$_SESSION['wechat_user']['original']['openid']=$username;
	        		$_SESSION['wechat_user']['id']=$username;
            		$res['code']=0;
            		if(empty($_SESSION['target_url']))
            		{
            			$res['url']=url('cms/user/index');
            		}
            		else{
            			$res['url']=$_SESSION['target_url'];
            		}
            		return $res;
	        	}
	        else{
	        	$res['err']="帐号或密码不正确！";
	            return $res;
	        }
        }
    	return $this->fetch('phonelogin');
    }

    public function reg(){
    	session_start();
    	if ($this->request->isPost()) {
            // 获取post数据
            $data = $this->request->post();
            $scode=DB::table('ien_sendsms_log')->where('phone',input('post.user_mobile/d'))->where('code',input('post.sms_code/d'))->whereTime('addtime','-1 hours')->order('addtime desc')->fetchSql(true)->find();
            if(!$scode || empty($scode))
            {
            	$res['err']="验证码不正确！";
	            return $res;
            }
            $preg="/1[\d]{3,15}/is";
	        if (!preg_match($preg,$data['user_mobile'],$arr)) {
				$res['err']="手机号不正确";
	            return $res;
				}
			$ishas=DB::table('ien_admin_user')->where('username',input('post.user_mobile/d'))->find();
			if($ishas)
			{
				$res['err']="手机号已被注册，如有问题请联系管理员！";
	            return $res;
			}
			if(strlen(input('post.user_pass/s'))<6)
			{
				$res['err']="密码长度必须大于6位！";
	            return $res;
			}
			if(strlen(input('post.user_nick/s'))<1)
			{
				$res['err']="请输出正确昵称！";
	            return $res;
			}
			$insert['username']=input('post.user_mobile/d');
			$insert['nickname']=input('post.user_nick/s');
			$insert['password']=Hash::make((string)input('post.user_pass/s'));
			$insert['mobile']=input('post.user_mobile/d');
			$insert['mobile_bind']=1;
			$insert['avatar']="http://".module_config('agent.agent_rooturl')."/images/homeuser.png";
			$insert['create_time']=time();
            $insert['role']=3;
            $insert['status']=1;
			$insert['openid']=input('post.user_mobile/d');
			$insert['agentlogin']=3;
			$insert['sxid']= $_SESSION['sxid']?$_SESSION['sxid']:0;
            $insert['tgid']=$_SESSION['tgid']?$_SESSION['tgid']:0;
            $insert['gzopenid']=$_SESSION['gzopenid']?$_SESSION['gzopenid']:0;

            if(DB::table('ien_admin_user')->insert($insert))
            	{
            		$_SESSION['wechat_user']['original']['openid']=$insert['username'];
            		$_SESSION['wechat_user']['id']=$insert['username'];
            		$res['code']=0;
            		if(empty($_SESSION['target_url']))
            		{
            			$res['url']=url('cms/user/index');
            		}
            		else{
            			$res['url']=$_SESSION['target_url'];
            		}
            	}
            	return $res;
        }

    	return $this->fetch('phonereg');
    }

    public function checkcode($re=null){
    	if(empty($re))
    	{
	    	$captcha = $_POST['captcha'];
	        if(!captcha_check($captcha, '', config('captcha'))){
	            //验证失败
	            $res['err']="验证码不正确";
	            return $res;
	        };
    	}
    	else{
    		$issend=DB::table('ien_sendsms_log')->where('phone',input('post.user_mobile/d'))->order('addtime desc')->find();
    		if(!empty($issend))
    		{
    			$sc=time()-$issend['addtime'];
    			if($sc<60)
    			{
    				$res['err']="两次发送间隔太短，请耐心等待！";
    				return $res;
    			}
    		}
    	}
        $preg="/1[\d]{3,15}/is";
        if (!preg_match($preg,$_POST['user_mobile'],$arr)) {
			$res['err']="手机号不正确";
            return $res;
			}
		$ishas=DB::table('ien_admin_user')->where('username',input('post.user_mobile/d'))->find();
		if($ishas)
		{
			$res['err']="手机号已被注册，如有问题请联系管理员！";
            return $res;
		}
		if(strlen(input('post.user_pass/s'))<6)
		{
			$res['err']="密码长度必须大于6位！";
            return $res;
		}
		if(strlen(input('post.user_nick/s'))<1)
		{
			$res['err']="请输出正确昵称！";
            return $res;
		}
		sendsms(input('post.user_mobile/d'));
		$res['code']=0;
		$res['suc']="短信发送成功";
		return $res;



    }

    public function index()

    {
    	session_start();

    	if(!is_weixin())
        {
        	//非微信端未登录，跳转登录
        	//判断登录状态
        	if(empty($_SESSION['wechat_user'])){
            $this->redirect('user/login');
        	}

        $user=DB::table('ien_admin_user')->where('username',$_SESSION['wechat_user']['original']['openid'])->find();
		$vip="";
		if($user['isvip']=1 && $user['vipstime']<=time() && $user['vipetime']>=time())
		{
			$vip="VIP会员有效期：".date('Y/m/d',$user['vipstime'])."-".date('Y/m/d',$user['vipetime']);
		}

		$this->assign('user', $user);
		$this->assign('vip', $vip);

        return $this->fetch(); // 渲染模板
        	

        }
        else{
    	// 微信网页授权接口
		 /*登陆验证方法*/
		session_start();
        $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(empty($_SESSION['wechat_user'])){
            $this->redirect('oauth/oauth');
            //$this-> checklogin();
        }
		//如果上线ID有人,并且设置了关注.那么拉取关注openid去公众号里面查用户信息
		$user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();
		if($user['sxid']!=0 && $user['gzopenid']!="")
		{
			//查上线代理对接信息
			$userdl=DB::table('ien_wechat_uconfig')->where('uid',$user['sxid'])->where('isopen',"on")->find();
			if(!empty($userdl))
			{
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
				    try{
				    $cacheDriver = new RedisCache();
			        // 创建 redis 实例
			        $redis = new \Redis();
			        $redis->connect('localhost', 6379);
			        $cacheDriver->setRedis($redis); 

			        //$config2 = module_config('wechat');
			        $config2['cache']=$cacheDriver;
			        $config = array_merge($config, $config2);
			        $app = new Application($config);
			        $userService = $app->user;
			        $userinfo = $userService->get($user['gzopenid']);
			        if(!empty($userinfo))
			        {
			        	$data=['nickname'=>$userinfo['nickname'],'name'=>$userinfo['nickname'],'sex'=>$userinfo['sex'],'avatar'=>$userinfo['headimgurl'],];

			        	DB::table('ien_admin_user')->where('openid', $_SESSION['wechat_user']['original']['openid'])->update($data);
			        }
			    }
			    catch(\Exception $e){
			    	
			    }


			}


		}	

		$user=DB::table('ien_admin_user')->where('openid',$_SESSION['wechat_user']['original']['openid'])->find();
		$vip="";
		if($user['isvip']=1 && $user['vipstime']<=time() && $user['vipetime']>=time())
		{
			$vip="VIP会员有效期：".date('Y/m/d',$user['vipstime'])."-".date('Y/m/d',$user['vipetime']);
		}

		$this->assign('user', $user);
		$this->assign('vip', $vip);

		

        return $this->fetch(); // 渲染模板
    	}
    }
	//送书币
	public function free(){
		session_start();
		$map['uid']=$_SESSION['wechat_user']['original']['openid'];
		$map['type']=0;
		$addlog=DB::table('ien_pay_log')->where($map)->whereTime('addtime', 'today')->find();
		if(!empty($addlog))
		{
			$log="<a href='javascript:;' class='weui_btn weui_btn_primary weui_btn_disabled'>您今天已签到过, 明天再来吧</a>";
		}
		else
		{
			$log="<a href='JavaScript:checkin();' class='weui_btn weui_btn_primary'>立即签到</a>";
		}
		$this->assign('log', $log);
		return $this->fetch('free');
		
		}


   //书签
   public function bookmark(){
	   session_start();
	   if(!is_weixin())
        {
        	//非微信端&开启后台微信端
        	//判断登录状态
        	if(empty($_SESSION['wechat_user'])){
            $this->redirect('user/login');
        	}
        }
	   /*登陆验证方法*/
        $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        if(empty($_SESSION['wechat_user'])){
            $this->redirect('oauth/oauth');
            //$this-> checklogin();
        }
		
		$openid=$_SESSION['wechat_user']['original']['openid'];
		$bookmark=DB::view('ien_bookmarks')
		->view('ien_chapter','bid,idx,title','ien_chapter.id=ien_bookmarks.zid')
		->where('ien_bookmarks.uid',$openid)
		->select();
		
		$this->assign('bookmark', $bookmark);
		return $this->fetch('bookmark');
		
	   
	   
	   }
	 public function delmark($id=null)
	 {
		 /*登陆验证方法*/
		session_start();
		if(!is_weixin())
        {
        	//非微信端&开启后台微信端
        	//判断登录状态
        	if(empty($_SESSION['wechat_user'])){
            $this->redirect('user/login');
        	}
        }
        $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(empty($_SESSION['wechat_user'])){
            $this->redirect('oauth/oauth');
            //$this-> checklogin();
        }
		
		$openid=$_SESSION['wechat_user']['original']['openid'];
		$bookmark=DB::table('ien_bookmarks')->where('uid',$openid)->select();
		
		if(DB::table('ien_bookmarks')->where('id',$id)->delete())
		return true;
		else
		return false;
		
		 
		 
		 }
		 public function delread($id=null)
	 {
		
		
		if(DB::table('ien_read_log')->where('id',$id)->delete())
		return true;
		else
		return false;
		
		 
		 
		 }
		 //自动阅读历史跳转
   	public function readold($openid=null)
	{
		$gzopenid=$openid;
		 /*登陆验证方法*/
		session_start();
		if(!is_weixin())
        {
        	//非微信端&开启后台微信端
        	//判断登录状态
        	if(empty($_SESSION['wechat_user'])){
            $this->redirect('user/login');
        	}
        }
        $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(empty($_SESSION['wechat_user'])){
            $this->redirect('oauth/oauth');
            //$this-> checklogin();
        }
		$openid=$_SESSION['wechat_user']['original']['openid'];
		$old=DB::table('ien_read_log')->where('uid',$openid)->order('update_time desc')->find();
		
		$guanzhu=DB::table('ien_admin_user')->where('openid',$openid)->update(['isguanzhu'=>1,'gzopenid'=>$gzopenid]);
		if(empty($old))
		{
			 $this->redirect('index/index');
			}
		else{
			$this->redirect('document/detail',['id'=>$old['zid']]);
			}
		
		
		
		
		
		
	}
   
   

    public function readhistory()

    {

      // 微信网页授权接口
		/*登陆验证方法*/
		session_start();
		if(!is_weixin())
        {
        	//非微信端&开启后台微信端
        	//判断登录状态
        	if(empty($_SESSION['wechat_user'])){
            $this->redirect('user/login');
        	}
        }
        $_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(empty($_SESSION['wechat_user'])){
            $this->redirect('oauth/oauth');
            //$this-> checklogin();
        }
    

        $history = Db::view('read_log','id,bid,zid')

        ->view('chapter',['title'=>'ctitle','idx'=>'idx'],'chapter.id=read_log.zid','LEFT')

        ->view('book',['title'=>'btitle'],'book.id=read_log.bid','LEFT')

        ->where("read_log.uid" , $_SESSION['wechat_user']['original']['openid'])

        ->order('read_log.id desc')

        ->select();

                

        $this->assign('history', $history);    

        return $this->fetch(); // 渲染模板

    }
    public function gifts()
    {
    	return $this->fetch('gifts');
    }
   



}