<?php

defined('JSON_THROW_ON_ERROR') || define('JSON_THROW_ON_ERROR', 4194304);

if (!class_exists('JsonException')) {
    class JsonException extends \Exception
    {
    }
}
