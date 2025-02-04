<?php

namespace Public\Modules\propertyListing\Classes;

use EvoPhp\Api\Operations;
use EvoPhp\Resources\DbTable;
use EvoPhp\Database\Session;

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
            user_id BIGINT(20) UNSIGNED NOT NULL,
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

    public static function unavailableDates($apartmentId, $startDate, $endDate)
    {
        $self = new self;

        $statement = "WITH RECURSIVE unavailable_dates AS (
            -- Step 1: Select the initial set of unavailable dates from bookings
            SELECT start_date AS unavailable_date, end_date
            FROM bookings
            WHERE status != 'declined'
            AND apartment_id = ?
            AND (
                start_date <= ? -- End of desired range
                AND end_date >= ? -- Start of desired range
            )
            UNION ALL
            -- Step 2: Generate subsequent dates for each booking's range
            SELECT DATE_ADD(unavailable_date, INTERVAL 1 DAY), end_date
            FROM unavailable_dates
            WHERE DATE_ADD(unavailable_date, INTERVAL 1 DAY) < end_date
        )
        SELECT DISTINCT unavailable_date
        FROM unavailable_dates
        ORDER BY unavailable_date;
    ";

        // Execute the query with parameterized values
        return $self->dbTable
            ->query($statement, "iss", $apartmentId, $endDate, $startDate)
            ->execute()
            ->rows();
    }



    public static function unavailableDatesCount($apartmentId, $startDate, $endDate)
    {
        $self = new self;

        $statement = "WITH RECURSIVE unavailable_dates AS (
                -- Step 1: Select the initial set of unavailable dates from bookings
                SELECT start_date AS unavailable_date, end_date
                FROM bookings
                WHERE status != 'declined'
                AND apartment_id = ?
                AND (
                    start_date <= ? -- End of desired range
                    AND end_date >= ? -- Start of desired range
                )
                UNION ALL
                -- Step 2: Generate subsequent dates for each booking's range
                SELECT DATE_ADD(unavailable_date, INTERVAL 1 DAY), end_date
                FROM unavailable_dates
                WHERE DATE_ADD(unavailable_date, INTERVAL 1 DAY) <= end_date
            )
            SELECT DISTINCT COUNT(unavailable_date) as count
            FROM unavailable_dates
            ORDER BY unavailable_date;";

        return $self->dbTable->query($statement, "iss", $apartmentId, $endDate, $startDate)->execute()->row()->count;
    }

    public static function new($data)
    {
        extract($data);

        $session = Session::getInstance();

        if (!($user = $session->getResourceOwner())) {
            http_response_code(401);
            return "User not logged in";
        }

        $user_id = $user->user_id;

        $self = new self;

        if ($self::unavailableDatesCount($apartment_id, $start_date, $end_date) > 0) {
            http_response_code(400);
            $unavailableDates = $self::unavailableDates($apartment_id, $start_date, $end_date);
            $dates = implode(", ", array_map(function ($v) {
                return $v->unavailable_date;
            }, $unavailableDates));
            return (Operations::count($unavailableDates) > 1) ?
                "The following dates in your selection are not available: $dates" :
                "$dates is not available.";
        }

        $id = $self->dbTable->insert("bookings", "iiss", [
            "apartment_id" => (int) $apartment_id,
            "user_id" => (int) $user_id,
            "start_date" => $start_date,
            "end_date" => $end_date
        ])->execute();

        return $self->dbTable->select("bookings")
            ->where("id", $id)
            ->execute()->row();
    }
}
