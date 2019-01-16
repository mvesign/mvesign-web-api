<?php
class ArticlesController
{
    private $context;
    private $snippets_controller;

    public function __construct()
    {
        $this->context = new DataService(Settings::CONTEXT_NAME, Settings::CONTEXT_USERNAME, Settings::CONTEXT_PASSWORD, Settings::CONTEXT_HOST);
        $this->snippets_controller = new SnippetsController($this->context);
    }

    public function create(
        $article
    )
    {
        $validate_result = $this->validate($article);
        if ($validate_result !== null)
            return $validate_result;

        $article = Article::FromApi($article->title, $article->snippets);

        $this->context->perform_query(
            "INSERT INTO articles (
                `reference`, `title`
            ) VALUES (
                '".$this->context->escape($article->reference)."', '".$this->context->escape($article->title)."'
            )"
        );

        if (is_array($article->tags) && count($article->tags) > 0)
        {
            foreach ($article->tags as &$tag)
                $tag = "SELECT $tag AS `tag`";

            $this->context->perform_query(
                "INSERT INTO tags (`article_id`, `value`)
                SELECT A.`id`, T.`tag`
                FROM articles A,
                    (".implode(" UNION ", $article->tags).") T
                WHERE A.`reference` = '$article->reference';"
            );
        }

        if (is_array($article->references) && count($article->references) > 0)
        {
            foreach ($article->references as &$reference)
                $reference = "SELECT $reference->url AS `url`, $reference->sequence AS `sequence`";

            $this->context->perform_query(
                "INSERT INTO references (`article_id`, `url`, `sequence`)
                SELECT A.`id`, R.`url`, R.`sequence`
                FROM articles A,
                    (".implode(" UNION ", $article->reference).") R
                WHERE A.`reference` = '$article->reference';"
            );
        }
        
        if (is_array($article->snippets) && count($article->snippets) > 0)
        {
            foreach ($article->snippets as &$snippet)
                $snippet = "SELECT $snippet->type AS `type`, $snippet->value AS `value`, $snippet->highlight AS `highlight`, $snippet->language AS `language`, $snippet->sequence AS `sequence`";

            $this->context->perform_query(
                "INSERT INTO references (`article_id`, `type`, `value`, `highlight`, `language`, `sequence`)
                SELECT A.`id`, S.`type`, S.`value`, S.`highlight`, S.`language`, S.`sequence`
                FROM articles A,
                    (".implode(" UNION ", $article->snippet).") S
                WHERE A.`reference` = '$article->snippet';"
            );
        }

        return $this->retrieve_by_reference($article->reference);
    }

    public function retrieve_by_reference(
        $reference
    )
    {
        if (strlen($reference) !== 36)
            return new CustomError(201, "Reference must be in the valid format, like '12345678-1234-1234-1234-1234567890AB'.");

        $query_result = $this->context->retrieve_row(
            $this->context->perform_query(
                "SELECT A.`id`, A.`reference`, A.`title`, A.`created_on`,
                    GROUP_CONCAT(DISTINCT T.`value` SEPARATOR ';;;') AS tags,
                    GROUP_CONCAT(DISTINCT R.`url` ORDER BY R.`sequence` SEPARATOR ';;;') AS `references`
                FROM `articles` A
                    LEFT JOIN `tags` T ON T.`article_id` = A.`id`
                    LEFT JOIN `references` R ON R.`article_id` = A.`id`
                WHERE A.`reference` = '".$this->context->escape($reference)."'
                GROUP BY A.`id`, A.`reference`, A.`title`, A.`created_on`"
            )
        );

        if (!$query_result)
            return new CustomError(201, "No unique article found for reference '$reference'.");

        $article = Article::FromResultSet($query_result, true);
        $article->snippets = $this->snippets_controller->retrieve_by_article_reference($article->reference);
        
        return $article;
    }

    public function retrieve_multiple(
        $take,
        $skip
    )
    {
        $query_results = $this->context->retrieve_rows(
            $this->context->perform_query(
                "SELECT A.`id`, A.`reference`, A.`title`, A.`created_on`, GROUP_CONCAT(DISTINCT T.`value` SEPARATOR ';;;') AS tags
                FROM `articles` A
                    LEFT JOIN `tags` T ON T.`article_id` = A.`id`
                GROUP BY A.`id`, A.`reference`, A.`title`, A.`created_on`
                ORDER BY A.`created_on` DESC
                LIMIT $skip, $take"
            )
        );

        $articles = array();

        for($count = 0; $count < count($query_results); $count++)
            $articles[$count] = Article::FromResultSet($query_results[$count], false);

        return $articles;
    }

    public function retrieve_summary(
        $take,
        $skip
    )
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

    public function validate(
        $article
    )
    {
        //TODO Check every property
        
        if (!property_exists($article, "title"))
            return new CustomError(201, "Article has not the mandatory 'title' property.");

        $title_length = strlen($article->title);
        if ($title_length < 5 || $title_length > 50)
            return new CustomError(202, "Article requires the 'title' property to be between 5 and 50 characters.");

        if (!property_exists($article, "snippets"))
            return new CustomError(201, "Article has not the mandatory 'snippets' property.");

        if ($article->snippets !== null && count($article->snippets) <= 0)
            return new CustomError(201, "Article must at least contain one item in the 'snippets' property.");

        $types = array("text", "code", "image");
        foreach ($article->snippets as $snippet)
        {
            if (!in_aray($snippet->type, $types))
                return new CustomError(202, "Article requires the 'snippets.type' property to be 'text', 'code' or 'image'.");

            if (strlen($snippet->value) < 30)
                return new CustomError(202, "Article requires the 'snippets.value' property to have each snippet above 30 characters.");
        }
    }
}