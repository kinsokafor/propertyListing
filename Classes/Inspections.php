<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;
use Public\Modules\Invoice\Classes\Invoice;

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
            user_id BIGINT UNSIGNED NOT NULL,
            schedule_id BIGINT,
            inspection_date DATE NOT NULL,
            status ENUM('pending', 'approved', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id),
            FOREIGN KEY (schedule_id) REFERENCES schedule(id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            INDEX (property_id),
            INDEX (schedule_id)
        )";
        $self->dbTable->query($statement)->execute();

    }

    private function maintainTable() {}
    
    public static function new($data)
    {
        extract($data);

        $session = Session::getInstance();

        if (!($user = $session->getResourceOwner())) {
            http_response_code(401);
            return "User not logged in";
        }

        $user_id = $user->user_id;

        $meta = [
            "property_id" => (int) $property_id,
            "user_id" => (int) $user_id,
            "inspection_date" => $inspection_date
        ];

        $self = new self;

        $property = $self->dbTable->merge($self->dbTable->
            select("properties")->where("id", (int) $property_id)
            ->execute()->row());

        return Invoice::new([
            "amount" => $property->inspection_fee,
            "currency" => $property->inspection_fee_currency,
            "description" => "Inspection fee for: $property->name [$property->type_of_property]",
            "success" => "Public\Modules\propertyListing\Classes\Inspections::onPaid",
            "user_id" => (int) $user_id,
            "meta" => $meta
        ]);
        
    }

    public static function onPaid($meta) {
        extract($meta);

        $self = new self;

        $ins = [
            "property_id" => (int) $property_id,
            "user_id" => (int) $user_id,
            "inspection_date" => $inspection_date
        ];

        $types = "iis";

        $schedule = Schedules::available($property_id, $inspection_date);

        if($schedule !== null) {
            $ins["schedule_id"] = (int) $schedule->id;
            $ins["status"] = "approved";
            $types .= "is";
        }

        $id = $self->dbTable->insert("inspections", $types, $ins)->execute();

        return $self->dbTable->select("inspections")
            ->where("id", $id)
            ->execute()->row();
    }
}
