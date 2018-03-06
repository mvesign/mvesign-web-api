<?php
class ArticlesController
{
    private $context;

    public function __construct()
    {
        $this->context = new DataService(
            Settings::ARTICLES_CONTEXT_NAME, Settings::ARTICLES_CONTEXT_USERNAME, Settings::ARTICLES_CONTEXT_PASSWORD
        );
    }

    public function retrieve_multiple($limit, $offset)
    {
        //"SELECT * FROM articles LIMIT $offset, $limit"
        $articles = $this->context->retrieve_rows(
            $this->context->perform_query(
                "SELECT A.id, A.reference, A.title, A.content, A.created_on, GROUP_CONCAT(T.value SEPARATOR ';') AS tags
                FROM articles A
                    LEFT JOIN tags T ON T.article_id = A.id
                GROUP BY A.id, A.reference, A.title, A.content, A.created_on
                LIMIT $offset, $limit"
            )
        );

        $result = array();

        for($count = 0; $count < count($articles); $count++)
        {
            $result[$count] = new Article($articles[$count]);
        }

        return $result;
    }

    public function retrieve_single($reference)
    {
        if (strlen($reference) !== 36)
        {
            return new CustomError(201, "Reference must be in the valid format, like '12345678-1234-1234-1234-1234567890AB'.");
        }

        $article = $this->context->retrieve_row(
            $this->context->perform_query(
                "SELECT A.id, A.reference, A.title, A.content, A.created_on, GROUP_CONCAT(T.value SEPARATOR ';') AS tags
                FROM articles A
                    LEFT JOIN tags T ON T.article_id = A.id
                WHERE reference = '".$this->context->escape($reference)."'
                GROUP BY A.id, A.reference, A.title, A.content, A.created_on"
            )
        );

        if (!$article)
        {
            return new CustomError(201, "No unique article found for reference '$reference'.");
        }
        
        return new Article($article);
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