<?php
/**
 * Ruckusing configuration file optimized for Modern PHP 8+ and WordPress 7+
 */

// نیازی به بررسی نسخه PHP 5.3 نیست؛ چون در محیط مدرن اجرا می‌شویم.
// فقط در صورتی که فایل اتولود مدرن وجود دارد آن را لود می‌کنیم (در صورت نیاز ابزار)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

$local = __DIR__ . '/ruckusing.conf.local.php';
if (file_exists($local)) {
    return require $local;
}

// استفاده از اطلاعات پویا و امن دیتابیس وردپرس در صورت در دسترس بودن (Best Practice)
$db_host     = defined('DB_HOST')     ? DB_HOST     : 'localhost';
$db_name     = defined('DB_NAME')     ? DB_NAME     : 'wordpress';
$db_user     = defined('DB_USER')     ? DB_USER     : 'root';
$db_password = defined('DB_PASSWORD') ? DB_PASSWORD : '';
$db_charset  = defined('DB_CHARSET')  ? DB_CHARSET  : 'utf8mb4'; // تغییر به utf8mb4 که در وردپرس‌های جدید استاندارد است

// مدیریت پیشوندهای جدول دیتابیس
global $wpdb;
$wpPrefix  = isset($wpdb) ? $wpdb->prefix : 'wp_';
$wp0Prefix = $wpPrefix; 
$wptPrefix = $wpPrefix . 't_'; // ایجاد پیشوند اختصاصی برای پلاگین (مثلاً wp_t_)

defined('WP_PLUGIN_URL')          || define('WP_PLUGIN_URL', '/wp-content/plugins');
defined('RUCKUSING_WORKING_BASE') || define('RUCKUSING_WORKING_BASE', __DIR__);

$databaseDirectory = RUCKUSING_WORKING_BASE;

// تفکیک پورت از Host در صورت وجود (پشتیبانی از هاست‌هایی مثل localhost:3307)
$port = 3306;
if (strpos($db_host, ':') !== false) {
    list($db_host, $port) = explode(':', $db_host, 2);
    $port = (int) $port;
}

return [
    'db' => [
        'development' => [
            'type'                      => 'mysql',
            'host'                      => $db_host,
            'port'                      => $port,
            'database'                  => $db_name,
            'directory'                 => 'wp_testing',
            'user'                      => $db_user,
            'password'                  => $db_password,
            'charset'                   => $db_charset,
            'globalPrefix'              => $wp0Prefix,
            'blogPrefix'                => $wpPrefix,
            'pluginPrefix'              => $wptPrefix,
            'schema_version_table_name' => $wptPrefix . 'schema_migrations',
        ],
    ],
    'db_dir'         => $databaseDirectory,
    'migrations_dir' => ['default' => $databaseDirectory . '/migrations'],
    'log_dir'        => $databaseDirectory . '/log',
];
