<?php
/**
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
if (!defined('IN_DISCUZ')){
    exit('Access Denied');
}
defined('TENCENT_DISCUZX_SMS_DIR')||define( 'TENCENT_DISCUZX_SMS_DIR', __DIR__.DIRECTORY_SEPARATOR);
defined('TENCENT_DISCUZX_SMS_PLUGIN_NAME')||define( 'TENCENT_DISCUZX_SMS_PLUGIN_NAME', 'tencentcloud_sms');
if (!is_file(TENCENT_DISCUZX_SMS_DIR.'vendor/autoload.php')) {
    exit(lang('plugin/tencentcloud_sms','require_sdk'));
}
require_once 'vendor/autoload.php';

use TencentDiscuzSMS\SMSActions;
use TencentDiscuzSMS\SMSOptions;
class mobileplugin_tencentcloud_sms
{
    public function common()
    {
    }

    function global_footer_mobile()
    {
        global $_G;

    }

}

class mobileplugin_tencentcloud_sms_member extends mobileplugin_tencentcloud_sms
{
    function logging_bottom_mobile()
    {
        $html = <<<HTML
<p class="reg_link"><a href="plugin.php?id=tencentcloud_sms:mobile_login&mobile=2">使用手机号登录</a></p>
<p class="reg_link"><a href="plugin.php?id=tencentcloud_sms:mobile_reset_pwd&mobile=2">使用手机号找回密码</a></p>
HTML;
        return $html;
    }
}
