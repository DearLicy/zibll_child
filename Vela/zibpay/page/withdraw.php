<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2020-11-01 17:08:02
 * @LastEditTime: 2024-05-10 10:51:15
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : 一款极其优雅的Wordpress主题->后台提现管理模板
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}
$user_Info = wp_get_current_user();
if (!is_user_logged_in()) {
    exit;
}
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : false;

if ('process_submit' == $action) {
    $is_allow = ($_REQUEST['process'] == 1);
    if (zibpay_withdraw_process($_REQUEST['process_id'], $is_allow, $_REQUEST['msg'])) {
        echo '<div class="updated notice-alt"><h4 style="color: #0aaf19;">提现处理成功，已' . ($is_allow ? '批准' : '拒绝') . '该提现申请</h4></div>';
    } else {
        echo '<div class="updated notice-alt"><h4 style="color: #ed2273;">提现处理失败</h4></div>';
    }
}

//准备参数
$page_url = add_query_arg('page', 'zibpay_withdraw', admin_url('admin.php'));
$s        = !empty($_REQUEST['s']) ? $_REQUEST['s'] : false;

$WHERE = array('type' => 'withdraw');

//状态
if (isset($_REQUEST['status'])) {
    $WHERE['status'] = (int) $_REQUEST['status'];
}
//用户
if (isset($_REQUEST['send_user'])) {
    $WHERE['send_user'] = (int) $_REQUEST['send_user'];
}
//id
if (isset($_REQUEST['id'])) {
    $WHERE['id'] = (int) $_REQUEST['id'];
}

//搜索
if ($s) {
    $WHERE = "
    `type` = 'withdraw' and (
    `title` LIKE '%$s%' OR
    `content` LIKE '%$s%' OR
    `meta` LIKE '%$s%')";

    $page_url = $page_url . '&amp;s=' . $s;
}


global $wpdb;
//统计数据
$all_count = ZibMsg::get_count($WHERE);

//分页计算
$ice_perpage = 20;
$pages       = ceil($all_count / $ice_perpage);
$page        = isset($_REQUEST['paged']) ? intval($_REQUEST['paged']) : 1;
$offset      = $ice_perpage * ($page - 1);
//排序
$order = !empty($_REQUEST['orderby']) ? $_REQUEST['orderby'] : 'id';
$desc  = !empty($_REQUEST['desc']) ? $_REQUEST['desc'] : 'DESC';

$list = ZibMsg::get($WHERE, $order, $offset, $ice_perpage, $desc);

//echo json_encode($list);
?>
<style>
    .table-box>table {
        min-width: 1000px;
    }
</style>

