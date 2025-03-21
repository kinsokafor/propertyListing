<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Resources\DbTable;

final class Schedules
{
    public $dbTable;

    public function __construct()
    {
        $this->dbTable = new DbTable;
    }

    public static function createTable()
    {
        $self = new self;
        if ($self->dbTable->checkTableExist("schedule")) {
            $self->maintainTable();
            return;
        }

        $statement = "CREATE TABLE schedule (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            property_id INT NOT NULL,
            inspection_date DATE NOT NULL,
            take_off_location TEXT NOT NULL,
            take_off_time TIME NOT NULL,
            status ENUM('pending', 'approved', 'cancelled') DEFAULT 'approved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id),
            INDEX (property_id)
        )";
        $self->dbTable->query($statement)->execute();
    }

    private function maintainTable() {}

    public static function new($data)
    {
        extract($data);

        $self = new self;

        if($self::available($property_id, $inspection_date) !== null) {
            http_response_code(401);
            return "There is already an existing schedule for the specified date.";
        }

        $id = $self->dbTable->insert("schedule", "isss", [
            "property_id" => (int) $property_id,
            "inspection_date" => $inspection_date,
            "take_off_location" => $take_off_location,
            "take_off_time" => $take_off_time
        ])->execute();

        return $self->dbTable->select("schedule")
            ->where("id", $id)
            ->execute()->row();
    }

    public static function available($property_id, $inspection_date) {
        $self = new self;
        return $self->dbTable->select("schedule")
            ->where("property_id", (int) $property_id, "i")
            ->where("inspection_date", $inspection_date)
            ->execute()->row();
    }
}
