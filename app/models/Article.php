<?php
class Article
{
    public function __construct()
    {
        $hash = strtoupper(md5(uniqid(rand(), true)));

        $this->createdOn = date("Y-m-d H:i:s");
        $this->reference = implode("-", array(substr($hash, 0, 8), substr($hash, 8, 4), substr($hash, 12, 4), substr($hash, 16, 4), substr($hash, 20, 12)));
    }

    public static function FromApi($title, $content)
    {
        $instance = new self();
        $instance->content = $content;
        $instance->title = $title;
        return $instance;
    }

    public static function FromResultSet($result_set)
    {
        $instance = new self();
        $instance->content = $result_set->content;
        $instance->createdOn = $result_set->created_on;
        $instance->title = $result_set->title;
        $instance->reference = $result_set->reference;
        $instance->tags = array_filter(explode(";", $result_set->tags));
        return $instance;
    }

    public $reference;
    public $title;
    public $content;
    public $createdOn;
    public $tags;
}