<div class="wrap">
    <h2>佣金提现管理</h2>

    <ul class="subsubsub">
        <li class="all"><a href="<?php echo $page_url; ?>">全部</a> |</li>
        <li class="all"><a href="<?php echo add_query_arg('status', '0', $page_url); ?>">待处理</a> |</li>
        <li class="all"><a href="<?php echo add_query_arg('status', '1', $page_url); ?>">已批准</a> |</li>
        <li class="all"><a href="<?php echo add_query_arg('status', '2', $page_url); ?>">已拒绝</a></li>
    </ul>

    <div class="order-header" style="margin: 6px 0 16px;">
        <form class="form-inline form-order" method="post" action="<?php echo $page_url; ?>">
            <div class="form-group">
                <input type="text" class="form-control" name="s" placeholder="搜索记录">
                <button type="submit" class="button button-primary">提交</button>
            </div>
        </form>
        <?php
        echo $s ? '<div class="order-header">"' . esc_attr($s) . '" 的搜索结果</div>' : '';

        ?>
    </div>

    <div class="table-box">
        <table class="widefat fixed striped posts">
            <thead>
                <tr>
                    <?php
                    $theads   = array();
                    $theads[] = array('width' => '8%', 'orderby' => 'status', 'name' => '状态');
                    $theads[] = array('width' => '10%', 'orderby' => 'send_user', 'name' => '申请用户');
                    $theads[] = array('width' => '10%', 'orderby' => '', 'name' => '提现金额');
                    $theads[] = array('width' => '10%', 'orderby' => '', 'name' => '提现详情');
                    $theads[] = array('width' => '20%', 'orderby' => '', 'name' => '说明');
                    $theads[] = array('width' => '7%', 'orderby' => 'create_time', 'name' => '申请时间');
                    $theads[] = array('width' => '7%', 'orderby' => 'modified_time', 'name' => '更新时间');

                    foreach ($theads as $thead) {
                        $orderby = '';
                        if ($thead['orderby']) {
                            $orderby_url = add_query_arg('orderby', $thead['orderby'], $page_url);
                            $orderby .= '<a title="降序" href="' . add_query_arg('desc', 'ASC', $orderby_url) . '"><span class="dashicons dashicons-arrow-up"></span></a>';
                            $orderby .= '<a title="升序" href="' . add_query_arg('desc', 'DESC', $orderby_url) . '"><span class="dashicons dashicons-arrow-down"></span></a>';
                            $orderby = '<span class="orderby-but">' . $orderby . '</span>';
                        }
                        echo '<th class="" width="' . $thead['width'] . '">' . $thead['name'] . $orderby . '</th>';
                    } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($list) {
                    $ii = 1;
                    foreach ($list as $value) {
                        //整理数据
                        $user_data = get_userdata((int) $value->send_user);
                        $user_name = $user_data->display_name;
                        $user_name = '<a href="' . add_query_arg('send_user', (int) $value->send_user, $page_url) . '">' . $user_name . '</a>';

                        $user_name .= '<div class="row-actions">';
                        $user_name .= '<a href="' . get_edit_user_link((int) $value->send_user) . '">管理</a> | ';
                        $user_name .= '<a href="' . zib_get_user_home_url((int) $value->send_user) . '">查看</a>';
                        $user_name .= '</div>';

                        $meta             = (array) maybe_unserialize($value->meta);
                        $withdraw_message = !empty($meta['withdraw_message']) ? '用户留言：' . esc_attr($meta['withdraw_message']) : "";
                        $withdraw_message .= !empty($meta['admin_message']) ? '<div style="color: #3437f7;">处理留言：' . esc_attr($meta['admin_message']) . '</div>' : "";
                        $__withdraw_price = $meta['withdraw_price'];
                        $__service_price  = isset($meta['service_price']) ? $meta['service_price'] : 0;

                        $__rebate_sum  = isset($meta['withdraw_detail']['rebate']) ? $meta['withdraw_detail']['rebate'] : 0;
                        $__income_sum  = isset($meta['withdraw_detail']['income']) ? $meta['withdraw_detail']['income'] : 0;
                        $__balance_sum = isset($meta['withdraw_detail']['balance']) ? $meta['withdraw_detail']['balance'] : 0;

                        $withdraw_price_count = '<b style="color: #4066fb;font-weight: bold;font-size:1.2em;">￥' . $__withdraw_price . '</b>' . ($__service_price > 0 ? '<div style="font-size: 12px;opacity: .8;">手续费￥' . $__service_price . '</div>' : '');
                        $withdraw_details     = '<div style="font-size: 12px;">' . ($__rebate_sum ? '推广佣金' . $__rebate_sum . '元<br>' : '') . ($__income_sum ? '创作分成' . $__income_sum . '元<br>' : '') . ($__balance_sum > 0 ? '余额' . $__balance_sum . '元' : '') . ($__balance_sum < 0 ? '<div style="color: #e8720a;">其中' . abs($__balance_sum) . '元转入余额</div>' : '') . '</div>';

                        $status     = $value->status;
                        $status_but = $status;
                        if (1 == $status) {
                            $status_but = '<span style=" color: #0989fd; ">处理完成</span>';
                        } elseif (2 == $status) {
                            $status_but = '<span style=" color: #fb4444; ">已拒绝</span>';
                        } elseif (0 == $status) {
                            $status_but = '<a class="button" href="' . add_query_arg(array('action' => 'process', 'id' => $value->id), $page_url) . '">立即处理</a>';
                        }
                        if ('process' == $action && $WHERE['id'] == $value->id) {
                            $status_but = '<span style=" color: #fb4444; ">正在处理</span>';
                        }
                        echo "<tr>\n";
                        echo "<td>$status_but</td>\n";
                        echo "<td>$user_name</td>\n";
                        echo "<td>$withdraw_price_count</td>\n";
                        echo "<td>$withdraw_details</td>\n";
                        echo "<td><div style=\"max-height:39px;overflow:hidden;\">$withdraw_message</div></td>\n";
                        echo "<td>$value->create_time</td>\n";
                        echo "<td>$value->modified_time</td>\n";

                        echo "</tr>";
                        $ii++;
                        // 构建处理函数
                        if ('process' == $action && $WHERE['id'] == $value->id) {

                            $rewards_img_urls = zib_get_user_rewards_img_urls($value->send_user,'full');
                            $weixin           = $rewards_img_urls['weixin'];
                            $alipay           = $rewards_img_urls['alipay'];

                            $weixin_img = $weixin ? '<span style="display: inline-block; text-align: center;margin-right: 20px; "><img style="max-height: 240px;max-width: 300px;vertical-align: top;" src="' . $weixin . '" alt="微信收款码"><p>微信收款码</p></span>' : '';
                            $alipay_img = $alipay ? '<span style="display: inline-block; text-align: center; "><img style="max-height: 240px;max-width: 300px;vertical-align: top;" src="' . $alipay . '" alt="支付宝收款码"><p>支付宝收款码</p></span>' : '';

                            $html_args   = array();
                            $html_args[] = array(
                                'title' => '提现金额',
                                'con'   => $__withdraw_price . '元',
                            );

                            $html_args[] = array(
                                'title' => '支付金额',
                                'con'   => ($__service_price > 0 ? '<div style="color: #fb4040;font-weight: bold;font-size:1.5em;">' . ($__withdraw_price - $__service_price) . '元</div><div>扣除手续费' . $__service_price . '元</div>' : '<div style="color: #4066fb;font-weight: bold;font-size:1.2em;">' . $__withdraw_price . '元</div>'),
                            );

                            $withdraw_orders      = !empty($meta['withdraw_orders']) ? $meta['withdraw_orders'] : "";
                            $withdraw_order_links = '';

                            if (!empty($withdraw_orders['rebate'])) {
                                $order_link_url = add_query_arg('page', 'zibpay_rebate_page', admin_url('admin.php')); //前缀
                                $ids            = is_array($withdraw_orders['rebate']) ? implode(',', $withdraw_orders['rebate']) : $withdraw_orders['rebate'];
                                $order_db       = $wpdb->get_results("SELECT id,order_num FROM {$wpdb->zibpay_order} WHERE id IN ($ids)");
                                if ($order_db) {
                                    $withdraw_order_links .= '佣金订单：';
                                    foreach ($order_db as $order_v) {
                                        $withdraw_order_links .= '[<a target="_blank" href="' . add_query_arg('s', $order_v->order_num, $order_link_url) . '">' . $order_v->id . '</a>] ';
                                    }
                                }
                            }
                            if (!empty($withdraw_orders['income'])) {
                                $order_link_url = add_query_arg('page', 'zibpay_income_page', admin_url('admin.php')); //前缀
                                $ids            = is_array($withdraw_orders['income']) ? implode(',', $withdraw_orders['income']) : $withdraw_orders['income'];
                                $order_db       = $wpdb->get_results("SELECT id,order_num FROM {$wpdb->zibpay_order} WHERE id IN ($ids)");
                                if ($order_db) {
                                    $withdraw_order_links .= '<br>分成订单：';
                                    foreach ($order_db as $order_v) {
                                        $withdraw_order_links .= '[<a target="_blank" href="' . add_query_arg('s', $order_v->order_num, $order_link_url) . '">' . $order_v->id . '</a>] ';
                                    }
                                }
                            }

                            if ($withdraw_order_links) {
                                $html_args[] = array(
                                    'title' => '提现订单',
                                    'con'   => $withdraw_order_links,
                                );
                            }
                            if ($withdraw_message) {
                                $html_args[] = array(
                                    'title' => '用户留言',
                                    'con'   => $withdraw_message,
                                );
                            }
                            $html_args[] = array(
                                'title' => '收款码',
                                'con'   => $weixin_img . $alipay_img,
                            );
                            $html_args[] = array(
                                'title' => '处理留言',
                                'con'   => '<input style=" width: 95%; max-width: 500px; " name="msg" type="text" value="" placeholder="给用户留言"><p class="description">如需给用户留言请填写此处，如果拒绝提现请填写拒绝原因</p>',
                            );

                            $process = '';
                            $process .= '<p><input type="radio" name="process" id="process_1" value="1" checked="checked"><label for="process_1" style=" color: #036ee2; ">已付款->批准提现</label></p>';
                            $process .= '<p><input type="radio" name="process" id="process_2" value="2"><label for="process_2" style=" color:#eb1b65; ">未付款->拒绝提现</label></p>';
                            $process .= '<p class="description">如批准此申请，请通过收款码付款后，选择已付款并提交。<br>如拒绝此申请，建议给用户留言告知原因，用户可在用户中心重新申请</p>';
                            $process .= '<input name="process_id" type="hidden" value="' . esc_attr($value->id) . '">';
                            $process .= '<input name="action" type="hidden" value="process_submit">';
                            $html_args[] = array(
                                'title' => '',
                                'con'   => $process,
                            );
                            $html_args[] = array(
                                'title' => '',
                                'con'   => '<p><button type="submit" class="button button-primary process-submit">确认提交</button></p>',
                            );
                            $html = '';

                            foreach ($html_args as $html_arg) {
                                $html .= '<tr>';
                                $html .= '<th>' . $html_arg['title'] . '</th>';
                                $html .= '<td>';
                                $html .= $html_arg['con'];
                                $html .= '</td>';
                                $html .= '</tr>';
                            }
                            echo '<form action="' . add_query_arg('page', 'zibpay_withdraw', admin_url('admin.php')) . '" method="post"><table class="form-table"><tbody>' . $html . '</tbody></table></form>';
                        }
                    }
                } else {
                    echo '<tr><td colspan="7" align="center"><strong>暂无提现记录</strong></td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    if (!empty($list[0]) && 'process' == $action) { ?>



    <?php } ?>
    <?php echo zibpay_admin_pagenavi($all_count, $ice_perpage); ?>
</div>