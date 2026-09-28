<?php

namespace Pageflow\Core\modules\webforms\handlers;


use Pageflow\Core\database\MysqlConnector;
use Pageflow\Core\modules\articles\model\ArticleComment;
use Pageflow\Core\database\dao\SettingsDaoMysql;
use Pageflow\Core\database\dao\SettingsDao;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\pages\model\Page;

class ArticleCommentFormHandler extends FormHandler {

    public static string $TYPE = 'article_comment_form_handler';
    private MysqlConnector $mysqlConnector;
    private SettingsDao $settingsDao;

    public function __construct() {
        parent::__construct();
        $this->mysqlConnector = MysqlConnector::getInstance();
        $this->settingsDao = SettingsDaoMysql::getInstance();
    }

    public function getRequiredProperties(): array {
        return array(
            new HandlerProperty('name', 'textfield'),
            new HandlerProperty('email', 'textfield'),
            new HandlerProperty('message', 'textarea'),
            new HandlerProperty('response_message', 'textarea'),
            new HandlerProperty('response_message_email_subject', 'textfield'),
            new HandlerProperty('parent', 'textfield')
        );
    }

    public function getNameResourceIdentifier(): string {
        return 'webforms_article_comment_form_handler_name';
    }

    public function getType(): string {
        return self::$TYPE;
    }

    public function handle(array $fields, Page $page, ?Article $article): void {
        $name = $this->getFilledInPropertyValue('name');
        $email = $this->getFilledInPropertyValue('email');
        $message = $this->getFilledInPropertyValue('message');
        $responseMessage = $this->getFilledInPropertyValue('response_message');
        $responseMessageEmailSubject = $this->getFilledInPropertyValue('response_message_email_subject');
        $parent = intval($this->getFilledInPropertyValue('parent'));
        if (!$parent) {
            $parent = null;
        } else {
            $parentArticleComment = $this->getParentComment($parent);
            $targetEmailAddress = $this->settingsDao->getSettings()->getEmailAddress();
            $headers = array('From' => $targetEmailAddress);
            mail($parentArticleComment->getEmailAddress(), $responseMessageEmailSubject, $responseMessage, $headers);
        }
        $articleId = $article->getId();

        $query = "INSERT INTO article_comments (`name`, `message`, email_address, created_at, article_id, parent) VALUES (?, ?, ?, now(), ?, ?)";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param("sssii", $name, $message, $email, $articleId, $parent);
        $this->mysqlConnector->executeStatement($statement);
    }

    private function getParentComment(int $id): ArticleComment {
        $query = "SELECT * FROM article_comments WHERE id = ?";
        $statement = $this->mysqlConnector->prepareStatement($query);
        $statement->bind_param("i", $id);
        $result = $this->mysqlConnector->executeStatement($statement);
        while ($row = $result->fetch_assoc()) {
            return ArticleComment::constructFromRecord($row);
        }
    }
}