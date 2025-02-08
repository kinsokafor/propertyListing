<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Api\Operations;
use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;

final class Reviews
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("reviews")) {
            $self->maintainTable();
            return;
        }

        $statement = "CREATE TABLE reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_type ENUM('apartment', 'property') NOT NULL, -- To distinguish between apartments and properties
            content_id INT NOT NULL, -- ID of the apartment or property
            user_id BIGINT(20) UNSIGNED NOT NULL, -- User who left the review
            rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5), -- Rating (1-5 scale)
            comment TEXT, -- Review text
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Review timestamp
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Last update timestamp
            INDEX (content_type, content_id), -- Index for faster queries on content
            FOREIGN KEY (user_id) REFERENCES users(id) -- Foreign key to users table
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}

    public static function new($data) {
        extract($data);

        $session = Session::getInstance();

        $user = $session->getResourceOwner();

        if(!$user) return false;

        $user_id = $user->user_id;

        if($id = self::exists($data)) {
            return self::update($id, $data);
        }

        $self = new self;

        $values = [
            "content_type" => $content_type,
            "content_id" => (int) $content_id,
            "user_id" => $user_id,
            "rating" => (int) $rating,
            "comment" => $comment
        ];

        $id = $self->dbTable->insert("reviews", "siiis", $values)->execute();

        return $self->dbTable->select("reviews")
            ->where("id", $id)->execute()->row();
    }

    public static function update($id, $data) {
        extract($data);

        $self = new self;

        $self->dbTable->update("reviews")
            ->set("comment", $comment, "s")
            ->set("rating", (int) $rating, "i")
            ->where("id", $id, "i")
            ->execute();

        return $self->dbTable->select("reviews")
            ->where("id", $id)->execute()->row();
    }

    public static function exists($data) {
        extract($data);

        $session = Session::getInstance();

        $user = $session->getResourceOwner();

        if(!$user) return false;

        $user_id = $user->user_id;

        $self = new self;

        $statement = "
            SELECT id 
            FROM reviews 
            WHERE content_type = ? 
            AND content_id = ? 
            AND (user_id = ?)
        ";

        $res = $self->dbTable->query(
            $statement, 
            "sii", 
            $content_type, 
            (int) $content_id,
            $user_id)->execute()->row();

        return $res == NULL ? false : $res->id;
    }
}