<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
setlocale(LC_ALL, 'nl_NL');

require_once("Settings.php");
require_once("services/DataService.php");
require_once("services/ValidationService.php");
require_once("controlers/ArticlesController.php");
require_once("controlers/SnippetsController.php");
require_once("controlers/TagsController.php");
require_once("models/Article.php");
require_once("models/CustomError.php");
require_once("models/Snippet.php");
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
        if ($className !== "ArticleController" && $httpRequestMethod !== "POST")
        {
            return new CustomError(102, "HTTP request method '$httpRequestMethod' is not supported for model type '$modelType'.");
        }
    }

    $object = new $className();
    $result = null;

    if ($httpRequestMethod === "POST")
    {
        if (!ValidationService::is_valid_password($_SERVER["X-MVESIGN-PASSWORD"]))
        {
            return new CustomError(103, "No valid password supplied in header.");
        }
        
        $result = $object->create(json_decode(http_get_request_body()));
    }
    else if (array_key_exists("reference", $output))
    {
        $result = $object->retrieve_by_reference($output["reference"]);
    }
    else
    {
        $take = array_key_exists("take", $output) ? $output["take"] : 20;
        $skip = array_key_exists("skip", $output) ? $output["skip"] : 0;
        
        $result = array_key_exists("summary", $output)
            ? $object->retrieve_summary($take, $skip)
            : $object->retrieve_multiple($take, $skip);
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