<?php

/**
 * 提交文章或术语链接至必应搜索引擎
 *
 * @param string|array $url 单个URL字符串或多个URL组成的数组
 * @return array 包含提交结果信息的关联数组
 */
function vela_bing_resource_submission($url) {
    if (!is_array($url)) {
        $urls = [$url];
    } else {
        $urls = $url;
    }

    // 检查设置选项和API密钥
    if (!_v('bing_post_on') || !_v('bing_post_token') || empty($urls)) {
        return [];
    }

    $api_key     = _v('bing_post_token');
    $site        = home_url();
    // 注意：请根据实际的API文档确认具体的API端点
    $api_url     = 'https://ssl.bing.com/webmaster/api.svc/json/SubmitUrlbatch?apikey=' . urlencode($api_key);
    $result_meta = [];

    // 构造请求体
    $body = json_encode([
        'siteUrl' => $site,
        'urlList' => $urls
    ]);

    // 使用WP_Http类发送POST请求
    $response = wp_remote_post($api_url, [
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'body'    => $body,
        'timeout' => 30, // 设置合理的超时时间
    ]);

    // 处理响应
    if (is_wp_error($response)) {
        $result_meta['error'] = $response->get_error_message();
        return $result_meta;
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $body      = wp_remote_retrieve_body($response);

    if ($http_code === 200) {
        $result_meta['success'] = true;
        $result_meta['message'] = 'URL提交成功！';
    } else {
        $result_meta['success'] = false;
        $result_meta['message'] = "URL提交失败！HTTP状态码: $http_code";

        // 解析API返回的错误信息
        if (!empty($body)) {
            $error_info = json_decode($body, true);
            if (isset($error_info['ErrorCode']) && isset($error_info['Message'])) {
                $result_meta['details'] = "错误代码: {$error_info['ErrorCode']}，消息: {$error_info['Message']}";
            } else {
                $result_meta['details'] = $body;
            }
        } else {
            $result_meta['details'] = '接口未返回额外数据';
        }
    }

    $result_meta['update_time'] = current_time("Y-m-d H:i:s");

    return $result_meta;
}
add_action('save_post', 'vela_post_bing_resource_submission'); // 触发文章提交
add_action('saved_term', 'vela_term_bing_resource_submission'); // 触发分类目录提交

/**
 * 文章保存后提交到必应
 *
 * @param int $post_id 文章ID
 */
function vela_post_bing_resource_submission($post_id) {

    if (_v('bing_post_on') && _v('bing_post_token') == '') {
        return;
    }

    $post = get_post($post_id);
    if (empty($post->ID) || 'publish' !== $post->post_status) {
        return;
    }

    // 重新提交逻辑
    if (!empty($_POST['bing_post_resubmit'])) {
        zib_update_post_meta($post_id, 'bing_tui_back', false);
    }

    $ok = zib_get_post_meta($post_id, 'bing_tui_back', true);

    if (!empty($ok['success'])) {
        return;
    }

    $plink = get_permalink($post_id);
    $bing_result = vela_bing_resource_submission($plink);
    zib_update_post_meta($post_id, 'bing_tui_back', $bing_result);
}

/**
 * 分类目录保存后提交到必应
 *
 * @param int $term_id 分类目录ID
 */
function vela_term_bing_resource_submission($term_id) {

    if (_v('bing_post_on') && _v('bing_post_token') == '') {
        return;
    }

    // 重新提交逻辑
    if (!empty($_POST['bing_post_resubmit'])) {
        zib_update_term_meta($term_id, 'bing_tui_back', false);
    }

    $ok = zib_get_term_meta($term_id, 'bing_tui_back', true);

    if (!empty($ok['success'])) {
        return;
    }

    $plink = get_term_link($term_id);
    $bing_result = vela_bing_resource_submission($plink);
    zib_update_term_meta($term_id, 'bing_tui_back', $bing_result);
}
