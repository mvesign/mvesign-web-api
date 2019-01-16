<?php
class Summary
{
    public function __construct(
        $take,
        $skip,
        $result_set
    )
    {
        $this->currentPage = ceil($skip / $take) + 1;
        $this->itemsPerPage = $take;
        $this->numberOfItems = $result_set->numberOfItems + 0;
        $this->numberOfPages = ceil($this->numberOfItems / $take);
    }

    public $numberOfItems;
    public $itemsPerPage;
    public $numberOfPages;
    public $currentPage;
}