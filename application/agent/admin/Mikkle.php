<?php
namespace app\agent\admin;


use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Exception;
use think\Log;
use app\agent\admin\Redis;
use think\Db;
use EasyWeChat\Foundation\Application;
use Doctrine\Common\Cache\RedisCache;
use app\agent\admin\Wechat;


/**
 * Created by PhpStorm.
 * Power by Mikkle
 * QQ:776329498
 * Date: 2017/6/12
 * Time: 15:07
 */
class Mikkle extends Command
{
    protected $sleep = 3;
    protected $redis;
    protected $listName;
    protected $pcntl;
    public function __construct($name= null)
    {
        parent::__construct($name);
        $this->redis=Redis::instance(Config::get("command.redis"));
        $this->listName = "worker_list";
        $this->pcntl =true;
    }

    protected function configure()
    {
        $this->setName('mikkle')->setDescription('Here is the mikkle\'s command ');
    }

    protected function execute(Input $input, Output $output)
    {
        
        while(true){

            //标记后端服务运行中
            $this->signWorking();
            echo "==================================================".PHP_EOL;
            $this->autoClass();
            echo "==================================================".PHP_EOL;
            $this->sleep();
        }
    }
 


    /**
     * 自动执行
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return bool
     */
    protected function autoClass()
    {

        $works = $this->getWorks();
        if ($works) {
            $tminfo=DB::table('ien_tem_info')->where('id',$works)->find();
            $userdl=DB::table('ien_wechat_uconfig')->where('uid',$tminfo['uid'])->where('isopen',"on")->find();
            $config = [];
            //获取全部用户
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
                   
                    $app = new Application($config);
                    $userService = $app->user;
                    $users = $userService->lists();

                    $openid=$users->data['openid'];
                    $next_openid=$users->next_openid;
                    while(!empty($next_openid))
                    {
                        try{
                        $users = $userService->lists($nextOpenId=$next_openid);  
                        $openid=array_merge($openid,$users->data['openid']);
                        $next_openid=$users->next_openid;
                        }
                        catch(\Exception $e){
                            $next_openid="";
                        }
                    }

            $work='app\agent\admin\Wechat';
            //$item='tmsend';
            $id=$works;

            foreach ($openid as $item => $openid) {
                $user=DB::table('ien_admin_user')->whereOr('openid',$openid)->whereOr('gzopenid',$openid)->find();
                $username=$user['nickname'];
                $score=$user['score'];
                if ($this->pcntl) {
                    $this->pcntlWorker($work, $item,$id,$openid,$tminfo,$userdl,$username,$score);
                } else {
                    $this->runWorker($work, $item,$id,$openid,$tminfo,$userdl,$username,$score);
                }
            }
            DB::table('ien_tem_info')->where('id',$id)->update(['issend'=>1]);
            $works="";   
        } else {
            return false;
        }


    }

    public function getWorks(){

        try{
            $tminfo=DB::table('ien_tem_info')->whereTime('sendtime', '<', time())->where('issend',0)->limit(1)->find();
            DB::table('ien_tem_info')->where('id',$tminfo['id'])->update(['issend'=>2]);
            return $tminfo['id'];
        }catch (Exception $e){
            return false;
        }
    }


    /**
     * 检测执行方法是否存在
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $work
     * @param $item
     * @return bool
     */
    protected function checkWorkerExists($work,$item){

        if (class_exists($work)){
            if(method_exists($work,'tmsend')){
                return true;
            }else{
                return false;
            }
        }

    }

    /**
     * 运行任务
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $work
     * @param $item
     */
    protected function runWorker($work,$item,$id,$openid,$tminfo,$userdl,$username,$score){
        try{
            if($this->checkWorkerExists($work, $item)) {
                echo "执行[{$work}]任务" . PHP_EOL;
                $w=new $work;
                $w->tmsend($id,$openid,$tminfo,$userdl,$username,$score);
                Log::notice("执行[{$work}]任务");
            }else{
                echo "执行[{$work}]任务的tmsend方法不存在".PHP_EOL;
                //$this->redis->hdel($this->listName,$item);
            }
        }catch (Exception $e){
            echo "执行[{$work}]任务失败" . PHP_EOL;
            Log::notice($e->getMessage());
            if ($this->pcntl) {
                $this->pcntlKill();
            }
        }
    }




    /**
     * 分进程
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $work
     * @param $item
     */
    protected function pcntlWorker($work,$item,$id,$openid,$tminfo,$userdl,$username,$score)
    {
        try{
            // 通过pcntl得到一个子进程的PID
            $pid = pcntl_fork();
            if ($pid == -1) {
                // 错误处理：创建子进程失败时返回-1.
                die ('could not fork');
            } else if ($pid) {
                // 父进程逻辑

                // 等待子进程中断，防止子进程成为僵尸进程。
                // WNOHANG为非阻塞进程，具体请查阅pcntl_wait PHP官方文档
                pcntl_wait($status, WNOHANG);
            } else {
                // 子进程逻辑
                $pid_2 = pcntl_fork();
                if ($pid_2 == -1) {
                    // 错误处理：创建子进程失败时返回-1.
                    die ('could not fork');
                } else if ($pid_2) {
                    // 父进程逻辑
                    echo "父进程逻辑开始" . PHP_EOL;
                    // 等待子进程中断，防止子进程成为僵尸进程。
                    // WNOHANG为非阻塞进程，具体请查阅pcntl_wait PHP官方文档
                    pcntl_wait($status, WNOHANG);
                    echo "父进程逻辑结束" . PHP_EOL;
                } else {
                    // 子进程逻辑
                    echo "子进程逻辑开始" . PHP_EOL;

                    $this->runWorker($work,$item,$id,$openid,$tminfo,$userdl,$username,$score);

                    echo "子进程逻辑结束" . PHP_EOL;
                    $this->pcntlKill();
                }
                $this->pcntlKill();
            }
        }catch (Exception $e){
            Log::error($e->getMessage());
        }
    }

    /**
     * Kill子进程
     * Power: Mikkle
     * Email：776329498@qq.com
     */
    protected function pcntlKill(){
        // 为避免僵尸进程，当子进程结束后，手动杀死进程
        if (function_exists("posix_kill")) {
            posix_kill(getmypid(), SIGTERM);
        } else {
            system('kill -9' . getmypid());
        }
        exit ();
    }

    public function signWorking(){
        $this->redis->set("command","true");
        $this->redis->setExpire("command",10);
    }
    public function sleep($second=""){
        $second = $second ? $second : $this->sleep;
      //  echo "开始睡眠{$second}秒!当前时间:" . date('h:i:s') . PHP_EOL;
        sleep(sleep($second));   //TP5的命令行 sleep($second) 不生效
        echo "睡眠{$second}秒成功!当前时间:" . date('h:i:s') . PHP_EOL;
    }

    /**
     * @return int
     */
    public function getSleep()
    {
        return $this->sleep;
    }

    /**
     * @param  int $sleep
     * @return void
     */
    public function setSleep($sleep)
    {
        $this->sleep = $sleep;
    }



}