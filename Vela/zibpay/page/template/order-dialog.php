<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-21 15:27:57
 * @LastEditTime : 2025-07-24 17:09:18
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_super_admin()) {
    wp_die('您不能访问此页面', '权限不足');
    exit;
}

$vue_data = array(
    'order_dialog_data'    => array(
        'order_id' => '',
    ),
    //赠品
    'gift_dialog_data'     => array(
        'gift_id' => '',
    ),

    //优惠券
    'discount_dialog_data' => [],

    'express_dialog_data'  => [
        'title'        => '物流信息',
        'show'         => 0,
        'address_data' => [],
        'express_data' => [],
    ],
);

zibpay_admin_page_vue_data_filter($vue_data);

?>

<el-drawer
    v-model="express_dialog_data.show"
    :title="express_dialog_data.title"
    direction="rtl"
    :size="win.width>640 ? '600px' : '100%'"
    :destroy-on-close="true" z-index="100030">
    <div v-loading="loading.express_dialog">
        <div v-if="express_dialog_data.express_data.traces">
        <div v-if="express_dialog_data.address_data.name" class="mb20 text-box">
                <div class="flex">
                    <div class="opacity8 mr20 flex0">收件人</div>
                    <div class="flex1">
                        <div class="flex ac">
                            <b>{{ express_dialog_data.address_data.name }}</b>
                            <div class="ml10">{{ express_dialog_data.address_data.phone }}</div>
                        </div>
                        <div class="mt6">{{ express_dialog_data.address_data.province + express_dialog_data.address_data.city + express_dialog_data.address_data.county + express_dialog_data.address_data.address }}</div>
                    </div>
                    <el-button type="primary" plain size="small" @click="copyAddress(express_dialog_data.address_data)">复制地址</el-button>
                </div>
            </div>
            <el-timeline>
                <el-timeline-item
                    v-for="(item, index) in express_dialog_data.express_data.traces"
                    :key="index"
                    :timestamp="item.time"
                    :type="index === 0 ? 'success' : ''">
                    {{ item.context }}
                </el-timeline-item>
            </el-timeline>
        </div>
        <div v-else class="flex jc ac" style="height: 100%;">
            <el-empty description="暂无物流信息"></el-empty>
        </div>
    </div>
</el-drawer>
