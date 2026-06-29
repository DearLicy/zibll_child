<?php
/*
 * @Author        : Qinver
 * @Url           : vela.com
 * @Date          : 2020-11-11 11:41:45
 * @LastEditTime: 2025-09-27 19:42:40
 * @Email         : 770349780@qq.com
 * @Project       : vela子比主题
 * @Description   : 一款极其优雅的Wordpress主题
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

class Vela_Module
{

    public static function bing_submit()
    {
        $con = '<p><b>批量推送链接到必应，请注意链接格式一行一个：</b></p>
                <ajaxform class="ajax-form">
                    <textarea class="mt6 mr10" style="width: 100%;" rows="5" placeholder="每个链接占一行..." ajax-name="urls"></textarea>
                    <br/>
                    <a href="javascript:;" class="but jb-yellow ajax-submit mt6"><i class="fa fa-paper-plane-o"></i> 批量提交</a>
                    <div class="ajax-notice mt6"></div>
                    <input type="hidden" ajax-name="action" value="bulk_bing_url_submission">
                </ajaxform>';
        return $con;
    }

    public static function backup()
    {
        $csf            = array();
        $prefix         = 'vela_options';
        $options        = get_option($prefix . '_backup');
        $lists          = '暂无备份数据！';
        $admin_ajax_url = admin_url('admin-ajax.php', 'relative');
        $delete_but     = '';
        if ($options) {
            $lists   = '';
            $options = array_reverse($options);
            $count   = 0;
            foreach ($options as $key => $val) {
                $ajax_url = add_query_arg('key', $key, $admin_ajax_url);
                $del      = '<a href="javascript:;" ajax-url="' . add_query_arg('action', 'vela_options_backup_delete', $ajax_url) . '" data-confirm="确认要删除此备份[' . $key . ']？删除后不可恢复！" class="but c-yellow ajax-get ml10">删除</a>';
                $restore  = '<a href="javascript:;" ajax-url="' . add_query_arg('action', 'vela_options_backup_restore', $ajax_url) . '" data-confirm="确认将主题设置恢复到此备份吗？[' . $key . ']？" class="but c-blue ajax-get ml10">恢复</a>';
                $lists .= '<div class="backup-item flex ac jsb">';
                $lists .= '<div class="item-left"><div>' . $val['time'] . '</div><div> [' . $val['type'] . ']</div></div>';
                $lists .= '<span class="shrink-0">' . $restore . $del . '</span>';
                $lists .= '</div>';
                $count++;
            }
            if ($count > 3) {
                $delete_but = '<a href="javascript:;" ajax-url="' . add_query_arg(array('action' => 'vela_options_backup_delete_surplus', 'key' => 'all'), $admin_ajax_url) . '" data-confirm="确认要删除多余的备份数据吗？删除后不可恢复！" class="but jb-red ajax-get">删除备份 保留最新三份</a>';
            }
            if ($count > 0) {
                $delete_but .= '<a href="javascript:;" ajax-url="' . add_query_arg(array('action' => 'vela_options_backup_delete_all', 'key' => 'all'), $admin_ajax_url) . '" data-confirm="确认要删除全部备份吗？删除后不可恢复！" class="but jb-red ajax-get">删除全部备份</a>';
            }
        }
        $csf[] = array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '<h3 style="color:#fd4c73;"><i class="csf-tab-icon fa fa-fw fa-copy"></i> 备份&恢复</h3>
            <ajaxform class="ajax-form">
            <div style="margin:10px 0">
            <p>系统会在重置、更新等重要操作时自动备份主题设置，您可以此进行恢复备份或手动备份</p>
            <p>恢复备份后，请先保存一次主题设置，然后刷新后再做其它操作！</p>
            <p class="c-yellow">系统最多只能保存20次备份，如需长期保存，请手动下载后存留</p>
            <p class="c-yellow">请注意：恢复非当前网站或非当前主题版本的备份数据，可能会出现异常</p>
            <p><b>备份列表：</b></p>
            <div class="card-box backup-box">
            ' . $lists . '
            </div>
            </div>
            <a href="javascript:;" ajax-url="' . add_query_arg('action', 'vela_options_backup', $admin_ajax_url) . '" class="but jb-blue ajax-get">备份当前配置</a>
            ' . $delete_but . '
            <div class="ajax-notice" style="margin-top: 10px;"></div>
            </ajaxform>',
        );

        $csf[] = array(
            'type'    => 'submessage',
            'style'   => 'warning',
            'content' => '<h3 style="color:#fd4c73;"><i class="csf-tab-icon fa fa-fw fa-copy"></i> 导入&导出</h3>
            <ajaxform class="ajax-form">
            <div style="margin:10px 0">
            <p>您可以在此处将主题配置导出为json文件，同时也可以使用json格式的配置内容进行配置导入，导入时请确保json格式正确</p>
            <textarea ajax-name="import_data" style="width: 100%;min-height: 200px;" placeholder="粘贴导出的json数据以进行导入"></textarea>
            </div>
            <input type="hidden" ajax-name="action" value="vela_options_import">
            <a href="javascript:;" class="but jb-yellow ajax-submit"><i class="fa fa-paper-plane-o"></i> 导入配置</a>
            <a href="' . add_query_arg(array('action' => 'csf-export', 'unique' => $prefix, 'nonce' => wp_create_nonce('csf_backup_nonce')), $admin_ajax_url) . '" class="but jb-green" target="_blank">导出当前配置</a>
            <div class="ajax-notice" style="margin-top: 10px;"></div>
            </ajaxform>',
        );

        return $csf;
    }

}
