<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-18 22:18:07
 * @LastEditTime : 2025-08-27 21:13:11
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

$time = current_time('m/d/Y');

$mian_chart = array(
    'order_type' => [],
    'minitable'  => array(),
    'timefilter' => array(date('m/d/Y', strtotime('-60 day', strtotime($time))), $time),
    'chart'      => array(
        'tooltip'  => array(
            'trigger'      => 'axis',
            'extraCssText' => 'z-index: 1900;',
            'axisPointer'  => array(
                'type' => 'shadow',
                'z'    => 1900,
            ),
        ),
        'grid'     => array(
            'left'         => '5px',
            'right'        => '5px',
            'bottom'       => '55px',
            'containLabel' => true,
        ),
        'toolbox'  => array(),
        'dataZoom' => array(
            array(
                'type' => 'inside',
            ),
            array(
                'start' => 0,
                'end'   => 20,
            ),
        ),
        'xAxis'    => array(
            'type' => 'category',
            'data' => '',
        ),
        'legend'   => array(
            'show'     => true,
            'data'     => ['销售额', '订单量'],
            'selected' => [
                '销售额' => true,
                '订单量' => false,
            ],
        ),
        'yAxis'    => array(
            array(
                'type' => 'value',
                'name' => '',
            ),
            array(
                'type' => 'value',
                'name' => '',
            ),
        ),
        'series'   => array(
            array(
                'name'       => '销售额',
                'data'       => '',
                'smooth'     => true,
                'showSymbol' => false,
                'type'       => 'line',
                'areaStyle'  => array(
                    'opacity' => .3,
                ),
                'lineStyle'  => array(
                    'width' => 1.5,
                    'color' => 'rgb(53, 216, 94)',
                ),
                'itemStyle'  => array(
                    'emphasis' => array(
                        'color'       => 'rgb(53, 216, 94)',
                        'borderColor' => 'rgba(53, 216, 94,0.2)',
                        'borderWidth' => 8,
                    ),
                    'color'    => array(
                        'type'       => 'linear',
                        'x'          => 0,
                        'y'          => 0,
                        'x2'         => 0,
                        'y2'         => 1,
                        'colorStops' => array(
                            array(
                                'offset' => 0,
                                'color'  => 'rgb(109, 251, 145)',
                            ),
                            array(
                                'offset' => .8,
                                'color'  => 'rgba(59, 232, 180, .1)',
                            ),
                        ),
                        'global'     => false,
                    ),
                ),
            ),
            array(
                'show'        => false,
                'name'        => '订单量',
                'areaStyle'   => array(
                    'opacity' => .1,
                ),
                'showSymbol'  => false,
                'yAxisIndex'  => 1,
                'type'        => 'bar',
                'smooth'      => true,
                'data'        => '',
                'barMaxWidth' => 20,
                'itemStyle'   => array(
                    'borderRadius' => [100, 100, 0, 0],
                    'color'        => array(
                        'type'       => 'linear',
                        'x'          => 0,
                        'y'          => 0,
                        'x2'         => 0,
                        'y2'         => 1,
                        'colorStops' => array(
                            array(
                                'offset' => 0,
                                'color'  => 'rgba(140, 214, 243, 0.8)',
                            ),
                            array(
                                'offset' => 1,
                                'color'  => 'rgb(99, 182, 241)',
                            ),
                        ),
                        'global'     => false,
                    ),
                ),
            ),
        ),
    ),
);

$mini_chart_data = array(
    'tooltip' => array(
        'trigger'      => 'axis',
        'extraCssText' => 'z-index: 1900;',
        'axisPointer'  => array(
            'type' => 'shadow',
            'z'    => 1900,
        ),
    ),
    'grid'    => array(
        'top'    => '6px',
        'left'   => '6px',
        'right'  => '6px',
        'bottom' => '6px',
    ),
    'xAxis'   => array(
        'show'        => false,
        'type'        => 'category',
        'boundaryGap' => false,
        'data'        => '',
    ),
    'yAxis'   => array(
        'show' => false,
        'type' => 'value',
        'name' => '销售额',
    ),
    'series'  => array(
        'data'       => '',
        'smooth'     => true,
        'showSymbol' => false,
        'type'       => 'line',
        'areaStyle'  => array(
            'opacity' => .8,
        ),
        'lineStyle'  => array(
            'width' => 1.5,
            'color' => 'rgb(61, 190, 255)',
        ),
        'itemStyle'  => array(
            'emphasis' => array(
                'color'       => 'rgb(61, 190, 255)',
                'borderColor' => 'rgba(42, 138, 255,0.2)',
                'borderWidth' => 8,
            ),
            'color'    => array(
                'type'       => 'linear',
                'x'          => 0,
                'y'          => 0,
                'x2'         => 0,
                'y2'         => 1,
                'colorStops' => array(
                    array(
                        'offset' => 0,
                        'color'  => 'rgba(61, 190, 255, 0.3)',
                    ),
                    array(
                        'offset' => .9,
                        'color'  => 'rgba(61, 190, 255, 0)',
                    ),
                ),
                'global'     => false,
            ),
        ),
    ),
);

