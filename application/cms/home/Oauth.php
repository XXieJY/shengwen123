<?php
// +----------------------------------------------------------------------
// | 浩森PHP框架 [ IeasynetPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2017~2018 北京浩森宇特互联科技有限公司 [ http://www.ieasynet.com ]
// +----------------------------------------------------------------------
// | 官方网站：http://ieasynet.com
// +----------------------------------------------------------------------
// | 5LiK6aW25biC55ub5paH56eR5oqA5pyJ6ZmQ5YWs5Y+4
// +----------------------------------------------------------------------
// | 作者: 拼搏 <378184@qq.com>
// +----------------------------------------------------------------------

namespace app\cms\home;

use app\index\controller\Home;
use think\Db;
use util\Tree;
use EasyWeChat\Foundation\Application;
use Doctrine\Common\Cache\RedisCache;
//use think\Session;
/**
 * 前台公共控制器
 * @package app\cms\admin
 * http://book.ieasynet.com/index.php/cms/document/detail/id/7.html?t=5555
 */
class oauth extends Home
{
    
    
    public function oauth()
    {
        if(!is_weixin())
        {
            $this->assign('name', module_config('wechat.name'));
            return $this->fetch('oauth/pcwechat');
        }

        session_start();
        //$_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $url=$_SERVER['HTTP_HOST'];

        //获取代理商ID
        preg_match("/[1-9]\d*/",$url,$dlid);
        if(!empty($dlid['0']))
        {
          $wid=$dlid['0'];
        }
        else
        {
          $wid=0;
        }

        
        //如果用代理登陆
        $userdl=DB::table('ien_wechat_uconfig')->where('uid',$wid)->where('isoauth',"on")->find();
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
                    
                    if(module_config('agent.agent_base_login')=="on")
                    {
                    $config2 = [
                      // ...
                      'oauth' => [
                      'scopes'   => ['snsapi_base'],
                      //'scopes'   => ['snsapi_userinfo'],
                      'callback' => 'http://'.$_SERVER['HTTP_HOST'].'/index.php/cms/oauth/oauth_callback/',
                
                                  ],
                    ];
                    }
                    else
                    {
                      $config2 = [
                      // ...
                      'oauth' => [
                      //'scopes'   => ['snsapi_base'],
                      'scopes'   => ['snsapi_userinfo'],
                      'callback' => 'http://'.$_SERVER['HTTP_HOST'].'/index.php/cms/oauth/oauth_callback/',
                
                                  ],
                    ];

                    }
              }
              else{
                $config=module_config('wechat');
               if(module_config('agent.agent_base_login')=="on")
                    {
                    $config2 = [
                      // ...
                      'oauth' => [
                      'scopes'   => ['snsapi_base'],
                      //'scopes'   => ['snsapi_userinfo'],
                      'callback' => 'http://'.module_config('agent.agent_rooturl').'/index.php/cms/oauth/oauth_callback/?url='.$_SESSION['target_url'],
                
                                  ],
                    ];
                    }
                    else
                    {
                      $config2 = [
                      // ...
                      'oauth' => [
                      //'scopes'   => ['snsapi_base'],
                      'scopes'   => ['snsapi_userinfo'],
                      'callback' => 'http://'.module_config('agent.agent_rooturl').'/index.php/cms/oauth/oauth_callback/?url='.$_SESSION['target_url'],
                
                                  ],
                    ];

                    }
              }


       

        $cacheDriver = new RedisCache();
        // 创建 redis 实例
        $redis = new \Redis();
        $redis->connect('localhost', 6379);
        $cacheDriver->setRedis($redis); 
        
        
        $config['cache']=$cacheDriver;
        $config = array_merge($config, $config2);
      
        $app = new Application($config);
        $oauth = $app->oauth;
        //dump($oauth);
        
        //$this->checklogin();

