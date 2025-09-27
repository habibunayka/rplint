<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceTrigger extends Migration
{
    public function up()
    {
        DB::statement('
            CREATE TRIGGER update_attendance_after_insert
            AFTER INSERT ON attendances
            FOR EACH ROW
            BEGIN
                DECLARE present INT;
                DECLARE permit INT;
                DECLARE absent INT;

                SELECT COUNT(*) INTO present
                FROM attendances
                WHERE student_id = NEW.student_id
                  AND status IN ("ontime", "late");

                SELECT COUNT(*) INTO permit
                FROM attendances
                WHERE student_id = NEW.student_id
                  AND status IN ("permit", "sick");

                SELECT COUNT(*) INTO absent
                FROM attendances
                WHERE student_id = NEW.student_id
                  AND status = "absent";

                UPDATE students
                SET present = present, permit = permit, absent = absent
                WHERE id = NEW.student_id;
            END
        ');

        DB::statement('
            CREATE TRIGGER update_attendance_after_update
            AFTER UPDATE ON attendances
            FOR EACH ROW
            BEGIN
                DECLARE present INT;
                DECLARE permit INT;
                DECLARE absent INT;

                SELECT COUNT(*) INTO present
                FROM attendances
                WHERE student_id = OLD.student_id
                  AND status IN ("ontime", "late");

                SELECT COUNT(*) INTO permit
                FROM attendances
                WHERE student_id = OLD.student_id
                  AND status IN ("permit", "sick");

                SELECT COUNT(*) INTO absent
                FROM attendances
                WHERE student_id = OLD.student_id
                  AND status = "absent";

                UPDATE students
                SET present = present, permit = permit, absent = absent
                WHERE id = OLD.student_id;
            END
        ');

        DB::statement('
            CREATE TRIGGER update_attendance_after_delete
            AFTER DELETE ON attendances
            FOR EACH ROW
            BEGIN
                DECLARE present INT;
                DECLARE permit INT;
                DECLARE absent INT;

                SELECT COUNT(*) INTO present
                FROM attendances
                WHERE student_id = OLD.student_id
                  AND status IN ("ontime", "late");

                SELECT COUNT(*) INTO permit
                FROM attendances
                WHERE student_id = OLD.student_id
                  AND status IN ("permit", "sick");

                SELECT COUNT(*) INTO absent
                FROM attendances
                WHERE student_id = OLD.student_id
                  AND status = "absent";

                UPDATE students
                SET present = present, permit = permit, absent = absent
                WHERE id = OLD.student_id;
            END
        ');
    }

    public function down()
    {
        DB::statement('DROP TRIGGER IF EXISTS update_attendance_after_insert');
        DB::statement('DROP TRIGGER IF EXISTS update_attendance_after_update');
        DB::statement('DROP TRIGGER IF EXISTS update_attendance_after_delete');
    }
}
