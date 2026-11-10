<?php
declare(strict_types=1);

return [
    'title' => 'Cookie 政策',
    'updated' => '2026年11月10日',
    'sections' => [
        [
            'heading' => '1. 本政策涵盖范围',
            'body' => [
                '本政策说明 pontelog.com 使用的 Cookie 与本地存储，且仅描述网站当前实际做法。',
            ],
        ],
        [
            'heading' => '2. 我们使用的 Cookie 与技术',
            'body' => [
                '必要（PHP 会话）：用于安全运行联系表单（CSRF 防护与重复发送限制）。在浏览器会话结束时或按服务器配置过期。',
                '语言偏好（pontelog_lang Cookie）：记住您选择的语言。大约保存 1 年。',
                'Cookie 提示偏好（cookie 和/或本地存储中的 pontelog_consent）：保存您是否接受或限制非必要 Cookie。大约保存 180 天。',
            ],
        ],
        [
            'heading' => '3. 我们当前不使用的内容',
            'body' => [
                '本网站不安装网站分析 Cookie、广告网络 Cookie，也不使用第三方跟踪像素进行受众测量或行为广告。',
                '通讯订阅不依赖 Cookie 横幅：只有在联系表单中勾选相应选项时才会发生。',
            ],
        ],
        [
            'heading' => '4. 查看地图时的第三方内容',
            'body' => [
                '若加载带地图的联系区块，浏览器会向 OpenStreetMap 请求地图瓦片。该提供商可能产生技术日志（例如 IP 与请求资源）。这些不是 Pontelog 的 Cookie。',
            ],
        ],
        [
            'heading' => '5. 同意与管理',
            'body' => [
                '必要 Cookie 用于网站运行。偏好 Cookie 会在您使用语言切换或 Cookie 提示时保存。',
                '您可在浏览器中删除 Cookie 与本地存储。若删除必要 Cookie，联系表单可能无法正常提交。',
                '隐私问题请联系：info@pontelog.com。',
            ],
        ],
    ],
];