        $oauth->redirect()->send();


    }
    public function oauth_callback($url=null)
    {
        
      
        //ini_set('session.cookie_domain', 't1.ieasynet.net');

        session_start();

        if(empty($url))
        {
          $urlzz=$_SESSION['target_url'];
        }
        else
        {
          $urlzz=$url;
        }



        $rooturl=$_SERVER['HTTP_HOST'];

        //获取代理商ID
        preg_match("/[1-9]\d*/",$rooturl,$dlid);
        if(!empty($dlid['0']))
        {
          $wid=$dlid['0'];
        }
        else
        {
          $wid=0;
        }
        $agentlogin=0;

        
        //如果用代理登陆
        $userdl=DB::table('ien_wechat_uconfig')->where('uid',$wid)->where('isoauth',"on")->find();
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
                    $agentlogin=1;
                   
              }
              else
              {
                $config = module_config('wechat');
              }


        $cacheDriver = new RedisCache();
        // 创建 redis 实例
        $redis = new \Redis();
        $redis->connect('localhost', 6379);
        $cacheDriver->setRedis($redis); 

        
        $config2['cache']=$cacheDriver;

        $app = new Application($config);
        $oauth = $app->oauth;
        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();

        $dbuser=DB::table('ien_admin_user')->where('openid',$user['original']['openid'])->find();
        $urla['1']="";
        if(!$dbuser)
        {
        if(strpos($urlzz,"?"))
        {
            $urlb=explode("?",$urlzz);
            $urla=explode("=", $urlb[1]);
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
        if(module_config('agent.agent_base_login')=="on")
        {
        $data = [
                     'username' => $user['nickname']."读者", 
                 //'username' => $user['nickname'], 
                     'nickname' => $user['nickname']."读者", 
               //'nickname' => $user['nickname'],
                     'password' => '$2y$10$wwJ7bP4SLfGWZ3.DTQ0RdeglgBLAW5iY4mA6LvoDuQrvcV6qsKdou',
                     'email'    =>  $user['email']." ", 
                     'avatar'    => $user['avatar']."http://".module_config('agent.agent_rooturl')."/images/homeuser.png",
                 //'avatar'    => $user['avatar'],
                     'create_time'    => time(),
                     'last_login_time'    => time(),
                     'openid'    => $user['original']['openid'],
                     'role'      =>   '3',
                     'tgid'      => $urla['1'],
                     'status'    => '1', 
                     'sxid'      =>$sxid['uid'],
                     'agentlogin'=>$agentlogin,
                     //'sex'       =>$user['original']['sex'],
                    ];
            }
            else
            {
              $data = [
                     //'username' => $user['nickname']."读者", 
                 'username' => $user['nickname'], 
                     //'nickname' => $user['nickname']."读者", 
               'nickname' => $user['nickname'],
                     'password' => '$2y$10$wwJ7bP4SLfGWZ3.DTQ0RdeglgBLAW5iY4mA6LvoDuQrvcV6qsKdou',
                     'email'    =>  $user['email']." ", 
                     //'avatar'    => $user['avatar']."http://".module_config('agent.agent_rooturl')."/images/homeuser.png",
                 'avatar'    => $user['avatar'],
                     'create_time'    => time(),
                     'last_login_time'    => time(),
                     'openid'    => $user['original']['openid'],
                     'role'      =>   '3',
                     'tgid'      => $urla['1'],
                     'status'    => '1', 
                     'sxid'      =>$sxid['uid'],
                     'agentlogin'=>$agentlogin,
                     //'sex'       =>$user['original']['sex'],
                    ];

            }
            
            Db::table('ien_admin_user')->insert($data);
            }
            else{
               if(module_config('agent.agent_fltime')!=0 || module_config('agent.agent_fltime') !="")
               {
                 $timecha=ceil((time()-$dbuser['create_time'])/86400); 
                 if($timecha>module_config('agent.agent_fltime'))
                 {
                    $nokou=explode(',',module_config('agent.agent_nokou'));
                    if(!in_array($dbuser['sxid'],$nokou))
                    {
                      DB::table('ien_admin_user')->where('openid', $dbuser['openid'])->update(['isout'=>'1']);
                    }
                 }
               }

            }
          
        //判断跳转
            //dump($data);
            //session_destroy();

            //ini_set('session.cookie_domain', 't1.ieasynet.net');
            //如果是用子代理登陆，没有传送url值，直接保存session就可以
            if(empty($url))
            {

            $_SESSION['wechat_user'] = $user->toArray();

            if(empty($urlzz))
            {
              $url="/index.php/cms/index/index/";
            }
            else
            {
              $url=$urlzz;
            }
            header('location:'. $url);
            }
            else
            {
              //如果有url，就是用平台登陆的，跳转到域名，然后保存session
              //传跳转url $urlzz和用户信息 $user->toArray();
              preg_match("/(\w+\.){2}\w+/",$urlzz,$jumpurl);

              $user=base64_encode(json_encode($user->toArray()));
              
              $jumpurl="http://".$jumpurl[0]."/index.php/cms/oauth/jump/?user=".$user."&dump=".$urlzz;
              
              header('location:'. $jumpurl);
            }


        //dump($_SESSION['wechat_user']);
        //$_SESSION['wechat_user'] = $user->toArray();
       // $targetUrl = empty($_SESSION['target_url']) ? '/' : $_SESSION['target_url'];
        //header('location:'. $targetUrl); // 跳转到 user/profile

    }


    public function jump($user=null,$dump=null){
        session_start();
        $user=json_decode(base64_decode($user),true);
        $_SESSION['wechat_user'] = $user;
        header('location:'. $dump);
    }


    }