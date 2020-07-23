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
defined('TENCENT_DISCUZX_SMS_SENT_TABLE')||define( 'TENCENT_DISCUZX_SMS_SENT_TABLE', 'tencent_discuzx_sms_sent_records');
if (!is_file(TENCENT_DISCUZX_SMS_DIR.'vendor/autoload.php')) {
    exit('缺少依赖文件，请确保安装了腾讯云sdk');
}
require_once 'vendor/autoload.php';
use TencentDiscuzSMS\SMSActions;

try {
    global $_G;
    $dzxSMS = new SMSActions();
    $phone = $dzxSMS->filterGetParam('phone');
    $page = $dzxSMS->filterGetParam('page',1);
    $pageSize = $dzxSMS->filterGetParam('pageSize',15);
    $type = $dzxSMS->filterGetParam('type',0);
    $dateStart = $dzxSMS->filterGetParam('dateStart',date('Y-m-d',time() - 86400));
    $dateEnd = $dzxSMS->filterGetParam('dateEnd',date('Y-m-d'));
    $commonUrl = "plugins&page={$page}&pageSize={$pageSize}&type={$type}&phone={$phone}&dateStart={$dateStart}&dateEnd={$dateEnd}&operation=config&identifier=tencentcloud_sms&pmod=sms_sent_records&do={$pluginid}";
    if ( $page < 1 || $page > 99999 || !is_numeric($page)) {
        $page = 1;
    }
    //页大小选项数组
    $pageSizeValues = array(15,45,100);
    if (!in_array($pageSize,$pageSizeValues)) {
        $page = $pageSizeValues[0];
    }
    //类型数组
    $typeMaps = array(
        0=>'全部',
        $dzxSMS::TYPE_LOGIN=>'登录',
        $dzxSMS::TYPE_BIND=>'绑定',
        $dzxSMS::TYPE_RESET_PWD=>'重置密码',
    );
    if (!in_array($type,array_keys($typeMaps))) {
        $type = 0;
    }
    $type = intval($type);
    $pageSize = intval($pageSize);
    $page = intval($page);
    $skip = ($page - 1) * $pageSize;
    $pageSizeOptions = '';
    foreach ($pageSizeValues as $value) {
        $selected = '';
        if ($value === $pageSize) {
            $selected = "selected='selected'";
        }
        $pageSizeOptions .= "<option value='{$value}' {$selected} >$value</option>";
    }

    $typeOptions = '';
    foreach ($typeMaps as $key => $value) {
        $selected = '';
        if ($key === $type) {
            $selected = "selected='selected'";
        }
        $typeOptions .= "<option value='{$key}' {$selected} >$value</option>";
    }
    showformheader($commonUrl);
    showtableheader('验证码短信发送记录');
    $html = <<<HTML
<td>开始日期：<input value="$dateStart" name="dateStart" onclick="showcalendar(event, this)"></td>
<td>截止日期：<input value="$dateEnd" name="dateEnd" onclick="showcalendar(event, this)"></td>
<td>类型：<select name="type">$typeOptions</select></td>
<td>每页大小：<select name="pageSize">$pageSizeOptions</select></td>
<td>手机号：<input size="22" name="phone" value="$phone" ><button style="margin-left: 1rem;" class="btn">搜索</button></td>
HTML;
    echo $html;
    showtablefooter();
    showformfooter();
    $where = '`send_date` BETWEEN %s AND %s';
    $params = array(TENCENT_DISCUZX_SMS_SENT_TABLE,$dateStart.' 00:00:00',$dateEnd.' 23:59:59');

    if (in_array($type,array($dzxSMS::TYPE_LOGIN,$dzxSMS::TYPE_BIND,$dzxSMS::TYPE_RESET_PWD,$dzxSMS::TYPE_REGISTER))) {
        $where .= 'AND `type`= %d';
        $params[] = $type;
    }

    if (!empty($phone)) {
        $where .= 'AND `phone` LIKE  %s';
        $params[] = '%'.$phone.'%';
    }

    showtableheader();
    showsubtitle(array( 'ID', '手机号', '验证码', '验证码用途', '验证码状态', '发送时间'));

    $sql = "SELECT COUNT(*) FROM %t WHERE {$where}";
    $count = DB::result_first($sql,$params);

    $sql = "SELECT * FROM %t  WHERE {$where} ORDER BY `id` DESC LIMIT {$skip},{$pageSize}";
    $records = DB::fetch_all($sql,$params);
    foreach ($records as $record) {
        if ($record['status'] === '0') {
            $status = '有效';
        } elseif($record['status'] === '1') {
            $status = '无效';
        } else {
            $status = '已使用';
        }
        showtablerow('', array(), array(
            $record['id'],
            $record['phone'],
            $record['verify_code'],
            $typeMaps[intval($record['type'])],
            $status,
            $record['send_date']
        ));
    }
    $queryString = "admin.php?action={$commonUrl}";
    $pagination = multi($count, $pageSize, $page, $queryString, 99999);
    echo '<tr>
            <td colspan="6">
                <div class="cuspages" style="float: right;">'.$pagination.'</div>
            </td>
        </tr>
        <script src="static/js/calendar.js" type="text/javascript"></script>';
    showtablefooter();
    echo '<div style="text-align: center;flex: 0 0 auto;margin-top: 3rem;">
            <a href="https://openapp.qq.com/docs/DiscuzX/sms.html" target="_blank">文档中心</a> | 
            <a href="https://github.com/Tencent-Cloud-Plugins/tencentcloud-wordpress-plugin-sms" 
            target="_blank">GitHub</a> | 
            <a href="https://support.qq.com/product/164613" target="_blank">意见反馈</a>
        </div>';
}catch (\Exception $exception) {
    cpmsg($exception->getMessage(), '', 'error');
    return;
}
