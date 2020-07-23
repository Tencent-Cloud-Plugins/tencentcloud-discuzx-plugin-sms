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
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
defined('TENCENT_DISCUZX_SMS_DIR')||define( 'TENCENT_DISCUZX_SMS_DIR', __DIR__.DIRECTORY_SEPARATOR);
if (!is_file(TENCENT_DISCUZX_SMS_DIR.'vendor/autoload.php')) {
    exit('缺少依赖文件，请确保安装了腾讯云sdk');
}
require_once 'vendor/autoload.php';
use TencentDiscuzSMS\SMSActions;

try {
    if (submitcheck('sms_send_test')) {
        $dzxSMS = new SMSActions();
        global $_G;
        $phone = $dzxSMS->filterPostParam('phone');
        if (!$dzxSMS::isPhoneNumber($phone)) {
            cpmsg('请填写正确的手机号码', '', 'error');
        }
        $_G['setting']['tencentcloud_sms_setting'];
        $dzxSMS->sendVerifyCodeSMS($phone,$dzxSMS::TYPE_TEST);
        cpmsg('参数填写正确，短信发送成功！', "action=plugins&operation=config&do={$pluginid}&identifier=tencentcloud_sms&pmod=sms_send_test&subaction=send", 'succeed');
        return;
    }
    $tips = '<ol>
                <li>使用<a href="admin.php?action=plugins&operation=config&do='.$pluginid.'">设置</a>里填写的参数进行短信发送测试</li>
                <li>发送的短信仅用于测试接口，不会记录</li>
            </ol>';
    showtips($tips);
    showformheader("plugins&operation=config&identifier=tencentcloud_sms&pmod=sms_send_test&do={$pluginid}");
    showtableheader('发送短信');
    showsetting('手机号', 'phone', '', 'text', 0, 0);
    showsubmit('sms_send_test', '发送短信');
    showtablefooter();
    showformfooter();
    echo '<div style="text-align: center;flex: 0 0 auto;margin-top: 3rem;">
            <a href="https://openapp.qq.com/docs/DiscuzX/sms.html" target="_blank">文档中心</a> | <a href="https://github.com/Tencent-Cloud-Plugins/tencentcloud-wordpress-plugin-sms" target="_blank">GitHub</a> | <a
                    href="https://support.qq.com/product/164613" target="_blank">意见反馈</a>
        </div>';
}catch (\Exception $exception) {
    cpmsg($exception->getMessage(), '', 'error');
    return;
}

