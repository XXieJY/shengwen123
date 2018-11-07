<?php
/**
 * User: pinbo
 * Date: 2017/4/8
 * Time: 上午9:50
 * b363c3ddb38a38e6c4fd7653fdc3cf00
 */
//后台访问控制器
namespace app\agent\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use think\Db;
use EasyWeChat\Foundation\Application;
use Doctrine\Common\Cache\RedisCache;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Text;


class User extends Admin{
    public function index(){

        $map = $this->getMap();
        $user=DB::table('ien_admin_user')->where('id',UID)->find();
        if($user['role']==1)
        {
            //管理员查看所有,有问题.不能所有用户
            $datalist=DB::table('ien_admin_user')
            ->where($map)
            ->whereNotIn('role',1)
            ->whereNotIn('role',3)
            ->order('create_time desc')
            ->paginate();

        }
        else{
            $map['did']=UID;
            $datalist=DB::table('ien_admin_user')
            ->where($map)
            ->order('create_time desc')
            ->paginate();
        }
    
        //SELECT ien_admin_user.id,ien_admin_user.username,ien_admin_user.nickname,ien_admin_user.create_time,ien_admin_user.last_login_time,ien_admin_user.xingming,ien_admin_user.fcbl,a.czje FROM `ien_admin_user` LEFT JOIN (SELECT did,sum(money) as czje FROM `ien_pay_log` group by did) as a ON `a`.`did`=`ien_admin_user`.`id` ORDER BY create_time desc
        
        $btn_optionurl = [
            'title' => '代理登陆设置信息',
            'icon'  => 'fa fa-fw fa-key',
            'class' => 'btn btn-xs btn-default ',
            'href'  => url('optionurl', ['id' => '__id__']),
        ];
        $btn_dingdan = [
            'title' => '代理全部订单',
            'icon'  => 'fa fa-fw fa-shopping-cart',
            'class' => 'btn btn-xs btn-default ',
            'href'  => url('dingdan', ['id' => '__id__']),
        ];



        return ZBuilder::make('table')
            ->hideCheckbox()
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['username', '用户名'],
                ['nickname','昵称'],
                ['role','用户组',['2'=>'代理商','4'=>'渠道商']]
                //['czje','充值金额'],
            ])
            ->addColumn('czje','推广充值金额','callback',function($data){
                    $czje=DB::table('ien_pay_log')->where('did',$data['id'])->where('status','1')->sum('money');
                    return $czje;
                },'__data__')
                
             ->addColumns([
                ['create_time','注册时间','datetime'],
                ['last_login_time','最后登录时间','datetime'],
                ['xingming','姓名'],
                ['fcbl','分成比例'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('add')
            ->addRightButton('edit')
            ->addRightButton('custom', $btn_optionurl) 
            ->addRightButton('custom', $btn_dingdan) 
            ->setRowList($datalist) // 设置表格数据
            ->fetch(); // 渲染模板
        }
        
    public function add(){
        // 添加代理商
        if ($this->request->isPost()) {
            $data = $this->request->post();

        if ($data['username'] == '') {
            $this->error('用户名不能为空');
            return false;
        }

        if ($data['xingming'] == '') {
            $this->error('姓名不能为空');
            return false;
        }
        if ($data['did'] == '') {
            $this->error('出错了!联系管理员');
            return false;
        }
        if ($data['fcbl'] == '') {
            $this->error('分成比例不能为空');
            return false;
        }

        if ($data['fcbl'] != 0.1 && $data['fcbl'] != 0.2 && $data['fcbl'] != 0.3 && $data['fcbl'] != 0.4 && $data['fcbl'] != 0.5 && $data['fcbl'] != 0.6 && $data['fcbl'] != 0.7 && $data['fcbl'] != 0.8 && $data['fcbl'] != 0.9 ) {
            $this->error('分成比例写错啦!例:0.6,0.7,0.8,0.9');
            return false;
        }
        if ($data['fangshi'] == '') {
            $this->error('提现方式不能为空');
            return false;
        }
        if ($data['zhanghao'] == '') {
            $this->error('提现账号不能为空');
            return false;
        }
        $data['nickname']=$data['xingming'];
            if (false === DB::table('ien_admin_user')->insert($data)) {
                $this->error('创建失败');
            }
            $this->success('创建成功');
        }

        $user=DB::table('ien_admin_user')->where('id',UID)->find();
        if($user['role']==1)
        {
            //管理员可以添加其他级别   
            $list_jb = ['4' => '渠道商', '2' => '代理商'];
        }
        else if($user['role']==4){
            $list_jb = ['2' => '代理商'];

        }
          // 显示添加页面
        return ZBuilder::make('form')
            ->setPageTips('新建账号密码为"123456",请用户登录后自行修改!<br/>设置成功之后,在代理商管理中,点击代理登陆信息,复制发送给用户!')
            ->addFormItems([
                ['hidden', 'did', UID],
                ['hidden', 'status', 1],
                ['text', 'username', '用户名', '必填'],
                ['hidden', 'password', '$2y$10$cRk7.QQQr310e5jmBCbKrOEPuUC6WCpHKyYTsnA8NpxGAnhWjp8gq'],
                ['text', 'guanzhu', '关注章节', '选填,20章开始付费阅读'],
                ['text', 'ewm', '二维码链接', '选填'],
                ['text', 'fcbl', '分成比例', '必填,请填写格式:0.6'],
                ['text', 'xingming', '姓名', '必填,提现姓名'],
                ['text', 'fangshi', '提现方式', '必填,提现方式'],
                ['text', 'zhanghao', '提现账号', '必填,提现账号'],
                ['hidden', 'create_time', $this->request->time()],
                ['hidden', 'update_time', $this->request->time()],

            ])
            ->addSelect('role', '选择级别', '请选择代理级别', $list_jb, '2')
            ->isAjax(true)
            ->layout(['username' => 6, 'guanzhu' => 6, 'ewm' => 6, 'xingming' => 3, 'fangshi' => 3, 'zhanghao' => 3,'role' => 3, 'fcbl' => 6])
            ->fetch();

    }
    
        public function edit($id = null){
        if ($id === 0) $this->error('参数错误');
        // 添加代理商
        if ($this->request->isPost()) {
            $data = $this->request->post();

        if ($data['xingming'] == '') {
            $this->error('姓名不能为空');
            return false;
        }

        if ($data['fcbl'] == '') {
            $this->error('分成比例不能为空');
            return false;
        }

        if ($data['fcbl'] != 0.1 && $data['fcbl'] != 0.2 && $data['fcbl'] != 0.3 && $data['fcbl'] != 0.4 && $data['fcbl'] != 0.5 && $data['fcbl'] != 0.6 && $data['fcbl'] != 0.7 && $data['fcbl'] != 0.8 && $data['fcbl'] != 0.9 ) {
            $this->error('分成比例写错啦!例:0.6,0.7,0.8,0.9');
            return false;
        }
        if ($data['fangshi'] == '') {
            $this->error('提现方式不能为空');
            return false;
        }
        if ($data['zhanghao'] == '') {
            $this->error('提现账号不能为空');
            return false;
        }
        $data['nickname']=$data['xingming'];
            if (false === DB::table('ien_admin_user')->where('id',$data['id'])->update($data)) {
                $this->error('更新失败');
            }
            $this->success('更新成功');
        }

        $user=DB::table('ien_admin_user')->where('id',$id)->find();
          // 显示修改页面
        return ZBuilder::make('form')
            ->setPageTips('如果修改密码,请联系管理员!')
            ->addFormItems([
                ['hidden', 'id'],
                ['hidden', 'did'],
                ['hidden', 'status'],
                ['static', 'username', '用户名', '必填'],
                ['text', 'guanzhu', '关注章节', '选填,20章开始付费阅读'],
                ['text', 'ewm', '二维码链接', '选填'],
                ['text', 'fcbl', '分成比例', '必填,请填写格式:0.6'],
                ['text', 'xingming', '姓名', '必填,提现姓名'],
                ['text', 'fangshi', '提现方式', '必填,提现方式'],
                ['text', 'zhanghao', '提现账号', '必填,提现账号'],
                ['hidden', 'update_time', $this->request->time()],

            ])
            ->isAjax(true)
            ->layout(['username' => 6, 'guanzhu' => 6, 'ewm' => 6, 'xingming' => 4, 'fangshi' => 4, 'zhanghao' => 4, 'fcbl' => 6])
            ->setFormData($user)
            ->fetch();

    }
    //代理配置信息
    public function optionurl($id=null)
    {   

        $readurl='http://'.preg_replace("/\{\d\}/", $id, module_config('agent.agent_tuiguangurl')).'/index.php/cms/user/readold.html';
        $adminurl='http://'.preg_replace("/\{\d\}/", $id, module_config('agent.agent_dailiurl')).'/admin.php';
        return ZBuilder::make('form')
            ->setPageTips('请发给代理商下面内容.')
            ->addFormItems([
                ['text', 'readold', '阅读历史', '阅读历史链接,请在关注公众号添加此链接',$readurl],
                ['text', 'loginurl', '登陆链接', '代理商登陆链接',$adminurl],


            ])
            ->hideBtn('submit')
            ->fetch();
    }
    //代理全部订单
    public function dingdan($id=null)
    {

            $map="";
            $mapa = $this->getMap();
            $key=array_keys($mapa);
            $i=0;
            foreach($mapa as $k=>$value)
            {   
                
                $name="ien_".$key[$i];
                $map[$name]=$value;
                $i++;
            }

        $user=DB::view('ien_pay_log')
            ->view('ien_admin_user','nickname','ien_admin_user.openid=ien_pay_log.uid')
            ->where('ien_pay_log.paytype <> 0')
            ->where('ien_pay_log.did',$id)->where($map)->order('ien_pay_log.addtime desc')->paginate();


            return ZBuilder::make('table')  
            ->hideCheckbox()
            ->setSearch(['admin_user.nickname' => '用户名'])
            ->addFilter('pay_log.paytype',['1'=>'VIP会员','2'=>'普通充值'])
            ->addFilter('pay_log.type',['1'=>'公众号支付','2'=>'第三方支付'])
            ->addFilter('pay_log.status',['1'=>'已支付','0'=>'未支付'])
            ->addTimeFilter('pay_log.addtime')
            ->addColumns([// 批量添加数据列

                ['payid', '订单ID','text'],
                ['paytype','订单类型',['1'=>'VIP会员','2'=>'普通充值']],
                ['nickname','用户'],
                ['money','充值金额'],
                ['type', '支付方式', ['1'=>'公众号支付']],
                ['status', '订单状态', ['1'=>'已支付','0'=>'未支付']],
                ['addtime', '添加时间', 'datetime'],

            ])
            ->setRowList($user) // 设置表格数据
            ->fetch(); // 渲染模板
    }
    //全部读者
    public function duzhe()
    {
        $order = $this->getOrder();
        $map = $this->getMap();
        $btn_dingdanmx = [
            'title' => '读者全部订单',
            'icon'  => 'fa fa-fw fa-shopping-cart',
            'class' => 'btn btn-xs btn-default ',
            'href'  => url('dingdanmx', ['id' => '__id__']),
        ];
        $datalist=DB::table('ien_admin_user')->where('role',3)->where($map)->order($order)->paginate();
            return ZBuilder::make('table')
            ->hideCheckbox()
            ->addTimeFilter('create_time')
            ->setSearch(['id' => 'ID', 'username' => '用户名', 'nickname' => '昵称'])
            ->addOrder('id,score')
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['username', '用户名'],
                ['nickname','昵称'],
                ['score','书币','text.edit'],
                ['isguanzhu','关注','status','',['1'=>'已关注:success','0'=>'未关注:danger']],
            ])
            ->setTableName('admin_user')
            ->addColumn('leiji','累计充值','callback',function($data){
                    $leiji=DB::table('ien_pay_log')->where('uid',$data['openid'])->where('status','1')->sum('money');
                    return $leiji;
                },'__data__')
                
             ->addColumns([
                ['create_time','注册时间','datetime'],
                ['last_login_time','最后登录时间','datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addRightButton('custom', $btn_dingdanmx) 
            ->setRowList($datalist) // 设置表格数据
            ->fetch(); // 渲染模板

    }
    //读者订单明细
    public function dingdanmx($id=null)
    {

            $map="";
            $mapa = $this->getMap();
            $key=array_keys($mapa);
            $i=0;
            foreach($mapa as $k=>$value)
            {   
                
                $name="ien_".$key[$i];
                $map[$name]=$value;
                $i++;
            }

            $user=DB::view('ien_pay_log')
            ->view('ien_admin_user','nickname','ien_admin_user.openid=ien_pay_log.uid')
            ->where('ien_pay_log.paytype <> 0')
            ->where('ien_admin_user.id',$id)->where($map)->order('ien_pay_log.addtime desc')->paginate();


            return ZBuilder::make('table')  
            ->hideCheckbox()
            //->setSearch(['admin_user.nickname' => '用户名'])
            ->addFilter('pay_log.paytype',['1'=>'VIP会员','2'=>'普通充值'])
            ->addFilter('pay_log.type',['1'=>'公众号支付','2'=>'第三方支付'])
            ->addFilter('pay_log.status',['1'=>'已支付','0'=>'未支付'])
            ->addTimeFilter('pay_log.addtime')
            ->addColumns([// 批量添加数据列

                ['payid', '订单ID','text'],
                ['paytype','订单类型',['1'=>'VIP会员','2'=>'普通充值']],
                ['nickname','用户'],
                ['money','充值金额'],
                ['type', '支付方式', ['1'=>'公众号支付']],
                ['status', '订单状态', ['1'=>'已支付','0'=>'未支付']],
                ['addtime', '添加时间', 'datetime'],

            ])
            ->setRowList($user) // 设置表格数据
            ->fetch(); // 渲染模板
    }
    //全部订单明细
    public function dingdanall($id=null)
    {
            $map="";
            $mapa = $this->getMap();
            $key=array_keys($mapa);
            $i=0;
            foreach($mapa as $k=>$value)
            {   
                
                $name="ien_".$key[$i];
                $map[$name]=$value;
                $i++;
            }

            $user=DB::table('ien_admin_user')->alias('w')->join('ien_pay_log ien_pay_log','ien_pay_log.uid = w.openid','LEFT')->where('ien_pay_log.paytype <> 0')->where($map)->order('ien_pay_log.addtime desc')->paginate();

            return ZBuilder::make('table')  
            ->hideCheckbox()
            
            ->setSearch(['admin_user.nickname' => '用户名','ien_pay_log.payid' => '订单号'])
            ->addFilter('pay_log.paytype',['1'=>'VIP会员','2'=>'普通充值'])
            ->addFilter('pay_log.type',['1'=>'公众号支付','2'=>'第三方支付'])
            ->addFilter('pay_log.status',['1'=>'已支付','0'=>'未支付'])
            ->addTimeFilter('pay_log.addtime')
            ->addColumns([// 批量添加数据列

                ['payid', '订单ID','text'],
                ['paytype','订单类型','status','',['1'=>'VIP会员:primary','2'=>'普通充值:info']],
                ['nickname','用户',],
                ['money','充值金额'],
                ['type', '支付方式', ['1'=>'公众号支付','2'=>'第三方支付']],
                ['status', '订单状态','status','',['1'=>'已支付:success','0'=>'未支付:danger']],
                ['addtime', '添加时间', 'datetime'],

            ])
            ->setRowList($user) // 设置表格数据
            ->fetch(); // 渲染模板
    }

    //修改配置信息
    public function uconfigedit($id = null){
        if ($id === 0) $this->error('参数错误');

        // 添加代理商
        if ($this->request->isPost()) {
            
            $data = $this->request->post();
            if(empty($data["isopen"]))
            {
            $data["isopen"]="0";
            }
            if(empty($data["isoauth"]))
            {
            $data["isoauth"]="0";
            }
            /*if(empty($data["isnopay"]))
            {
            $data["isnopay"]="0";
            }
            if(empty($data["isdingzhi"]))
            {
            $data["isdingzhi"]="0";
            }
            if(empty($data["iscaini"]))
            {
            $data["iscaini"]="0";
            }*/

        if(!empty($data['file']))
        {
            $file=DB::table('ien_admin_attachment')->where('id',$data['file'])->find();
            //[^/]*$
            if(preg_match('/[^ \/ ]*$/',$file['path'],$oldfile))
            {
                
                //try{
                copy($_SERVER['DOCUMENT_ROOT']."/public/".$file['path'],$_SERVER['DOCUMENT_ROOT']."/".$oldfile[0]); 
                unlink($_SERVER['DOCUMENT_ROOT']."/public/".$file['path']);
                rename($_SERVER['DOCUMENT_ROOT']."/".$oldfile[0],$_SERVER['DOCUMENT_ROOT']."/".$file['name']);
                //}
               // catch(\Exception $e){
                //     $this->error('文件上传失败,如已经上传过,不需要再上传!');
               // }
            }

        }
        if(DB::table('ien_wechat_uconfig')->where('id',$data['id'])->find())
        {
            if (false === DB::table('ien_wechat_uconfig')->where('id',$data['id'])->update($data)) {
                $this->error('更新失败');
            }
        }
        else{
            if (false === DB::table('ien_wechat_uconfig')->insert($data)) {
                $this->error('更新失败');
            }

        }

            $this->success('更新成功');
        }


    }
    //添加配置信息
    public function uconfig(){
        $user="";

        $user=DB::table('ien_wechat_uconfig')->where('uid',UID)->find();
        if(empty($user['token']))
        {
            $user['token']=$this->getRandom("32");
        }


          // 显示修改页面
        return ZBuilder::make('form')
            ->setUrl(url('uconfigedit'))
            ->setPageTips('如果不进行对接,请关闭!对接后可提升用户关注精度. <br/>公众号服务器地址为  http://'.preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl')).'/index.php/wechat.html<br/>加入公众号请在公众号中添加IP白名单: '.$_SERVER['SERVER_ADDR'].'<br/>如果开启用户登陆，必须在授权域名中添加:'.preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl')),'danger')
            ->addFormItems([
                ['hidden', 'id'],
                ['hidden', 'uid',UID],
                ['text', 'name', '公众号名称', '必填'],
                ['text', 'gid', '公众号原始ID', '必填'],
                ['text', 'wxh', '微信号', '必填'],
                ['text', 'appid', 'Appid', '必填'],
                ['text', 'appsecret', 'Appsecret', '必填'],
                ['text', 'token', 'token', '必填'],
                ['text', 'encodingaeskey', 'EncodingAESKey', '必填'],
                ['image', 'kefu', '客服二维码', '选填,只允许JPG格式','','','jpg'],
                ['radio', 'gzmoshi', '关注模式', '',['0'=>'主动关注','1'=>'强制关注']],
          
            ])
            //->addFile('file', '微信域名验证文件(只需上传一次即可)', '', '', '50', 'txt')
            ->addSwitch('isopen', '是否开启')
            ->addSwitch('isoauth', '是否开启登陆认证(仅支持认证服务号！)','开启之后，所有用户登陆都使用本服务号，如果取消对接，所有下线用户无法访问！！')
            //->addSwitch('isnopay', '未支付提醒(仅支持认证服务号！)','开启后系统将自动向符合条件的用户推送相应客服消息；推送时间：在未支付订单生成时间的24小时后推送。最多发送一次')
            //->addSwitch('isdingzhi', '定制书籍推荐(仅支持认证服务号！)','开启后系统将自动向符合条件的用户推送相应客服消息；推送时间：在用户最后一次阅读时间的24小时后推送')
            //->addSwitch('iscaini', '猜你喜欢的书籍(仅支持认证服务号！)','开启后系统自动向符合条件的用户推送相应的客服消息，根据用户阅读特点猜测其可能喜欢的书籍；推送时间：在用户最后一次阅读时间的24小时后推送')
            ->isAjax(true)
            ->layout(['name' => 6, 'gid' => 6, 'wxh' => 6, 'appid' => 4, 'appsecret' => 4, 'token' => 4, 'encodingaeskey' => 6])
            ->setFormData($user)
            ->fetch();

    }
    public function tempay(){ 

       if ($this->request->isPost()) {
            $data = $this->request->post();
            $dataup['id']=$data['id'];
            $dataup['ispay']=$data['ispay'];
            $dataup['tempay']=$data['TaskValue'];
            $dataup['tempid']=$data['tmid'];
            if(empty($data["ispay"]))
            {
            $dataup["ispay"]="0";
            }

            if (false === DB::table('ien_wechat_uconfig')->update($dataup)) {
                $this->error('更新失败');
            }
            $this->success('更新成功');
            
        }
        $data=[];
        $userdl=DB::table('ien_wechat_uconfig')->where('uid',UID)->where('isopen',"on")->find();
        if(!empty($userdl) || UID==1)
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

                    $cacheDriver = new RedisCache();
                    // 创建 redis 实例
                    $redis = new \Redis();
                    $redis->connect('localhost', 6379);
                    $cacheDriver->setRedis($redis); 

                    //如果管理员的话,用平台服务号
                    if(UID==1)
                    {
                    $config2 = module_config('wechat');
                    }
                    $config2['cache']=$cacheDriver;
                    $config = array_merge($config, $config2);
                    try{
                    $app = new Application($config);
                    $notice = $app->notice;
                    $a=$notice->getPrivateTemplates();
                    }
                   catch(\Exception $e){
                      $this->success('公众号接入有误!');
                    }
                   foreach($a->template_list as $key=>$value)
                   {
                        if($key>0)
                        {
                        $data[$key]['name']=$value['title'];
                        $data[$key]['template_id']=$value['template_id'];
                        $data[$key]['content']=$value['content'];
                        }
                   }
                   $this->assign('tmp', $data);
        }
        else{
            $this->success('请先接入认证服务号!');
        }
        return ZBuilder::make('form')
            ->setPageTitle('本功能认证服务号可用，读者支付订单成功后收到通知信息！<br>微信接入配置中，必须对接正确并开启！<br>每次修改需从新设置！')// 设置页面标题
            ->addFormItems([
                //['text', 'name', '任务名称', '必填'],
                ['select', 'tmid', '模板ID', '必填' ,'','',''],
                //['text', 'title', '推送标题', '必填',"您获得一次七夕会员活动奖励,充77元送77元!"],
               // ['text', 'temp', '推送内容', '必填'],
                //['text', 'youxiao', '推送第三行', '必填',"活动截止日期:8月28日24点"],
               // ['text', 'yindao', '推送第四行', '必填',"点击立即参加活动~"],
                //['text', 'url', '跳转链接', '只可以本平台内链接'],
                ['switch', 'ispay', '是否开启支付通知', ''],
                ['hidden','id',$userdl['id']],
            ])
            ->setFormData($userdl)
            //->addStatic('', '当前小说章节', '', $data_listxs['title'])
            ->isAjax(true)
            ->fetch('tempay');
            

    }

    public function temcode(){ 

       if ($this->request->isPost()) {
            $data = $this->request->post();
            $dataup['id']=$data['id'];
            $dataup['iscore']=$data['iscore'];
            $dataup['addcore']=$data['TaskValue'];
            $dataup['cordid']=$data['tmid'];
            if(empty($data["iscore"]))
            {
            $dataup["iscore"]="0";
            }

            if (false === DB::table('ien_wechat_uconfig')->update($dataup)) {
                $this->error('更新失败');
            }
            $this->success('更新成功');
            
        }
        $data=[];
        $userdl=DB::table('ien_wechat_uconfig')->where('uid',UID)->where('isopen',"on")->find();
        if(!empty($userdl) || UID==1)
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

                    $cacheDriver = new RedisCache();
                    // 创建 redis 实例
                    $redis = new \Redis();
                    $redis->connect('localhost', 6379);
                    $cacheDriver->setRedis($redis); 

                    //如果管理员的话,用平台服务号
                    if(UID==1)
                    {
                    $config2 = module_config('wechat');
                    }
                    $config2['cache']=$cacheDriver;
                    $config = array_merge($config, $config2);
                    try{
                    $app = new Application($config);
                    $notice = $app->notice;
                    $a=$notice->getPrivateTemplates();
                    }
                   catch(\Exception $e){
                      $this->success('公众号接入有误!');
                    }
                   foreach($a->template_list as $key=>$value)
                   {
                        if($key>0)
                        {
                        $data[$key]['name']=$value['title'];
                        $data[$key]['template_id']=$value['template_id'];
                        $data[$key]['content']=$value['content'];
                        }
                   }
                   $this->assign('tmp', $data);
        }
        else{
            $this->success('请先接入认证服务号!');
        }
        return ZBuilder::make('form')
            ->setPageTitle('本功能认证服务号可用，读者自动或手动签到成功后收到通知信息！<br>微信接入配置中，必须对接正确并开启！<br>每次修改需从新设置！')// 设置页面标题
            ->addFormItems([
                //['text', 'name', '任务名称', '必填'],
                ['select', 'tmid', '模板ID', '必填' ,'','',''],
                //['text', 'title', '推送标题', '必填',"您获得一次七夕会员活动奖励,充77元送77元!"],
               // ['text', 'temp', '推送内容', '必填'],
                //['text', 'youxiao', '推送第三行', '必填',"活动截止日期:8月28日24点"],
               // ['text', 'yindao', '推送第四行', '必填',"点击立即参加活动~"],
                //['text', 'url', '跳转链接', '只可以本平台内链接'],
                ['switch', 'iscore', '是否开启支付通知', ''],
                ['hidden','id',$userdl['id']],
            ])
            ->setFormData($userdl)
            //->addStatic('', '当前小说章节', '', $data_listxs['title'])
            ->isAjax(true)
            ->fetch('temcore');
            

    }
    //随机字符串
    function getRandom($param){
    $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key = "";
    for($i=0;$i<$param;$i++)
     {
         $key .= $str{mt_rand(0,32)};    //生成php随机数
     }
     return $key;
 }

    //生成自定义菜单
    public function diymenu(){
        $data=module_config('agent.agent_diy_menu');
        $tgurl=preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'));
        $rooturl=module_config('agent.agent_rooturl');
        $diymenu=str_replace($rooturl,$tgurl,$data);
        
        $userdl=DB::table('ien_wechat_uconfig')->where('uid',UID)->where('isopen',"on")->find();
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

                    $cacheDriver = new RedisCache();
                    // 创建 redis 实例
                    $redis = new \Redis();
                    $redis->connect('localhost', 6379);
                    $cacheDriver->setRedis($redis); 

                    //$config2 = module_config('wechat');
                    $config2['cache']=$cacheDriver;
                    $config = array_merge($config, $config2);
                    
                    
                    try { 
                     $app = new Application($config);
                    $menu = $app->menu;
                     $return=$menu->add(json_decode($diymenu));
                    }
                   catch(\Exception $e){
                    $this->success('更新失败,请先接入认证公众号!');
                    }
          			$this->success('更新成功');

            }
        else{
            $this->success('请先接入认证公众号!');
        }

        

    }

    //模板消息
    public function teminfo(){
            $user=DB::table('ien_tem_info')->where('uid',UID)->paginate();

            $btnindex = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '新建模板消息',
                'href'  => url('user/teminfoadd')
            ];
             $btnorder = [
                'class' => 'btn btn-xs btn-default',
                'icon'  => 'fa fa-fw fa-forward',
                'title' => '发送',
                'href'  => url('user/tmsend', ['id' => '__id__'])
            ];
            $btndel = [
                'class' => 'btn btn-xs btn-default',
                'icon'  => 'fa fa-fw fa-remove',
                'title' => '删除',
                'href'  => url('user/tmdel', ['id' => '__id__'])
            ];

            return ZBuilder::make('table')  
            ->setPageTips('本功能只有对接认证服务号并且添加模板之后才可以使用!!!<br>推送给所有关注用户!<br>为接入成功或未添加模板报错!!!','danger')
            ->hideCheckbox()
            ->addColumns([// 批量添加数据列

                ['name', '任务名称','text'],
                ['issend','发送状态',['1'=>'已发送','0'=>'未发送','2'=>'发送中']],
                ['temp','发送内容'],
                ['addtime', '添加时间', 'datetime'],
                ['sendtime', '发送时间', 'datetime'],
                ['right_button', '操作', 'btn']

            ])
            ->addTopButton('custom',$btnindex)
            //->addRightButton('custom',$btnorder)
            ->addRightButton('custom',$btndel)
            ->setRowList($user) // 设置表格数据
            ->fetch(); // 渲染模板

    }
    //删除模板消息
    public function tmdel($id=null)
    {
        if (false === DB::table('ien_tem_info')->delete($id)) {
                $this->error('删除失败');
            }
            $this->success('删除成功');
    }
    //新建模板消息
    public function teminfoadd(){

        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['temp']=$data['TaskValue'];
            $data['sendtime']=strtotime($data['sendtime']);
            if (false === DB::table('ien_tem_info')->insert($data)) {
                $this->error('创建失败');
            }
            $this->success('创建成功');
            
        }
        $data=[];
        $userdl=DB::table('ien_wechat_uconfig')->where('uid',UID)->where('isopen',"on")->find();
        if(!empty($userdl) || UID==1)
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

                    $cacheDriver = new RedisCache();
                    // 创建 redis 实例
                    $redis = new \Redis();
                    $redis->connect('localhost', 6379);
                    $cacheDriver->setRedis($redis); 

                    //如果管理员的话,用平台服务号
                    if(UID==1)
                    {
                    $config2 = module_config('wechat');
                    }
                    $config2['cache']=$cacheDriver;
                    $config = array_merge($config, $config2);
                    try{
                    $app = new Application($config);
                    $notice = $app->notice;
                    $a=$notice->getPrivateTemplates();
                    }
                   catch(\Exception $e){
                      $this->success('公众号对接有误!');
                    }
                   foreach($a->template_list as $key=>$value)
                   {
                        if($key>0)
                        {
                        $data[$key]['name']=$value['title'];
                        $data[$key]['template_id']=$value['template_id'];
                        $data[$key]['content']=$value['content'];
                        }
                   }
                   $this->assign('tmp', $data);
        }
        else{
            $this->success('请先接入认证公众号!');
        }
        return ZBuilder::make('form')
            ->setPageTitle('请在认证服务号里面添加模板<br>可任意添加模版，自动读取！')// 设置页面标题
            ->addFormItems([
                ['text', 'name', '任务名称', '必填'],
                ['select', 'tmid', '模板ID', '必填' ,'','',''],
                //['text', 'title', '推送标题', '必填',"您获得一次七夕会员活动奖励,充77元送77元!"],
               // ['text', 'temp', '推送内容', '必填'],
                //['text', 'youxiao', '推送第三行', '必填',"活动截止日期:8月28日24点"],
               // ['text', 'yindao', '推送第四行', '必填',"点击立即参加活动~"],
                ['text', 'url', '跳转链接', '只可以本平台内链接'],
                ['datetime', 'sendtime', '发布时间', ''],
                ['hidden', 'uid', UID],
                ['hidden', 'issend', 0],
                ['hidden', 'iskefu', 0],
                ['hidden', 'addtime', $this->request->time()],


            ])
            //->addStatic('', '当前小说章节', '', $data_listxs['title'])
            ->isAjax(true)
            ->fetch('teminfoadd');

    }

    //发送模板消息
    public function tmsend($id = null ,$openid = null)
    {
       

        $tminfo=DB::table('ien_tem_info')->where('id',$id)->find();
        $userdl=DB::table('ien_wechat_uconfig')->where('uid',$tminfo['uid'])->where('isopen',"on")->find();

        if(!empty($userdl) || $tminfo['uid']==1)
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
                    $notice = $app->notice;
  
                    $userId = $openid;
                    $templateId = $tminfo['tmid'];
                    $url = $tminfo['url'];
                    $data = json_decode($tminfo['temp'],true);  

                    try{
                        $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
                    }
                    catch(\Exception $e){
                      return true;
                     }               
                    return true;
            }
        else{
            return false;
        }



    }

    //促销列表
    public function cuxiao(){
        $cuxiao=DB::table('ien_cuxiaolist')->whereTime('endtime','>',time())->paginate();
        $btnindex = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '新建促销',
                'href'  => url('user/cuxiaoadd')
            ];
        $btndel = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-fw fa-remove',
                'title' => '删除促销',
                'href'  => url('user/cuxiaodelete',['id' => '__id__'])
            ];
        $btnedit = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'list-icon fa fa-pencil fa-fw',
                'title' => '修改促销',
                'href'  => url('user/cuxiaoedit',['id' => '__id__'])
            ];
        $btnpro = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '商品管理',
                'href'  => url('user/product',['id' => '__id__'])
            ];

        return ZBuilder::make('table')  
            //->setPageTips('本功能只有对接认证服务号并且添加模板之后才可以使用!!!<br>推送给所有关注用户!<br>为接入成功或未添加模板报错!!!','danger')
            ->hideCheckbox()
            ->addColumns([// 批量添加数据列

                ['name', '活动名称','text'],              
                ['starttime', '开始时间', 'datetime'],
                ['endtime', '结束时间', 'datetime'],
                ['id','活动链接','callback', function($value){
        return "http://".preg_replace("/\{\d\}/", UID, module_config('agent.agent_tuiguangurl'))."/index.php/cms/pay/index/cxid/".$value;
    }],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('custom',$btnindex)
            ->addRightButton('custom',$btnpro)
            ->addRightButton('custom',$btnedit)
            ->addRightButton('custom',$btndel)
            ->setRowList($cuxiao) // 设置表格数据
            ->fetch(); // 渲染模板
    }
    //新增促销活动
    public function cuxiaoadd(){
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['starttime']=strtotime($data['starttime']);
            $data['endtime']=strtotime($data['endtime']);
            if (false === DB::table('ien_cuxiaolist')->insert($data)) {
                $this->error('创建失败');
            }
            $this->success('创建成功');
            
        }


        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'name', '促销名称', '必填'],
                ['datetime','starttime','活动开始时间','必填'],
                ['datetime','endtime','活动结束时间','必填'],

            ])
            ->isAjax(true)
            ->fetch();

    }
    public function cuxiaodelete($id=null)
    {
        Db::table('ien_cuxiaolist')->where('id',$id)->delete();
        $this->success('删除成功!');
    }

    public function cuxiaoedit($id=null)
    {
        if ($id === 0) $this->error('参数错误');

        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['starttime']=strtotime($data['starttime']);
            $data['endtime']=strtotime($data['endtime']);
            if (false === DB::table('ien_cuxiaolist')->update($data)) {
                $this->error('更新失败');
            }
            $this->success('更新成功');
            
        }

        $info=DB::table('ien_cuxiaolist')->where('id',$id)->find();
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id',$id],
                ['text', 'name', '促销名称', '必填'],
                ['datetime','starttime','活动开始时间','必填'],
                ['datetime','endtime','活动结束时间','必填'],

            ])
            ->setFormData($info)
            ->isAjax(true)
            ->fetch();
        
    }



        //商品列表
    public function product($id=null){
        if(!empty($id))
        {
            $btnindex = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '新建商品',
                'href'  => url('user/proadd',['cxid' => $id])
            ];
            $cuxiao=DB::table('ien_cuxiao')->where('leixing',2)->where('cxid',$id)->paginate();
        }
        else
        {
            $btnindex = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '新建商品',
                'href'  => url('user/proadd')
            ];
        $cuxiao=DB::table('ien_cuxiao')->where('leixing',1)->paginate();
        }
        
        $btndel = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-fw fa-remove',
                'title' => '删除商品',
                'href'  => url('user/prodel',['id' => '__id__'])
            ];
        $btnedit = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'list-icon fa fa-pencil fa-fw',
                'title' => '修改商品',
                'href'  => url('user/proedit',['id' => '__id__'])
            ];

        return ZBuilder::make('table')  
            ->hideCheckbox()
            ->addColumns([// 批量添加数据列

                ['name', '商品名称','text'],              
                ['orderby', '排序', 'text'],
                ['money', '金额', 'text'],
                ['score', '书币', 'text'],
                ['day', '天数', 'text'],
                ['titilea', '提示一', 'text'],
                ['titileb', '提示二', 'text'],
                ['titilec', '提示三', 'text'],
               
                ['right_button', '操作', 'btn']
            ])
            ->addTopButton('custom',$btnindex)
            ->addRightButton('custom',$btndel)
            ->addRightButton('custom',$btnedit)
            ->setRowList($cuxiao) // 设置表格数据
            ->fetch(); // 渲染模板
    }
    //新增商品
    public function proadd($cxid=null){
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (false === DB::table('ien_cuxiao')->insert($data)) {
                $this->error('创建失败');
            }
            $this->success('创建成功');
            
        }
        if(!empty($cxid))
        {
            $leixing=2;
        }
        else{
            $leixing=1;
        }

        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'name', '商品名称', '必填'],
                ['text', 'money', '价格', '必填'],
                ['select', 'type', '活动类型', '必填',['1' => '书币', '2' => '年费'],1],
                ['text', 'day', '天数'],
                ['text', 'score', '书币'],
                ['text', 'titilea', '前台显示第一行', '必填'],
                ['text', 'titileb', '前台显示第二行', '必填'],
                ['text', 'titilec', '前台显示第三行', '必填'],
                ['hidden', 'leixing', $leixing],
                ['text', 'orderby', '排序'],
                ['hidden','cxid',$cxid]

            ])
            //->addStatic('', '当前小说章节', '', $data_listxs['title'])
            ->setTrigger('type', '1', 'score')
            ->setTrigger('type', '2', 'day')
            ->isAjax(true)
            ->fetch();

    }
    public function prodel($id=null)
    {
        Db::table('ien_cuxiao')->where('id',$id)->delete();
        $this->success('删除成功!');
    }
    public function proedit($id=null)
    {
        if ($id === 0) $this->error('参数错误');

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (false === DB::table('ien_cuxiao')->update($data)) {
                $this->error('更新失败');
            }
            $this->success('更新成功');
            
        }

        $info=DB::table('ien_cuxiao')->where('id',$id)->find();
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id',$id],
                ['text', 'name', '商品名称', '必填'],
                ['text', 'money', '价格', '必填'],
                ['select', 'type', '活动类型', '必填',['1' => '书币', '2' => '年费'],1],
                ['text', 'day', '天数'],
                ['text', 'score', '书币'],
                ['text', 'titilea', '前台显示第一行', '必填'],
                ['text', 'titileb', '前台显示第二行', '必填'],
                ['text', 'titilec', '前台显示第三行', '必填'],
                ['hidden', 'leixing'],
                ['text', 'orderby', '排序'],

            ])
            //->addStatic('', '当前小说章节', '', $data_listxs['title'])
            ->setTrigger('type', '1', 'score')
            ->setTrigger('type', '2', 'day')
            ->setFormData($info)
            ->isAjax(true)
            ->fetch();
        
    }

     //模板消息
    public function kefuinfo(){
            $user=DB::table('ien_tem_info')->where('uid',UID)->where('iskefu',1)->paginate();

            $btnindex = [
                'class' => 'btn btn-primary confirm',
                'icon'  => 'fa fa-plus-circle',
                'title' => '新建客服消息',
                'href'  => url('user/kefuadd')
            ];
            $btndel = [
                'class' => 'btn btn-xs btn-default',
                'icon'  => 'fa fa-fw fa-remove',
                'title' => '删除',
                'href'  => url('user/kefudel', ['id' => '__id__'])
            ];

            return ZBuilder::make('table')  
            ->setPageTips('本功能只有对接认证服务号才可以使用!!!<br>推送给所有四十八小时内与公众号互动过的用户!','danger')
            ->hideCheckbox()
            ->addColumns([// 批量添加数据列

                ['name', '任务名称','text'],
                ['issend','发送状态',['1'=>'已发送','0'=>'未发送','2'=>'发送中']],
                //['temp','发送内容'],
                ['addtime', '添加时间', 'datetime'],
                ['sendtime', '发送时间', 'datetime'],
                ['right_button', '操作', 'btn']

            ])
            ->addTopButton('custom',$btnindex)
            //->addRightButton('custom',$btnorder)
            ->addRightButton('custom',$btndel)
            ->setRowList($user) // 设置表格数据
            ->fetch(); // 渲染模板

    }
    //删除模板消息
    public function kefudel($id=null)
    {
        if (false === DB::table('ien_tem_info')->delete($id)) {
                $this->error('删除失败');
            }
            $this->success('删除成功');
    }
    //新建模板消息
    public function kefuadd(){

        if ($this->request->isPost()) {
            $data = $this->request->post();
            $temp['titles']=explode("^",substr($data['titles'],0,-1));
            $temp['img']=explode("^",substr($data['coverImgs'],0,-1));
            $temp['url']=explode("^",substr($data['linkUrls'],0,-1));
            $temp=json_encode($temp,true);
            $data['temp']=$temp;
            $data['sendtime']=strtotime($data['sendtime']);
            if (false === DB::table('ien_tem_info')->insert($data)) {
                $this->error('创建失败');
            }
            $this->success('创建成功');
            
        }
        $data=[];
        $userdl=DB::table('ien_wechat_uconfig')->where('uid',UID)->where('isopen',"on")->find();
        if(!empty($userdl) || UID==1)
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

                    $cacheDriver = new RedisCache();
                    // 创建 redis 实例
                    $redis = new \Redis();
                    $redis->connect('localhost', 6379);
                    $cacheDriver->setRedis($redis); 

                    //如果管理员的话,用平台服务号
                    if(UID==1)
                    {
                    $config2 = module_config('wechat');
                    }
                    $config2['cache']=$cacheDriver;
                    $config = array_merge($config, $config2);
                    try{
                    $app = new Application($config);
                    $staff = $app->staff;
                    }
                   catch(\Exception $e){
                      $this->success('公众号对接有误!');
                    }
                   
        }
        else{
            $this->success('请先接入认证公众号!');
        }
        return ZBuilder::make('form')
            //->setPageTitle('请在认证服务号里面添加模板: 会员卡升级通知 TM405959619')// 设置页面标题
            ->addFormItems([
                ['text', 'name', '任务名称', '必填'],
                //['select', 'tmid', '模板ID', '必填' ,'','',''],
                //['text', 'title', '推送标题', '必填',"您获得一次七夕会员活动奖励,充77元送77元!"],
               // ['text', 'temp', '推送内容', '必填'],
                //['text', 'youxiao', '推送第三行', '必填',"活动截止日期:8月28日24点"],
               // ['text', 'yindao', '推送第四行', '必填',"点击立即参加活动~"],
                //['text', 'url', '跳转链接', '只可以本平台内链接'],
                ['datetime', 'sendtime', '发布时间', '',date("Y-m-d H:i",time())],
                ['hidden', 'uid', UID],
                ['hidden', 'issend', 0],
                ['hidden', 'iskefu', 1],
                ['hidden', 'addtime', $this->request->time()],


            ])
            //->addStatic('', '当前小说章节', '', $data_listxs['title'])
            ->fetch('kefuadd');

    }

    





    public function kefusend(){

        //$this->send(1,"oZN9Q1NSkKFBCwnvCEKrN0jHHQ9Y",17,3965);oZN9Q1H4qDq7BA_VkvuegKbhZ65k
        //die;

        $userdl=DB::table('ien_wechat_uconfig')->where('uid',1)->where('isopen',"on")->find();
        $tminfo=DB::table('ien_tem_info')->where('id',10)->find();
        $openid="oZN9Q1NSkKFBCwnvCEKrN0jHHQ9Y";
        $username="拼搏";
        $score="12";
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
                    
                    //try{
                    $app = new Application($config);
                    //$notice = $app->notice;
                    $staff = $app->staff;
                    $userId = $openid;
                    $templateId = $tminfo['tmid'];
                    //$url = $tminfo['url']; 
                    $tempinfoatt=preg_replace('/{nickname}/',$username,$tminfo['temp']);
                    $tempinfoatt=preg_replace('/{score}/',$score,$tempinfoatt);

                    $temp=json_decode($tempinfoatt,true);


                        foreach ($temp['titles'] as $key => $value) {
                            $send[$key]['titles']=$value;
                        }
                        foreach ($temp['img'] as $key => $value) {
                            $send[$key]['img']=$value;
                        }
                        foreach ($temp['url'] as $key => $value) {
                            $send[$key]['url']=$value."?kfid=".$tminfo['id'];
                        }
                        dump($send);
                        die;
                        
                        foreach ($send as $key => $value) {
                            $news[$key] = new News();
                            $news[$key]->title = $value['titles'];
                            $news[$key]->description = $value['titles'];
                            $news[$key]->image = $value['img'];
                            $news[$key]->url = $value['url'];
                        }
                    $staff->message($news)->to($userId)->send();
                    

                    //$data = json_decode($tempinfoatt,true);  

                    //$result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
                   // }
                    //catch(\Exception $e){
                    // echo "error" . PHP_EOL;
                    // }  

    }



    


    
}