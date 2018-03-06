<?php
class Article
{
    public function __construct($result_set)
    {
        $this->content = $result_set->content;
        $this->createdOn = $result_set->created_on;
        $this->title = $result_set->title;
        $this->reference = $result_set->reference;
        $this->tags = explode(";", $result_set->tags);
    }

    public $reference;
    public $title;
    public $content;
    public $createdOn;
    public $tags;
}