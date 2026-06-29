<?php
/*
 * @Author       : Qinver
 * @Url          : zibll.com
 * @Date         : 2025-07-21 14:38:40
 * @LastEditTime: 2025-09-10 13:16:26
 * @Project      : Zibll子比主题
 * @Description  : 更优雅的Wordpress主题
 * Copyright (c) 2025 by Qinver, All Rights Reserved.
 * @Email        : 770349780@qq.com
 * @Read me      : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发
 * @Remind       : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

?>

<el-dialog
    v-model="after_sale_data.handle_dialog_show"
    title="售后处理"
    :width="win.width>580 ? '500px' : '100%'">
    <el-form class="order-edit-form" :model="after_sale_data.handle_dialog_data" label-position="top">
        <!-- 商品信息 -->
        <div class="text-box mb20">
            <div class="flex ac">
                <el-avatar shape="square" :size="60" :src="after_sale_data.handle_dialog_data.product_info.thumbnail || ''" class="mr10"></el-avatar>
                <div class="flex1 mr10 overflow-hidden">
                    <div class="mb6 font-bold"><span class="el-text is-truncated">{{ after_sale_data.handle_dialog_data.product_info.title }}</span></div>
                    <div class="el-text el-text--info is-truncated">{{ after_sale_data.handle_dialog_data.product_info.opt_name }}</div>
                </div>
                <div class="text-right flex0">
                    <div class="mb6 font-bold">x{{ after_sale_data.handle_dialog_data.order_data.count }}</div>
                    <div class="font-bold c-red">{{ after_sale_data.handle_dialog_data.order_data.prices.pay_price }}</div>
                </div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">订单号：</div>
                <div>{{after_sale_data.handle_dialog_data.order_num}}</div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">购买时间：</div>
                <div>{{after_sale_data.handle_dialog_data.pay_time}}</div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">支付方式：</div>
                <div v-html="after_sale_data.handle_dialog_data.pay_detail_lists"></div>
            </div>

            <div class="flex jsb mt6 pointer" v-if="isExist(after_sale_data.handle_dialog_data.order_data.gift_data)" @click="refreshModal('order_gift_modal&order_id='+after_sale_data.handle_dialog_data.id)">
                <div class="opacity5 mr20 flex0">赠品：</div>
                <div class="flex ac">
                    <div class="text-right">
                        <span v-for="(gift_item, index) in after_sale_data.handle_dialog_data.order_data.gift_data" :key="gift_item.desc + '_after_sale_handle_details'">
                            {{index > 0 ? '、' : ''}}{{gift_item.desc}}
                        </span>
                        <div class="c-yellow px12">虚拟赠品系统会自动处理，其他赠品请与用户沟通处理</div>
                    </div>
                    <div class="flex ac"><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </div>
            </div>

            <div class="flex ac jsb mt6 pointer" @click="shippingDetails(after_sale_data.handle_dialog_data)">
                <div class="opacity5 mr10 flex0">发货状态：</div>
                <div class="flex ac">
                    <el-tag :type="['warning', 'primary', 'success'][after_sale_data.handle_dialog_data.shipping_status] || 'warning'">{{ shipping_status_name[after_sale_data.handle_dialog_data.shipping_status] || '未知' }}</el-tag>
                    <div class="badg c-purple" v-if="after_sale_data.handle_dialog_data.shipping_status == '1'" ><count-down :end-time="after_sale_data.handle_dialog_data.shipping_receipt_over_time"></count-down>后自动收货</div>
                    <div class="flex ac"><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </div>
            </div>
        </div>

        <el-timeline>
            <el-timeline-item :timestamp="after_sale_data.handle_dialog_data.after_sale_data.user_apply_time" placement="top">
                <div>
                    <div class="flex ac">
                        <b>用户发起售后申请</b>
                        <div class="badg ml10" :class="after_sale_data.type_color[after_sale_data.handle_dialog_data.after_sale_type] || 'c-yellow'">{{after_sale_type_name[after_sale_data.handle_dialog_data.after_sale_type] || '未知'}}</div>
                    </div>
                    <div class="flex ac mt6" v-if="after_sale_data.handle_dialog_data.after_sale_data.reason">
                        <div class="opacity5 mr10 flex0">售后原因：</div>
                        <div>{{after_sale_data.handle_dialog_data.after_sale_data.reason}}</div>
                    </div>
                    <div class="flex ac mt6" v-if="after_sale_data.handle_dialog_data.after_sale_data.price || ['refund', 'insured_price'].includes(after_sale_data.handle_dialog_data.after_sale_type)">
                        <div class="opacity5 mr10 flex0">退款金额：</div>
                        <div class="c-red">{{after_sale_data.handle_dialog_data.after_sale_data.price || '0'}}</div>
                    </div>
                    <div class="flex ac mt6" v-if="after_sale_data.handle_dialog_data.after_sale_data.remark">
                        <div class="opacity5 mr10 flex0">申请备注：</div>
                        <div class="c-yellow">{{after_sale_data.handle_dialog_data.after_sale_data.remark}}</div>
                    </div>
                </div>
            </el-timeline-item>
            <el-timeline-item timestamp="等待商家同意或拒绝" placement="top" v-if="after_sale_data.handle_dialog_data.after_sale_status == 1" color="#0bbd87">
                <el-form-item>
                    <el-radio-group v-model="after_sale_data.handle_dialog_data.handle_type">
                        <el-radio border v-for="(name, type) in after_sale_data.handle_type" :key="type" :label="type">{{ name }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <template v-if="after_sale_data.handle_dialog_data.pay_modo == 'points'">
                    <el-form-item label="退款到用户" v-if="after_sale_data.handle_dialog_data.handle_type == 'agree'">
                        <div>积分账户</div>
                    </el-form-item>
                </template>

                <template v-else-if="['refund', 'insured_price'].includes(after_sale_data.handle_dialog_data.after_sale_type)">
                    <template v-if="after_sale_data.handle_dialog_data.handle_type == 'agree'">
                        <!-- 选择退款渠道 -->
                        <el-form-item label="退款到用户">
                            <div class="c-yellow px12" style="flex-basis:100%;" v-if="after_sale_data.handle_dialog_data.pay_type == 'balance'">用户使用余额付款，建议退款到余额</div>
                            <el-radio-group v-model="after_sale_data.handle_dialog_data.refund_channel">
                                <el-radio v-for="(name, type) in after_sale_data.refund_channel_type" :key="type" :label="type">{{ name }}</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="用户收款码" v-if="after_sale_data.handle_dialog_data.refund_channel == 'wechat' || after_sale_data.handle_dialog_data.refund_channel == 'alipay'">
                            <div class="text-center mr10">
                                <el-image show-progress fit="cover" :initial-index="0" :preview-src-list="Object.values(after_sale_data.handle_dialog_data.user_info.rewards)" v-if="after_sale_data.handle_dialog_data.user_info.rewards.weixin" :src="after_sale_data.handle_dialog_data.user_info.rewards.weixin" style="width: 100px;height: 100px;"></el-image>
                                <div class="text-center">微信收款码</div>
                            </div>
                            <div class="text-center">
                                <el-image show-progress fit="cover" :initial-index="1" :preview-src-list="Object.values(after_sale_data.handle_dialog_data.user_info.rewards)" v-if="after_sale_data.handle_dialog_data.user_info.rewards.alipay" :src="after_sale_data.handle_dialog_data.user_info.rewards.alipay" style="width: 100px;height: 100px;"></el-image>
                                <div class="text-center">支付宝收款码</div>
                            </div>
                        </el-form-item>
                    </template>
                </template>

                <template v-if="['refund_return', 'replacement','warranty'].includes(after_sale_data.handle_dialog_data.after_sale_type)">
                    <el-form-item label="商家收货地址" v-if="after_sale_data.handle_dialog_data.handle_type == 'agree'">

                        <div @click="afterSaleReturnAddressDialog" style="cursor: pointer;width: 1100%;">
                            <div v-if="after_sale_data.handle_dialog_data.return_address">
                                <div class="text-box flex ac">
                                    <div class="address-content flex1">
                                        <div class="address-detail muted-color flex ac">
                                            <div class="name-phone mb6"><span class="name">{{after_sale_data.handle_dialog_data.return_address.name}}</span><span class="phone ml10">{{after_sale_data.handle_dialog_data.return_address.phone}}</span><span class="badg badg-sm c-blue">家</span></div>
                                        </div>
                                        <div class="address-detail muted-2-color em09 mb6">{{after_sale_data.handle_dialog_data.return_address.province}} {{after_sale_data.handle_dialog_data.return_address.city}} {{after_sale_data.handle_dialog_data.return_address.county}} {{after_sale_data.handle_dialog_data.return_address.address}}</div>
                                    </div>
                                    <div class="ml10 opacity5"><i class="dashicons dashicons-arrow-right-alt2"></i></div>
                                </div>
                            </div>
                            <div v-else>
                                <div class="but c-yellow">选择退回地址</div>
                            </div>
                        </div>

                    </el-form-item>
                </template>
                <el-form-item label="备注信息">
                    <el-input type="textarea" v-model="after_sale_data.handle_dialog_data.author_remark" placeholder="请填写处理留言" :rows="3"></el-input>
                    <div class="c-yellow" v-if="after_sale_data.handle_dialog_data.handle_type == 'refuse' && !after_sale_data.handle_dialog_data.author_remark">请填写拒绝原因等信息</div>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" :loading="loading.handle_dialog_submit_but" :disabled="!after_sale_data.handle_dialog_data.handle_type" @click="afterSaleHandleSubmit">确认提交</el-button>
                </el-form-item>
            </el-timeline-item>

            <el-timeline-item :timestamp="after_sale_data.handle_dialog_data.after_sale_data.author_handle_time" placement="top" v-if="after_sale_data.handle_dialog_data.after_sale_status == 2 && after_sale_data.handle_dialog_data.after_sale_data.author_handle_time">
                <div class="flex ac">
                    <b>商家已同意</b>
                </div>
                <div class="flex mt6" v-if="after_sale_data.handle_dialog_data.after_sale_data.return_address">
                    <div class="opacity5 mr10 flex0">退货地址：</div>
                    <div>
                        <div>{{after_sale_data.handle_dialog_data.after_sale_data.return_address.name}} {{after_sale_data.handle_dialog_data.after_sale_data.return_address.phone}}</div>
                        <div class="px12 opacity8">{{after_sale_data.handle_dialog_data.after_sale_data.return_address.province}} {{after_sale_data.handle_dialog_data.after_sale_data.return_address.city}} {{after_sale_data.handle_dialog_data.after_sale_data.return_address.county}} {{after_sale_data.handle_dialog_data.after_sale_data.return_address.address}}</div>
                    </div>
                </div>
                <div class="flex ac mt6" v-if="after_sale_data.handle_dialog_data.after_sale_data.author_remark">
                    <div class="opacity5 mr10 flex0">处理备注：</div>
                    <div class="c-yellow">{{after_sale_data.handle_dialog_data.after_sale_data.author_remark}}</div>
                </div>
            </el-timeline-item>

            <el-timeline-item :timestamp="after_sale_data.handle_dialog_data.after_sale_data.user_return_time" placement="top" v-if="after_sale_data.handle_dialog_data.after_sale_status == 2 && after_sale_data.handle_dialog_data.after_sale_data.user_return_time">
                <div class="flex ac">
                    <b>用户已发货</b>
                    <div class="badg ml10" :class="c-blue">{{after_sale_data.handle_dialog_data.after_sale_data.user_return_data.express_company_name}}</div>
                </div>
                <div class="flex ac mt6">
                    <div class="opacity5 mr10 flex0">快递单号：</div>
                    <div class="flex ac">
                        <div>{{after_sale_data.handle_dialog_data.after_sale_data.user_return_data.express_number}}</div>
                        <el-button class="ml10" size="small" @click="afterSaleExpressDialog(after_sale_data.handle_dialog_data, 'user_return')">查看物流信息</el-button>
                    </div>
                </div>
                <div class="flex ac mt6" v-if="after_sale_data.handle_dialog_data.after_sale_data.user_return_data.return_remark">
                    <div class="opacity5 mr10 flex0">发货备注：</div>
                    <div class="c-yellow">{{after_sale_data.handle_dialog_data.after_sale_data.user_return_data.return_remark}}</div>
                </div>
            </el-timeline-item>

            <el-timeline-item timestamp="等待商家退款" placement="top" v-if="after_sale_data.handle_dialog_data.after_sale_status == 2 && after_sale_data.handle_dialog_data.after_sale_data.progress == 2" color="#0bbd87">
                <!-- 退货退款商家处理 -->
                <template v-if="after_sale_data.handle_dialog_data.after_sale_type === 'refund_return'">

                    <div class="c-yellow mb10">请确认收到退货后，再处理退款，如遇到货物问题，请与客户沟通</div>

                    <template v-if="after_sale_data.handle_dialog_data.pay_modo == 'points'">
                        <el-form-item label="退款到用户" v-if="after_sale_data.handle_dialog_data.handle_type == 'agree'">
                            <div>积分账户</div>
                        </el-form-item>
                    </template>
                    <template v-else>
                        <!-- 选择退款渠道 -->
                        <el-form-item label="退款到用户">
                            <div class="c-yellow px12" style="flex-basis:100%;" v-if="after_sale_data.handle_dialog_data.pay_type == 'balance'">用户使用余额付款，建议退款到余额</div>
                            <el-radio-group v-model="after_sale_data.handle_dialog_data.refund_channel">
                                <el-radio v-for="(name, type) in after_sale_data.refund_channel_type" :key="type" :label="type">{{ name }}</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <el-form-item label="用户收款码" v-if="after_sale_data.handle_dialog_data.refund_channel == 'wechat' || after_sale_data.handle_dialog_data.refund_channel == 'alipay'">
                            <div class="text-center mr10">
                                <el-image show-progress fit="cover" :initial-index="0" :preview-src-list="Object.values(after_sale_data.handle_dialog_data.user_info.rewards)" v-if="after_sale_data.handle_dialog_data.user_info.rewards.weixin" :src="after_sale_data.handle_dialog_data.user_info.rewards.weixin" style="width: 100px;height: 100px;"></el-image>
                                <div class="text-center">微信收款码</div>
                            </div>
                            <div class="text-center">
                                <el-image show-progress fit="cover" :initial-index="1" :preview-src-list="Object.values(after_sale_data.handle_dialog_data.user_info.rewards)" v-if="after_sale_data.handle_dialog_data.user_info.rewards.alipay" :src="after_sale_data.handle_dialog_data.user_info.rewards.alipay" style="width: 100px;height: 100px;"></el-image>
                                <div class="text-center">支付宝收款码</div>
                            </div>
                        </el-form-item>
                    </template>
                    <el-form-item label="备注信息">
                        <el-input type="textarea" v-model="after_sale_data.handle_dialog_data.author_remark" placeholder="请填写处理留言" :rows="3"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" :loading="loading.handle_dialog_submit_but" @click="afterSalerefundReturnSubmit">确认退货退款</el-button>
                    </el-form-item>


                </template>
                <template v-if="after_sale_data.handle_dialog_data.after_sale_type === 'replacement'">




                </template>
                <template v-if="after_sale_data.handle_dialog_data.after_sale_type === 'warranty'">




                </template>
            </el-timeline-item>
        </el-timeline>
    </el-form>
</el-dialog>

<!-- 售后详情抽屉 -->
<el-drawer
    title="售后详情"
    v-model="after_sale_data.details_drawer_show"
    direction="rtl"
    :size="win.width>640 ? '600px' : '100%'"
    :destroy-on-close="true" z-index="100030">
    <div class="drawer-content" v-if="after_sale_data.details_drawer_data">
        <div class="card-box mb20">
            <div class="flex ac">
                <el-avatar shape="square" :size="60" :src="after_sale_data.details_drawer_data.product_info.thumbnail || ''" class="mr10"></el-avatar>
                <div class="flex1 mr10 overflow-hidden">
                    <div class="mb6 font-bold"><span class="el-text is-truncated">{{ after_sale_data.details_drawer_data.product_info.title }}</span></div>
                    <div class="el-text el-text--info is-truncated">{{ after_sale_data.details_drawer_data.product_info.opt_name }}</div>
                </div>
                <div class="text-right flex0">
                    <div class="mb6 font-bold">x{{ after_sale_data.details_drawer_data.order_data.count }}</div>
                    <div class="font-bold c-red">{{ after_sale_data.details_drawer_data.order_data.prices.pay_price }}</div>
                </div>
            </div>
            <div class="flex ac jsb mt20">
                <div class="opacity5 mr10 flex0">订单号：</div>
                <div>{{after_sale_data.details_drawer_data.order_num}}</div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">购买时间：</div>
                <div>{{after_sale_data.details_drawer_data.pay_time}}</div>
            </div>
            <div class="flex ac jsb mt6">
                <div class="opacity5 mr10 flex0">支付方式：</div>
                <div v-html="after_sale_data.details_drawer_data.pay_detail_lists"></div>
            </div>
            <div class="flex jsb mt6 pointer" v-if="isExist(after_sale_data.details_drawer_data.order_data.gift_data)" @click="refreshModal('order_gift_modal&order_id='+after_sale_data.details_drawer_data.id)">
                <div class="opacity5 mr20 flex0">赠品：</div>
                <div class="flex ac">
                    <div class="text-right">
                        <span v-for="(gift_item, index) in after_sale_data.details_drawer_data.order_data.gift_data" :key="gift_item.desc + '_after_sale_details'">
                            {{index > 0 ? '、' : ''}}{{gift_item.desc}}
                        </span>
                    </div>
                    <div class="flex ac"><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </div>
            </div>
            <div class="flex ac jsb mt6 pointer" @click="shippingDetails(after_sale_data.details_drawer_data)">
                <div class="opacity5 mr10 flex0">发货状态：</div>
                <div class="flex ac">
                    <el-tag :type="['warning', 'primary', 'success'][after_sale_data.details_drawer_data.shipping_status] || 'warning'">{{ shipping_status_name[after_sale_data.details_drawer_data.shipping_status] || '未知' }}</el-tag>
                    <div class="badg c-purple" v-if="after_sale_data.details_drawer_data.shipping_status == '1'" ><count-down :end-time="after_sale_data.details_drawer_data.shipping_receipt_over_time"></count-down>后自动收货</div>
                    <div class="flex ac"><i class="dashicons dashicons-arrow-right-alt2 opacity5 em09 ml3"></i></div>
                </div>
            </div>
        </div>
        <div class="" v-if="[1,2,'1','2'].includes(after_sale_data.details_drawer_data.after_sale_status)">
            <div class="box-title mb10">当前售后进度</div>
            <el-timeline>
                <el-timeline-item :timestamp="after_sale_data.details_drawer_data.after_sale_data.user_apply_time" placement="top">
                    <div>
                        <div class="flex ac">
                            <b>用户发起售后申请</b>
                            <div class="badg ml10" :class="after_sale_data.type_color[after_sale_data.details_drawer_data.after_sale_type] || 'c-yellow'">{{after_sale_type_name[after_sale_data.details_drawer_data.after_sale_type] || '未知'}}</div>
                        </div>
                        <div class="flex ac mt6" v-if="after_sale_data.details_drawer_data.after_sale_data.reason">
                            <div class="opacity5 mr10 flex0">售后原因：</div>
                            <div>{{after_sale_data.details_drawer_data.after_sale_data.reason}}</div>
                        </div>
                        <div class="flex ac mt6" v-if="after_sale_data.details_drawer_data.after_sale_data.price || ['refund', 'insured_price'].includes(after_sale_data.details_drawer_data.after_sale_type)">
                            <div class="opacity5 mr10 flex0">退款金额：</div>
                            <div class="c-red">{{after_sale_data.details_drawer_data.after_sale_data.price || '0'}}</div>
                        </div>
                        <div class="flex ac mt6" v-if="after_sale_data.details_drawer_data.after_sale_data.remark">
                            <div class="opacity5 mr10 flex0">申请备注：</div>
                            <div class="c-yellow">{{after_sale_data.details_drawer_data.after_sale_data.remark}}</div>
                        </div>
                    </div>
                </el-timeline-item>
                <el-timeline-item timestamp="等待商家同意或拒绝" placement="top" v-if="after_sale_data.details_drawer_data.after_sale_status == 1" color="#0bbd87">
                </el-timeline-item>
                <el-timeline-item :timestamp="after_sale_data.details_drawer_data.after_sale_data.author_handle_time" placement="top" v-if="after_sale_data.details_drawer_data.after_sale_status == 2 && after_sale_data.details_drawer_data.after_sale_data.author_handle_time">
                    <div class="flex ac">
                        <b>商家已同意</b>
                    </div>
                    <div class="flex mt6" v-if="after_sale_data.details_drawer_data.after_sale_data.return_address">
                        <div class="opacity5 mr10 flex0">退货地址：</div>
                        <div>
                            <div>{{after_sale_data.details_drawer_data.after_sale_data.return_address.name}} {{after_sale_data.details_drawer_data.after_sale_data.return_address.phone}}</div>
                            <div class="px12 opacity8">{{after_sale_data.details_drawer_data.after_sale_data.return_address.province}} {{after_sale_data.details_drawer_data.after_sale_data.return_address.city}} {{after_sale_data.details_drawer_data.after_sale_data.return_address.county}} {{after_sale_data.details_drawer_data.after_sale_data.return_address.address}}</div>
                        </div>
                    </div>
                    <div class="flex ac mt6" v-if="after_sale_data.details_drawer_data.after_sale_data.author_remark">
                        <div class="opacity5 mr10 flex0">处理备注：</div>
                        <div class="c-yellow">{{after_sale_data.details_drawer_data.after_sale_data.author_remark}}</div>
                    </div>
                </el-timeline-item>
                <el-timeline-item :timestamp="after_sale_data.details_drawer_data.after_sale_data.user_return_time" placement="top" v-if="after_sale_data.details_drawer_data.after_sale_status == 2 && after_sale_data.details_drawer_data.after_sale_data.user_return_time">
                    <div class="flex ac">
                        <b>用户已发货</b>
                        <div class="badg ml10" :class="c-blue">{{after_sale_data.details_drawer_data.after_sale_data.user_return_data.express_company_name}}</div>
                    </div>
                    <div class="flex ac mt6">
                        <div class="opacity5 mr10 flex0">快递单号：</div>
                        <div class="flex ac">
                            <div>{{after_sale_data.details_drawer_data.after_sale_data.user_return_data.express_number}}</div>
                            <el-button class="ml10" size="small" @click="afterSaleExpressDialog(after_sale_data.details_drawer_data,'user_return')">查看物流信息</el-button>
                        </div>
                    </div>
                    <div class="flex ac mt6" v-if="after_sale_data.details_drawer_data.after_sale_data.user_return_data.return_remark">
                        <div class="opacity5 mr10 flex0">发货备注：</div>
                        <div class="c-yellow">{{after_sale_data.details_drawer_data.after_sale_data.user_return_data.return_remark}}</div>
                    </div>
                </el-timeline-item>

                <el-timeline-item timestamp="最新进度" placement="top">
                    <el-button type="primary" plain @click="afterSaleHandle(after_sale_data.details_drawer_data)" v-if="after_sale_data.details_drawer_data.after_sale_status == 1">立即处理</el-button>
                    <div class="c-yellow em09 mt6" v-if="after_sale_data.details_drawer_data.after_sale_status == 1">待商家审核</div>
                    <el-tag v-if="after_sale_data.details_drawer_data.after_sale_status > 2" :type="[ 'warning','warning','success','success','danger','danger'][after_sale_data.details_drawer_data.after_sale_status] || 'warning'">
                        {{ after_sale_data.status_name[after_sale_data.details_drawer_data.after_sale_status] || '未知' }}
                    </el-tag>
                    <el-button type="primary" @click="afterSaleHandle(after_sale_data.details_drawer_data)" v-if="after_sale_data.details_drawer_data.after_sale_status == 2 && after_sale_data.details_drawer_data.after_sale_data.progress == 2">立即处理</el-button>
                    <div v-if="after_sale_data.details_drawer_data.after_sale_status == 2" class="em09" :class="[ 'c-yellow','c-yellow','c-yellow','c-green','c-red','c-red'][after_sale_data.details_drawer_data.after_sale_data.progress] || 'c-yellow'">
                        {{ after_sale_data.progress_name[after_sale_data.details_drawer_data.after_sale_data.progress] || '未知' }}
                    </div>
                    <div class="badg c-yellow em09" v-if="after_sale_data.details_drawer_data.after_sale_status == 2 && after_sale_data.details_drawer_data.after_sale_data.progress == 1"><count-down :end-time="after_sale_data.details_drawer_data.after_sale_return_express_over_time"></count-down>后未发货自动取消</div>
                </el-timeline-item>
            </el-timeline>
        </div>

        <div class="box-title mb10">历史售后记录</div>
        <div v-loading="loading.after_sale_record_html">
            <div v-if="after_sale_data.details_drawer_data.after_sale_record_html" v-html="after_sale_data.details_drawer_data.after_sale_record_html"></div>
            <div class="text-center" v-else>
                <el-empty description="暂无售后记录" />
            </div>
        </div>
    </div>

    <!-- 加载中状态 -->
    <div v-else class="drawer-loading">
        <el-skeleton :rows="10" animated />
    </div>
</el-drawer>


<!-- 选择退回地址 -->
<el-dialog
    title="选择退回地址"
    v-model="after_sale_data.return_address_dialog_show"
    :width="win.width>580 ? '500px' : '100%'">
    <div class="return-address-dialog-content">
        <!-- 商家收货地址列表 -->
        <div v-if="after_sale_data.return_address_dialog_data.length" class="address-list">
            <div class="text-box mb10 pointer" v-for="(item, index) in after_sale_data.return_address_dialog_data" :key="'address-' + index" @click="afterSaleSelectAddress(item)">
                <div class="address-content">
                    <div class="address-detail muted-color flex ac">
                        <div class="name-phone mb6">
                            <span class="name">{{ item.name }}</span>
                            <span class="phone ml10">{{ item.phone }}</span>
                            <span class="badg badg-sm c-blue" v-if="item.tag">{{ item.tag }}</span>
                        </div>
                    </div>
                    <div class="address-detail muted-2-color em09 mb6">{{ item.province }} {{ item.city }} {{ item.county }} {{ item.address }}</div>
                </div>
                <div class="address-actions flex ac jsb">
                    <span class="default-badge badg badg-sm c-red" v-if="item.is_default">默认</span>
                    <button class="el-button el-button--small" v-else @click.prevent.stop="setDefaultAddress(item)" :item-id="item.id">设为默认</button>
                    <div class="action-btns px12">
                        <button class="el-button el-button--small" @click.prevent.stop="editAddress(item)">编辑</button>
                        <button class="el-button el-button--small c-red ml6" @click.prevent.stop="deleteAddress(item.id)" v-if="!item.is_default">删除</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 无地址提示 -->
        <div v-else class="text-center">
            <el-empty description="暂无收货地址" />
        </div>

        <!-- 底部操作按钮 -->
        <div class="dialog-footer mt20 flex jc">
            <el-button type="primary" @click="afterSaleAddNewAddress" class="mr10">添加新地址</el-button>
        </div>


    </div>

    <!-- 添加新地址弹窗 -->
    <el-dialog
        :title="author_data.new_address_dialog_data.id ? '编辑地址' : '添加新地址'"
        v-model="author_data.new_address_dialog_show"
        :width="win.width>580 ? '500px' : '100%'">
        <div class="add-new-address-dialog-content">
            <!-- 地址表单 -->
            <el-form :model="author_data.new_address_dialog_data" label-width="120px">
                <el-form-item label="收货人">
                    <el-input v-model="author_data.new_address_dialog_data.name" placeholder="请输入收货人"></el-input>
                </el-form-item>
                <el-form-item label="联系电话">
                    <el-input v-model="author_data.new_address_dialog_data.phone" placeholder="请输入联系电话"></el-input>
                </el-form-item>
                <el-form-item label="省份">
                    <el-input v-model="author_data.new_address_dialog_data.province" placeholder="请输入省份"></el-input>
                </el-form-item>
                <el-form-item label="城市">
                    <el-input v-model="author_data.new_address_dialog_data.city" placeholder="请输入城市"></el-input>
                </el-form-item>
                <el-form-item label="区县">
                    <el-input v-model="author_data.new_address_dialog_data.county" placeholder="请输入区县"></el-input>
                </el-form-item>
                <el-form-item label="详细地址">
                    <el-input v-model="author_data.new_address_dialog_data.address" placeholder="请输入详细地址"></el-input>
                </el-form-item>
                <el-form-item label="标签">
                    <el-input v-model="author_data.new_address_dialog_data.tag" placeholder="请输入地址标签"></el-input>
                </el-form-item>
                <el-form-item label="设为默认">
                    <el-switch v-model="author_data.new_address_dialog_data.is_default"></el-switch>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" :loading="loading.author_address_submit_but" @click="AuthorAddressSubmit">确认提交</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-dialog>

</el-dialog>
