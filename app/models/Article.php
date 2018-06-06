<?php
class Article
{
    public function __construct()
    {
        $hash = strtoupper(md5(uniqid(rand(), true)));

        $this->createdOn = date("Y-m-d H:i:s");
        $this->reference = implode("-", array(substr($hash, 0, 8), substr($hash, 8, 4), substr($hash, 12, 4), substr($hash, 16, 4), substr($hash, 20, 12)));
    }

    public static function FromApi($title, $snippets)
    {
        $instance = new self();
        $instance->snippets = $snippets;
        $instance->title = $title;
        return $instance;
    }

    public static function FromResultSet($result_set, $with_references)
    {
        $instance = new self();
        $instance->createdOn = $result_set->created_on;
        $instance->reference = $result_set->reference;
        $instance->title = $result_set->title;
        $instance->tags = array_filter(explode(";;;", $result_set->tags));
        
        if ($with_references === true)
        {
            $instance->references = array_filter(explode(";;;", $result_set->references));
        }

        return $instance;
    }

    public $reference;
    public $title;
    public $createdOn;
    public $tags;
    public $snippets;
    public $references;
}