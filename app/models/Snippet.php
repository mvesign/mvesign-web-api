<?php
class Snippet
{
    public function __construct()
    {
    }

    public static function FromApi(
        $type,
        $value,
        $language
    )
    {
        $instance = new self();
        $instance->type = $type;
        $instance->value = $value;
        $instance->language = $language;
        return $instance;
    }

    public static function FromResultSet(
        $result_set,
        $with_references
    )
    {
        $instance = new self();
        $instance->value = $result_set->value;
        $instance->type = $result_set->type;
        $instance->language = $result_set->language;
        return $instance;
    }

    public $type;
    public $value;
    public $language;
}