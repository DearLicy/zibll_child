<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-21 14:38:13
 * @LastEditTime : 2025-07-24 18:31:56
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

?>
<!-- 发货弹窗 -->
<el-dialog
    v-model="shipping_data.shipping_dialog_show"
    title="发货"
    :width="win.width>580 ? '500px' : '100%'">
    <el-form class="order-edit-form" :model="shipping_data.shipping_dialog_data"
        :label-position="win.width>580 ? 'right' : 'top'" :label-width="win.width>580 ? '130px' : ''">

        <!-- 商品信息 -->
        <div class="text-box mb10">
            <div class="flex ac">
                <el-avatar shape="square" :size="60" :src="shipping_data.shipping_dialog_data.product_info.thumbnail || ''" class="mr10"></el-avatar>
                <div class="flex1 mr10 overflow-hidden">
                    <div class="mb6 font-bold"><span class="el-text is-truncated">{{ shipping_data.shipping_dialog_data.product_info.title }}</span></div>
                    <div class="el-text el-text--info is-truncated">{{ shipping_data.shipping_dialog_data.product_info.opt_name }}</div>
                </div>
                <div class="text-right flex0">
                    <div class="mb6 font-bold em12">x {{ shipping_data.shipping_dialog_data.order_data.count }}</div>
                    <div class="font-bold c-red">{{ shipping_data.shipping_dialog_data.order_data.prices.pay_price }}</div>
                    <div class="px12 muted-color" v-if="shipping_data.shipping_dialog_data.order_data.prices.shipping_fee">运费：{{ shipping_data.shipping_dialog_data.order_data.prices.shipping_fee }}</div>
                    <div v-if="[1,2].includes(~~shipping_data.shipping_dialog_data.after_sale_status)" class="mt6">
                        <div class="flex ac jsb c-red" @click="afterSaleDetails(shipping_data.shipping_dialog_data)" style=" cursor: pointer; ">
                            <div class="font-bold flex ac"><span class="badg c-yellow">{{after_sale_type_name[shipping_data.shipping_dialog_data.after_sale_data.type]}}</span>售后正在处理中</div>
                            <div class="flex ac">
                                <div class="flex ac"><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <el-form-item label="订单编号">
            <div>{{ shipping_data.shipping_dialog_data.order_num }}</div>
        </el-form-item>

        <el-form-item label="赠品" class=" pointer" v-if="isExist(shipping_data.shipping_dialog_data.order_data.gift_data)" @click="refreshModal('order_gift_modal&order_id='+shipping_data.shipping_dialog_data.id)">
            <div class="flex ac">
                <div class="text-right">
                    <span class="badg" v-for="(gift_item, index) in shipping_data.shipping_dialog_data.order_data.gift_data" :key="gift_item.desc + '_shipping_dialog_data'">
                        {{gift_item.desc}}
                    </span>
                    <div class="c-yellow px12">虚拟赠品系统会自动处理，其他赠品请与用户沟通处理</div>
                </div>
                <div class="flex ac"><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
            </div>
        </el-form-item>

        <el-form-item label="用户备注" v-if="shipping_data.shipping_dialog_data.order_data.remark">
            <div class="c-yellow">{{ shipping_data.shipping_dialog_data.order_data.remark }}</div>
        </el-form-item>
        <el-form-item label="其它信息" v-if="isExist(shipping_data.shipping_dialog_data.order_data.user_required)">
            <div class="flex ac hh" v-for="user_required_item in shipping_data.shipping_dialog_data.order_data.user_required" :key="user_required_item.name"><b class="mr6">{{ user_required_item.name }}</b><span class="mr10">{{ user_required_item.value }}</span></div>
        </el-form-item>

        <template v-if="shipping_data.shipping_dialog_data.shipping_type == 'auto'">
            <el-form-item label="发货类型">
                <div>{{ shipping_data.delivery_type_name[shipping_data.shipping_dialog_data.shipping_data.delivery_type] || '虚拟内容' }}<span class="ml6 px12 c-red">自动发货失败，待处理</span></div>
            </el-form-item>

            <el-form-item label="收件人邮箱">
                <div>{{ shipping_data.shipping_dialog_data.consignee.email || shipping_data.shipping_dialog_data.user_info.email || '无' }}</div>
            </el-form-item>
            <el-form-item label="发货内容">
                <el-input
                    v-model="shipping_data.shipping_dialog_data.delivery_content"
                    :autosize="{ minRows: 2, maxRows: 4 }"
                    type="textarea"
                    placeholder="请输入发货内容" />
            </el-form-item>
        </template>
        <template v-else-if="shipping_data.shipping_dialog_data.shipping_type == 'manual'">


        </template>
        <template v-else>
            <el-form-item label="收件人信息">
                <div class="text-box" style="width: 100%;">
                    <div class="flex ac jsb hh mb10">
                        <div class="flex1"><b class="mr6">{{ shipping_data.shipping_dialog_data.consignee.address_data.name }}</b><span class="mr10">{{ shipping_data.shipping_dialog_data.consignee.address_data.phone }}</span></div>
                        <!-- 点击复制 -->
                        <el-button type="primary" size="small" plain link @click="copyAddress(shipping_data.shipping_dialog_data.consignee.address_data)" data-clipboard-tag="地址">复制</el-button>
                    </div>
                    <div style="min-width: 100%;">
                        <span class="opacity8">{{ shipping_data.shipping_dialog_data.consignee.address_data.province + shipping_data.shipping_dialog_data.consignee.address_data.city + shipping_data.shipping_dialog_data.consignee.address_data.county + shipping_data.shipping_dialog_data.consignee.address_data.address }}</span>
                    </div>
                </div>
            </el-form-item>

            <!-- 发货方式选择 -->
            <el-form-item label="发货方式">
                <el-radio-group v-model="shipping_data.shipping_dialog_data.manual_delivery_type">
                    <el-radio v-for="(name, type) in shipping_data.manual_delivery_type" :key="type" :label="type">{{ name }}</el-radio>
                </el-radio-group>
            </el-form-item>


            <!-- 单号 -->
            <template v-if="shipping_data.shipping_dialog_data.manual_delivery_type !== 'no_express'">
                <el-form-item label="物流单号">
                    <el-input v-model="shipping_data.shipping_dialog_data.express_number" placeholder="请输入物流单号"></el-input>
                </el-form-item>
                <!-- 选择物流公司 -->
                <el-form-item label="物流公司">
                    <el-select v-model="shipping_data.shipping_dialog_data.express_company_name" placeholder="请选择物流公司">
                        <el-option v-for="item in shipping_data.express_companies" :key="item" :label="item" :value="item"></el-option>
                    </el-select>
                </el-form-item>
            </template>


        </template>
        <el-form-item label="备注信息">
            <el-input type="textarea" v-model="shipping_data.shipping_dialog_data.delivery_remark" placeholder="可选填写备注信息" :rows="3"></el-input>
        </el-form-item>

        <el-form-item>
            <el-button type="primary" :loading="loading.shipping_dialog_submit_but" :disabled="shipping_data.shipping_dialog_data.shipping_type !== 'manual' && !shipping_data.shipping_dialog_data.express_number && !shipping_data.shipping_dialog_data.delivery_content && shipping_data.shipping_dialog_data.manual_delivery_type !== 'no_express'" @click="shippingSubmit">确认提交</el-button>
        </el-form-item>
    </el-form>
