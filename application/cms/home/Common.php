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

use app\index\controller\Home;
use think\Db;
use util\Tree;
use EasyWeChat\Foundation\Application;
use Doctrine\Common\Cache\RedisCache;
//use think\Session;
/**
 * 前台公共控制器
 * @package app\cms\admin
 */
class Common extends Home
{
    /**
     * 初始化方法
     * @author 拼搏 <378184@qq.com>
     */
    protected function _initialize()
    {
        parent::_initialize();
        /*Session::init([
            'prefix'         => 'module',
            'type'           => '',
            'auto_start'     => true,
        ]);*/
        // 获取菜单
        $this->getNav();
        // 获取滚动图片
        $this->assign('slider', $this->getSlider());
        // 获取客服
        $this->assign('support', $this->getSupport());

      
    }

    /**
     * 获取导航
     * @author 拼搏 <378184@qq.com>
     */
    private function getNav()
    {
        $list_nav = Db::name('cms_nav')->where('status', 1)->column('id,tag');

        foreach ($list_nav as $id => $tag) {
            $data_list = Db::view('cms_menu', true)
                ->view('cms_column', ['name' => 'column_name'], 'cms_menu.column=cms_column.id', 'left')
                ->view('cms_page', ['title' => 'page_title'], 'cms_menu.page=cms_page.id', 'left')
                ->where('cms_menu.nid', $id)
                ->where('cms_menu.status', 1)
                ->order('cms_menu.sort,cms_menu.pid,cms_menu.id')
                ->select();

            foreach ($data_list as &$item) {
                if ($item['type'] == 0) { // 栏目链接
                    $item['title'] = $item['column_name'];
                    $item['url'] = url('cms/column/index', ['id' => $item['column']]);
                } elseif ($item['type'] == 1) { // 单页链接
                    $item['title'] = $item['page_title'];
                    $item['url'] = url('cms/page/detail', ['id' => $item['page']]);
                } else {
                    if ($item['url'] != '#' && substr($item['url'], 0, 4) != 'http') {
                        $item['url'] = url($item['url']);
                    }
                }
            }

            $this->assign($tag, Tree::toLayer($data_list));
        }
    }

    /**
     * 获取滚动图片
     * @author 拼搏 <378184@qq.com>
     */
    private function getSlider()
    {
        return Db::name('cms_slider')->where('status', 1)->select();
    }

    /**
     * 获取在线客服
     * @author 拼搏 <378184@qq.com>
     */
    private function getSupport()
    {
        return Db::name('cms_support')->where('status', 1)->order('sort')->select();
    }

    public function checklogin()
    {
    
        // 未登录
        if (empty($_SESSION['wechat_user'])) {
          //$_SESSION['target_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $this->oauth();
          //return $oauth->redirect();
          // 这里不一定是return，如果你的框架action不是返回内容的话你就得使用
          // $oauth->redirect()->send();
        }
        else{
        // 已经登录过 
            //header('location:'. $_SESSION['target_url']);
            return $user=$_SESSION['wechat_user'];
        }
    }

        

}


