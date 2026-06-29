<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-18 21:54:02
 * @LastEditTime : 2025-08-27 22:04:29
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

$vue_data = [
    'config'                      => [
        'shop_s'    => (bool) _pz('shop_s'),
        'income_s'  => (bool) _pz('pay_income_s'),
        'rebate_s'  => (bool) _pz('pay_rebate_s'),
        'admin_url' => admin_url(),
    ],
    'after_sale_status_name'      => [
        1 => '待处理',
        2 => '处理中',
        3 => '处理完成',
        4 => '用户取消',
        5 => '商家驳回',
    ],
    'after_sale_type_name'        => [
        'refund'        => '仅退款',
        'refund_return' => '退货退款',
        'replacement'   => '换货',
        'warranty'      => '保修',
        'insured_price' => '保价',
    ],
    'after_sale_progress_name'    => [
        1 => '等待用户发货',
        2 => '等待商家处理',
        3 => '等待用户收货',
        4 => '处理完成',
    ],
    'shipping_status_name'        => [
        0 => '待发货',
        1 => '待收货', //待收货，需要用户确认收货
        2 => '已完成', //已完成
    ],
    'shipping_delivery_type_name' => [
        'invit_code' => '邀请码',
        'card_pass'  => '卡密',
        'express'    => '快递',
        'no_express' => '无需物流',
        'auto'       => '自动发货',
        'fixed'      => '虚拟商品',
        'opts'       => '虚拟商品',
        'manual'     => '手动发货',
    ],
    'withdraw_status_name'        => [
        0 => '未提现',
        1 => '已提现',
        3 => '提现待处理',
    ],
    'order_type_name'             => [
        1  => '付费阅读', //文章，帖子
        2  => '付费下载', //文章
        5  => '付费图片', //文章
        6  => '付费视频', //文章
        4  => '购买会员', //用户
        8  => '余额充值', //用户
        9  => '购买积分', //用户
        10 => '购买商品', //商城，商品
    ],
    'status_name'                 => [
        -2 => '已退款',
        -1 => '已关闭',
        0  => '待支付',
        1  => '已支付',
    ],
    'marks'                       => [
        'pay'    => zibpay_get_pay_mark(),
        'points' => '积分',
    ],
    'colors'                      => ['#ff4747', '#ee5307', '#1e8608', '#1a8a65', '#0c9cc8', '#086ae8', '#3353fd', '#4641e8', '#853bf2', '#e94df7', '#ca2b7d', '#d7354c', '#ff4747', '#8e24ac'],
];
zibpay_admin_page_start();
zibpay_admin_page_vue_data_filter($vue_data);

?>
<style>
    #wpbody-content .notice{
        display: none;
    }
    .loading-mask {
        position: fixed;
        inset: 0;
        background: #fff;
        z-index: 10;
    }
</style>
<div class="zibpay-shop admin-container" id="zibpay_app">
    <?php require ZIB_ROOT_PATH . '/zibpay/page/template/header.php'; ?>
    <div class="zibpay-shop-content">
        <transition name="slide-down" mode="out-in" tag="div">
            <div v-if="$route.path == '/order'" key="order">
                <?php require_once ZIB_ROOT_PATH . '/zibpay/page/template/order.php'; ?>
            </div>
            <div v-if="$route.path == '/shipping' && config.shop_s" key="shipping">
                <?php require_once ZIB_ROOT_PATH . '/zibpay/page/template/shipping.php'; ?>
            </div>
            <div v-if="$route.path == '/after-sale' && config.shop_s" key="after-sale">
                <?php require_once ZIB_ROOT_PATH . '/zibpay/page/template/after-sale.php'; ?>
            </div>
            <div v-else key="dashboard">
                <!---默认仪表盘-->
                <?php require_once ZIB_ROOT_PATH . '/zibpay/page/template/dashboard.php'; ?>
            </div>
        </transition>
    </div>
    <?php require_once ZIB_ROOT_PATH . '/zibpay/page/template/after-sale-dialog.php'; ?>
    <?php require_once ZIB_ROOT_PATH . '/zibpay/page/template/shipping-dialog.php'; ?>
    <?php require_once ZIB_ROOT_PATH . '/zibpay/page/template/order-dialog.php'; ?>
    <?php require ZIB_ROOT_PATH . '/zibpay/page/template/footer.php'; ?>
</div>
<div class="flex jc loading-mask shop-page-loading"><div class="loading"></div></div>
