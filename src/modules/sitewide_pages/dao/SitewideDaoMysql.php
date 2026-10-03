<?php

namespace Pageflow\Core\modules\sitewide_pages\dao;

use Pageflow\Core\database\MysqlConnector;

class SitewideDaoMysql implements SitewideDao {
    private static string $myAllColumns = "id, page_id";

    private static ?SitewideDaoMysql $instance = null;
    private MysqlConnector $mysqlConnector;

    private function __construct() {
        $this->mysqlConnector = MysqlConnector::getInstance();
    }

    public static function getInstance(): SitewideDaoMysql {
        if (!self::$instance) {
            self::$instance = new SitewideDaoMysql();
        }
        return self::$instance;
    }

    public function getSitewidePages(): array {
        $statement = $this->mysqlConnector->prepareStatement("SELECT " . self::$myAllColumns . " FROM sitewide_pages ORDER BY order_number ASC");
        $result = $this->mysqlConnector->executeStatement($statement);
        $pages = array();
        while ($row = $result->fetch_assoc()) {
            $pages[] = $row["page_id"];
        }
        return $pages;
    }

    public function addSitewidePage(int $id): void {
        $statement = $this->mysqlConnector->prepareStatement("INSERT INTO sitewide_pages (page_id, order_number) VALUES (?, (SELECT COUNT(*) FROM sitewide_pages p) + 1)");
        $statement->bind_param("i", $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function removeSitewidePage(int $id): void {
        $statement = $this->mysqlConnector->prepareStatement("DELETE FROM sitewide_pages WHERE page_id = ?");
        $statement->bind_param("i", $id);
        $this->mysqlConnector->executeStatement($statement);
    }

    public function updateSitewidePage(int $id, int $orderNumber): void {
        $statement = $this->mysqlConnector->prepareStatement("UPDATE sitewide_pages SET order_number = ? WHERE page_id = ?");
        $statement->bind_param("ii", $orderNumber, $id);
        $this->mysqlConnector->executeStatement($statement);
    }
}
