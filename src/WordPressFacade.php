<?php

/**
 * Facade into WordPress
 * Optimized for PHP 8.x and WordPress 7+ (Part 1)
 */
class WpTesting_WordPressFacade implements WpTesting_Addon_IWordPressFacade
{
    /**
     * Plugin filename (required for hooks)
     * @var string|null
     */
    private $pluginFile = null;

    /**
     * @param string $pluginFile Plugin filename (required for hooks)
     */
    public function __construct($pluginFile)
    {
        $this->setPluginFile($pluginFile);
    }

    /**
     * Creates a duplicate instance
     * @param string $pluginFile
     * @return WpTesting_WordPressFacade
     */
    public function duplicate($pluginFile)
    {
        $class = get_class($this);
        return new $class($pluginFile);
    }

    /**
     * @return string
     */
    public function getDbHost()
    {
        return defined('DB_HOST') ? DB_HOST : '';
    }

    /**
     * @return string
     */
    public function getDbName()
    {
        return defined('DB_NAME') ? DB_NAME : '';
    }

    /**
     * @return string
     */
    public function getDbUser()
    {
        return defined('DB_USER') ? DB_USER : '';
    }

    /**
     * @return string
     */
    public function getDbPassword()
    {
        return defined('DB_PASSWORD') ? DB_PASSWORD : '';
    }

    /**
     * @return wpdb
     */
    private function getDb()
    {
        // در لایه‌های مدرن وردپرس، نیاز به فراخوانی مکرر require_wp_db نیست مگر در لودهای بسیار زودهنگام
        if (!isset($GLOBALS['wpdb']) && function_exists('require_wp_db')) {
            require_wp_db();
        }
        return $GLOBALS['wpdb'];
    }

