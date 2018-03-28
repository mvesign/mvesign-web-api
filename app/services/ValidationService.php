<?php
class ValidationService
{
    public static function is_valid_password($value)
    {
        return strcmp(md5(Settings::USER_PASSWORD), $value);
    }
}