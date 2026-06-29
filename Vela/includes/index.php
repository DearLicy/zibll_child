<?php

/**
 * 获取插件配置项的值
 * 使用示例：$value = vela_get_option('xxx_text', '默认值');
 * @param string $key 配置项的键
 * @param mixed $default 如果配置项不存在，返回的默认值
 * @return mixed 配置项的值或默认值
 */
function vela_get_option($key, $default = false)
{

    // 声明静态变量以加快检索速度
    static $options = null;
    if ($options === null) {
        //定义插件唯一的options储存KEY，需要和vela_csf_admin_options函数中的一致
        $options_key = 'vela_options';
        $options = get_option($options_key);
    }

    return $options[$key] ?? $default;
}

// 载入文件，这是子比主题引入文件的函数调用示例，详见父主题目录下的inc/inc.php第80行zib_require()函数
zib_require(array(
    'includes/options/options', // 配置文件
    'includes/functions/functions', // 功能函数
), true);
