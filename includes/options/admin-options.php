<?php

//后台主题设置
function vela_csf_admin_options()
{
    //只有后台才执行此代码
    if (!is_admin()) {
        return;
    }

    $prefix    = 'vela_options';
    $no_create = ((wp_doing_ajax() && (empty($_POST['action']) || !strstr($_POST['action'], 'csf_' . $prefix))) || (!empty($_GET['page']) && $_GET['page'] !== $prefix));

    //开始构建
    CSF::createOptions($prefix, array(
        'menu_title'         => 'Vela主题设置',
        'menu_slug'          => 'vela_options',
        'framework_title'    => 'Vela主题',
        'show_in_customizer' => false, //在wp-customize中也显示相同的选项
        'save_defaults'      => !$no_create, //首次安装自动保存默认设置：全部加载时，再自动保存，避免保存的设置丢失
        'footer_text'        => '更优雅的wordpress主题-Vela主题 V' . wp_get_theme()['Version'],
        'footer_credit'      => '<i class="fa fa-fw fa-heart-o" aria-hidden="true"></i> ',
        'theme'              => 'light',
    ));

    //必须先添加一个，不然后台不显示菜单
    CSF::createSection($prefix, array(
        'id'    => 'basic',
        'title' => '全局&功能',
        'icon'  => 'fa fa-fw fa-bullseye',
    ));

    //页面限制：后台主题配置
    //非自身保存的ajax不执行
    if ($no_create) {
        return;
    }

    //SEO优化
    CSF::createSection($prefix, array(
        'parent'      => 'basic',
        'title'       => 'SEO优化',
        'icon'        => 'fa fa-fw fa-superpowers',
        'description' => '',
        'fields'      => array(
            array(
                'id'      => 'bing_post_on',
                'type'    => 'switcher',
                'title'   => '启用必应推送',
                'default' => false,
            ),
            array(
                'dependency' => array('bing_post_on', '==', 'true'),
                'id'      => 'bing_post_token',
                'type'    => 'text',
                'title'   => '必应API密钥',
                'desc'    => '请前往<a href="https://learn.microsoft.com/en-us/bingwebmaster/getting-access" target="_blank">必应站长工具</a>查看如何获取',
            ),
            array(
                'dependency' => array('bing_post_on', '==', 'true'),
                'title'    => '批量提交链接',
                'id'    => 'bing_post_batch',
                'type'    => 'submessage',
                'style'   => '',
                'content' => Vela_Module::bing_submit(),
            ),
        ),
    ));

    $img = get_stylesheet_directory_uri() . '/img/';

    CSF::createSection($prefix, array(
        'title'  => '备份&导入',
        'icon'   => 'fa fa-fw fa-copy',
        'fields' => Vela_Module::backup(),
    ));
}
vela_csf_admin_options();
