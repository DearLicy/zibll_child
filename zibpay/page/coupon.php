<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2024-06-26 11:52:43
 * @LastEditTime: 2024-07-15 13:11:57
 * @Email: 770349780@qq.com
 * @Project: Zibll子比主题
 * @Description: 更优雅的Wordpress主题 | 后台优惠码页面
 * @Read me: 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind: 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 * Copyright (c) 2024 by Qinver, All Rights Reserved.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_super_admin()) {
    wp_die('您不能访问此页面', '权限不足');
    exit;
}

$this_url  = esc_url(admin_url('admin.php?page=zibpay_coupon_page'));
$tab       = !empty($_GET['tab']) ? $_GET['tab'] : '';
$action    = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$s         = !empty($_REQUEST['s']) ? esc_sql($_REQUEST['s']) : '';
$this_name = '优惠码';

if ($action) {
    switch ($action) {
        case 'add':

            $add_type  = !empty($_REQUEST['add_type']) ? $_REQUEST['add_type'] : 'auto';
            $pass_type = !empty($_REQUEST['type']) || $_REQUEST['type'] !== 'vip_coupon' ? $_REQUEST['type'] : 'coupon';

            if ($add_type === 'import') {
                $import_data     = !empty($_REQUEST['import_data']) ? $_REQUEST['import_data'] : '';
                $import_division = !empty($_REQUEST['import_division']) ? wp_unslash($_REQUEST['import_division']) : ' ';

                if (!$import_data) {
                    zib_admin_page_notice('错误！', '请粘贴您需要导入的数据', 'error');
                    break;
                }

                $import_data_array = explode("\r\n", $import_data);

                if (!$import_data_array) {
                    zib_admin_page_notice('错误！', '未找到需要导入的数据', 'error');
                    break;
                }

                $success_i = 0;
                $error_i   = 0;
                foreach ($import_data_array as $v) {
                    $v = explode($import_division, $v);

                    if (isset($v[6]) && in_array($v[2], ['multiply', 'subtract']) && is_numeric($v[3])) {

                        if ($pass_type === 'vip_exchange') {

                        } else {
                            //时间格式转换为年月日 23:59:59
                            $expire_time = $v[4] ? $v[4] : 0;
                            if ($expire_time) {
                                $expire_time = date('Y-m-d 23:59:59', strtotime($expire_time));
                            }

                            $discount = array(
                                'type' => $v[2],
                                'val'  => $v[3],
                            );
                            $meta = array(
                                'discount'    => $discount,
                                'title'       => $v[5],
                                'reuse'       => $v[6],
                                'expire_time' => $expire_time,
                            );
                        }

                        $success_i++;
                        ZibCardPass::add(array(
                            'password' => $v[0],
                            'post_id'  => (int) $v[1],
                            'type'     => $pass_type,
                            'status'   => '0', //正常
                            'meta'     => $meta,
                            'other'    => !empty($v[7]) ? $v[7] : '',
                        ));
                    } else {
                        $error_i++;
                    }
                }

                if ($success_i) {
                    zib_admin_page_notice('导入完成', '成功导入' . $success_i . '个卡密' . ($error_i ? '，' . $error_i . '个导入失败' : ''));
                    break;
                } else {
                    zib_admin_page_notice('导入失败', '数据格式错误', 'error');
                    break;
                }

            } else {
                //自动生成
                $auto_num    = !empty($_REQUEST['auto_num']) ? (int) $_REQUEST['auto_num'] : 0;
                $post_id     = !empty($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;
                $discount    = !empty($_REQUEST['discount']) ? (array) $_REQUEST['discount'] : array();
                $title       = !empty($_REQUEST['title']) ? esc_sql($_REQUEST['title']) : ''; //标题，过滤为纯文字
                $reuse       = isset($_REQUEST['reuse']) ? (int) $_REQUEST['reuse'] : 1; //标题，过滤为纯文字
                $expire_time = !empty($_REQUEST['expire_time']) ? esc_sql($_REQUEST['expire_time']) : 0;
                $mate        = array();

                if (!$auto_num) {
                    zib_admin_page_notice('错误！', '请输入需要生成的数量', 'error');
                    break;
                }

                if (empty($discount['type']) || empty($discount['val'])) {
                    zib_admin_page_notice('错误！', '请设置优惠券的优惠折扣', 'error');
                    break;
                }

                if ($pass_type === 'vip_coupon') {
                } else {
                    $mate = array(
                        'discount'    => $discount,
                        'title'       => $title,
                        'reuse'       => $reuse,
                        'expire_time' => $expire_time,
                    );
                }

                $rand_password = 8;
                if (!empty($_REQUEST['auto_top_s'])) {
                    $rand_password = !empty($_REQUEST['auto_rand_password_limit']) ? (int) $_REQUEST['auto_rand_password_limit'] : 8;
                }

                $remarks = !empty($_REQUEST['auto_remarks']) ? $_REQUEST['auto_remarks'] : '';

                zibpay_generate_coupon($pass_type, $auto_num, $post_id, $mate, $rand_password, $remarks);

                zib_admin_page_notice('完成！', '已自动生成' . $auto_num . '个优惠码');
                break;
            }

            zib_admin_page_notice('错误！', '参数传入错误', 'error');

            break;

        case 'delete':
            $delete_ids = !empty($_REQUEST['action_id']) ? $_REQUEST['action_id'] : 0;
            if (!$delete_ids) {
                zib_admin_page_notice('错误！', '未选择需要删除的内容', 'error');
                break;
            }
            $delete_i = ZibCardPass::delete(array(
                'id'   => $delete_ids,
                'type' => ['coupon', 'vip_coupon'],
            ));

            zib_admin_page_notice('删除完成', '已删除' . $delete_i . '个卡密');
            break;
    }
}

function zib_admin_page_notice($title = '', $msg = '', $type = 'success')
{
    $html = '';
    $html .= $title ? '<h3>' . $title . '</h3>' : '';
    $html .= $msg ? '<p>' . $msg . '</p>' : '';

    if ($html) {
        echo '<div class="notice notice-' . $type . '">' . $html . '</div>';
    }
}

$page_title = $this_name . '管理';
$head_but   = '<a href="' . add_query_arg('tab', 'add', $this_url) . '" class="page-title-action">添加' . $this_name . '</a>';
$sub_but    = array();

//准备查询参数
$msg_type    = !empty($_REQUEST['msg_type']) ? $_REQUEST['msg_type'] : 0;
$user_id     = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
$orderby     = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'modified_time';
$paged       = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
$ice_perpage = !empty($_REQUEST['ice_perpage']) ? $_REQUEST['ice_perpage'] : 30;
$desc        = !empty($_REQUEST['desc']) ? $_REQUEST['desc'] : 'DESC';
$offset      = $ice_perpage * ($paged - 1);

$count_all = 0;
$db_data   = false;
$csf_args  = false;
$table     = false;
$pagenavi  = false;
$search    = false;
$page_html = false;

switch ($tab) {

    case 'add':
        $page_title = '添加' . $this_name;
        $head_but   = '<a href="' . $this_url . '" class="page-title-action">返回列表</a>';

        $csf_fields = array();

        $csf_fields[] = array(
            'content' => '<p><b>在此添加优惠码</b></p>
            <li>如果您已经准备好了相关数据，请选择导入的方式添加</li>
            <li>您也可以采用系统生成的方式，自动批量添加</li>',
            'style'   => 'warning',
            'type'    => 'submessage',
        );

        $csf_fields[] = array(
            'id'      => 'type',
            'type'    => 'button_set',
            'class'   => 'hide', //暂时隐藏
            'title'   => '卡密类型',
            'inline'  => true,
            'options' => array(
                'coupon'     => '购买商品优惠券',
                'vip_coupon' => '购买会员优惠券',
            ),
            'default' => 'coupon',
        );

        $csf_fields[] = array(
            'id'      => 'add_type',
            'type'    => 'button_set',
            'title'   => '添加方式',
            'inline'  => true,
            'options' => array(
                'auto'   => '系统生成',
                'import' => '导入', //导入
            ),
            'default' => 'auto',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '生成数量',
            'id'         => 'auto_num',
            'default'    => 10,
            'min'        => 1,
            'max'        => 1000,
            'step'       => 10,
            'unit'       => '张',
            'desc'       => '需要生成多少（单次生成数量太多可能会对服务器性能造成影响）',
            'type'       => 'spinner',
        );

        $csf_fields[] = array(
            'dependency'  => array('add_type', '==', 'auto'),
            'title'       => __('绑定文章', 'zib_language'),
            'id'          => 'post_id',
            'default'     => '',
            'options'     => 'post',
            'type'        => 'select',
            'placeholder' => '输入关键词以搜索',
            'chosen'      => true,
            'desc'        => '选择一个文章，绑定优惠券到该文章，如果不绑定则所有商品都可以使用',
            'multiple'    => false,
            'sortable'    => false,
            'ajax'        => true,
            'settings'    => array(
                'min_length' => 2,
            ),
            'query_args'  => array(
                'post_type' => array('plate', 'forum_post', 'post', 'page'),
            ),
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '优惠折扣',
            'id'         => 'discount',
            'type'       => 'fieldset',
            'fields'     => array(
                array(
                    'title'   => '优惠方式(必填)',
                    'id'      => 'type',
                    'type'    => 'radio',
                    'default' => 'multiply',
                    'inline'  => true,
                    'options' => array(
                        'multiply' => '打折',
                        'subtract' => '减价',
                    ),
                ),
                array(
                    'title'   => '优惠数据(必填)',
                    'desc'    => '折扣比例或者减价金额<br>选择打折时候，填0.01-0.99，对应0.1折到9.9折',
                    'id'      => 'val',
                    'default' => '',
                    'type'    => 'text',
                ),
            ),
        );

        $csf_fields[] = array(
            'title'    => '优惠券有效期',
            'id'       => 'expire_time',
            'type'     => 'date',
            'desc'     => '优惠券的有效期，过期后将无法使用。留空则不限制',
            'default'  => '',
            'settings' => array(
                'dateFormat'  => 'yy-mm-dd 23:59:59',
                'changeMonth' => true,
                'changeYear'  => true,
            ),
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '优惠码名称',
            'desc'       => '一句话描述这个优惠码的作用，例如：国庆大促 限时8折',
            'id'         => 'title',
            'default'    => '',
            'type'       => 'text',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '重复使用',
            'desc'       => '一个优惠码可以被使用的次数，0为无限次，默认1次',
            'id'         => 'reuse',
            'default'    => 1,
            'min'        => 0,
            'max'        => 100,
            'step'       => 1,
            'unit'       => '次',
            'type'       => 'spinner',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '高级选项',
            'id'         => 'auto_top_s',
            'default'    => false,
            'type'       => 'switcher',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type|auto_top_s', '==|!=', 'auto|'),
            'title'      => '自定义位数',
            'desc'       => '自定义自动生成的长度（不能太短，太短可能会出现重复）',
            'id'         => 'auto_rand_password_limit',
            'default'    => 8,
            'min'        => 1,
            'max'        => 50,
            'step'       => 2,
            'unit'       => '位数',
            'type'       => 'spinner',
        );
        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '备注',
            'desc'       => '对生成的数据做标记备注，方便后期查找管理',
            'id'         => 'auto_remarks',
            'default'    => 'coupon_' . current_time('YmdHis'),
            'type'       => 'text',
        );
        //导入
        $csf_fields[] = array(
            'dependency' => array('add_type|type', '!=', 'auto'),
            'content'    => '<p><b>导入卡密</b></p>
            <li>一行一个卡密，单行格式为：<code>优惠码 绑定文章ID 折扣方式 折扣数额 有效期 优惠码名称 重复使用次数 备注</code></li>
            <li>默认使用空格分割，您可以在下方自定义分割符号，与您的数据对应即可</li>
            <li>绑定文章ID填写需要绑定的文章、帖子的ID，如需全部商品可用，填0即可</li>
            <li>折扣方式只有两种可选：multiply和subtract，对应打折和减价</li>
            <li>折扣数额为折扣比例或者减价金额</li>
            <li>有效期仅支持年月日，不支持时间格式为 2024-08-08，填0则为不限制</li>
            <li>优惠码名称为可选，如不需要填0</li>
            <li>重复使用次数填0则可无限重复使用</li>
            <li></li>
            <li>单次导入数量太多可能会对服务器性能造成影响</li>
            <p><b>数据示例</b></p>
            <div>oPYum6KSA 0 multiply 0.88 0 0 0 所有商品打8.8折扣永久无限重复使用</div>
            <div>LPWbpmtTG 0 subtract 50 2024-12-31 24年50元优惠券 10 所有商品立减50元24年有效仅10可使用10次</div>',
            'style'      => 'warning',
            'type'       => 'submessage',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'title'      => '卡密数据',
            'id'         => 'import_data',
            'default'    => '',
            'attributes' => array(
                'rows'  => 10,
                'style' => 'resize: both;max-width: none;',
            ),
            'sanitize'   => false,
            'type'       => 'textarea',
        );
        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'import'),
            'id'         => 'import_division', //分割
            'title'      => '自定义分隔符号',
            'subtitle'   => '',
            'class'      => 'mini-input',
            'default'    => ' ',
            'desc'       => '卡号和密码之间分割符号（默认为空格分割）',
            'type'       => 'text',
        );
        $csf_fields[] = array(
            'title'   => ' ',
            'type'    => 'content',
            'content' => '<button type="submit" class="but jb-blue">确认提交</button>',
        );

        $csf_args = array(
            'class'  => 'csf-profile-options',
            'method' => 'post',
            'value'  => array(),
            'hidden' => array(
                array(
                    'name'  => 'action',
                    'value' => 'add',
                ),
            ),
            'fields' => $csf_fields,
        );

        break;

    case 'export':

        $page_title = '导出' . $this_name;
        $head_but   = '<a href="' . $this_url . '" class="page-title-action">返回列表</a>';

        $csf_fields   = array();
        $csf_fields[] = array(
            'id'      => 'type',
            'type'    => 'button_set',
            'class'   => 'hide', //暂时隐藏
            'title'   => $this_name . '类型',
            'inline'  => true,
            'options' => array(
                'coupon'     => '商品优惠码',
                'vip_coupon' => '会员优惠码', //导入
            ),
            'default' => 'coupon',
        );
        $csf_fields[] = array(
            'id'      => 'status',
            'type'    => 'radio',
            'title'   => '选择状态',
            'inline'  => true,
            'options' => array(
                'all'  => '全部',
                '0'    => '可用', //导入
                'used' => '不可用', //导入
            ),
            'default' => 'all',
        );
        $csf_fields[] = array(
            'id'      => 'export_format',
            'type'    => 'radio',
            'title'   => '导出格式',
            'inline'  => true,
            'options' => array(
                'text' => '文本文档',
                'xls'  => 'Excel表格', //导入
            ),
            'default' => 'xls',
        );

        $csf_fields[] = array(
            'dependency' => array('export_format', '==', 'text'),
            'id'         => 'text_division', //分割
            'title'      => ' ',
            'subtitle'   => '分隔符号',
            'class'      => 'mini-input',
            'default'    => ' ',
            'desc'       => '卡号和密码之间分割符号（默认为空格分割）',
            'type'       => 'text',
        );

        $csf_fields[] = array(
            'title'   => ' ',
            'type'    => 'content',
            'content' => '<button type="submit" class="but jb-blue">确认提交</button>',
        );

        $csf_args = array(
            'class'  => 'csf-profile-options',
            'method' => 'post',
            'action' => admin_url('admin-ajax.php'),
            'value'  => array(),
            'hidden' => array(
                array(
                    'name'  => 'action',
                    'value' => 'card_pass_export',
                ),
            ),
            'fields' => $csf_fields,
        );

        break;

    default: //文章类型的
        //默认页面，展示卡密列表
        $pagenavi  = true;
        $sub_but[] = array(
            'name' => '全部',
            'href' => $this_url,
        );

        $head_but .= '<a href="' . add_query_arg(['tab' => 'export'], $this_url) . '" class="page-title-action">导出</a>';

        if ($s) {
            $head_but .= '<div><div class="update-nag" style="margin: 10px 0 0;">搜索 “' . $s . '” 的内容 </div></div>';
        } else {
            $sub_but[] = array(
                'name' => '可用',
                'href' => add_query_arg('status', '0', $this_url),
            );

            $sub_but[] = array(
                'name' => '不可用',
                'href' => add_query_arg('status', 'used', $this_url),
            );

            /**
             *  //暂时不开放

            $sub_but[] = array(
            'name' => '商品优惠券',
            'href' => add_query_arg('type', 'coupon'),
            );

            $sub_but[] = array(
            'name' => '购买会员优惠券',
            'href' => add_query_arg('type', 'vip_coupon'),
            );
             */
        }

        $where = array(
            'type' => ['coupon', 'vip_coupon'],
        );

        if (isset($_GET['type'])) {
            $where['type'] = $_GET['type'];
        }

        if (isset($_GET['status'])) {
            $where['status'] = $_GET['status'];
        }
        if (isset($_GET['other'])) {
            $where['other'] = $_GET['other'];
        }
        if (isset($_GET['post_id'])) {
            $where['post_id'] = (int) $_GET['post_id'];
        }

        if ($s) {
            $where = '`type` in (\'coupon\',\'vip_coupon\') and (`other` like \'%' . $s . '%\' or `card` like \'%' . $s . '%\' or `password` like \'%' . $s . '%\' or `meta` like \'%' . $s . '%\')';
        }

        $count_all = ZibCardPass::get_count($where);
        $db_data   = ZibCardPass::get($where, $orderby, $offset, $ice_perpage, $desc);

        $table = '<thead><tr><td style="color: #ff4a4a;text-align: center;">未找到对应内容，或暂无内容</td></tr></thead>';
        if ($db_data) {
            $table    = '';
            $theads[] = array('width' => '12%', 'orderby' => 'password', 'name' => '优惠码');
            $theads[] = array('width' => '12%', 'orderby' => 'post_id', 'name' => '绑定商品');
            $theads[] = array('width' => '15%', 'orderby' => '', 'name' => '使用限制');
            $theads[] = array('width' => '15%', 'orderby' => '', 'name' => '状态');
            $theads[] = array('width' => '10%', 'orderby' => 'create_time', 'name' => '创建时间');
            $theads[] = array('width' => '10%', 'orderby' => 'modified_time', 'name' => '更新时间');
            $theads[] = array('width' => '15%', 'orderby' => 'other', 'name' => '备注');

            $thead_th = '<td id="cb" class="manage-column column-cb check-column" style="width: 2%;"><label class="screen-reader-text" for="cb-select-all-1">全选</label><input id="cb-select-all-1" type="checkbox"></td>';
            foreach ($theads as $thead) {
                $orderby = '';
                if ($thead['orderby']) {
                    $orderby_url = add_query_arg('orderby', $thead['orderby']);
                    $orderby .= '<a title="降序" href="' . add_query_arg('desc', 'ASC', $orderby_url) . '"><span class="dashicons dashicons-arrow-up"></span></a>';
                    $orderby .= '<a title="升序" href="' . add_query_arg('desc', 'DESC', $orderby_url) . '"><span class="dashicons dashicons-arrow-down"></span></a>';
                    $orderby = '<span class="orderby-but">' . $orderby . '</span>';
                }
                $thead_th .= '<th class="" width="' . $thead['width'] . '">' . $thead['name'] . $orderby . '</th>';
            }
            $table .= '<thead><tr>' . $thead_th . '</tr></thead>';

            $tbody = '';
            foreach ($db_data as $msg) {
                $order_link_url = add_query_arg('page', 'zibpay_order_page', admin_url('admin.php')); //前缀
                $coupon_data    = zibpay_filter_coupon_data($msg);
                $meta           = maybe_unserialize($msg->meta);
                $discount_text  = $coupon_data['discount_text'];

                $limit_html = '';
                if ($coupon_data['reuse'] == 1) {
                    $limit_html .= '<span style="color: #975106;">单次使用</span>';
                } elseif (!$coupon_data['reuse']) {
                    $limit_html .= '<span style="color: #289d0f;">无限重复可用</span>';
                } else {
                    $limit_html .= '<span style="color: #3d7ffd;">可重复使用' . $coupon_data['reuse'] . '次</span>';
                }

                if ($coupon_data['expire_time']) {
                    if (current_time('timestamp') > strtotime($coupon_data['expire_time'])) {
                        $limit_html .= '<div style="color: #f93b3b;" title="有效期' . $coupon_data['expire_time'] . '">【已过期】</div>';
                    } else {
                        $limit_html .= '<div style="color: #db8426;">有效期' . $coupon_data['expire_time'] . '</div>';
                    }
                }

                $status_html = '';
                if ($msg->status === 'used') {
                    $status_html .= '<sapn class="badg c-red">不可用</sapn>';
                } else {
                    $status_html .= '<sapn class="badg c-green">可用</sapn>';
                }

                if ($coupon_data['used_count']) {
                    $status_html .= '<sapn style="color: #db8426;">已用' . $coupon_data['used_count'] . '次</sapn>';
                    foreach ($coupon_data['used_order_num'] as $i => $order_num) {
                        $status_html .= ($i === 0 ? '<br>' : '') . '<a target="_blank" href="' . add_query_arg('order_num', $order_num, $order_link_url) . '">[' . ($i + 1) . ']</a> ';
                    }
                } else {
                    $status_html .= '<sapn style="color: #3d7ffd;">未使用</sapn>';
                }

                $other_a = '';
                if ($coupon_data['title']) {
                    $other_a .= '<div><a style="color: #269e95;font-weight: bold;" href="' . add_query_arg('s', $coupon_data['title'], $this_url) . '">' . $coupon_data['title'] . '</a></div>';
                }
                if ($msg->other) {
                    $other_a .= '<a style=" color:#4f647b " href="' . add_query_arg('other', $msg->other, $this_url) . '">' . $msg->other . '</a>';
                }

                if ($msg->type === 'vip_coupon') {
                    $post_a = '购买会员';

                    $type_html = '<a style="color:#975106" href="' . add_query_arg('type', 'vip_coupon', $this_url) . '">购买会员</a>';
                } else {
                    $post_a    = '全部商品';
                    $type_html = '<a style="color:#289d0f" href="' . add_query_arg('type', 'coupon', $this_url) . '">购买商品</a>';
                }

                $post_a = '<a href="' . $this_url . '&post_id=0">全部商品</a>';
                if ($msg->post_id) {
                    $the_title = get_the_title($msg->post_id);
                    $post_a    = '<div title="' . $the_title . '"  style="overflow: hidden; text-overflow:ellipsis; white-space: nowrap; display: block;" ><a href="' . $this_url . '&post_id=' . $msg->post_id . '">' . $the_title . '</a></div><a style="color: #6e6a6f;" target="_blank" href="' . get_permalink($msg->post_id) . '">[查看] </a>';
                }

                $tbody .= '<tr>';
                $tbody .= '<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-232">选择</label>
                <input id="cb-select-232" type="checkbox" name="action_id[]" value="' . $msg->id . '">
                    </th>';
                $tbody .= "<td><a class=\"badg c-blue mr6\" href=\"javascript:;\" data-clipboard-text='$msg->password' data-clipboard-tag='优惠码'>$msg->password</a><span class=\"badg c-yellow\">$discount_text</span></td>";
                $tbody .= "<td>$post_a</td>";
                // $tbody .= "<td>$type_html</td>";
                $tbody .= "<td>$limit_html</td>"; //限制使用次数
                $tbody .= "<td>$status_html</td>";
                $tbody .= "<td>$msg->create_time</td>";
                $tbody .= "<td>$msg->modified_time</td>";
                $tbody .= "<td>$other_a</td>";
                $tbody .= '</tr>';
            }
            $table .= '<tbody>' . $tbody . '</tbody>';
        }

        $search = '<form class="form-inline form-order" method="post">
                    <div class="form-group" style="float: right;">
                        <input type="text" class="form-control" name="s" placeholder="搜索优惠码">
                        <button type="submit" class="button">提交</button>
                    </div>
                </form>';

        break;
}

