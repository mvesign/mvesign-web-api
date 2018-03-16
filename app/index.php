<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
setlocale(LC_ALL, 'nl_NL');

require_once("Settings.php");
require_once("services/DataService.php");
require_once("controlers/ArticlesController.php");
require_once("controlers/TagsController.php");
require_once("models/Article.php");
require_once("models/CustomError.php");
require_once("models/Summary.php");

function handle_request()
{
    parse_str($_SERVER['QUERY_STRING'], $output);
    if (!array_key_exists("type", $output))
    {
        return new CustomError(101, "Model type must be present at the request.");
    }

    $modelType = $output["type"];
    $className = $modelType."Controller";
    if (!class_exists($className))
    {
        return new CustomError(101, "No model type found for '$modelType'.");
    }

    $httpRequestMethod = $_SERVER['REQUEST_METHOD'];
    if ($httpRequestMethod !== "GET")
    {
        return new CustomError(102, "HTTP request method '$httpRequestMethod' is not supported for model type '$modelType'.");
    }

    $object = new $className();
    $result = null;

    if (array_key_exists("reference", $output))
    {
        $result = $object->retrieve_by_reference($output["reference"]);
    }
    else
    {
        $limit = array_key_exists("limit", $output) ? $output["limit"] : 20;
        $offset = array_key_exists("offset", $output) ? $output["offset"] : 0;
        
        $result = array_key_exists("summary", $output)
            ? $object->retrieve_summary($limit, $offset)
            : $object->retrieve_multiple($limit, $offset);
    }

    return $result;
}

function retrieve_http_response_code($result)
{
    if (is_object($result) && get_class($result) === "CustomError")
    {
        switch ($result->code)
        {
            case 101:
                return 404;
            case 102:
                return 405;
            case 103:
                return 500;
            default:
                return 400;
        }
    }
    
    return 200;
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$result = handle_request();

http_response_code(retrieve_http_response_code($result));

print_r(json_encode($result));