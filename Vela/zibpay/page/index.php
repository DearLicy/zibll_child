<?php
/*
商城数据统计
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_super_admin()) {
    wp_die('您不能访问此页面', '权限不足');
    exit;
}


function zib_this_card()
{

    $data =zibpay_get_admin_dashboard_data();
    $html = '';
    foreach ($data as $v) {
        $html .= '<div class="row-3">
                <div class="box-panel">
                    <span class="count_top">' . $v['top'] . '</span>
                    <div class="count">' . $v['val'] . '</div>
                    <span class="count_bottom">' . $v['bottom'] . '</span>
                </div>
            </div>';
    }
    return $html;
}

function zib_this_charts_data($order_type = 0)
{
    $cycle        = 'day';
    $time_day     = '30';
    $time_end     = current_time('Y-m-d 23:59:59');
    $time_start   = date('Y-m-d 00:00:00', strtotime("-$time_day day", strtotime($time_end)));
    $filling      = zib_this_get_time_filling($cycle, array($time_start, $time_end));
    $cycle_format = '%Y-%m-%d';

    global $wpdb;
    $order_type_where = $order_type ? " and order_type=$order_type" : ' and order_type != 8';
    $db_data          = $wpdb->get_results("SELECT COUNT(*) as count,SUM(pay_price) as price,date_format(create_time, '$cycle_format') as time FROM {$wpdb->zibpay_order} WHERE `status` = 1 AND pay_price > 0 AND pay_time BETWEEN '$time_start' AND '$time_end' $order_type_where group by date_format(create_time,'$cycle_format')");

    $nums   = $filling['data'];
    $total  = $filling['data'];
    $result = $filling['time'];
    array_walk($db_data, function ($value, $key) use ($result, &$nums, &$total) {
        $value         = (array) $value;
        $index         = array_search($value['time'], $result);
        $nums[$index]  = $value['count'];
        $total[$index] = floatval($value['price']);
    });
    $chart_data = [
        'time'  => $result,
        'count' => $nums,
        'price' => $total,
    ];
    return $chart_data;
}

$charts_data     = zib_this_charts_data();
$vip_charts_data = zib_this_charts_data(4);

//获取填充时间
function zib_this_get_time_filling($cycle, $time)
{
    $cycle_format_array = array(
        'day'   => 'Y-m-d',
        'month' => 'Y-m',
        'year'  => 'Y',
    );
    $count_x = array(
        'day'   => 86400,
        'month' => 259200,
        'year'  => 'Y',
    );

    $new_time   = current_time('mysql');
    $time_start = $time[0];
    $time_end   = !empty($time[1]) ? $time[1] : '';

    if (!$time_end) {
        $time_start = $new_time;
        $time_end   = $time[0];
    }

    if (strtotime($time_end) > strtotime($new_time)) {
        $time_end = $new_time;
    }
    //结束时间不高于当前时间

    if (strtotime($time_end) < strtotime($time_start)) {
        throw new Exception('结束时间不能小于开始时间');
    }

    if ('day' == $cycle) {
        $count = ceil((strtotime($time_end) - strtotime($time_start)) / 86400);
    } elseif ('month' == $cycle) {
        $date1_stamp                     = strtotime($time_end);
        $date2_stamp                     = strtotime($time_start);
        list($date_1['y'], $date_1['m']) = explode("-", date('Y-m', $date1_stamp));
        list($date_2['y'], $date_2['m']) = explode("-", date('Y-m', $date2_stamp));
        $count                           = abs($date_1['y'] - $date_2['y']) * 12 + ($date_1['m'] - $date_2['m']) + 1;
    }

    for ($i = $count - 1; 0 <= $i; $i--) {
        $time_end_sum = date($cycle_format_array[$cycle], strtotime($time_end));
        $result[]     = date($cycle_format_array[$cycle], strtotime('-' . $i . ' ' . $cycle, strtotime($time_end_sum)));
        $data[]       = 0;
    }

    $asd = array(
        'time'       => $result,
        'data'       => $data,
        'count'      => $count,
        'cycle'      => $cycle,
        'time_start' => $time_start,
        'time_end'   => $time_end,
    );

    return array(
        'time' => $result,
        'data' => $data,
    );
}

?>

<div class="pay-container">
<?php echo zib_this_card(); ?>
    <div class="row-6">
        <div class="box-panel highcharts">
            <div class="highcharts-title">有效收款单量</div>
            <div style="margin:0 -30px -20px 0px;"><div id="highcharts_count" style="height:300px"></div></div>
        </div>
    </div>
    <div class="row-6">
        <div class="box-panel highcharts">
            <div class="highcharts-title">有效收款金额</div>
            <div style="margin:0 -30px -20px 0px;"><div id="highcharts_price" style="height:300px"></div></div>
        </div>
    </div>
    <script type="text/javascript">
(function ($, document) {
    $(document).ready(function ($) {
        var option_1 = {
            legend: {
                data: ['全部订单',  '购买会员']
            },
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                type: 'category',
                data: <?php echo json_encode($charts_data['time']); ?>
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '全部订单',
                data: <?php echo json_encode($charts_data['count']); ?>,
                type: 'line',
                smooth: true
            }, {
                name: '购买会员',
                data: <?php echo json_encode($vip_charts_data['count']); ?>,
                type: 'line',
                smooth: true
            }]
        };

        var myChart = echarts.init(document.getElementById('highcharts_count'), 'westeros');
        myChart.setOption(option_1);

        var option_2 = {
            legend: {
                data: ['全部订单', '购买会员']
            },
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                type: 'category',
                data: <?php echo json_encode($charts_data['time']); ?>
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '全部订单',
                data: <?php echo json_encode($charts_data['price']); ?>,
                type: 'line',
                smooth: true
            }, {
                name: '购买会员',
                data: <?php echo json_encode($vip_charts_data['price']); ?>,
                type: 'line',
                smooth: true
            }]
        };

        var myChart = echarts.init(document.getElementById('highcharts_price'), 'westeros');
        myChart.setOption(option_2);
    });
})(jQuery, document);
    </script>
</div>