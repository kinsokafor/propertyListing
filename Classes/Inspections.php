<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Resources\DbTable;

final class Inspections
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("inspections")) {
            $self->maintainTable();
            return;
        }

        $statement = "CREATE TABLE inspections (
            id INT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            user_id INT NOT NULL,
            inspection_date DATE NOT NULL,
            status ENUM('pending', 'approved', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX (property_id)
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}
}
