<?php
/**
 * 应用程序配置选项
 */

return array(

    'kvServer' => array( /* KV存储 */
        'engine' => 'secache',
        'file' => ROOT_DIR.'/cache/system.db'
    ),
    'dbServer' => 'mysql', /* 数据库 */
    'view' => array(
            'template' => 'default', /* 默认模版 */
            'language' => 'zh-cn' /* 语言包 */
        ),
    'defaultEntry' => 'base/default/welcome', /* 默认入口 */
    'loginEntry' => 'base/default/login', /* 登入页面 */
    'auth' => true, /* 是否开启认证，关闭的话所有页面所有人都可以查看 */
    'defaultApp' => 'base', /* 默认APP，基础服务放在默认APP里面，比如认证，队列，视图插件等 */
    'queueServer' => array( /* 队列服务器 */
        'engine' => 'redis',
        'host' => 'localhost',
        'port' => 2218
    ),
    'staticsServer' => 'http://seephp.com' /* 留空则取本机地址 */

);
