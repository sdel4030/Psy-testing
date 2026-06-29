<?php

/**
 * Knows about WordPress paths and directories
 */
interface WpTesting_WordPress_IPath
{
    /**
     * Absolute path to the WordPress directory.
     * * @return string
     */
    public function getAbsPath();

    /**
     * Allows for the plugins directory to be moved from the default location.
     *
     * @return string
     */
    public function getPluginDir();

    /**
     * Determines a writable directory for temporary files (with trailing slash added).
     *
     * @return string Writable temporary directory
     */
    public function getTempDir();

    /**
     * Where WordPress holds its public content
     *
     * @return string
     */
    public function getContentDir();

    /**
     * Appends a trailing slash.
     *
     * Will remove trailing forward and backslashes if it exists already before adding
     * a trailing forward slash. This prevents double slashing a string or path.
     *
     * @param string $path What to add the trailing slash to.
     * @return string String with trailing slash added.
     */
    public function appendSlash($path);
}
