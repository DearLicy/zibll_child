<?php
/*
订单中心
 */
if (!defined('ABSPATH')) {
    exit;
}
$user_Info = wp_get_current_user();
if (!is_user_logged_in()) {
    exit;
}

$order_url = admin_url('admin.php?page=zibpay_order_page');
$desc_url  = $order_url;
$s         = !empty($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : false;

$WHERE = '';

if ($s) {
    if (!empty($_REQUEST['search_user'])) {
        $users_args = array(
            'search'         => '*' . $s . '*',
            'search_columns' => array('user_email', 'user_nicename', 'display_name', 'user_login'),
            'count_total'    => false,
            'number'         => -1,
            'fields'         => 'ids',
        );
        $user_search = new WP_User_Query($users_args);
        $users       = $user_search->get_results();
        $users_in    = implode(',', (array) $users);

        $WHERE = "WHERE
        `user_id` in ($users_in)";
        $desc_url = $order_url . '&s=' . $s . '&search_user=1';
    } else {
        $WHERE = "WHERE
        `pay_num` LIKE '%$s%' OR
        `order_num` LIKE '%$s%' OR
        `other` LIKE '%$s%' OR
        `user_id` LIKE '%$s%' OR
        `post_id` LIKE '%$s%'";
        $desc_url = $order_url . '&amp;s=' . $s;
    }

} else {
}

if (isset($_GET['status'])) {
    $WHERE_status = (int) $_GET['status'];
    $WHERE        = "WHERE
     `status` = $WHERE_status";
    $desc_url = $order_url . '&amp;status=' . $WHERE_status;
}
$WHERE_order_type = !empty($_GET['order_type']) ? $_GET['order_type'] : false;
if ($WHERE_order_type) {
    $WHERE = "WHERE
     `order_type` = $WHERE_order_type AND `status` = 1";
    $desc_url = $order_url . '&amp;order_type=' . $WHERE_order_type;
}

if (!empty($_GET['delete'])) {
    $delete_num = $_GET['delete'];
    $result     = ZibPay::delete_order($delete_num);
    if ($result) {
        echo '<div class="updated notice-alt"><h4 style="color: #0aaf19;">删除成功[订单号：' . $delete_num . ']</h4></div>';
    }
}
if (!empty($_GET['action']) && $_GET['action'] == 'clear_order') {
    $result = ZibPay::clear_order(14);
    if ($result) {
        echo '<div class="updated notice-alt"><h4 style="color: #0aaf19;">清理完成[共清理：' . $result . '个订单]</h4><p>备注：系统每个月会自动清理一次未支付订单</p></div>';
    } else {
        echo '<div class="updated notice-alt"><h4 style="color: #0aaf19;">没有需要清理的订单！</h4><p>备注：系统每个月会自动清理一次未支付订单</p></div>';
    }
}

if (isset($_GET['post_author'])) {
    $post_author = (int) $_GET['post_author'];
    $WHERE       = $WHERE ?: 'WHERE 1=1';
    $WHERE .= " and `post_author` = $post_author";
}

if (isset($_GET['referrer_id'])) {
    $post_author = (int) $_GET['referrer_id'];
    $WHERE       = $WHERE ?: 'WHERE 1=1';
    $WHERE .= " and `referrer_id` = $referrer_id";
}

if (isset($_GET['user_id'])) {
    $user_id = (int) $_GET['user_id'];
    $WHERE   = $WHERE ?: 'WHERE 1=1';
    $WHERE .= " and `user_id` = $user_id";
}

if (isset($_GET['post_id'])) {
    $post_id = (int) $_GET['post_id'];
    $WHERE   = $WHERE ?: 'WHERE 1=1';
    $WHERE .= " and `post_id` = $post_id";
}

if (isset($_GET['pay_type'])) {
    $pay_type = $_GET['pay_type'];
    $WHERE    = $WHERE ?: 'WHERE 1=1';
    $WHERE .= " and `pay_type` = '$pay_type'";
    $desc_url = $order_url . '&amp;pay_type=' . $pay_type;
}

if (isset($_GET['order_num'])) {
    $order_num = $_GET['order_num'];
    $WHERE     = $WHERE ?: 'WHERE 1=1';
    $WHERE .= " and `order_num` = '$order_num'";
    $desc_url = $order_url . '&amp;order_num=' . $order_num;
}

global $wpdb;
//统计数据
$total_trade = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->zibpay_order $WHERE");

//分页计算
$ice_perpage = 20;
$pages       = ceil($total_trade / $ice_perpage);
$page        = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$offset      = $ice_perpage * ($page - 1);
$order       = !empty($_GET['orderby']) ? $_GET['orderby'] : 'pay_time';
$desc        = !empty($_GET['desc']) ? $_GET['desc'] : 'DESC';

$list = $wpdb->get_results("SELECT * FROM $wpdb->zibpay_order $WHERE order by $order $desc limit $offset,$ice_perpage");

//echo  json_encode($list);
//echo "SELECT * FROM $wpdb->zibpay_order $WHERE order by $order $desc limit $offset,$ice_perpage";

$no_c    = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->zibpay_order WHERE `status` = 0");
$paid_c  = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->zibpay_order WHERE `status` = 1");
$all_4_c = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->zibpay_order WHERE `order_type` = 4 AND `status` = 1");
$all_8_c = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->zibpay_order WHERE `order_type` = 8 AND `status` = 1");
$all_p_c = $wpdb->get_var("SELECT COUNT(id) FROM $wpdb->zibpay_order WHERE `pay_type` = 'points' AND `status` = 1");

?>
<div class="wrap">
     <h2>全部订单</h2>

     <div class="order-header">
        <?php echo $s ? '<div class="">"' . esc_attr($s) . '" 的搜索结果</div>' : ''; ?>
        <div class="flex">
            <ul class="subsubsub">
                <li class=""><a class="" href="<?php echo $order_url . '&amp;status=1'; ?>">已支付</a>(<?php echo $paid_c ?>)</li> |
                <li class=""><a title="系统每个月会自动清理一次未支付订单" href="<?php echo $order_url . '&amp;status=0'; ?>">未支付</a>(<?php echo $no_c ?>)</li> |
                <li class=""><a class="" href="<?php echo $order_url . '&amp;order_type=4'; ?>">购买会员</a>(<?php echo $all_4_c ?>)</li> |
                <li class=""><a class="" href="<?php echo $order_url . '&amp;order_type=8'; ?>">余额充值</a>(<?php echo $all_8_c ?>)</li> |
                <li class=""><a class="" href="<?php echo $order_url . '&amp;pay_type=points'; ?>">积分订单</a>(<?php echo $all_p_c ?>)</li>
            </ul>
          </div>
          <div class="flex jsb">
            <div class="">
                <a class="button" onclick="return confirm('清理订单会删除2周前所有未支付的订单，不可恢复！确认清理订单？')" href="<?php echo $order_url . '&amp;action=clear_order'; ?>">清理订单</a>
            </div>

            <form class="form-inline form-order" method="post" action="<?php echo $order_url; ?>">
                <div class="form-group">
                        <label class="button"><input name="search_user" type="checkbox" value="on">按用户搜索</label>
                        <input type="text" class="form-control" name="s" placeholder="搜索订单" value="<?php echo $s; ?>">
                        <button type="submit" class="button button-primary">提交</button>
                </div>
            </form>
          </div>
     </div>

     <div class="table-box">
          <table class="widefat fixed striped posts">
               <thead>
                    <tr>
                         <?php
$theads   = array();
$theads[] = array('width' => '5%', 'orderby' => 'order_num', 'name' => '订单号');
$theads[] = array('width' => '5%', 'orderby' => 'post_id', 'name' => '商品');
$theads[] = array('width' => '3%', 'orderby' => 'user_id', 'name' => '用户');
$theads[] = array('width' => '3%', 'orderby' => 'order_price', 'name' => '订单价格');
$theads[] = array('width' => '4%', 'orderby' => 'create_time', 'name' => '时间');
$theads[] = array('width' => '5%', 'orderby' => 'pay_num', 'name' => '支付单号');
$theads[] = array('width' => '3%', 'orderby' => 'status', 'name' => '订单状态');
$theads[] = array('width' => '3%', 'orderby' => 'pay_price', 'name' => '支付金额');
$theads[] = array('width' => '3%', 'orderby' => 'rebate_price', 'name' => '推广信息');
$theads[] = array('width' => '3%', 'orderby' => 'income_price', 'name' => '分成信息');

foreach ($theads as $thead) {
    $orderby = '';
    if ($thead['orderby']) {
        $orderby_url = add_query_arg('orderby', $thead['orderby'], $desc_url);
        $orderby .= '<a title="降序" href="' . add_query_arg('desc', 'ASC', $orderby_url) . '"><span class="dashicons dashicons-arrow-up"></span></a>';
        $orderby .= '<a title="升序" href="' . add_query_arg('desc', 'DESC', $orderby_url) . '"><span class="dashicons dashicons-arrow-down"></span></a>';
        $orderby = '<span class="orderby-but">' . $orderby . '</span>';
    }
    echo '<th class="" width="' . $thead['width'] . '">' . $thead['name'] . $orderby . '</th>';
}?>
                    </tr>
               </thead>
               <tbody>
                    <?php
if ($list) {
    $ii = 1;
    foreach ($list as $value) {

        $edit   = '<a class="" onclick="return confirm(\'确认删除此订单?  删除后数据不可恢复!\')" href="' . $order_url . '&amp;delete=' . $value->order_num . '">删除</a>';
        $status = $value->status ? '<span style="color: #3d7ffd;">已支付</span>' : '<span style="color: #e8720a;">未支付</span>';

        $order_type = zibpay_get_pay_type_name($value->order_type);

        $order_type_link = '<a style="color: ' . ['#ff4747', '#ee5307', '#1e8608', '#1a8a65', '#0c9cc8', '#086ae8', '#3353fd', '#4641e8', '#853bf2', '#e94df7', '#ca2b7d', '#d7354c', '#ff4747', '#8e24ac'][(int) $value->order_type] . ';" href="' . $order_url . '&order_type=' . $value->order_type . '">' . $order_type . '</a>';
        $user_a          = '未登录用户';
        if ($value->user_id) {
            $user_a = '<a target="_blank" style="color: #6e6a6f;" href="' . zib_get_user_home_url($value->user_id) . '">[查看] </a><a href="' . $order_url . '&user_id=' . $value->user_id . '">' . get_the_author_meta('display_name', $value->user_id) . '</a>';

            $pay_down_log = zib_get_user_meta($value->user_id, 'pay_down_log', true);
            $user_a .= $pay_down_log ? '<br>' . zibpay_get_paydown_log_admin_link('user', $value->user_id, "px12", "[资源下载记录]") : '';
        }

        $post_a = $order_type_link;
        if ($value->post_id) {
            $the_title = get_the_title($value->post_id);
            $post_a .= '<div title="' . $the_title . '"  style="overflow: hidden; text-overflow:ellipsis; white-space: nowrap; display: block;" ><a style="color: #6e6a6f;" target="_blank" href="' . get_permalink($value->post_id) . '">[查看] </a><a href="' . $order_url . '&post_id=' . $value->post_id . '">' . $the_title . '</a></div>';

            $post_a .= $value->order_type == 2 ? zibpay_get_paydown_log_admin_link('post', $value->post_id, "px12", "[查看资源下载记录]") : '';
        }

        $post_a .= $value->product_id ? '<div>' . $value->product_id . '</div>' : '';
        $pay_detail = $value->status ? zibpay_get_order_pay_detail_lists($value, '<br>') : 0;
        $time       = $value->status ? '支付时间:<br>' . $value->pay_time : '创建时间:<br>' . $value->create_time;

        $rebate_info = '无';
        if ($value->status && $value->rebate_price > 0 && $value->referrer_id) {
            $rebate_info   = '<a href="' . admin_url('admin.php?page=zibpay_rebate_page&referrer_id=' . $value->referrer_id) . '">' . get_the_author_meta('display_name', $value->referrer_id) . '：' . $value->rebate_price . '</a>';
            $rebate_status = '<span style="color: #3d7ffd;">未提现</span>';
            if ($value->rebate_status == 1) {
                $rebate_status = '<span style="color: #f93b3b;">已提现</span>';
            }

            if ($value->rebate_status == 3) {
                $rebate_status = '<span style="color: #e8720a;">提现待处理</span>';
            }

            $rebate_info .= '<br>' . $rebate_status;
        }

        $income_info = '无';
        if ($value->status && $value->income_price > 0 && $value->post_author) {
            $income_info   = '<a href="' . admin_url('admin.php?page=zibpay_income_page&post_author=' . $value->post_author) . '">' . get_the_author_meta('display_name', $value->post_author) . '：' . $value->income_price . '</a>';
            $income_status = '<span style="color: #3d7ffd;">未提现</span>';
            if ($value->income_status == 1) {
                $income_status = '<span style="color: #f93b3b;">已提现</span>';
            }

            if ($value->income_status == 3) {
                $income_status = '<span style="color: #e8720a;">提现待处理</span>';
            }

            $income_info .= '<br>' . $income_status;
        }

        $order_price = '￥' . $value->order_price;
        if ($value->pay_type === 'points') {
            $order_price  = zibpay_get_order_pay_points((array) $value) . '积分';
            $income_price = zibpay_get_order_income_points($value);
            $user_name    = get_the_author_meta('display_name', $value->post_author);

            if ($income_price) {
                $income_info = '<a href="' . admin_url('users.php?s=' . $user_name) . '">' . $user_name . '</a><br>' . $income_price . '积分';
            }
        }

        echo "<tr>\n";
        echo "<td>$value->order_num<div class='row-actions'>$edit</div></td>\n";
        echo "<td>$post_a</td>\n";
        echo "<td>$user_a<br>$value->ip_address</td>\n";
        echo "<td>$order_price</td>\n";
        echo "<td>$time</td>\n";
        echo "<td>$value->pay_num</td>\n";
        echo "<td>$status<br>$value->pay_type</td>\n";
        echo "<td>$pay_detail</td>\n";
        echo "<td>$rebate_info</td>\n";
        echo "<td>$income_info</td>\n";
        echo "</tr>";
        $ii++;
    }
} else {
    echo '<tr><td colspan="10" align="center"><strong>暂无订单</strong></td></tr>';
}
?>
               </tbody>
          </table>
     </div>
     <?php zibpay_admin_pagenavi($total_trade, $ice_perpage);?>

</div>