<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Resources\DbTable;

final class Properties
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("properties")) {
            $self->maintainTable();
            return;
        }

        $statement = "CREATE TABLE properties (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            location VARCHAR(255) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            meta JSON NOT NULL,
            status ENUM('available', 'sold') DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (owner_id)
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}
}