?>


<div class="wrap">
    <style>
        .orderby-but {
            position: relative;
        }

        .orderby-but>a {
            opacity: .4;
            position: absolute;
            transform: translateY(-3px);
            transition: .3s;
        }

        .orderby-but>a+a {
            transform: translateY(6px);
        }

        .orderby-but:hover a {
            opacity: .6;
        }

        .orderby-but>a:hover {
            opacity: 1;
        }
    </style>
    <h1 class="wp-heading-inline"><?php echo $page_title; ?></h1>
    <?php echo $head_but; ?>
    <?php
$but_html = '';
if ($sub_but) {
    foreach ($sub_but as $but) {
        $but_html .= '<li><a href="' . $but['href'] . '">' . $but['name'] . '</a></li> | ';
    }
}

echo '<div class="order-header"><ul class="subsubsub">' . substr($but_html, 0, -2) . '</ul>' . $search . '</div>';

if ($table) {

    echo '<div class="clear"></div>';
    echo '<form class="" method="post">';
    echo '<div class="bulkactions" style="margin: 10px 0;">
			<label for="bulk-action-selector-top" class="screen-reader-text">选择批量操作</label><select name="action" id="bulk-action-selector-top">
                <option value="-1">批量操作</option>
                    <option value="delete">删除</option>
                </select>
                <input type="submit" class="button action" value="应用">
		</div>';

    echo '<div style="overflow-y: auto;width: 100%;">';
    echo '<table class="widefat fixed striped posts table table-bordered" style="min-width: 1000px;">';
    echo $table;
    echo '</table>';
    echo '</div>';
    echo '</form>';
    echo '<div class="clear"></div>';

} elseif ($csf_args) {
    ZCSF::instance('add_msg', $csf_args);
}
if ($page_html) {
    echo $page_html;
}
if ($pagenavi) {
    zibpay_admin_pagenavi($count_all, $ice_perpage);
}

?>


</div>