    /**
     * @return string
     */
    public function getGlobalTablePrefix()
    {
        return $this->isMultisite()
            ? $this->getDb()->base_prefix
            : $this->getTablePrefix();
    }

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $GLOBALS['table_prefix'] ?? 'wp_';
    }

    /**
     * @return string
     */
    public function getDbCharset()
    {
        return defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsPath()
    {
        return defined('ABSPATH') ? ABSPATH : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPluginDir()
    {
        return defined('WP_PLUGIN_DIR') ? WP_PLUGIN_DIR : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getTempDir()
    {
        return get_temp_dir();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentDir()
    {
        return defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : '';
    }

    /**
     * {@inheritdoc}
     */
    public function appendSlash($path)
    {
        return trailingslashit($path);
    }

    /**
     * Holds the WordPress Rewrite object for creating pretty URLs
     * @return WP_Rewrite|null
     */
    public function getRewrite()
    {
        return $GLOBALS['wp_rewrite'] ?? null;
    }

    /**
     * WordPress Object
     * @return WP|null
     */
    public function getWP()
    {
        return $GLOBALS['wp'] ?? null;
    }

    /**
     * Main WordPress Query
     * @return WP_Query|null
     */
    public function getMainQuery()
    {
        return $GLOBALS['wp_the_query'] ?? null;
    }

    /**
     * Is passed query the main query?
     * @param WP_Query $query
     * @return boolean
     */
    public function isQueryMain($query)
    {
        return $query === $this->getMainQuery();
    }

    /**
     * Current WordPress Query
     * @return WP_Query|null
     */
    public function getQuery()
    {
        return $GLOBALS['wp_query'] ?? null;
    }

    /**
     * Removes an item or list from the query string.
     */
    public function removeQueryArgument($argument, $uri = false)
    {
        return remove_query_arg($argument, $uri);
    }

    /**
     * Retrieve referer from '_wp_http_referer' or HTTP referer.
     */
    public function getReferer()
    {
        return wp_get_referer();
    }

    /**
     * Retrieve the ID of the current post (Fully Safeguarded for PHP 8+)
     * @return integer
     */
    public function getCurrentPostId()
    {
        if (isset($GLOBALS['post']) && $GLOBALS['post'] instanceof WP_Post) {
            return (int) $GLOBALS['post']->ID;
        }
        
        // بک‌آپ بومی وردپرس در صورتی که متغیر گلوبال در آن لحظه ست نشده باشد
        return function_exists('get_the_ID') ? (int) get_the_ID() : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPostMeta($key)
    {
        return $this->getPostMeta($this->getCurrentPostId(), $key, true);
    }

    /**
     * Retrieve a post status object by name.
     */
    public function getPostStatusObject($postStatus)
    {
        return get_post_status_object($postStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostMeta($postId, $key, $isSingle)
    {
        return get_post_meta($postId, $key, $isSingle);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePostMeta($postId, $key, $value, $previousValue = '')
    {
        return update_post_meta($postId, $key, $value, $previousValue);
    }

    /**
     * Get the current user's ID
     */
    public function getCurrentUserId()
    {
        return get_current_user_id();
    }

    /**
     * Whether current user has capability or role.
     */
    public function isCurrentUserCan($capability)
    {
        return current_user_can($capability);
    }

    /**
     * Retrieve user info by user ID.
     */
    public function getUserdata($userId)
    {
        return get_userdata($userId);
    }

    /**
     * Retrieve user meta field for a user.
     */
    public function getUserMeta($userId, $key = '', $isSingle = false)
    {
        return get_user_meta($userId, $key, $isSingle);
    }

    /**
     * Retrieve the avatar for a user.
     */
    public function getAvatar($idOrEmail, $size = 96, $default = '', $alt = false)
    {
        return get_avatar($idOrEmail, $size, $default, $alt);
    }

    /**
     * Retrieve edit user link
     */
    public function getEditUserLink($userId = null)
    {
        if (function_exists('get_edit_user_link')) {
            return get_edit_user_link($userId);
        }

        $currentUserId = $this->getCurrentUserId();
        if (is_null($userId)) {
            $userId = $currentUserId;
        }

        if (empty($userId) || !current_user_can('edit_user', $userId)) {
            return '';
        }

        $user = $this->getUserdata($userId);
        if (!$user || !isset($user->ID)) {
            return '';
        }

        if ($currentUserId == $user->ID) {
            $link = get_edit_profile_url($user->ID);
        } else {
            $link = add_query_arg('user_id', $user->ID, self_admin_url('user-edit.php'));
        }

        // بررسی وجود متد اختیاری فیلترها جهت جلوگیری از خطا در پارت‌های جلوتر
        return method_exists($this, 'applyFilters') 
            ? $this->applyFilters('get_edit_user_link', $link, $user->ID)
            : apply_filters('get_edit_user_link', $link, $user->ID);
    }/**
     * Retrieves the URL to the admin area for the current site.
     *
     * @param string $path   Optional path relative to the admin URL.
     * @param string $scheme The scheme to use.
     * @return string Admin URL link with optional path appended.
     */
    public function adminUrl($path = '', $scheme = 'admin')
    {
        return admin_url($path, $scheme);
    }

    /**
     * Sets admin page title.
     *
     * @param string $title
     * @return void
     */
    public function setAdminPageTitle($title)
    {
        $GLOBALS['title'] = $title;
    }

    /**
     * The WordPress version string
     *
     * @return string
     */
    public function getVersion()
    {
        return $GLOBALS['wp_version'] ?? '7.0.0';
    }

    /**
     * Retrieve a URL within the plugin
     *
     * @param string $pluginRelatedPath
     * @return string
     */
    public function getPluginUrl($pluginRelatedPath = '')
    {
        return plugins_url($pluginRelatedPath, $this->pluginFile);
    }

    /**
     * Gets the basename of a plugin.
     *
     * @param string|null $file
     * @return string
     */
    public function getPluginBaseName($file = null)
    {
        if (is_null($file)) {
            $file = $this->pluginFile;
        }
        return plugin_basename($file);
    }

    /**
     * Check the plugins directory and retrieve all plugin files with plugin data.
     *
     * @param string $pluginFolder
     * @return array
     */
    public function getPlugins($pluginFolder = '')
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return get_plugins($pluginFolder);
    }

    /**
     * Sanitizes a title, or returns a fallback title.
     */
    public function sanitizeTitle($title, $fallbackTitle = '', $context = 'save')
    {
        return sanitize_title($title, $fallbackTitle, $context);
    }

    protected function setPluginFile($pluginFile)
    {
        $this->pluginFile = $this->guessPluginFilePath($pluginFile);
        return $this;
    }

    /**
     * @param string $pluginFile
     * @return string
     */
    private function guessPluginFilePath($pluginFile)
    {
        if (!defined('WP_PLUGIN_DIR')) {
            return $pluginFile;
        }

        $pluginFileParts = explode(DIRECTORY_SEPARATOR, $pluginFile);
        if (count($pluginFileParts) <= 2) {
            return $pluginFile;
        }

        // رفع خطای PHP 8+ برای استفاده از تابع end() روی خروجی مستقیم تابع
        $lastPart = end($pluginFileParts);
        if ('src' === $lastPart) {
            array_pop($pluginFileParts);
            $secondLastPart = end($pluginFileParts);
            $pluginFileParts[] = $secondLastPart . '.php';
            $pluginFile = implode(DIRECTORY_SEPARATOR, $pluginFileParts);
        }

        $pluginBaseName = implode(DIRECTORY_SEPARATOR, array_slice($pluginFileParts, -2));
        $candidate      = rtrim(WP_PLUGIN_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pluginBaseName;
        if (file_exists($candidate) && realpath($candidate) === $pluginFile) {
            return $candidate;
        }

        return $pluginFile;
    }

    /**
     * Enqueue a CSS stylesheet.
     */
    public function enqueueStyle($name, $src = false, $dependencies = array(), $version = false, $media = 'all')
    {
        wp_enqueue_style($name, $src, $dependencies, $version, $media);
        return $this;
    }

    /**
     * Enqueue a CSS stylesheet related to plugin path.
     */
    public function enqueuePluginStyle($name, $pluginRelatedPath)
    {
        return $this->enqueueStyle($name, $this->getPluginUrl($pluginRelatedPath));
    }

    /**
     * Enqueue an JS script related to plugin path.
     */
    public function enqueuePluginScript($name, $pluginRelatedPath, array $dependencies = array(), $version = false, $isInFooter = false)
    {
        $path = $this->getPluginUrl($pluginRelatedPath);
        wp_enqueue_script($name, $path, $dependencies, $version, $isInFooter);
        return $this;
    }

    /**
     * Register an JS script related to plugin path.
     */
    public function registerPluginScript($name, $pluginRelatedPath, array $dependencies = array(), $version = false, $isInFooter = false)
    {
        $path = $this->getPluginUrl($pluginRelatedPath);
        return $this->registerScript($name, $path, $dependencies, $version, $isInFooter);
    }

    /**
     * Register new JavaScript file.
     */
    public function registerScript($name, $path, array $dependencies = array(), $version = false, $isInFooter = false)
    {
        wp_register_script($name, $path, $dependencies, $version, $isInFooter);
        return $this;
    }

    /**
     * Get WP_Scripts instance or create it if needed
     *
     * @return WP_Scripts
     */
    public function getScripts()
    {
        if (!isset($GLOBALS['wp_scripts']) || !($GLOBALS['wp_scripts'] instanceof WP_Scripts)) {
            $GLOBALS['wp_scripts'] = new WP_Scripts();
        }

        return $GLOBALS['wp_scripts'];
    }

    /**
     * Safely load WordPress core admin classes
     */
    public function loadClass($className)
    {
        if ('WP_List_Table' === $className && !class_exists('WP_List_Table')) {
            if (file_exists(ABSPATH . 'wp-admin/includes/class-wp-screen.php')) {
                require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');
            }
            if (file_exists(ABSPATH . 'wp-admin/includes/template.php')) {
                require_once(ABSPATH . 'wp-admin/includes/template.php');
            }
            if (file_exists(ABSPATH . 'wp-admin/includes/screen.php')) {
                require_once(ABSPATH . 'wp-admin/includes/screen.php');
            }
            if (file_exists(ABSPATH . 'wp-admin/includes/class-wp-list-table.php')) {
                require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
            }
        }
    }

    /**
     * Determine if SSL is used.
     */
    public function isSsl()
    {
        return is_ssl();
    }

    /**
     * Remove a registered script
     */
    public function deregisterScript($name)
    {
        wp_deregister_script($name);
        return $this;
    }/**
     * Loads the plugin's translated strings.
     *
     * If the path is not given then it will be the root of the plugin directory.
     * The .mo file should be named based on the domain with a dash, and then the locale exactly.
     *
     * @param string $domain Unique identifier for retrieving translated strings
     * @param string|bool $absoluteRelativePath Deprecated, but still functional.
     * @param string|bool $pluginsRelativePath Relative path to WP_PLUGIN_DIR.
     * @return boolean
     */
    public function loadPluginTextdomain($domain, $absoluteRelativePath = false, $pluginsRelativePath = false)
    {
        return load_plugin_textdomain($domain, $absoluteRelativePath, $pluginsRelativePath);
    }

    /**
     * Localize a script.
     *
     * Works only if the script has already been added.
     *
     * @param string $handle      Script handle the data will be attached to.
     * @param string $objectName  Name for the JavaScript object.
     * @param array $l10n         The data itself.
     * @return bool True if the script was successfully localized, false otherwise.
     */
    public function localizeScript($handle, $objectName, $l10n)
    {
        return wp_localize_script($handle, $objectName, $l10n);
    }

    /**
     * Retrieve the name of the highest priority template file that exists.
     *
     * @param string|array $templateNames Template file(s) to search for, in order.
     * @param bool $isLoad  If true the template file will be loaded if it is found.
     * @param bool $isRequireOnce Whether to require_once or require. Default true.
     * @return string The template filename if one is located.
     */
    public function locateTemplate($templateNames, $isLoad = false, $isRequireOnce = true)
    {
        return locate_template($templateNames, $isLoad, $isRequireOnce);
    }

    /**
     * Retrieve full permalink for post by ID or current post
     *
     * @param integer|WP_Post $id
     * @param bool $isLeaveName
     * @return string|bool
     */
    public function getPermalink($id = 0, $isLeaveName = false)
    {
        return get_permalink($id, $isLeaveName);
    }

    /**
     * Retrieve the permalink for a post with a custom post type.
     *
     * @param int $id Optional. Post ID.
     * @param bool $isLeavename Optional.
     * @param bool $isSample Optional.
     * @return string The post permalink.
     */
    public function getPostPermalink($id = 0, $isLeavename = false, $isSample = false)
    {
        return get_post_permalink($id, $isLeavename, $isSample);
    }

    /**
     * Retrieve edit posts link for post.
     *
     * @param int $id Optional. Post ID.
     * @param string $context Optional, defaults to display.
     * @return string The edit post link for the given post.
     */
    public function getEditPostLink($id = 0, $context = 'display')
    {
        return get_edit_post_link($id, $context);
    }

    /**
     * Retrieve edit term url.
     *
     * @param int $id Term ID
     * @param string $taxonomy Taxonomy
     * @param string $objectType The object type
     * @return string The edit term link URL for the given term.
     */
    public function getEditTermLink($id, $taxonomy, $objectType = '')
    {
        return get_edit_term_link($id, $taxonomy, $objectType);
    }

    /**
     * Get extended entry info ().
     *
     * @param string $post Post content.
     * @return array Post before ('main'), after ('extended'), and custom readmore ('more_text').
     */
    public function getExtended($post)
    {
        return get_extended($post);
    }

    /**
     * Redirects to another page.
     *
     * @param string $location The path to redirect to.
     * @param int $status Status code to use.
     * @return bool False if $location is not provided, true otherwise.
     */
    public function redirect($location, $status = 302)
    {
        return wp_redirect($location, $status);
    }

    /**
     * Performs a safe (local) redirect, using redirect().
     */
    public function safeRedirect($location, $status = 302)
    {
        return wp_safe_redirect($location, $status);
    }

    /**
     * Send mail, similar to PHP's mail
     */
    public function mail($to, $subject, $message, $headers = '', $attachments = array())
    {
        return wp_mail($to, $subject, $message, $headers, $attachments);
    }

    /**
     * Get salt to add to hashes.
     *
     * @param string $scheme Authentication scheme (auth, secure_auth, logged_in, nonce)
     * @return string Salt value
     */
    public function getSalt($scheme = 'auth')
    {
        return wp_salt($scheme);
    }

    /**
     * Hooks a function on to a specific action.
     */
    public function addAction($tag, $function, $priority = 10, $functionArgsCount = 1)
    {
        // در صورتی که ثابت کلاس تعریف نشده باشد از مقدار پیش‌فرض 10 استفاده می‌شود
        $fallbackPriority = defined('self::PRIORITY_DEFAULT') ? self::PRIORITY_DEFAULT : 10;
        $actualPriority = ($priority === 'self::PRIORITY_DEFAULT' || $priority === null) ? $fallbackPriority : $priority;

        add_action($tag, $function, $actualPriority, $functionArgsCount);
        return $this;
    }

    /**
     * Execute functions hooked on a specific action hook.
     * Fully Optimized for PHP 8.x using Variadic Arguments natively
     */
    public function doAction($tag, ...$args)
    {
        do_action($tag, ...$args);
        return null;
    }

    /**
     * Retrieve the number times an action is fired.
     */
    public function didAction($tag)
    {
        return did_action($tag);
    }

    /**
     * Removes a function from a specified action hook.
     */
    public function removeAction($tag, $function, $priority = 10)
    {
        remove_action($tag, $function, $priority);
        return $this;
    }

    /**
     * Hooks a function or method to a specific filter action.
     */
    public function addFilter($tag, $function, $priority = 10, $functionArgsCount = 1)
    {
        $fallbackPriority = defined('self::PRIORITY_DEFAULT') ? self::PRIORITY_DEFAULT : 10;
        $actualPriority = ($priority === 'self::PRIORITY_DEFAULT' || $priority === null) ? $fallbackPriority : $priority;

        add_filter($tag, $function, $actualPriority, $functionArgsCount);
        return $this;
    }

    /**
     * Check if any filter has been registered for a hook.
     */
    public function hasFilter($tag, $function = false)
    {
        return has_filter($tag, $function);
    }

    /**
     * Adds filter once
     */
    public function addFilterOnce($tag, $function, $priority = 10, $functionArgsCount = 1)
    {
        if ($this->hasFilter($tag, $function) !== false) {
            return $this;
        }
        return $this->addFilter($tag, $function, $priority, $functionArgsCount);
    }
    
    /**
     * Removes a function from a specified filter hook.
     *
     * @param string $tag
     * @param callable $functionToRemove
     * @param int $priority
     * @param int $acceptedArgs
     * @return WpTesting_WordPressFacade
     */
    public function removeFilter($tag, $functionToRemove, $priority = 10, $acceptedArgs = 1)
    {
        remove_filter($tag, $functionToRemove, $priority, $acceptedArgs);
        return $this;
    }

    /**
     * Call the functions added to a filter hook.
     * Fully Optimized for PHP 8.x natively using Variadic Arguments
     */
    public function applyFilters($tag, $value, ...$args)
    {
        return apply_filters($tag, $value, ...$args);
    }

    /**
     * Add hook for shortcode tag.
     */
    public function addShortcode($tag, $function)
    {
        add_shortcode($tag, $function);
        return $this;
    }

    /**
     * Search content for shortcodes and filter shortcodes through their hooks.
     */
    public function doShortcode($content, $ignoreHtml = false)
    {
        // در وردپرس‌های جدید برای بهینه‌سازی بهتر است از تابع استاندارد تری بهره برده شود اما این تابع همچنان سازگار است
        return do_shortcode($content, $ignoreHtml);
    }

    /**
     * Removes hook for shortcode.
     */
    public function removeShortcode($tag)
    {
        remove_shortcode($tag);
        return $this;
    }

    /**
     * Combine user attributes with known attributes and fill in defaults when needed.
     */
    public function sanitazeShortcodeAttributes($defaults, $attributes, $shortcode = '')
    {
        return shortcode_atts($defaults, $attributes, $shortcode);
    }

    /**
     * Add a meta box to an edit form.
     */
    public function addMetaBox($id, $title, $function, $screen = null, $context = 'advanced', $priority = 'default', $functionArgs = null)
    {
        add_meta_box($id, $title, $function, $screen, $context, $priority, $functionArgs);
        return $this;
    }

    /**
     * Get metaboxes by provided screen, context and priority
     */
    public function getMetaBoxes($screen = null, $context = 'advanced', $priority = 'default')
    {
        return $this->processMetaBoxes($screen, $context, $priority, 'getMetaBoxes', null);
    }

    /**
     * Set metaboxes by provided screen, context and priority to values
     */
    public function setMetaBoxes($values, $screen = null, $context = 'advanced', $priority = 'default')
    {
        $this->processMetaBoxes($screen, $context, $priority, 'setMetaBoxes', $values);
        return $this;
    }

    /**
     * Process Meta Boxes with PHP 8+ Null-safe protection
     */
    protected function processMetaBoxes($screen, $context, $priority, $action, $values)
    {
        global $wp_meta_boxes;

        if (empty($screen)) {
            $screen = $this->getCurrentScreen();
        } elseif (is_string($screen)) {
            $screen = convert_to_screen($screen);
        }

        // جلوگیری از Fatal Error در صورت null یا false بودن ساختار اسکرین فعلی
        if (!$screen || !isset($screen->id) || !is_object($screen)) {
            return 'getMetaBoxes' === $action ? array() : null;
        }

        $page = $screen->id;

        // مقداردهی اولیه امن برای جلوگیری از خطای ساختار Undefined Array Key
        if (!isset($wp_meta_boxes) || !is_array($wp_meta_boxes)) {
            $wp_meta_boxes = array();
        }

        if (!isset($wp_meta_boxes[$page][$context][$priority])) {
            $wp_meta_boxes[$page][$context][$priority] = array();
        }

        if ('getMetaBoxes' === $action) {
            return $wp_meta_boxes[$page][$context][$priority] ?? array();
        } elseif ('setMetaBoxes' === $action) {
            $wp_meta_boxes[$page][$context][$priority] = $values;
            return array();
        }

        return array();
    }

    /**
     * Get the current screen object safely
     *
     * @return WP_Screen|null Current screen object or null
     */
    public function getCurrentScreen()
    {
        return function_exists('get_current_screen') ? get_current_screen() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($option, $default = false)
    {
        return get_option($option, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function updateOption($option, $value)
    {
        return update_option($option, $value);
    }

    /**
     * Saves option for number of rows when listing posts, pages, comments, etc.
     */
    public function setScreenOptions()
    {
        if (function_exists('set_screen_options')) {
            set_screen_options();
        }
        return $this;
    }
    
    /**
     * Register and configure an admin screen option
     *
     * @param string $option An option name.
     * @param mixed $args Option-dependent arguments.
     * @return WpTesting_WordPressFacade
     */
    public function addScreenOption($option, $args = array())
    {
        if (function_exists('add_screen_option')) {
            add_screen_option($option, $args);
        }
        return $this;
    }

    /**
     * Add a top level menu page in the 'objects' section
     *
     * @return string The resulting page's hook_suffix
     */
    public function addObjectPage($pageTitle, $menuTitle, $capability, $menuSlug, $function = '', $iconUrl = '')
    {
        // با توجه به حذف کامل add_object_page، استفاده از add_menu_page در نسخه‌های جدید الزامی است.
        return add_menu_page($pageTitle, $menuTitle, $capability, $menuSlug, $function, $iconUrl);
    }

    /**
     * Add a sub menu page
     */
    public function addSubmenuPage($parentSlug, $pageTitle, $menuTitle, $capability, $menuSlug, $function = '')
    {
        return add_submenu_page($parentSlug, $pageTitle, $menuTitle, $capability, $menuSlug, $function);
    }

    /**
     * Retrieves the terms associated with the given object(s), in the supplied taxonomies.
     */
    public function getObjectTerms($objectIds, $taxonomies, $args = array())
    {
        return wp_get_object_terms($objectIds, $taxonomies, $args);
    }

    /**
     * Register a post type. Do not use before init.
     */
    public function registerPostType($name, $parameters = array())
    {
        register_post_type($name, $parameters);
        return $this;
    }

    /**
     * Create or modify a taxonomy object. Do not use before init.
     */
    public function registerTaxonomy($name, $objectType, $parameters = array())
    {
        register_taxonomy($name, $objectType, $parameters);
        return $this;
    }

    /**
     * Retrieves the taxonomy object of $taxonomy.
     */
    public function getTaxonomy($taxonomy)
    {
        return get_taxonomy($taxonomy);
    }

    /**
     * Whether the current request is for a network or blog admin page
     */
    public function isAdministrationPage()
    {
        return is_admin();
    }

    /**
     * Set the activation hook for a plugin.
     */
    public function registerActivationHook($function)
    {
        if (!empty($this->pluginFile) && file_exists($this->pluginFile)) {
            register_activation_hook($this->pluginFile, $function);
        }
        return $this;
    }

    /**
     * Set the deactivation hook for a plugin.
     */
    public function registerDeactivationHook($function)
    {
        if (!empty($this->pluginFile) && file_exists($this->pluginFile)) {
            register_deactivation_hook($this->pluginFile, $function);
        }
        return $this;
    }

    /**
     * Set the uninstallation hook for a plugin.
     */
    public function registerUninstallHook($function)
    {
        if (!empty($this->pluginFile) && file_exists($this->pluginFile)) {
            register_uninstall_hook($this->pluginFile, $function);
        }
        return $this;
    }

    /**
     * If Multisite is enabled.
     */
    public function isMultisite()
    {
        return is_multisite();
    }

    /**
     * Switch the current blog.
     */
    public function switchToBlog($blogId)
    {
        if (function_exists('switch_to_blog')) {
            switch_to_blog($blogId);
        }
        return true; // برای سازگاری با معماری قدیمی همواره true برمی‌گرداند
    }

    /**
     * Restore the current blog, after calling switch_to_blog()
     */
    public function restoreCurrentBlog()
    {
        return function_exists('restore_current_blog') ? restore_current_blog() : false;
    }

    /**
     * Check whether the plugin is active for the entire network.
     */
    public function isPluginActiveForNetwork($plugin = null)
    {
        if (is_null($plugin)) {
            $plugin = $this->pluginFile ?? '';
        }

        if (empty($plugin) || !function_exists('is_plugin_active_for_network')) {
            return false;
        }

        return is_plugin_active_for_network($plugin);
    }

    /**
     * Kill WordPress execution and display HTML message with error message.
     */
    public function dieMessage($message = '', $title = '', $arguments = array())
    {
        wp_die($message, $title, $arguments);
    }

    /**
     * Get WP_Locale safely with type preservation
     * * @return WP_Locale|null
     */
    public function getLocale()
    {
        return isset($GLOBALS['wp_locale']) && is_object($GLOBALS['wp_locale']) ? $GLOBALS['wp_locale'] : null;
    }

    /**
     * Add leading zeros when necessary.
     */
    public function zeroise($number, $threshold)
    {
        return zeroise($number, $threshold);
    }

    /**
     * Replaces double line-breaks with paragraph elements.
     */
    public function autoParagraphise($textToFormat, $isConvertRemainingBreaks = true)
    {
        return wpautop($textToFormat, $isConvertRemainingBreaks);
    }

    /**
     * Retrieve the translation of $text.
     */
    public function translate($text, $domain = 'default')
    {
        return translate($text, $domain);
    }

    /**
     * Retrieve the plural or single form based on the supplied amount.
     */
    public function translatePlural($single, $plural, $number, $domain = 'default')
    {
        return _n($single, $plural, $number, $domain);
    }

    /**
     * Retrieve the plural or single form based on the supplied amount with gettext context.
     */
    public function translatePluralWithContext($single, $plural, $number, $context, $domain = 'default')
    {
        return _nx($single, $plural, $number, $context, $domain);
    }

    /**
     * Retrieve the date in localized format, updated for modern WordPress and PHP 8+ timezones
     */
    public function dateI18n($dateFormat, $timestamp = false, $isUseGmt = false)
    {
        // تابع wp_date جایگزین مدرن و بدون باگ date_i18n برای مدیریت دقیق زمان است
        if (function_exists('wp_date')) {
            $timestamp = ($timestamp === false) ? null : (int)$timestamp;
            $timezone = $isUseGmt ? new DateTimeZone('UTC') : null;
            return wp_date($dateFormat, $timestamp, $timezone);
        }

        return date_i18n($dateFormat, $timestamp, $isUseGmt);
    }

    /**
     * Loads current admin locale.
     */
    public function loadCurrentAdminLocale()
    {
        $locale = function_exists('get_locale') ? get_locale() : 'en_US';
        $path = defined('WP_LANG_DIR') ? WP_LANG_DIR : WP_CONTENT_DIR . '/languages';
        
        return load_textdomain('default', $path . '/admin-' . $locale . '.mo');
    }

    /**
     * Creates a cryptographic token tied to a specific action.
     */
    public function createNonce($action = -1)
    {
        return wp_create_nonce($action);
    }
    /**
     * Verifies the Ajax request to prevent processing requests external of the blog.
     * Fully compatible with PHP 8+ strict execution and modern WordPress standards.
     *
     * @param int|string   $action        Action nonce.
     * @param false|string $queryArgument Optional. Key to check for the nonce in `$_REQUEST`.
     * @param bool         $isDieEarly    Optional. Whether to die early when the nonce cannot be verified. Default true.
     * @return false|int False if the nonce is invalid, 1 or 2 if valid based on generation time.
     */
    public function checkAjaxReferer($action = -1, $queryArgument = false, $isDieEarly = true)
    {
        // در صورت عدم تطبیق اکشن نامعتبر، مقدار بازگشتی تابع در حالت غیر die به صورت بولین false یا اینتجر خواهد بود.
        return check_ajax_referer($action, $queryArgument, $isDieEarly);
    }
}
