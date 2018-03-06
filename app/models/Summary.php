<?php
class Summary
{
    public function __construct($limit, $offset, $result_set)
    {
        $this->currentPage = ceil($offset / $limit) + 1;
        $this->itemsPerPage = $limit;
        $this->numberOfItems = $result_set->numberOfItems + 0;
        $this->numberOfPages = ceil($this->numberOfItems / $limit);
    }

    public $numberOfItems;
    public $itemsPerPage;
    public $numberOfPages;
    public $currentPage;
}