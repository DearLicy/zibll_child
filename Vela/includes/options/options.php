<?php

if (is_admin()) {
    zib_require(array(
        'options-module',
        'action',
        'admin-options',
        'metabox-options',
    ), false, 'includes/options/');
}

//使用Font Awesome 4
add_filter('csf_fa4', '__return_true');

//自定义css、js
function vela_csf_add_custom_wp_enqueue()
{
    // Style
    wp_enqueue_style('csf_custom_css', get_stylesheet_directory_uri() . '/inc/csf-framework/assets/css/style.min.css', array(), THEME_VERSION);
    echo '<div class="floating-elements"><div class="floating-circle"></div><div class="floating-circle"></div><div class="floating-circle"></div></div>';
}

// add_action('csf_enqueue', 'vela_csf_add_custom_wp_enqueue');

//后台底部感谢
function Vela_admin_footer_thank()
{
    return '感谢您使用<a href="https://wordpress.org">WordPress</a>和<a href="https://www.dearlicy.com">Vela</a>进行创作。';
}
add_filter('admin_footer_text', 'Vela_admin_footer_thank', 99999);

/**
 * 从 'vela_options' 数组中检索特定选项的值。
 *
 * @param string $name     选项的名称。
 * @param mixed  $default  如果未找到选项，则返回的默认值。
 * @param string $subname  （可选）如果选项是嵌套数组，则为选项的子名称。
 * @return mixed           选项的值，如果未找到则返回默认值。
 */
function _v($name, $default = false, $subname = '')
{
    // 声明静态变量以加快检索速度
    static $options = null;
    if ($options === null) {
        $options = get_option('vela_options');
    }

    if (isset($options[$name])) {
        if ($subname) {
            return isset($options[$name][$subname]) ? $options[$name][$subname] : $default;
        } else {
            return $options[$name];
        }
    }
    return $default;
}

//获取主题设置链接
function vela_get_admin_csf_url($tab = '')
{
    $tab                = trim(strip_tags($tab));
    $tab_array          = explode('/', $tab);
    $tab_array_sanitize = array();
    foreach ($tab_array as $tab_i) {
        $tab_array_sanitize[] = sanitize_title($tab_i);
    }
    $tab_attr = esc_attr(implode('/', $tab_array_sanitize));
    $url      = add_query_arg('page', 'vela_options', admin_url('admin.php'));
    $url      = $tab ? $url . '#tab=' . $tab_attr : $url;
    return esc_url($url);
}

//备份主题数据
function vela_options_backup($type = '自动备份')
{
    $prefix  = 'vela_options';
    $options = get_option($prefix);

    $options_backup = get_option($prefix . '_backup');
    if (!$options_backup) {
        $options_backup = array();
    }

    $time                  = current_time('Y-m-d H:i:s');
    $options_backup[$time] = array(
        'time' => $time,
        'type' => $type,
        'data' => $options,
    );

    //保留20次数据，删除多余的
    if (count($options_backup) > 20) {
        $options_backup = array_slice($options_backup, -20);
    }

    return update_option($prefix . '_backup', $options_backup);
}

function vela_csf_reset_to_backup()
{
    vela_options_backup('重置全部 自动备份');
}

add_action('csf_vela_options_reset_before', 'vela_csf_reset_to_backup');

function vela_csf_reset_section_to_backup()
{
    vela_options_backup('重置选区 自动备份');
}

add_action('csf_vela_options_reset_section_before', 'vela_csf_reset_section_to_backup');

//主题更新自动备份
function vela_new_vela_to_backup()
{
    $prefix         = 'vela_options';
    $options_backup = get_option($prefix . '_backup');
    $time           = false;

    if ($options_backup) {
        $options_backup = array_reverse($options_backup);
        foreach ($options_backup as $key => $val) {
            if ('更新主题 自动备份' == $val['type']) {
                $time = $key;
                break;
            }
        }
    }

    if (!$time || strtotime($time) < strtotime('-30 minutes', current_time('timestamp'))) {
        vela_options_backup('更新主题 自动备份');

        //更新主题刷新所有缓存
        wp_cache_flush();

        //更新主题，删除更新
        delete_option('vela_new_version');
    }
}
add_action('vela_update_notices', 'vela_new_vela_to_backup');

//定期自动备份
function vela_csf_save_section_to_backup()
{
    $prefix         = 'vela_options';
    $options_backup = get_option($prefix . '_backup');
    $time           = false;

    if ($options_backup) {
        $options_backup = array_reverse($options_backup);
        foreach ($options_backup as $key => $val) {
            if ('定期自动备份' == $val['type']) {
                $time = $key;
                break;
            }
        }
    }
    if (!$time || (floor((strtotime(current_time('Y-m-d H:i:s')) - strtotime($time)) / 3600) > 600)) {
        vela_options_backup('定期自动备份');
    }
}

add_action('csf_vela_options_saved', 'vela_csf_save_section_to_backup');
