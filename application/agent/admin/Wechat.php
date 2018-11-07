<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/7/4
 * Time: 13:56
 */

namespace app\agent\admin;

use think\Config;
use think\Exception;
use think\Log;
use think\Db;
use EasyWeChat\Foundation\Application;
use Doctrine\Common\Cache\RedisCache;

class Wechat
{
   //发送模板消息
    function tmsend($id = null ,$openid = null,$tminfo=[],$userdl=[],$username=null,$score=null)
    {   
        
        $config=[];
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
                }
                    $cacheDriver = new RedisCache();
                    // 创建 redis 实例
                    $redis = new \Redis();
                    $redis->connect('localhost', 6379);
                    $cacheDriver->setRedis($redis); 

                    //如果管理员的话,用平台服务号
                    if($tminfo['uid']==1)
                    {
                    $config2 = module_config('wechat');
                    }
                    $config2['cache']=$cacheDriver;
                    $config = array_merge($config, $config2);
                    try{
                    $app = new Application($config);
                    $notice = $app->notice;
                    $userId = $openid;
                    $templateId = $tminfo['tmid'];
                    $url = $tminfo['url']; 
                    $tempinfoatt=preg_replace('/{nickname}/',$username,$tminfo['temp']);
                    $tempinfoatt=preg_replace('/{score}/',$score,$tempinfoatt);
                    $data = json_decode($tempinfoatt,true);  

                    $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
                    }
                    catch(\Exception $e){
                     echo "error" . PHP_EOL;
                     }               
                    

    }

}