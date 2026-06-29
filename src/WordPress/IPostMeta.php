<?php

/**
 * Reads and writes post's meta
 */
interface WpTesting_WordPress_IPostMeta
{
    /**
     * Retrieve post meta field for a post.
     *
     * @param string $key The meta key to retrieve.
     * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
     */
    public function getCurrentPostMeta($key);

    /**
     * Retrieve post meta field for a post.
     *
     * @param int $postId Post ID.
     * @param string $key The meta key to retrieve.
     * @param bool $isSingle Whether to return a single value. Default false.
     * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
     */
    public function getPostMeta($postId, $key, $isSingle = false);

    /**
     * Update post meta field based on post ID.
     *
     * @param int $postId Post ID.
     * @param string $key Metadata key.
     * @param mixed $value Metadata value.
     * @param mixed $previousValue Optional. Previous value to check before removing.
     * @return bool False on failure, true if success.
     */
    public function updatePostMeta($postId, $key, $value, $previousValue = '');
}
