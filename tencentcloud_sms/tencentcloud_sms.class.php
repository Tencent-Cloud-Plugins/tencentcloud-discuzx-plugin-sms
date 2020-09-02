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
if (!defined('IN_DISCUZ')){
    exit('Access Denied');
}
defined('TENCENT_DISCUZX_SMS_DIR')||define( 'TENCENT_DISCUZX_SMS_DIR', __DIR__.DIRECTORY_SEPARATOR);
defined('TENCENT_DISCUZX_SMS_PLUGIN_NAME')||define( 'TENCENT_DISCUZX_SMS_PLUGIN_NAME', 'tencentcloud_sms');
defined('TENCENT_DISCUZX_USER_BIND_TABLE')||define( 'TENCENT_DISCUZX_USER_BIND_TABLE', 'tencent_discuzx_sms_user_bind');
defined('TENCENT_DISCUZX_SMS_SENT_TABLE')||define( 'TENCENT_DISCUZX_SMS_SENT_TABLE', 'tencent_discuzx_sms_sent_records');
if (!is_file(TENCENT_DISCUZX_SMS_DIR.'vendor/autoload.php')) {
    exit(lang('plugin/tencentcloud_sms','require_sdk'));
}
require_once 'vendor/autoload.php';

use TencentDiscuzSMS\SMSActions;
use TencentDiscuzSMS\SMSOptions;
class plugin_tencentcloud_sms
{
    public function common()
    {
        global $_G;
        if (empty($_G['uid'])) {
            return;
        }
        //非发帖回帖直接返回
        if($_GET['mod'] !== 'post' || !in_array($_GET['action'],array('newthread','reply'))) {
            return;
        }
        $userPhone = SMSActions::getPhoneByUid($_G['uid']);
        //手机号不为空直接返回
        if (!empty($userPhone)) {
            return;
        }
        $pluginOptions = unserialize($_G['setting'][TENCENT_DISCUZX_SMS_PLUGIN_NAME]);
        //发帖前验证是否绑定了手机号
        if ($pluginOptions['postNeedPhone'] === SMSOptions::POST_NEED_PHONE
            && $_GET['action'] == 'newthread') {
            showmessage('tencentcloud_sms:need_bind_phone');
        }
        //回帖前验证是否绑定了手机号
        if ($pluginOptions['commentNeedPhone'] === SMSOptions::COMMENT_NEED_PHONE
            && $_GET['action'] == 'reply') {
            showmessage('tencentcloud_sms:need_bind_phone');
        }
    }

    //登录区域
    public function global_login_extra()
    {
        include template('tencentcloud_sms:phone_functions_btn');
        return $phone_functions_btn;
    }

    //用户导航栏区域
    public function global_usernav_extra3()
    {
        global $_G;
        $pluginOptions = unserialize($_G['setting'][TENCENT_DISCUZX_SMS_PLUGIN_NAME]);
        //后台不开启
        if ($pluginOptions['bindPhoneTips'] === SMSOptions::HIDE_BIND_PHONE_TIPS) {
            return;
        }
        $userPhone = SMSActions::getPhoneByUid($_G['uid']);
        //用户已绑定手机号不显示
        if (!empty($userPhone)) {
            return;
        }
        //未登录不显示
        if (empty($_G['uid'])){
            return;
        }
        return '<a href="home.php?ac=plugin&mod=spacecp&id=tencentcloud_sms:bind_phone"><span style="color: red">'.lang('plugin/tencentcloud_sms','unbind').'</span></a>';
    }

}

class plugin_tencentcloud_sms_forum extends plugin_tencentcloud_sms
{
    public function viewthread_fastpost_btn_extra()
    {
        global $_G;
        $pluginOptions = unserialize($_G['setting'][TENCENT_DISCUZX_SMS_PLUGIN_NAME]);
        if ($pluginOptions['commentNeedPhone'] !== SMSOptions::COMMENT_NEED_PHONE) {
            return;
        }
        $userPhone = SMSActions::getPhoneByUid($_G['uid']);
        if (empty($userPhone)) {
            include template('tencentcloud_sms:need_bind_phone');
            return $need_bind_phone;
        }
    }

    public function forumdisplay_postbutton_top()
    {
        global $_G;
        $pluginOptions = unserialize($_G['setting'][TENCENT_DISCUZX_SMS_PLUGIN_NAME]);
        if ($pluginOptions['postNeedPhone'] !== SMSOptions::POST_NEED_PHONE) {
            return;
        }
        $userPhone = SMSActions::getPhoneByUid($_G['uid']);
        if (empty($userPhone)) {
            include template('tencentcloud_sms:need_bind_phone');
            return $need_bind_phone;
        }
    }

    public function forumdisplay_postbutton_bottom()
    {
        global $_G;
        $pluginOptions = unserialize($_G['setting'][TENCENT_DISCUZX_SMS_PLUGIN_NAME]);
        if ($pluginOptions['postNeedPhone'] !== SMSOptions::POST_NEED_PHONE) {
            return;
        }
        $userPhone = SMSActions::getPhoneByUid($_G['uid']);
        if (empty($userPhone)) {
            include template('tencentcloud_sms:need_bind_phone');
            return $need_bind_phone;
        }
    }

    public function forumdisplay_fastpost_btn_extra()
    {
        global $_G;
        $pluginOptions = unserialize($_G['setting'][TENCENT_DISCUZX_SMS_PLUGIN_NAME]);
        if ($pluginOptions['postNeedPhone'] !== SMSOptions::POST_NEED_PHONE) {
            return;
        }
        $userPhone = SMSActions::getPhoneByUid($_G['uid']);
        if (empty($userPhone)) {
            include template('tencentcloud_sms:need_bind_phone');
            return $need_bind_phone;
        }
    }

}
