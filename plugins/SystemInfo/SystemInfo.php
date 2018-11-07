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
namespace plugins\SystemInfo;

use app\common\controller\Plugin;

/**
 * 系统环境信息插件
 * @package plugins\SystemInfo
 * @author 拼搏 <378184@qq.com>
 */
class SystemInfo extends Plugin
{
    /**
     * @var array 插件信息
     */
    public $info = [
        // 插件名[必填]
        'name'        => 'SystemInfo',
        // 插件标题[必填]
        'title'       => '系统环境信息',
        // 插件唯一标识[必填],格式：插件名.开发者标识.plugin
        'identifier'  => 'system_info.ming.plugin',
        // 插件图标[选填]
        'icon'        => 'fa fa-fw fa-info-circle',
        // 插件描述[选填]
        'description' => '在后台首页显示服务器信息',
        // 插件作者[必填]
        'author'      => '拼搏',
        // 作者主页[选填]
        'author_url'  => 'http://www.ieasynet.com',
        // 插件版本[必填],格式采用三段式：主版本号.次版本号.修订版本号
        'version'     => '1.0.0',
        // 是否有后台管理功能[选填]
        'admin'       => '0',
    ];

    /**
     * @var array 插件钩子
     */
    public $hooks = [
        'admin_index'
    ];

    /**
     * 后台首页钩子
     * @author 拼搏 <378184@qq.com>
     */
    public function adminIndex()
    {
        $config = $this->getConfigValue();
        if ($config['display']) {
            $this->fetch('widget', $config);
        }
    }

    /**
     * 安装方法
     * @author 拼搏 <378184@qq.com>
     * @return bool
     */
    public function install(){
        return true;
    }

    /**
     * 卸载方法必
     * @author 拼搏 <378184@qq.com>
     * @return bool
     */
    public function uninstall(){
        return true;
    }
}