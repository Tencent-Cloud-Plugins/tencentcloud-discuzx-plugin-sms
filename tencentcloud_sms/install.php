<?php
/*
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')){
    exit('Access Denied');
}
defined('TENCENT_DISCUZX_SMS_DIR')||define( 'TENCENT_DISCUZX_SMS_DIR', __DIR__.DIRECTORY_SEPARATOR);
if (!is_file(TENCENT_DISCUZX_SMS_DIR.'vendor/autoload.php')) {
    exit('缺少依赖文件，请确保安装了腾讯云sdk');
}
require_once 'vendor/autoload.php';
use TencentDiscuzSMS\SMSActions;
global $_G;
$careatesql = "CREATE TABLE IF NOT EXISTS cdb_tencentcloud_pluginInfo (
       `plugin_name` varchar(255) NOT NULL DEFAULT '',
       `version` varchar(32) NOT NULL DEFAULT '',
       `href` varchar(255) NOT NULL  DEFAULT '',
       `plugin_id` varchar(255) NOT NULL DEFAULT '',
       `activation` varchar(32) NOT NULL DEFAULT '',
       `status` varchar(32) NOT NULL DEFAULT '',
       `install_datetime` timestamp NOT NULL DEFAULT  CURRENT_TIMESTAMP(),
       `last_modify_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP(),
       PRIMARY KEY (`plugin_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
runquery($careatesql);
$pluginId = $_G['gp_pluginid'];
$href = 'admin.php?action=plugins&operation=config&do='.$pluginId;
$inserSQL=<<<EOF
REPLACE INTO pre_tencentcloud_pluginInfo (`plugin_name`, `version`, `href`, `plugin_id`, `activation`,`status`)
 VALUES ( 'tencentcloud_sms', '1.0.0', '$href',  '$pluginId', 'true','false');
EOF;
runquery($inserSQL);

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `cdb_tencent_discuzx_sms_sent_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户uid',
  `verify_code` varchar(16) NOT NULL DEFAULT '' COMMENT '验证码',
  `phone` varchar(32) NOT NULL DEFAULT '' COMMENT '手机号',
  `type` int(10) unsigned NOT NULL  DEFAULT 1 COMMENT '发送类型 1：登录，2：绑定，3：找回密码，4：注册',
  `template_id` varchar(32) NOT NULL DEFAULT '' COMMENT '模板id',
  `template_params` text NOT NULL COMMENT '模板参数',
  `response` text NOT NULL COMMENT '接口返回',
  `status` int(10) unsigned  NOT NULL DEFAULT 0 COMMENT '状态 0：正常  1：验证码发送失败 2：验证码已失效',
  `send_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发送时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短信发送记录表';
SQL;
runquery($sql);

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `cdb_tencent_discuzx_sms_user_bind` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户uid',
  `phone` varchar(32) NOT NULL DEFAULT '' COMMENT '手机号',
  `valid` int(10) unsigned NOT NULL  DEFAULT 1 COMMENT '是否有效 0：失效，1：有效',
  `bind_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '绑定时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户手机号绑定表';
SQL;
runquery($sql);
SMSActions::uploadDzxStatisticsData('activate');
$finish = true;
