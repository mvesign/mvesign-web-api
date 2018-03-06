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

    public function retrieve_single($reference)
    {
        $articles = $this->context->retrieve_rows(
            $this->context->perform_query(
                "SELECT A.id, A.reference, A.title, A.content, A.created_on, GROUP_CONCAT(T.value SEPARATOR ';') AS tags
                FROM articles A
                    INNER JOIN tags T ON T.article_id = A.id
                WHERE A.id IN (
                    SELECT IT.article_id
                    FROM tags IT
                    WHERE IT.value = '".$this->context->escape($reference)."'
                )
                GROUP BY A.id, A.reference, A.title, A.content, A.created_on"
            )
        );

        $result = array();

        for($count = 0; $count < count($articles); $count++)
        {
            $result[$count] = new Article($articles[$count]);
        }

        return $result;
    }

    public function retrieve_summary($limit, $offset)
    {
        $summary = $this->context->retrieve_row(
            $this->context->perform_query(
                "SELECT COUNT(*) AS numberOfItems FROM articles"
            )
        );

        if (!$summary)
        {
            return new CustomError(103, "No summary could be created for the articles.");
        }
        
        return new Summary($limit, $offset, $summary);
    }
}