$vue_data = array(
    'dashboard'    => array(
        'time_options'     => array(
            'last_30_day' => '近30天',
            'thismonth'   => '本月',
            'thisyear'    => '今年',
            'all'         => '全部',
        ),
        'todo_data'        => [
            'shipping_count'   => 0,
            'after_sale_count' => 0,
            'withdraw_count'   => 0,
        ],
        'mini_card_data'   => array(),
        'mini_chart_data'  => array(
            'today_sales' => array(
                'title' => '今日',
                'unit'  => '元',
                'data'  => '0',
                'chart' => $mini_chart_data,
            ),
            'month_sales' => array(
                'title' => '本月',
                'unit'  => '元',
                'data'  => '0',
                'chart' => $mini_chart_data,
            ),
        ),
        'order_chart_data' => array(
            'show_tab' => 'day',
            'month'    => array_merge(
                $mian_chart,
                array(
                    'timefilter' => array(date('m/d/Y', strtotime('-12 month', strtotime($time))), $time),
                )
            ),
            'day'      => $mian_chart,
        ),
        'hot_product_data' => array(
            'time'               => 'all',
            'order_type'         => [],
            'type'               => 'price',
            'max'                => array(
                'count' => 0,
                'price' => 0,
            ),
            'colors'             => array(
                array('color' => '#bac1a4', 'percentage' => 25),
                array('color' => '#cac51c', 'percentage' => 50),
                array('color' => '#f1ad46', 'percentage' => 75),
                array('color' => '#ff775a', 'percentage' => 95),
                array('color' => '#ff4b7f', 'percentage' => 100),
            ),
            'order_type_options' => array(
                1  => '付费阅读', //文章，帖子
                2  => '付费下载', //文章
                5  => '付费图片', //文章
                6  => '付费视频', //文章
                10 => '商城商品', //商城，商品
            ),
            'data'               => array(),
        ),
        'type_pie_data'    => array(
            'time'  => 'all',
            'type'  => 'price',
            'chart' => array(
                'tooltip' => array(
                    'trigger'   => 'item',
                    'formatter' => '{b}<br/>{c} ({d}%)',
                ),
                'legend'  => array(
                    'top'  => '0',
                    'left' => 'center',
                ),
                'series'  => array(
                    'top'               => '60px',
                    'left'              => '50px',
                    'right'             => '50px',
                    'bottom'            => '0',
                    'type'              => 'pie',
                    'radius'            => array('40%', '70%'),
                    'avoidLabelOverlap' => false,
                    'itemStyle'         => array(
                        'borderRadius' => 10,
                        'borderColor'  => '#fff',
                        'borderWidth'  => 2,
                    ),
                    'label'             => array(
                        'show'      => true,
                        'formatter' => "{b}\n{d}%",
                        'position'  => 'outside',
                    ),
                    'emphasis'          => array(
                        'label' => array(
                            'show'     => true,
                            'fontSize' => 15,
                        ),
                    ),
                    'labelLine'         => array(
                        'show' => 1,
                    ),
                    'data'              => array(),
                ),
            ),
        ),
        'asset_data'       => array(
            'time'   => 'all',
            'colors' => ['c-red', 'c-yellow', 'c-purple', 'c-blue', 'c-green'],
            'data'   => array(),
        ),
    ),
);

zibpay_admin_page_vue_data_filter($vue_data);

