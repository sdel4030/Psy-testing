<?php
/**
 * Optimized Bootstrap Initializer for WordPress 7+
 * Replaces legacy dirname(__FILE__) calls with native high-performance __DIR__ constant.
 */

if (!defined('ABSPATH')) {
    exit; // شرط حفاظتی جهت جلوگیری از اجرای مستقیم فایل توسط هکرها خارج از محیط وردپرس
}

// 1. Core WordPress Abstraction Interfaces
require_once __DIR__ . '/WordPress/IPriority.php';
require_once __DIR__ . '/WordPress/IOption.php';
require_once __DIR__ . '/WordPress/IPostMeta.php';
require_once __DIR__ . '/WordPress/IPath.php';

// 2. Facade Architecture & Addon Bridges
require_once __DIR__ . '/Addon/IWordPressFacade.php';
require_once __DIR__ . '/WordPressFacade.php';
require_once __DIR__ . '/Facade/IORM.php';
require_once __DIR__ . '/Addon/IFacade.php';
require_once __DIR__ . '/Facade/ITestPasser.php';

// 3. Central Access Point
require_once __DIR__ . '/Facade.php';
