# Vela 子主题框架

Vela 是面向子比主题（Zibll）的官方子主题开发框架，用于承载站点级样式、模板覆盖、主题设置与功能增强。它适合需要长期维护的站点方案，也适合作为子比主题二次开发的标准起点。

> 当前版本：`0.0.1`

## 适用场景

- 覆盖或扩展子比主题模板。
- 维护整站样式、页面结构和主题级配置。
- 开发与当前站点强绑定的功能模块。
- 承载支付、订单、商品、佣金、提现等页面模板扩展。
- 作为团队项目的子主题开发基线。

如果功能需要独立安装、停用、分发给多个站点复用，建议优先使用插件框架：[DearLicy/zibll_plugin](https://github.com/DearLicy/zibll_plugin)。

## 运行要求

| 项目 | 要求 |
| --- | --- |
| WordPress | 5.0 或更高版本 |
| PHP | 7.0 - 8.2 |
| 父主题 | Zibll 子比主题 |
| 子主题版本 | 0.0.1 |

`style.css` 中已声明：

```css
Template: zibll
```

因此启用 Vela 前，请先安装并保留子比主题。

## 安装方式

1. 打开仓库页面：[DearLicy/zibll_child](https://github.com/DearLicy/zibll_child)。
2. 点击 `Code`，选择 `Download ZIP`。
3. 将下载后的压缩包解压或上传到 `wp-content/themes/`。
4. 确认主题目录内直接包含 `functions.php` 和 `style.css`。
5. 在 WordPress 后台进入 `外观 -> 主题`，启用 `Vela`。

建议目录名使用 `vela`：

```text
wp-content/themes/vela/
├── functions.php
├── style.css
├── includes/
└── zibpay/
```

## 目录结构

```text
vela/
├── functions.php
├── style.css
├── includes/
│   ├── index.php
│   ├── functions/
│   │   ├── functions.php
│   │   └── vela-theme.php
│   └── options/
│       ├── action.php
│       ├── admin-options.php
│       ├── metabox-options.php
│       ├── options.php
│       └── options-module.php
└── zibpay/
    └── page/
        ├── charge-card.php
        ├── coupon.php
        ├── income.php
        ├── index.php
        ├── order.php
        ├── product.php
        ├── rebate.php
        ├── shop.php
        ├── withdraw.php
        └── template/
```

## 加载流程

Vela 从 `functions.php` 开始加载：

```php
require_once get_theme_file_path('/inc/inc.php');
require_once get_theme_file_path('/includes/index.php');
```

加载顺序为：

1. 加载子比主题核心能力。
2. 加载 Vela 子主题核心入口。
3. 通过 `zib_require()` 加载设置模块和功能模块。
4. 如根目录存在 `func.php`，自动载入站点自定义代码。

`func.php` 适合放置少量站点级自定义代码。复杂功能建议拆分到 `includes/functions/` 中维护。

## 配置函数

Vela 后台配置统一保存在 `vela_options`。

读取普通配置：

```php
$value = vela_get_option('option_key', '默认值');
```

框架内部也提供 `_v()`：

```php
$enabled = _v('bing_post_on');
$token   = _v('bing_post_token', '');
```

读取嵌套配置：

```php
$value = _v('group_key', '默认值', 'sub_key');
```

## 后台设置

Vela 使用 Codestar Framework 创建后台设置页：

- 菜单标题：`Vela主题设置`
- 菜单标识：`vela_options`
- 配置保存 key：`vela_options`

当前内置设置能力：

- 全局与功能分组。
- SEO 优化。
- 必应 URL 推送。
- 主题设置备份、导入、恢复与清理。

## 内置功能

### 必应 URL 推送

核心函数：

```php
vela_bing_resource_submission($url);
```

支持传入单个 URL 或 URL 数组。启用后，框架会在文章保存和分类目录保存时尝试提交链接到必应，并将结果写入元数据，避免成功提交后重复提交。

相关 Hook：

```php
add_action('save_post', 'vela_post_bing_resource_submission');
add_action('saved_term', 'vela_term_bing_resource_submission');
```

### 设置备份

核心函数：

```php
vela_options_backup('手动备份');
```

框架会保存最近 20 份配置备份，并在重置设置、导入设置、主题更新提醒等关键操作前自动创建备份。

### AJAX 操作

当前注册的后台 AJAX action：

| Action | 作用 |
| --- | --- |
| `bulk_bing_url_submission` | 批量提交 URL 到必应。 |
| `vela_options_import` | 导入主题设置。 |
| `vela_options_backup` | 创建主题设置备份。 |
| `vela_options_backup_delete` | 删除指定备份。 |
| `vela_options_backup_delete_all` | 删除全部备份。 |
| `vela_options_backup_delete_surplus` | 仅保留最新 3 份备份。 |
| `vela_options_backup_restore` | 恢复指定备份。 |

## 开发建议

- 不直接修改子比主题核心文件。
- 新增函数、类、选项 key 使用唯一前缀。
- 模板文件只负责展示与轻量编排，复杂逻辑放入功能模块。
- 输出前做好转义，写操作前做好权限、nonce 和参数校验。
- 发布版本前更新 `style.css` 中的 `Version`。
- 涉及设置结构变化时，先创建配置备份。

## 文档

子比主题开发文档正在建设中。后续会在开发文档中持续补充 Vela 子主题开发、插件开发、钩子、函数、AJAX 和 JS 接口说明。

## Release 声明

`v0.0.1` 是 Vela 子主题框架的初始公开版本，主要用于建立子主题开发基线，包含基础加载结构、后台设置框架、配置备份能力、必应推送示例和支付相关页面模板。