?>
<el-row :gutter="10" class="dashboard-cards" v-loading="loading.statistics">
    <el-col :xs="12" :sm="12" :md="12" :lg="5" v-for="(item,index) in dashboard.mini_chart_data">
        <div class="card-box min-chart-box mb10">
            <div class="flex jsb mb6">
                <div class="key opacity5">{{item.title}}</div>
                <el-tooltip placement="top" content="不含积分订单、余额充值订单的数据">
                    <div class="flex ab xx">
                        <div class="em2x"><span class="unit px12">￥</span>{{ item.data }}</div>
                        <div class="mt6"><span class="opacity8 px12">同比</span><span :class="item.ratio > 0 ? 'c-blue' : 'c-red'"><i class="dashicons" :class="item.ratio > 0 ? ' dashicons-arrow-up' : ' dashicons-arrow-down'"></i>{{item.ratio}}%</span></div>
                    </div>
                </el-tooltip>
            </div>
            <v-chart autoresize :option="item.chart" :style="{height:win.width > 767 ? '95px' : '50px'}" />
        </div>
    </el-col>
    <el-col :xs="24" :sm="16" :md="16" :lg="10">
        <el-row type="flex" class="mini-card-box" :gutter="10">
            <el-col :xs="12" :sm="12" v-for="(card_item,index) in dashboard.mini_card_data" :key="index + 'mini_card_data_box'">
                <el-row class="card-box mini-card-box mb10" type="flex">
                    <el-col :span="12" v-for="(item,index) in card_item" :key="index + 'mini_card_data'">
                        <el-tooltip placement="top" content="不含积分、余额付款的数据" v-if="item.title==='总收款'">
                            <div>
                                <div class="val"><span class="unit px12" v-if="item.unit">{{item.unit}}</span>{{ priceCut(item.data) }}</div>
                                <div class="opacity5 mt6">{{ item.title }}</div>
                            </div>
                        </el-tooltip>
                        <div v-else>
                            <div class="val"><span class="unit px12" v-if="item.unit">{{item.unit}}</span>{{ priceCut(item.data) }}</div>
                            <div class="opacity5 mt6">{{ item.title }}</div>
                        </div>
                    </el-col>
                </el-row>
            </el-col>
        </el-row>
    </el-col>
    <el-col :xs="24" :sm="8" :md="8" :lg="4">
        <div class="card-box todo-box">
            <div class="opacity8 mb10 font-bold">待办事项</div>
            <div class="">
                <a class="flex jsb ac but mt6" href="javascript:;" @click="go('shipping',{'shipping_status':0})">
                    <div class="">商品待发货</div>
                    <div class="flex ac"><span class="c-red" v-if="dashboard.todo_data.shipping_count > 0">{{dashboard.todo_data.shipping_count}}件</span><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </a>
                <a class="flex jsb ac but mt6" href="javascript:;" @click="go('after-sale',{'after_sale_status':'1,2'})">
                    <div class="">售后待处理</div>
                    <div class="flex ac"><span class="c-red" v-if="dashboard.todo_data.after_sale_count > 0">{{dashboard.todo_data.after_sale_count}}件</span><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </a>
                <a class="flex jsb ac but mt6" :href="config.admin_url + 'admin.php?page=zibpay_withdraw&status=0'">
                    <div class="">提现待处理</div>
                    <div class="flex ac"><span class="c-red" v-if="dashboard.todo_data.withdraw_count > 0">{{dashboard.todo_data.withdraw_count}}件</span><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </a>
            </div>
        </div>
    </el-col>
    <el-col :xs="24" :sm="24" :md="24" :lg="24">
        <div class="card-box order-chart-box">
            <div class="card-header">
                <div class="box-title">销售统计</div>
            </div>
            <div class="header flex jsb hh">
                <div class="flex-left shrink0 mb6">
                    <el-button-group>
                        <el-radio-group v-model="dashboard.order_chart_data.show_tab">
                            <el-radio-button label="day">按天</el-radio-button>
                            <el-radio-button label="month">按月</el-radio-button>
                        </el-radio-group>
                    </el-button-group>
                    <el-segmented v-model="dashboard.order_chart_data.show_tab" :options="dashboard.order_chart_data.show_tab_options" size="large" />
                </div>
                <div class="flex-right mb10">
                    <transition name="slide-right" mode="out-in" tag="div">
                        <div v-if="dashboard.order_chart_data.show_tab == 'day'" key="order_chart_data_picker_day">
                            <el-tooltip placement="top" :content="isExist(dashboard.order_chart_data.day.order_type) ? '不含积分订单' : '默认不含积分订单，不含余额充值订单'">
                                <el-select @change="getOrderChartData('day')" v-model="dashboard.order_chart_data.day.order_type" class="mr6 mb6" clearable multiple collapse-tags placeholder="商品类型">
                                    <el-option v-for="(item,index) in order_type_name" :key="item" :label="item" :value="index">
                                        <span class="em09">{{ item }}</span>
                                        <span class="px12 float-right opacity5 ml10">{{ index }}</span>
                                    </el-option>
                                </el-select>
                            </el-tooltip>
                            <el-date-picker @change="getOrderChartData('day')" class="mb6" v-model="dashboard.order_chart_data.day.timefilter" format="YYYY-MM-DD" :shortcuts="dateShortcutsDate()" type="daterange" range-separator="-" start-placeholder="开始日期" end-placeholder="结束日期" unlink-panels>
                            </el-date-picker>
                        </div>
                        <div v-else-if="dashboard.order_chart_data.show_tab == 'month'" key="order_chart_data_picker_month">
                            <el-tooltip placement="top" :content="isExist(dashboard.order_chart_data.month.order_type) ? '不含积分订单' : '默认不含积分订单，不含余额充值订单'">
                                <el-select @change="getOrderChartData('month')" v-model="dashboard.order_chart_data.month.order_type" class="mr6 mb6" clearable multiple collapse-tags placeholder="商品类型">
                                    <el-option v-for="(item,index) in order_type_name" :key="item" :label="item" :value="index">
                                        <span class="em09">{{ item }}</span>
                                        <span class="px12 float-right opacity5 ml10">{{ index }}</span>
                                    </el-option>
                                </el-select>
                            </el-tooltip>

                            <el-date-picker @change="getOrderChartData('month')" class="mb6" v-model="dashboard.order_chart_data.month.timefilter" format="YYYY-MM" :shortcuts="dateShortcutsDate().slice(2)" type="monthrange" range-separator="-" start-placeholder="开始日期" end-placeholder="结束日期" unlink-panels>
                            </el-date-picker>
                        </div>
                    </transition>
                </div>
            </div>
            <transition name="slide-right" mode="out-in" tag="div">
                <el-row type="flex" class="" :gutter="20" v-if="dashboard.order_chart_data.show_tab == 'day'" key="order_chart_data_day" v-loading="loading.order_chart">
                    <el-col :xs="24" :md="24" :lg="17" :xl="17">
                        <div class="data-card">
                            <v-chart autoresize :option="dashboard.order_chart_data.day.chart" style="height: 397px" />
                        </div>
                    </el-col>
                    <el-col :xs="24" :md="24" :lg="7" :xl="7">
                        <div class="data-card">
                            <el-table show-summary :data="dashboard.order_chart_data.day.minitable" :default-sort="{prop: 'price', order: 'descending'}" style="width: 100%" height="390">
                                <el-table-column prop="time" label="日期" sortable min-width="120">
                                </el-table-column>
                                <el-table-column prop="nums" label="订单量" min-width="90" sortable>
                                </el-table-column>
                                <el-table-column prop="price" label="销售额" min-width="100" sortable>
                                    <template #default="{row}">
                                        <span class="unit px12">{{marks.pay}}</span>{{ priceCut(row.price) }}
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </el-col>
                </el-row>
                <el-row type="flex" class="" :gutter="20" v-else-if="dashboard.order_chart_data.show_tab == 'month'" key="order_chart_data_month" v-loading="loading.order_chart">
                    <el-col :xs="24" :md="24" :lg="17" :xl="17">
                        <div class="data-card">
                            <v-chart autoresize :option="dashboard.order_chart_data.month.chart" style="height: 397px" />
                        </div>
                    </el-col>
                    <el-col :xs="24" :md="24" :lg="7" :xl="7">
                        <div class="data-card">
                            <el-table show-summary :data="dashboard.order_chart_data.month.minitable" :default-sort="{prop: 'price', order: 'descending'}" style="width: 100%" height="390">
                                <el-table-column prop="time" label="日期" sortable>
                                </el-table-column>
                                <el-table-column prop="nums" label="订单量" width="80" sortable>
                                </el-table-column>
                                <el-table-column prop="price" label="销售额" width="100" sortable>
                                    <template #default="{row}">
                                        <span class="unit px12">{{marks.pay}}</span>{{ priceCut(row.price) }}
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </el-col>
                </el-row>
            </transition>
        </div>
    </el-col>
    <el-col :xs="24" :sm="12" :md="8" :lg="10">
        <div class="card-box">
            <div class="card-header flex jsb" style="margin-bottom: 6px;">
                <div class="box-title">热门商品</div>
                <div class="flex ac">
                    <el-radio-group v-model="dashboard.hot_product_data.type" size="small" @change="sortHotData()" class="mr10">
                        <el-radio-button label="price">销售额</el-radio-button>
                        <el-radio-button label="count">销量</el-radio-button>
                    </el-radio-group>
                    <el-popover placement="bottom-end" :width="250" trigger="hover">
                        <template #reference>
                            <div class="flex jc em16 opacity8" v-html="svg.table_option"></div>
                        </template>
                        <div v-loading="loading.hot_product_data">
                            <el-radio-group v-model="dashboard.hot_product_data.time" size="small" class="mb10">
                                <el-radio-button v-for="(item,value) in dashboard.time_options" :label="value" :value="value">{{item}}</el-radio-button>
                            </el-radio-group>
                            <el-checkbox-group v-model="dashboard.hot_product_data.order_type">
                                <el-checkbox v-for="(item,index) in dashboard.hot_product_data.order_type_options" :label="index" :value="index">{{item}}</el-checkbox>
                            </el-checkbox-group>
                            <div class="mt10">
                                <el-button type="primary" @click="getHotData()">筛选</el-button>
                            </div>
                        </div>
                    </el-popover>
                </div>
            </div>
            <div class="card-body" v-loading="loading.hot_product_data">
                <el-scrollbar class="asset-item-box" height="332px">
                    <div class="flex ac jsb padding-h6" v-for="(item,index) in dashboard.hot_product_data.data" :key="item.post_id + 'hot_product_data'">
                        <div class="flex0 mr10">
                            <div class="badg" :class="index < 5 ? dashboard.asset_data.colors[index] : ''" style="width: 17px;"><b class="em12">{{index + 1}}</b></div>
                        </div>
                        <div class="overflow-hidden flex1">
                            <div class="flex ac jsb">
                                <el-tooltip placement="top" effect="light">
                                    <div class="text-ellipsis mb6 opacity8">{{item.post_title}}</div>
                                    <template #content>
                                        <div class="tooltip-link-box">
                                            <a @click="go('order',{post_id:item.post_id})" href="javascript:void(0)">筛选此商品</a>
                                            <a target="_blank" v-if="item.product_url" :href="item.product_url">查看商品</a>
                                            <a target="_blank" v-if="item.product_edit_url" :href="item.product_edit_url">编辑商品</a>
                                        </div>
                                    </template>
                                </el-tooltip>
                                <div class="flex0 ml20">{{dashboard.hot_product_data.type == 'price' ? marks.pay + priceCut(item.price) : item.count + '件'}}</div>
                            </div>
                            <el-progress :text-inside="true" :percentage="~~(Math.max(15, dashboard.hot_product_data.type == 'price' ? item.price / dashboard.hot_product_data.max.price * 100 : item.count / dashboard.hot_product_data.max.count * 100))" :color="dashboard.hot_product_data.colors">{{''}}</el-progress>
                        </div>
                    </div>

                </el-scrollbar>
            </div>
        </div>
    </el-col>
    <el-col :xs="24" :sm="24" :md="16" :lg="14" :style="win.width < 992 ? 'order: 1;' : ''">
        <div class="card-box">
            <div class="card-header flex ac jsb" style="margin-bottom:0;">
                <div class="box-title">用户收入榜</div>
                <div class="flex jc">
                    <el-radio-group v-model="dashboard.asset_data.time" size="small" @change="getAssetData()">
                        <el-radio-button :disabled="loading.asset_data" v-for="(item,value) in dashboard.time_options" :label="value" :value="value">{{item}}</el-radio-button>
                    </el-radio-group>
                </div>
            </div>
            <div class="card-body" v-loading="loading.asset_data">
                <el-row :gutter="20" class="dashboard-cards">
                    <el-col :xs="24" :sm="12" :md="12" :lg="12">
                        <div class="asset-content-box">
                            <div class="opacity8 mb10 font-bold mt20">佣金排行</div>
                            <el-scrollbar class="asset-item-box" height="289px">
                                <div class="flex ac jsb padding-h6" v-for="(item,index) in dashboard.asset_data.rebate_data" :key="item.user_id + 'asset_data_rebate_data'">
                                    <div class="flex0 mr10">
                                        <div class="badg" :class="index < 5 ? dashboard.asset_data.colors[index] : ''" style="width: 17px;"><b class="em12">{{index + 1}}</b></div>
                                    </div>
                                    <div class="flex ac overflow-hidden flex1">
                                        <el-tooltip placement="top" effect="light" v-if="config.rebate_s">
                                            <div class="flex0 mr6"><el-avatar :size="30" :src="item.user_avatar"></el-avatar></div>
                                            <template #content>
                                                <div class="tooltip-link-box">
                                                    <a :href="config.admin_url + 'admin.php?page=zibpay_rebate_page&referrer_id='+item.user_id" target="_blank">佣金明细</a>
                                                </div>
                                            </template>
                                        </el-tooltip>
                                        <div class="flex0 mr6" v-else><el-avatar :size="30" :src="item.user_avatar"></el-avatar></div>
                                        <div class="flex1 overflow-hidden">
                                            <div class="text-ellipsis">{{ item.user_name }}</div>
                                        </div>
                                    </div>
                                    <div class="flex0 ml20 mr6">
                                        <div class="badg" :class="index < 5 ? dashboard.asset_data.colors[index] : ''"><span class="unit px12">{{marks.pay}}</span>{{ priceCut(item.price) }}</div>
                                    </div>
                                </div>
                            </el-scrollbar>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="12">
                        <div class="asset-content-box">
                            <div class="opacity8 mb10 font-bold mt20">分成排行</div>
                            <el-scrollbar class="asset-item-box" height="289px">
                                <div class="flex ac jsb padding-h6" v-for="(item,index) in dashboard.asset_data.income_data" :key="item.user_id + 'asset_data_income_data'">
                                    <div class="flex0 mr10">
                                        <div class="badg" :class="index < 5 ? dashboard.asset_data.colors[index] : ''" style="width: 17px;"><b class="em12">{{index + 1}}</b></div>
                                    </div>
                                    <div class="flex ac overflow-hidden flex1">
                                        <el-tooltip placement="top" effect="light" v-if="config.income_s">
                                            <div class="flex0 mr6"> <el-avatar :size="30" :src="item.user_avatar"></el-avatar> </div>
                                            <template #content>
                                                <div class="tooltip-link-box">
                                                    <a :href="config.admin_url + 'admin.php?page=zibpay_income_page&post_author='+item.user_id" target="_blank">分成明细</a>
                                                </div>
                                            </template>
                                        </el-tooltip>
                                        <div class="flex0 mr6" v-else><el-avatar :size="30" :src="item.user_avatar"></el-avatar></div>
                                        <div class="flex1 overflow-hidden">
                                            <div class="text-ellipsis">{{ item.user_name }}</div>
                                        </div>
                                    </div>
                                    <div class="flex0 ml20 mr6">
                                        <div class="badg" :class="index < 5 ? dashboard.asset_data.colors[index] : ''"><span class="unit px12">{{marks.pay}}</span>{{ priceCut(item.price) }}</div>
                                    </div>
                                </div>
                            </el-scrollbar>
                        </div>
                    </el-col>
                </el-row>
            </div>
        </div>
    </el-col>
    <el-col :xs="24" :sm="12" :md="8" :lg="10">
        <div class="card-box">
            <div class="card-header flex jsb">
                <div class="box-title">订单分布</div>
                <el-popover placement="bottom-end" :width="240" trigger="hover">
                    <template #reference>
                        <div class="flex jc em16 opacity8" v-html="svg.table_option"></div>
                    </template>
                    <div class="flex jc xx" v-loading="loading.type_pie">
                        <el-radio-group v-model="dashboard.type_pie_data.type" class="mb10" @change="getTypePieData()">
                            <el-radio-button label="price">销售额</el-radio-button>
                            <el-radio-button label="count">销量</el-radio-button>
                        </el-radio-group>
                        <el-radio-group v-model="dashboard.type_pie_data.time" size="small" @change="getTypePieData()">
                            <el-radio-button v-for="(item,value) in dashboard.time_options" :label="value" :value="value">{{item}}</el-radio-button>
                        </el-radio-group>
                    </div>
                </el-popover>
            </div>
            <div class="card-body" v-loading="loading.type_pie">
                <v-chart autoresize :option="dashboard.type_pie_data.chart" style="height: 318px" />
            </div>
        </div>
    </el-col>
    <el-col :xs="24" :sm="24" :md="16" :lg="14">
        <div class="card-box">
            <div class="card-header flex ac jsb" style="margin-bottom:0;">
                <div class="box-title">用户资产榜</div>
            </div>
            <div class="card-body" v-loading="loading.asset_data">
                <el-row :gutter="20" class="dashboard-cards">
                    <el-col :xs="24" :sm="12" :md="12" :lg="12">
                        <div class="asset-content-box">
                            <div class="opacity8 mb10 font-bold mt20">余额排行</div>
                            <el-scrollbar class="asset-item-box" height="289px">

                                <div class="flex ac jsb padding-h6" v-for="(item,index) in dashboard.asset_data.balance_data" :key="item.user_id + 'asset_data_balance_data'">
                                    <div class="flex0 mr10">
                                        <div class="badg" :class="index < 5 ? dashboard.asset_data.colors[index] : ''" style="width: 17px;"><b class="em12">{{index + 1}}</b></div>
                                    </div>
                                    <div class="flex ac overflow-hidden flex1">
                                        <el-tooltip placement="top" effect="light">
                                            <div class="flex0 mr6"> <el-avatar :size="30" :src="item.user_avatar"></el-avatar> </div>
                                            <template #content>
                                                <div class="tooltip-link-box">
                                                    <a href="javascript:void(0)" data-toggle="RefreshModal" :data-action="'admin_assets_details&user_id='+item.user_id">查看明细</a>
                                                </div>
                                            </template>
                                        </el-tooltip>
                                        <div class="flex1 overflow-hidden">
                                            <div class="text-ellipsis">{{ item.user_name }}</div>
                                        </div>
                                    </div>
                                    <div class="flex0 ml20 mr6">
                                        <div class="badg" :class="index < 5 ? dashboard.asset_data.colors[index] : ''"><span class="unit px12">{{marks.pay}}</span>{{ priceCut(item.price) }}</div>
                                    </div>
                                </div>

                            </el-scrollbar>
                        </div>
                    </el-col>
                    <el-col :xs="24" :sm="12" :md="12" :lg="12">
                        <div class="asset-content-box">
                            <div class="opacity8 mb10 font-bold mt20">积分排行</div>
                            <el-scrollbar class="asset-item-box" height="289px">
                                <div class="flex ac jsb padding-h6" v-for="(item,index) in dashboard.asset_data.points_data" :key="item.user_id + 'asset_data_points_data'">
                                    <div class="flex0 mr10">
                                        <div class="badg" :class="index < 5 ? dashboard.asset_data.colors[index] : ''" style="width: 17px;"><b class="em12">{{index + 1}}</b></div>
                                    </div>
                                    <div class="flex ac overflow-hidden flex1">
                                        <el-tooltip placement="top" effect="light">
                                            <div class="flex0 mr6"> <el-avatar :size="30" :src="item.user_avatar"></el-avatar> </div>
                                            <template #content>
                                                <div class="tooltip-link-box">
                                                    <a href="javascript:void(0)" data-toggle="RefreshModal" :data-action="'admin_assets_details&user_id='+item.user_id">查看明细</a>
                                                </div>
                                            </template>
                                        </el-tooltip>
                                        <div class="flex1 overflow-hidden">
                                            <div class="text-ellipsis">{{ item.user_name }}</div>
                                        </div>
                                    </div>
                                    <div class="flex0 ml20 mr6">
                                        <div class="badg" :class="index < 5 ? dashboard.asset_data.colors[index] : ''">{{ priceCut(item.price) }}积分</div>
                                    </div>
                                </div>

                            </el-scrollbar>
                        </div>
                    </el-col>
                </el-row>
            </div>
        </div>
    </el-col>
</el-row>
