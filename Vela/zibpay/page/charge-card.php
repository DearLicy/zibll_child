<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-12-21 22:54:02
 * @LastEditTime: 2024-06-22 21:52:33
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_super_admin()) {
    wp_die('您不能访问此页面', '权限不足');
    exit;
}

$this_url = esc_url(admin_url('admin.php?page=zibpay_charge_card_page'));
$tab      = !empty($_GET['tab']) ? $_GET['tab'] : '';
$action   = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';
$s        = !empty($_REQUEST['s']) ? esc_sql($_REQUEST['s']) : '';


if ($action) {
    switch ($action) {
        case 'add':
            $add_type  = !empty($_REQUEST['add_type']) ? $_REQUEST['add_type'] : 'auto';
            $pass_type = !empty($_REQUEST['type']) || $_REQUEST['type'] !== 'vip_exchange' ? $_REQUEST['type'] : 'balance_charge';

            if ($add_type === 'import') {
                $import_data     = !empty($_REQUEST['import_data']) ? $_REQUEST['import_data'] : '';
                $import_division = !empty($_REQUEST['import_division']) ? wp_unslash($_REQUEST['import_division']) : ' ';

                if (!$import_data) {
                    zib_admin_page_notice('错误！', '请粘贴您需要导入的数据', 'error');
                    break;
                }

                $import_data_array = explode("\r\n", $import_data);

                if (!$import_data_array) {
                    zib_admin_page_notice('错误！', '请输入需要生成的数量', 'error');
                    break;
                }

                $success_i = 0;
                $error_i   = 0;
                foreach ($import_data_array as $v) {
                    $v         = explode($import_division, $v);
                    $pass_meta = false;

                    if ($pass_type === 'vip_exchange') {
                        $vip_pass_data = !empty($v[2]) ? wp_parse_args($v[2]) : array();
                        if (!empty($vip_pass_data['level']) && !empty($vip_pass_data['time'])) {
                            $pass_meta = $vip_pass_data;
                        }
                    } else {
                        $v[2] = !empty($v[2]) ? (float) $v[2] : 0;

                        if ($pass_type === 'points_exchange') {
                            $pass_meta = $v[2] ? array('points' => $v[2]) : 0;
                        }
                        if ($pass_type === 'balance_charge') {
                            $pass_meta = $v[2] ? array('price' => $v[2]) : 0;
                        }
                    }

                    if (!empty($v[0]) && !empty($v[1]) && $pass_meta) {
                        $success_i++;
                        ZibCardPass::add(array(
                            'card'     => $v[0],
                            'password' => $v[1],
                            'type'     => $pass_type,
                            'status'   => '0', //正常
                            'meta'     => $pass_meta,
                            'other'    => !empty($v[3]) ? $v[3] : '',
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
                $auto_num   = !empty($_REQUEST['auto_num']) ? (int) $_REQUEST['auto_num'] : 0;
                $auto_price = !empty($_REQUEST['auto_price']) ? floatval(round((float) $_REQUEST['auto_price'], 2)) : 0;

                if (!$auto_num) {
                    zib_admin_page_notice('错误！', '请输入需要生成的数量', 'error');
                    break;
                }

                if ($pass_type === 'vip_exchange') {
                    if (empty($_REQUEST['vip_exchange']['time'])) {
                        zib_admin_page_notice('错误！', '请填写兑换会员参数', 'error');
                        break;
                    }
                    $pass_meta = $_REQUEST['vip_exchange'];
                } else {
                    if (!$auto_price) {
                        zib_admin_page_notice('错误！', '请输入卡密的面额', 'error');
                        break;
                    }
                    if ($pass_type === 'points_exchange') {
                        $pass_meta = array('points' => $auto_price);
                    }
                    if ($pass_type === 'balance_charge') {
                        $pass_meta = array('price' => $auto_price);
                    }
                }

                //生成充值卡
                $rand_number   = 20;
                $rand_password = 35;
                if (!empty($_REQUEST['auto_top_s'])) {
                    $rand_number   = isset($_REQUEST['auto_rand_number_limit']) ? (int) $_REQUEST['auto_rand_number_limit'] : 20;
                    $rand_password = !empty($_REQUEST['auto_rand_password_limit']) ? (int) $_REQUEST['auto_rand_password_limit'] : 20;
                }

                $remarks = !empty($_REQUEST['auto_remarks']) ? $_REQUEST['auto_remarks'] : '';

                zibpay_generate_pass_card($pass_type, $auto_num, $pass_meta, $rand_number, $rand_password, $remarks);

                zib_admin_page_notice('完成！', '已自动生成' . $auto_num . '个卡密');
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
                'type' => ['balance_charge','points_exchange','vip_exchange'],
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

$page_title = '卡密管理';
$head_but   = '<a href="' . add_query_arg('tab', 'add', $this_url) . '" class="page-title-action">添加卡密</a>';
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
        $page_title = '添加卡密';
        $head_but   = '<a href="' . $this_url . '" class="page-title-action">返回列表</a>';

        $csf_fields = array();

        $csf_fields[] = array(
            'content' => '<p><b>在此添加卡密</b></p>
            <li>如果您已经准备好了卡密资料，请选择导入的方式添加</li>
            <li>您也可以采用系统生成的方式，自动批量添加卡密</li>',
            'style'   => 'warning',
            'type'    => 'submessage',
        );

        $csf_fields[] = array(
            'id'      => 'type',
            'type'    => 'button_set',
            'title'   => '卡密类型',
            'inline'  => true,
            'options' => array(
                'balance_charge'  => '余额充值',
                'vip_exchange'    => '会员兑换',
                'points_exchange' => '积分兑换',
            ),
            'default' => 'balance_charge',
        );

        $csf_fields[] = array(
            'id'      => 'add_type',
            'type'    => 'button_set',
            'title'   => '添加方式',
            'inline'  => true,
            'options' => array(
                'auto'   => '系统自动生成',
                'import' => '导入卡密', //导入
            ),
            'default' => 'auto',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '生成数量',
            'id'         => 'auto_num',
            'default'    => 20,
            'min'        => 1,
            'max'        => 1000,
            'step'       => 10,
            'unit'       => '张',
            'desc'       => '需要生成多少张卡密（单次生成数量太多可能会对服务器性能造成影响）',
            'type'       => 'spinner',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type', '==', 'auto'),
            'title'      => '备注',
            'desc'       => '对生成的卡密做标记备注，方便后期查找管理',
            'id'         => 'auto_remarks',
            'default'    => 'cardpass_' . current_time('YmdHis'),
            'type'       => 'text',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type|type', '==|any', 'auto|balance_charge,points_exchange'),
            'id'         => 'auto_price',
            'title'      => '面额',
            'desc'       => '单张卡密的面额',
            'default'    => 0,
            'type'       => 'number',
            'unit'       => '',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type|type', '==|==', 'auto|vip_exchange'),
            'title'      => '兑换会员',
            'id'         => 'vip_exchange',
            'type'       => 'fieldset',
            'fields'     => array(
                array(
                    'title'   => '会员等级',
                    'id'      => 'level',
                    'type'    => 'radio',
                    'default' => '1',
                    'inline'     => true,
                    'options' => array(
                        '1' => _pz('pay_user_vip_1_name'),
                        '2' => _pz('pay_user_vip_2_name'),
                    ),
                ),
                array(
                    'title'    => ' ',
                    'subtitle' => '兑换会员时长',
                    'desc'     => '填<code>Permanent</code>为永久',
                    'id'       => 'time',
                    'default'  => '',
                    'type'     => 'text',
                ),
                array(
                    'dependency' => array('time', '!=', 'Permanent'),
                    'title'      => ' ',
                    'subtitle'   => '时长单位',
                    'id'         => 'unit',
                    'type'       => 'radio',
                    'default'    => 'month',
                    'options'    => array(
                        'day'   => '天',
                        'month' => '个月',
                    ),
                ),
            ),
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
            'title'      => '自定义卡号位数',
            'id'         => 'auto_rand_number_limit',
            'default'    => 20,
            'min'        => 1,
            'max'        => 50,
            'step'       => 5,
            'unit'       => '位数',
            'type'       => 'spinner',
        );

        $csf_fields[] = array(
            'dependency' => array('add_type|auto_top_s', '==|!=', 'auto|'),
            'title'      => '自定义密码位数',
            'class'      => 'compact',
            'id'         => 'auto_rand_password_limit',
            'default'    => 35,
            'min'        => 1,
            'max'        => 50,
            'step'       => 5,
            'unit'       => '位数',
            'desc'       => '自定义自动生成的长度（不能太短，太短可能会出现重复）如果启用了单密码模式，可以将卡号位数设置为0',
            'type'       => 'spinner',
        );

        //导入卡密
        $csf_fields[] = array(
            'dependency' => array('add_type|type', '!=|any', 'auto|balance_charge,points_exchange'),
            'content'    => '<p><b>导入卡密</b></p>
            <li>一行一个卡密，单行格式为：<code>卡号 密码 面额 备注</code></li>
            <li>卡号、密码、面额、备注默认使用空格分割，您可以在下方自定义分割符号，与您的数据对应即可</li>
            <li>单次导入数量太多可能会对服务器性能造成影响</li><li>如果使用的是单密码模式，卡号部分随便填什么都行，但不能留空</li>
            <p><b>数据示例</b></p>
            <div>4849977843617 3w20V4Qe7dRhAIoPYum6KSA 50 50元余额</div>
            <div>6484027668705 SkdJZ5vWcpqPxrbs3pqPcpq 100 100元余额</div>',
            'style'      => 'warning',
            'type'       => 'submessage',
        );
        $csf_fields[] = array(
            'dependency' => array('add_type|type', '!=|==', 'auto|vip_exchange'),
            'content'    => '<p><b>导入会员兑换卡密</b></p>
            <li>一行一个卡密，单行格式为：<code>卡号 密码 会员参数 备注</code></li>
            <li>卡号、密码、面额、备注默认使用空格分割，您可以在下方自定义分割符号，与您的数据对应即可</li>
            <li>会员参数格式为：<code>level=1&time=5&unit=day</code></li>
            <li>参数说明：level为等级，只能为1或2。time为时长，填Permanent或数字。unit为时长单位，只能填day(天)或者month(月)</li>
            <li>单次导入数量太多可能会对服务器性能造成影响</li><li>如果使用的是单密码模式，卡号部分随便填什么都行，但不能留空</li>
            <p><b>数据示例</b></p>
            <div>4849977843617 3w20V4Qe7dRhAIoPYum6KSA level=1&time=5&unit=day 5天一级会员</div>
            <div>6484027668705 SkdJZ5vWcpqPxrbs3pqPcpq level=2&time=Permanent 永久2级会员</div>
            ',
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

        $page_title = '导出卡密';
        $head_but   = '<a href="' . $this_url . '" class="page-title-action">返回列表</a>';

        $csf_fields   = array();
        $csf_fields[] = array(
            'id'      => 'type',
            'type'    => 'button_set',
            'title'   => '卡密类型',
            'inline'  => true,
            'options' => array(
                'balance_charge' => '余额充值',
                'vip_exchange'   => '会员兑换', //导入
                'points_exchange' => '积分兑换',
            ),
            'default' => 'balance_charge',
        );
        $csf_fields[] = array(
            'id'      => 'status',
            'type'    => 'radio',
            'title'   => '选择状态',
            'inline'  => true,
            'options' => array(
                'all'  => '全部',
                '0'    => '未使用', //导入
                'used' => '已使用', //导入
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

        $head_but .= '<a href="' . add_query_arg(['tab' => 'export'], $this_url) . '" class="page-title-action">导出卡密</a>';

        if ($s) {
            $head_but .= '<div><div class="update-nag" style="margin: 10px 0 0;">搜索 “' . $s . '” 的内容 </div></div>';
        } else {
            $sub_but[] = array(
                'name' => '未使用',
                'href' => add_query_arg('status', '0', $this_url),
            );

            $sub_but[] = array(
                'name' => '已使用',
                'href' => add_query_arg('status', 'used', $this_url),
            );

            $sub_but[] = array(
                'name' => '余额充值',
                'href' => add_query_arg('type', 'balance_charge'),
            );

            $sub_but[] = array(
                'name' => '积分兑换',
                'href' => add_query_arg('type', 'points_exchange'),
            );

            $sub_but[] = array(
                'name' => '会员兑换',
                'href' => add_query_arg('type', 'vip_exchange'),
            );
        }

        $where = array(
            'type' => ['balance_charge', 'vip_exchange', 'points_exchange'],
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

        if ($s) {
            $where = '`type` in (\'balance_charge\',\'vip_exchange\',\'points_exchange\') and (`other` like \'%' . $s . '%\' or `card` like \'%' . $s . '%\' or `password` like \'%' . $s . '%\' or `meta` like \'%' . $s . '%\')';
        }

        $count_all = ZibCardPass::get_count($where);
        $db_data   = ZibCardPass::get($where, $orderby, $offset, $ice_perpage, $desc);

        $table = '<thead><tr><td style="color: #ff4a4a;text-align: center;">未找到对应内容，或暂无内容</td></tr></thead>';
        if ($db_data) {
            $table    = '';
            $theads[] = array('width' => '6%', 'orderby' => 'card', 'name' => '卡号');
            $theads[] = array('width' => '10%', 'orderby' => 'password', 'name' => '密码');
            $theads[] = array('width' => '5%', 'orderby' => 'type', 'name' => '类型');
            $theads[] = array('width' => '5%', 'orderby' => 'create_time', 'name' => '创建时间');
            $theads[] = array('width' => '5%', 'orderby' => 'modified_time', 'name' => '更新时间');
            $theads[] = array('width' => '5%', 'orderby' => 'status', 'name' => '状态');
            $theads[] = array('width' => '8%', 'orderby' => 'other', 'name' => '备注');

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
                $meta        = maybe_unserialize($msg->meta);
                $status_html = '<span style="color: #3d7ffd;">未使用</span>';

                if ($msg->status === 'used') {
                    $order_link_url = add_query_arg('page', 'zibpay_order_page', admin_url('admin.php')); //前缀
                    $status_html    = '<span style="color: #f93b3b;">已使用</span>';
                    if ($msg->order_num) {
                        $status_html .= '<a style="color: #ff6215;" target="_blank" href="' . add_query_arg('s', $msg->order_num, $order_link_url) . '"> [查看]</a>';
                    }
                }

                $other_a = '';
                if ($msg->other) {
                    $other_a = '<a href="' . add_query_arg('other', $msg->other, $this_url) . '">' . $msg->other . '</a>';
                }

                if ($msg->type === 'vip_exchange') {
                    $type_html          = '<a style="color:#975106" href="' . add_query_arg('type', 'vip_exchange', $this_url) . '">会员兑换</a>';
                    $exchange_card_data = zibpay_get_vip_exchange_card_data($msg);
                    if ($exchange_card_data['time'] === 'Permanent') {
                        $exchange_card_time = '永久';
                    } else {
                        $exchange_card_time = (int) $exchange_card_data['time'] . ($exchange_card_data['unit_show']);
                    }

                    $type_html .= '<br>' . _pz('pay_user_vip_' . $exchange_card_data['level'] . '_name') . '：' . $exchange_card_time;

                } elseif ($msg->type === 'points_exchange') {
                    $type_html = '<a style="color:#289d0f" href="' . add_query_arg('type', 'points_exchange', $this_url) . '">积分兑换</a>';
                    $type_html .= '<br>积分：' . zibpay_get_pass_exchange_points($msg);
                } elseif ($msg->type === 'balance_charge') {
                    $type_html = '<a style="color:#0c76e6" href="' . add_query_arg('type', 'balance_charge', $this_url) . '">余额充值</a>';
                    $type_html .= '<br>金额：' . zibpay_get_recharge_card_price($msg);
                }

                $tbody .= '<tr>';
                $tbody .= '<th scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-232">选择</label>
                <input id="cb-select-232" type="checkbox" name="action_id[]" value="' . $msg->id . '">
                    </th>';
                $tbody .= "<td><span data-clipboard-text='$msg->card' data-clipboard-tag='卡号'>$msg->card</span></td>";
                $tbody .= "<td><span data-clipboard-text='$msg->password' data-clipboard-tag='密码'>$msg->password</span></td>";
                $tbody .= "<td>$type_html</td>";
                $tbody .= "<td>$msg->create_time</td>";
                $tbody .= "<td>$msg->modified_time</td>";
                $tbody .= "<td>$status_html</td>";
                $tbody .= "<td>$other_a</td>";
                $tbody .= '</tr>';
            }
            $table .= '<tbody>' . $tbody . '</tbody>';
        }

        $search = '<form class="form-inline form-order" method="post">
                    <div class="form-group" style="float: right;">
                        <input type="text" class="form-control" name="s" placeholder="搜索卡密">
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