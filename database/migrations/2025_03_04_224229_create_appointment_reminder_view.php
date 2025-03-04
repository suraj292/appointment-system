<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE OR REPLACE VIEW appointment_reminder_v AS
            SELECT
                `u`.`name` AS `user_name`,
                `u`.`email` AS `user_email`,
                `r`.`reminder_time` AS `reminder_time`,
                `a`.`title` AS `title`,
                `a`.`user_id` AS `user_id`,
                `a`.`date_time` AS `date_time`,
                `a`.`timezone` AS `timezone`,
                `a`.`status` AS `status`,
                `i`.`name` AS `guest_name`,
                `i`.`email` AS `guest_email`
            FROM
                (
                    (
                        (
                            `appointment-system`.`reminders` `r`
                        LEFT JOIN `appointment-system`.`appointments` `a`
                        ON
                            ((`r`.`appointment_id` = `a`.`id`))
                        )
                    LEFT JOIN `appointment-system`.`guest_invitations` `i`
                    ON
                        ((`a`.`id` = `i`.`appointment_id`))
                    )
                LEFT JOIN `appointment-system`.`users` `u`
                ON
                    ((`a`.`user_id` = `u`.`id`))
                )
            WHERE
                (`a`.`status` = 'Scheduled');");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS appointment_reminder_v");
    }
};
