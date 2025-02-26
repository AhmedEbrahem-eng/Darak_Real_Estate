<?php

namespace App\Repositories\Contracts;

interface ReportPropertyRepositoryInterface
{
    public function getAllReports();
    public function findReportById(int $id);
    public function deleteReportById(int $id);
    public function deletePropertyAndReportById(int $id);
    public function createReport(array $data);
}
