<?php

/**
 * Value object base class.
 * Fully compatible with PHP 8+ strict string return types.
 */
abstract class WpTesting_WordPress_Value_Base
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Ensures strict string return types required by PHP 8+.
     * * @return string
     */
    public function __toString(): string
    {
        // تبدیل صریح به رشته جهت جلوگیری از Fatal Error در صورت غیررشته‌ای بودن مقدار
        return (string)$this->value;
    }
}
