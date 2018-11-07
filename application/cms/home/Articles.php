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
// | 作者: 拼搏 <378184@qq.com>
// +----------------------------------------------------------------------

namespace app\cms\home;

use app\cms\model\Column as ColumnModel;
use think\Db;
use util\Tree;
use EasyWeChat\Foundation\Application;
use Doctrine\Common\Cache\RedisCache;

/**
 * 前台栏目文档列表控制器
 * @package app\cms\admin
 */
class Articles extends Common
{

    public function index($id=null,$tstype=null)
    { 
        if ($id === null) $this->error('缺少参数');
       Db::table('ien_articles')->where('id', $id)->setInc('click');
        $articles= Db::table('ien_articles')->where('id', $id)->find();

        $url="http://".preg_replace("/\{\d\}/", $articles['uid'], module_config('agent.agent_tuiguangurl'))."/index.php/cms/document/detail/id/".$articles['zid'].".html?t=".$articles['bid'];
        $rooturl="http://".preg_replace("/\{\d\}/", $articles['uid'], module_config('agent.agent_tuiguangurl'))."/";
            $this->assign('articles', $articles);
            $this->assign('url', $url);
            $this->assign('rooturl', $rooturl);

              $cacheDriver = new RedisCache();
              // 创建 redis 实例
              $redis = new \Redis();
              $redis->connect('localhost', 6379);
              $cacheDriver->setRedis($redis); 

              $config2 = module_config('wechat');
              $config2['cache']=$cacheDriver;
              $app = new Application($config2);
              $js = $app->js;
              $a="<script src='http://res.wx.qq.com/open/js/jweixin-1.2.0.js' type='text/javascript' charset='utf-8'></script>
<script type='text/javascript' charset='utf-8'>
    wx.config(".$js->config(array('onMenuShareAppMessage','onMenuShareTimeline','onMenuShareQQ', 'onMenuShareWeibo'), false).");
</script>";
   
              $this->assign('js', $a);


       
        return $this->fetch();

    }

    function getblob($title = null){

    //$a=file_get_contents('1.png');
    //DB::table('ien_blob')->insert(['title'=>'123456','content'=>$a]);
    //$file=DB::table('ien_blob')->where('title','123456')->value('content');
    $file=DB::table('ien_blob')->where('title',$title)->value('content');
    //dump($file);
    ob_end_clean();
    header("Content-type: image/png"); 
      echo $file;

  }
  
  
  
}