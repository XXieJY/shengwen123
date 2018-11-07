/*
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
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ieasynet_plugin_hello`
-- ----------------------------
DROP TABLE IF EXISTS `ieasynet_plugin_hello`;
CREATE TABLE `ieasynet_plugin_hello` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '名人',
  `said` text NOT NULL COMMENT '名言',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ieasynet_plugin_hello
-- ----------------------------
INSERT INTO `ieasynet_plugin_hello` VALUES ('1', '网络', '生活是一面镜子。你对它笑，它就对你笑；你对它哭，它也对你哭。', '1');
INSERT INTO `ieasynet_plugin_hello` VALUES ('2', '网络', '活着一天，就是有福气，就该珍惜。当我哭泣我没有鞋子穿的时候，我发现有人却没有脚。', '1');
INSERT INTO `ieasynet_plugin_hello` VALUES ('3', '爱迪生', '天才是百分之一的灵感加百分之九十九的汗水。', '1');
INSERT INTO `ieasynet_plugin_hello` VALUES ('4', '美华纳', '勿问成功的秘诀为何，且尽全力做你应该做的事吧。', '1');
INSERT INTO `ieasynet_plugin_hello` VALUES ('5', '陶铸', '如烟往事俱忘却，心底无私天地宽', '1');
