<?php

// 必应推送
function vela_page_main_meta_bing($post_id)
{
    // 检查是否是自动保存
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 检查权限
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 如果勾选了重新推送，则清空之前的数据
    if (!empty($_POST['bing_post_resubmit'])) {
        zib_update_post_meta($post_id, 'bing_tui_back', false);
    }
}

add_action('save_post', 'vela_page_main_meta_bing');

if (_v('bing_post_on') && _v('bing_post_token')) {
    CSF::createTaxonomyOptions('term_bing_resource_submission', array(
        'title'     => '必应资源提交',
        'taxonomy'  => ['category', 'post_tag', 'topics', 'plate_cat', 'forum_topic', 'forum_tag'],
        'data_type' => 'unserialize',
    ));
    CSF::createSection('term_bing_resource_submission', array(
        'fields' => array(
            array(
                'title'   => __('必应资源提交', 'zib_language'),
                'type'    => 'content',
                'content' => vela_get_bing_resource_submission_metabox(false),
            ),
        ),
    ));
    CSF::createMetabox('bing_resource_submission', array(
        'title'     => '必应资源提交',
        'post_type' => array('post', 'page', 'plate', 'forum_post'),
        'context'   => 'advanced',
        'data_type' => 'unserialize',
    ));
    CSF::createSection('bing_resource_submission', array(
        'fields' => array(
            array(
                'title'   => __('必应资源提交', 'zib_language'),
                'type'    => 'content',
                'content' => vela_get_bing_resource_submission_metabox(),
            ),
        ),
    ));
}

function vela_get_bing_resource_submission_metabox($is_post = true)
{
    if ($is_post) {
        if (isset($_GET['post'])) {
            $post_id = (int)$_GET['post'];
        } elseif (isset($_POST['post_ID'])) {
            $post_id = (int)$_POST['post_ID'];
        } else {
            $post_id = 0;
        }
        $tui = zib_get_post_meta($post_id, 'bing_tui_back', true);
    } else {
        if (isset($_GET['tag_ID'])) {
            $term_id = (int)$_GET['tag_ID'];
        } else {
            $term_id = 0;
        }
        $tui = zib_get_term_meta($term_id, 'bing_tui_back', true);
    }

    $Resubmit  = '';
    $show_text = '';

    if (!empty($tui['success'])) {
        $show_text .= '<strong>提交状态：成功</strong><br>';
    } elseif (isset($tui['success']) && false == $tui['success']) {
        $show_text .= '<strong>提交状态：失败</strong><br>';
    }

    if (!empty($tui['message'])) {
        $show_text .= '<strong>消息：</strong>' . esc_html($tui['message']) . '<br>';
    }

    if (!empty($tui['update_time'])) {
        $show_text .= '<strong>更新时间：</strong>' . esc_html($tui['update_time']) . '<br>';
        $Resubmit = '<span style="margin:0 20px 15px 0; display:inline-block;"><label><input type="checkbox" name="bing_post_resubmit"> 重新提交</label></span>';
    }

    if ($show_text) {
        $show_text = '<div>提交结果:</div>' . $show_text;
    } else {
        $show_text = '发布、更新后刷新页面后可查看提交结果';
    }

    return $Resubmit . $show_text;
}
