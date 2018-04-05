<?php
class ArticlesController
{
    private $context;

    public function __construct()
    {
        $this->context = new DataService(
            Settings::CONTEXT_NAME, Settings::CONTEXT_USERNAME, Settings::CONTEXT_PASSWORD, Settings::CONTEXT_HOST
        );
    }

    public function create($article)
    {
        $result = $this->validate_property($article, "title", 10);
        if ($result !== null)
        {
            return $result;
        }

        $result = $this->validate_property($article, "content", 100);
        if ($result !== null)
        {
            return $result;
        }

        $article = Article::FromApi($article->title, $article->content);

        $this->context->perform_query(
            "INSERT INTO articles (
                reference, title, content, created_on
            ) VALUES (
                '".$this->context->escape($article->reference)."', '".$this->context->escape($article->title)."',
                '".$this->context->escape($article->content)."', '".$this->context->escape($article->created_on)."'
            )"
        );

        //TODO Support create of tags

        return $this->retrieve_by_reference($article->reference);
    }

    public function retrieve_by_reference($reference)
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
        
        return Article::FromResultSet($article);
    }

    public function retrieve_multiple($take, $skip)
    {
        $articles = $this->context->retrieve_rows(
            $this->context->perform_query(
                "SELECT A.id, A.reference, A.title, A.content, A.created_on, GROUP_CONCAT(T.value SEPARATOR ';') AS tags
                FROM articles A
                    LEFT JOIN tags T ON T.article_id = A.id
                GROUP BY A.id, A.reference, A.title, A.content, A.created_on
                LIMIT $skip, $take"
            )
        );

        $result = array();

        for($count = 0; $count < count($articles); $count++)
        {
            $result[$count] = Article::FromResultSet($articles[$count]);
        }

        return $result;
    }

    public function retrieve_summary($take, $skip)
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
        
        return new Summary($take, $skip, $summary);
    }

    private function validate_property($article, $property_name, $minimum_length)
    {
        if (!property_exists($article, $property_name))
        {
            return new CustomError(201, "Article has not the mandatory '".$property_name."' property.");
        }

        if (strlen($article->$property_name) < $minimum_length)
        {
            return new CustomError(202, "Article has an invalid '".$property_name."' property.");
        }
    }
}