<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Api\Operations;
use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;

final class Views
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("views")) {
            $self->maintainTable();
            return;
        }

        $statement = "CREATE TABLE views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_type ENUM('apartment', 'property') NOT NULL, -- To distinguish between apartments and properties
            content_id INT NOT NULL, -- ID of the apartment or property
            user_id BIGINT(20) UNSIGNED, -- ID of the user (NULL if anonymous)
            session_id VARCHAR(255), -- To track unique anonymous visitors
            viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Time of the view
            UNIQUE (content_type, content_id, user_id, session_id), -- Prevent duplicate views
            INDEX (content_type, content_id), -- Index for faster queries on content
            FOREIGN KEY (user_id) REFERENCES users(id) -- Foreign key to users table
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}

    public static function new($data)
    {
        extract($data);

        if(self::exists($data)) {
            http_response_code(422);
            return null;
        }

        $session = Session::getInstance();

        $user = $session->getResourceOwner();

        $session_id = \session_id();

        $self = new self;

        $values = [
            "content_type" => $content_type,
            "content_id" => $content_id,
            "session_id" => $session_id
        ];

        $types = "sis";

        if ($user) {
            $values["user_id"] = $user->user_id;
            $types .= "i";
        }

        return $self->dbTable->insert("views", $types, $values)->execute();
    }

    public static function exists($data)
    {
        extract($data);

        $session = Session::getInstance();

        $user = $session->getResourceOwner();

        $user_id = $user ? $user->user_id : null;

        $session_id = \session_id();
        
        $self = new self;

        $statement = "
            SELECT COUNT(*) as count 
            FROM views 
            WHERE content_type = ? 
            AND content_id = ? 
            AND (user_id = ? OR session_id = ?)
        ";

        return $self->dbTable->query(
            $statement, 
            "siis", 
            $content_type, 
            (int) $content_id,
            $user_id,
            $session_id)->execute()->row()->count > 0;
    }
}