</el-dialog>

<!-- 物流信息详情抽屉 -->
<el-drawer
    v-model="shipping_data.details_drawer_show"
    title="物流详情"
    direction="rtl"
    :size="win.width>640 ? '600px' : '100%'"
    :destroy-on-close="true" z-index="100030">

    <div v-if="shipping_data.details_drawer_data">
        <!-- 商品信息 -->
        <div class="card-box mb10">
            <div class="flex ac">
                <el-avatar shape="square" :size="60" :src="shipping_data.details_drawer_data.product_info.thumbnail || ''" class="mr10"></el-avatar>
                <div class="flex1 mr10 overflow-hidden">
                    <div class="mb6 font-bold"><span class="el-text is-truncated">{{ shipping_data.details_drawer_data.product_info.title }}</span></div>
                    <div class="el-text el-text--info is-truncated">{{ shipping_data.details_drawer_data.product_info.opt_name }}</div>
                </div>
                <div class="text-right flex0">
                    <div class="mb6 font-bold">x{{ shipping_data.details_drawer_data.order_data.count }}</div>
                    <div class="font-bold c-red">{{ shipping_data.details_drawer_data.order_data.prices.pay_price }}</div>
                </div>
            </div>
            <div class="flex ac jsb mt20">
                <div class="opacity5 mr10 flex0">订单号：</div>
                <div>{{shipping_data.details_drawer_data.order_num}}</div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">购买时间：</div>
                <div>{{shipping_data.details_drawer_data.pay_time}}</div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">支付方式：</div>
                <div v-html="shipping_data.details_drawer_data.pay_detail_lists"></div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">发货状态：</div>
                <div class="flex ac">
                    <el-tag :type="['warning', 'primary', 'success'][shipping_data.details_drawer_data.shipping_status] || 'warning'">{{ shipping_status_name[shipping_data.details_drawer_data.shipping_status] || '未知' }}</el-tag>
                </div>
            </div>
            <div class="flex jsb mt6 pointer" v-if="isExist(shipping_data.details_drawer_data.order_data.gift_data)" @click="refreshModal('order_gift_modal&order_id='+shipping_data.details_drawer_data.id)">
                <div class="opacity5 mr20 flex0">赠品：</div>
                <div class="flex ac">
                    <div>
                        <span v-for="(gift_item, index) in shipping_data.details_drawer_data.order_data.gift_data" :key="gift_item.desc + '_shipping_details'">
                            {{index > 0 ? '、' : ''}}{{gift_item.desc}}
                        </span>
                    </div>
                    <div class="flex ac"><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </div>
            </div>
            <div class="flex ac jsb mt6 c-red" v-if="[1,2].includes(~~shipping_data.details_drawer_data.after_sale_status)" @click="afterSaleDetails(shipping_data.details_drawer_data)" style=" cursor: pointer; ">
                <div class="opacity5 mr10 flex0">售后：</div>
                <div class="flex ac">
                    <div class="font-bold flex ac"><span class="badg c-yellow">{{after_sale_type_name[shipping_data.details_drawer_data.after_sale_data.type]}}</span>正在处理中</div>
                    <div class="flex ac"><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </div>
            </div>
        </div>

        <!-- 购买用户信息 -->
        <div class="card-box mb10">
            <div class="flex ac">
                <el-avatar :size="40" :src="shipping_data.details_drawer_data.user_info.avatar || ''" class="mr10"></el-avatar>
                <div class="flex1">
                    <div v-if="shipping_data.details_drawer_data.shipping_type === 'express'">
                        <div class="flex ac jsb mb6">
                            <div class="flex ac">
                                <span class="mr6 font-bold">{{ shipping_data.details_drawer_data.consignee.address_data.name }}</span>
                                <span>{{ shipping_data.details_drawer_data.consignee.address_data.phone }}</span>
                            </div>
                            <el-button type="primary" size="small" plain link @click="copyAddress(shipping_data.details_drawer_data.consignee.address_data)" data-clipboard-tag="地址">复制</el-button>
                        </div>
                        <div class="opacity8">{{ shipping_data.details_drawer_data.consignee.address_data.province + shipping_data.details_drawer_data.consignee.address_data.city + shipping_data.details_drawer_data.consignee.address_data.county + shipping_data.details_drawer_data.consignee.address_data.address }}</div>
                    </div>
                    <div v-else>
                        <div class="mb6 font-bold">{{ shipping_data.details_drawer_data.user_info.name }}</div>
                        <div class="opacity8" v-if="shipping_data.details_drawer_data.consignee.email">{{ shipping_data.details_drawer_data.consignee.email }}</div>
                    </div>
                    <div v-if="isExist(shipping_data.details_drawer_data.order_data.user_required)" class="flex ac hh">
                        <div class="mt6 mr10" v-for="user_required_item in shipping_data.details_drawer_data.order_data.user_required" :key="user_required_item.name"><span class="mr3 opacity5">{{ user_required_item.name }}</span><span>{{ user_required_item.value }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="shipping_data.details_drawer_data.shipping_status == '0'">
            <!-- 订单信息 -->
            <div class="box-title mb10">订单信息</div>
            <div class="card-box mb10">
                <div class="mb10 flex at">
                    <div class="opacity8 mr10 flex0">订单号：</div>
                    <div class="flex ac">
                        <span class="mr10">{{ shipping_data.details_drawer_data.order_num }}</span>
                        <el-button type="primary" size="small" plain link @click="copy(shipping_data.details_drawer_data.order_num)" data-clipboard-tag="订单号">复制</el-button>
                    </div>
                </div>
                <div class="mb10 flex ac">
                    <div class="opacity8 mr10 flex0">购买时间：</div>
                    <div>{{ shipping_data.details_drawer_data.pay_time }}</div>
                </div>
                <div class="flex ac">
                    <div class="opacity8 mr10 flex0">订单状态：</div>
                    <div>
                        <el-tag type="warning">{{status_name[shipping_data.details_drawer_data.status]}}</el-tag>
                    </div>
                </div>
            </div>
            <el-button type="primary" @click="showShippingDialog(shipping_data.details_drawer_data)" v-if="shipping_data.details_drawer_data.status == 1">立即发货</el-button>
        </div>

        <!-- 发货信息 -->
        <div v-else>
            <template v-if="shipping_data.details_drawer_data.shipping_type === 'express'">
                <div class="box-title mb10">物流信息</div>
                <div class="card-box mb10">
                    <template v-if="shipping_data.details_drawer_data.shipping_data.delivery_type === 'express'">
                        <div class="mb10 flex ac">
                            <div class="opacity8 mr10 flex0">物流公司：</div>
                            <div>{{ shipping_data.details_drawer_data.shipping_data.express_company_name }}</div>
                        </div>
                        <div class="mb10 flex ac">
                            <div class="opacity8 mr10 flex0">物流单号：</div>
                            <div class="flex ac">
                                <span class="mr10">{{ shipping_data.details_drawer_data.shipping_data.express_number }}</span>
                                <el-button type="primary" size="small" plain link @click="copy(shipping_data.details_drawer_data.shipping_data.express_number)" data-clipboard-tag="物流单号">复制</el-button>
                            </div>
                        </div>
                    </template>
                    <template v-if="shipping_data.details_drawer_data.shipping_data.delivery_type === 'no_express'">
                        <div class="mb10 flex ac">
                            <div class="opacity8 mr10 flex0">发货方式：</div>
                            <div>无需物流发货</div>
                        </div>
                    </template>

                    <div class="mb10 flex ac">
                        <div class="opacity8 mr10 flex0">发货时间：</div>
                        <div>{{ shipping_data.details_drawer_data.shipping_data.delivery_time }}</div>
                    </div>
                    <!-- 备注信息 -->
                    <div class="mb10 flex" v-if="shipping_data.details_drawer_data.shipping_data.delivery_remark">
                        <div class="opacity8 mr10 flex0">发货备注：</div>
                        <div class="text-box">{{ shipping_data.details_drawer_data.shipping_data.delivery_remark }}</div>
                    </div>
                    <div class="flex ac">
                        <div class="opacity8 mr10 flex0 fle'x">最新状态：</div>
                        <el-tag :type="shipping_data.details_drawer_data.shipping_status == '2' ? 'success' : 'warning'">{{ shipping_data.details_drawer_data.shipping_status == '2' ? '已确认收货' : '待收货' }}</el-tag>
                        <div class="badg c-purple" v-if="shipping_data.details_drawer_data.shipping_status == '1'" ><count-down :end-time="shipping_data.details_drawer_data.shipping_receipt_over_time"></count-down>后自动收货</div>
                    </div>
                </div>

                <!-- 物流跟踪信息 -->
                <template v-if="shipping_data.details_drawer_data.shipping_type === 'express' && shipping_data.details_drawer_data.shipping_data.express_number">
                    <div class="box-title mb10 flex ac">物流跟踪
                        <el-tag class="ml10" v-if="shipping_data.details_drawer_data.express_data.state">{{shipping_data.details_drawer_data.express_data.state}}</el-tag>
                        <el-icon v-if="loading.express_traces && shipping_data.details_drawer_data.express_data.traces" class="ml10 is-loading">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                                <path fill="currentColor" d="M512 64a32 32 0 0 1 32 32v192a32 32 0 0 1-64 0V96a32 32 0 0 1 32-32m0 640a32 32 0 0 1 32 32v192a32 32 0 1 1-64 0V736a32 32 0 0 1 32-32m448-192a32 32 0 0 1-32 32H736a32 32 0 1 1 0-64h192a32 32 0 0 1 32 32m-640 0a32 32 0 0 1-32 32H96a32 32 0 0 1 0-64h192a32 32 0 0 1 32 32M195.2 195.2a32 32 0 0 1 45.248 0L376.32 331.008a32 32 0 0 1-45.248 45.248L195.2 240.448a32 32 0 0 1 0-45.248zm452.544 452.544a32 32 0 0 1 45.248 0L828.8 783.552a32 32 0 0 1-45.248 45.248L647.744 692.992a32 32 0 0 1 0-45.248zM828.8 195.264a32 32 0 0 1 0 45.184L692.992 376.32a32 32 0 0 1-45.248-45.248l135.808-135.808a32 32 0 0 1 45.248 0m-452.544 452.48a32 32 0 0 1 0 45.248L240.448 828.8a32 32 0 0 1-45.248-45.248l135.808-135.808a32 32 0 0 1 45.248 0z"></path>
                            </svg>
                        </el-icon>
                    </div>
                    <div v-loading="loading.express_traces">
                        <div v-if="shipping_data.details_drawer_data.express_data.traces" class="mt6">
                            <el-timeline>
                                <el-timeline-item
                                    v-for="(item, index) in shipping_data.details_drawer_data.express_data.traces"
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
                </template>
            </template>
            <template v-else>
                <div class="card-box mb10">
                    <div class="mb10 flex ac">
                        <div class="opacity8 mr10 flex0">发货类型：</div>
                        <div>{{ shipping_data.delivery_type_name[shipping_data.details_drawer_data.shipping_data.delivery_type] || '虚拟内容' }}</div>
                    </div>
                    <div class="mb10 flex ac" v-if="shipping_data.details_drawer_data.order_data.remark">
                        <div class="opacity8 mr10 flex0">用户备注：</div>
                        <div>{{ shipping_data.details_drawer_data.order_data.remark }}</div>
                    </div>
                    <div class="mb10 flex at" v-if="shipping_data.details_drawer_data.shipping_data.delivery_content">
                        <div class="opacity8 mr10 flex0">发货内容：</div>
                        <div class="text-box" v-html="shipping_data.details_drawer_data.shipping_data.delivery_content"></div>
                    </div>
                    <div class="mb10 flex ac">
                        <div class="opacity8 mr10 flex0">发货时间：</div>
                        <div>{{ shipping_data.details_drawer_data.shipping_data.delivery_time }}</div>
                    </div>
                    <!-- 状态 -->
                    <div class="mb10 flex ac">
                        <div class="opacity8 mr10 flex0">收货状态：</div>
                        <el-tag :type="shipping_data.details_drawer_data.shipping_status == '2' ? 'success' : 'warning'">{{ shipping_data.details_drawer_data.shipping_status == '2' ? '已确认收货' : '待收货' }}</el-tag>
                        <div class="badg c-purple" v-if="shipping_data.details_drawer_data.shipping_status == '1'" ><count-down :end-time="shipping_data.details_drawer_data.shipping_receipt_over_time"></count-down>后自动收货</div>
                    </div>
                    <!-- 备注信息 -->
                    <div class="mb10 flex" v-if="shipping_data.details_drawer_data.shipping_data.delivery_remark">
                        <div class="opacity8 mr10 flex0">发货备注</div>
                        <div class="text-box">{{ shipping_data.details_drawer_data.shipping_data.delivery_remark }}</div>
                    </div>
                </div>
            </template>
        </div>
    </div>


    <div v-else class="flex jc ac" style="height: 100%;">
        <el-empty description="暂无发货信息"></el-empty>
    </div>
</el-drawer>
