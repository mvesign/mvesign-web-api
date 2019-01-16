<?php
class TagsController
{
    private $context;

    public function __construct()
    {
        $this->context = new DataService(
            Settings::CONTEXT_NAME, Settings::CONTEXT_USERNAME, Settings::CONTEXT_PASSWORD, Settings::CONTEXT_HOST
        );
    }

    public function retrieve_by_reference(
        $reference
    )
    {
        $query_results = $this->context->retrieve_rows(
            $this->context->perform_query(
                "SELECT A.`id`, A.`reference`, A.`title`, A.`created_on`, GROUP_CONCAT(DISTINCT T.`value` SEPARATOR ';;;') AS tags
                FROM `articles` A
                    LEFT JOIN `tags` T ON T.`article_id` = A.`id`
                WHERE A.`id` IN (
                    SELECT IT.`article_id`
                    FROM `tags` IT
                    WHERE IT.`value` LIKE '%".$this->context->escape($reference)."%'
                )
                GROUP BY A.`id`, A.`reference`, A.`title`, A.`created_on`"
            )
        );

        $articles = array();

        for($count = 0; $count < count($query_results); $count++)
            $articles[$count] = Article::FromResultSet($query_results[$count], false);

        return $articles;
    }

    public function retrieve_summary($take, $skip)
    {
        $summary = $this->context->retrieve_row(
            $this->context->perform_query(
                "SELECT COUNT(`id`) AS numberOfItems FROM articles"
            )
        );

        if (!$summary)
            return new CustomError(103, "No summary could be created for the articles.");
        
        return new Summary($take, $skip, $summary);
    }
}