<?php
class CustomError
{
    public function __construct($code, $description)
    {
        $this->code = $code;
        $this->description = $description;
    }

    public $code;
    public $description;
}