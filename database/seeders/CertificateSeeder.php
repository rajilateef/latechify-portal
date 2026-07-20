<?php

namespace Database\Seeders;

use App\Models\Certificate;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $certificates = [
            ['LAT-2025-0001', 'Abdulhafeez Olawale', 'Fullstack Development', '2025-06-15', 'Distinction'],
            ['LAT-2025-0002', 'Warith Adeyemi', 'Fullstack Development', '2025-06-15', 'Distinction'],
            ['LAT-2025-0003', 'Emmanuel Chukwu', 'Fullstack Development', '2025-06-15', 'Merit'],
            ['LAT-2025-0004', 'Grace Okonkwo', 'Frontend Web Development', '2025-04-20', 'Distinction'],
        ];

        foreach ($certificates as [$id, $name, $course, $date, $grade]) {
            Certificate::updateOrCreate(
                ['certificate_id' => $id],
                ['student_name' => $name, 'course_name' => $course, 'issue_date' => $date, 'grade' => $grade, 'status' => 'valid']
            );
        }
    }
}
