<?php
class Snippet
{
    public function __construct()
    {
    }

    public static function FromApi($value, $language)
    {
        $instance = new self();
        $instance->value = $value;
        $instance->highlight = !empty($language) ? 1 : 0;
        $instance->language = $language;
        return $instance;
    }

    public static function FromResultSet($result_set, $with_references)
    {
        $instance = new self();
        $instance->value = $result_set->value;
        $instance->highlight = intval($result_set->highlight);
        $instance->language = $result_set->language;
        return $instance;
    }

    public $value;
    public $highlight;
    public $language;
}