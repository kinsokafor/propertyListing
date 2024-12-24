<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Resources\DbTable;

final class Apartments
{
    public $dbTable;

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
            owner_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            location VARCHAR(255) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            meta JSON NOT NULL,
            status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (owner_id)
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}
}
