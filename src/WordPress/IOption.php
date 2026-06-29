<?php

/**
 * Reads and writes global options
 */
interface WpTesting_WordPress_IOption
{
    /**
     * Retrieve option value based on name of option.
     *
     * If the option does not exist or does not have a value, then the return value
     * will be false or the defined default value.
     *
     * @param string $option  Name of option to retrieve. Expected to not be SQL-escaped.
     * @param mixed  $default Optional. Default value to return if the option does not exist.
     * @return mixed Value set for the option.
     */
    public function getOption($option, $default = false);

    /**
     * Update the value of an option that was already added.
     *
     * If the option does not exist, then the option will be added.
     *
     * @param string    $option   Option name. Expected to not be SQL-escaped.
     * @param mixed     $value    Option value. Must be serializable if non-scalar.
     * @param bool|null $autoload Optional. Whether to load the option when WordPress starts.
     * @return bool False if value was not updated and true if value was updated.
     */
    public function updateOption($option, $value, $autoload = null);
}
