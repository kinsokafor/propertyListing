<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Resources\DbTable;

final class Bookings
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("bookings")) {
            $self->maintainTable();
            return;
        }

        $statement = "CREATE TABLE bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            apartment_id INT NOT NULL,
            user_id INT NOT NULL,
            status ENUM('pending', 'approved', 'declined') DEFAULT 'pending',
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (apartment_id) REFERENCES apartments(id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX (apartment_id)
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}
}
