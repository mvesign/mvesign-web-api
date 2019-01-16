<?php
class SnippetsController
{
    private $context;

    public function __construct(
        $context
    )
    {
        $this->context = $context !== null
            ? $context
            : new DataService(Settings::CONTEXT_NAME, Settings::CONTEXT_USERNAME, Settings::CONTEXT_PASSWORD, Settings::CONTEXT_HOST);
    }

    public function retrieve_by_article_reference(
        $article_reference
    )
    {
        if (strlen($article_reference) !== 36)
            return new CustomError(201, "Reference must be in the valid format, like '12345678-1234-1234-1234-1234567890AB'.");

        $query_results = $this->context->retrieve_rows(
            $this->context->perform_query(
                "SELECT S.`type`, S.`value`, S.`language`
                FROM `snippets` S
                    INNER JOIN `articles` A ON A.`id` = S.`article_id`
                WHERE A.`reference` = '".$this->context->escape($article_reference)."'
                ORDER BY S.`sequence`"
            )
        );

        $snippets = array();

        for($count = 0; $count < count($query_results); $count++)
            $snippets[$count] = Snippet::FromResultSet($query_results[$count], false);

        return $snippets;
    }
}