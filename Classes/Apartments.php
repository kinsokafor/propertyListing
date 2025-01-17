<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;

final class Apartments
{
    public $dbTable;

    public $keys = ["id", "owner_id", "name", "description", "location", "price", "meta", "status", "created_at", "updated_at"];

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("apartments")) {
            $self->maintainTable();
            return;
        }

        $statement = "CREATE TABLE apartments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id BIGINT(20) UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            location VARCHAR(255) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            meta JSON NOT NULL,
            status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES users(id),
            INDEX (owner_id)
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}

    public static function new($data) {
        extract($data);
        $session = Session::getInstance();

        if(!($owner = $session->getResourceOwner())) {
            http_response_code(401);
            return "User not logged in";
        }

        $self = new self;

        $meta = [];

        foreach ($data as $key => $value) {
            if(!in_array($key, $self->keys)) {
                $meta[$key] = $value;
            }
        }

        $id = $self->dbTable->insert("apartments", "isssds", [
            "owner_id" => (int) $owner->user_id ?? 0,
            "name" => substr($name ?? "", 0, 255),
            "description" => $description ?? "",
            "location" => substr($location ?? "", 0, 255),
            "price" => (double) $price,
            "meta" => json_encode($meta)
        ])->execute();

        return $self->dbTable->select("apartments")
            ->where("id", $id)
            ->execute()->row();
    }

    public static function update(int $id, array $data) {
        $self = new self;

        $self->dbTable->update("apartments")
            ->metaSet($data, $self->keys, $id, "apartments")
            ->where("id", $id)->execute();

        return $self->dbTable->select("apartments")
            ->where("id", $id)
            ->execute()->row();
    }
}
