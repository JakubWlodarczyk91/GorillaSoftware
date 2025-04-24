<?php

namespace App\Service;

use App\Data\ReportAccident;
use App\Data\Service;
use App\Enum\AccidentEnum;
use App\Enum\PriorityEnum;
use App\Enum\StatusEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AccidentService
{
    private array $processedDescriptions = [];

    private array $services = [];

    private array $reportAccidents = [];

    private array $unprocessedAccidents = [];

    public function processAccidents(array $accidents): array
    {
        foreach ($accidents as $accident) {
            $this->processAccident($accident);
        }

        $this->saveFile();

        return [
            'processedDataCount' => count($accidents),
            'servicesCount' => count($this->services),
            'reportAccidentsCount' => count($this->reportAccidents),
            'unprocessedAccidentsCount' => count($this->unprocessedAccidents),
        ];
    }

    private function processAccident(array $accident): void
    {
        if (in_array($accident['description'], $this->processedDescriptions)) {
            $accident['comment'] = 'Duplicated Accident';
            $this->unprocessedAccidents[] = $accident;

            return;
        }

        $this->processedDescriptions[] = $accident['description'];
        $type = $this->getType($accident);
        $dateAndWeek = $this->getDate($accident['dueDate']);
        $priority = $this->getPriority($accident);

        if ($type == AccidentEnum::SERVICE) {
            $status = empty($dateAndWeek) ? StatusEnum::NEW : StatusEnum::PLANNED;

            $this->services[] = new Service($accident['description'], $priority, empty($dateAndWeek['date']) ? null : $dateAndWeek['date'], empty($dateAndWeek['week']) ? null : $dateAndWeek['week'], $status, null, empty($accident['phone']) ? null : $accident['phone'], now()->format('Y-m-d'));
        } else {
            $status = empty($dateAndWeek) ? StatusEnum::NEW : StatusEnum::DEADLINE;

            $this->reportAccidents[] = new ReportAccident($accident['description'], $priority, empty($dateAndWeek['date']) ? null : $dateAndWeek['date'], $status, null, empty($accident['phone']) ? null : $accident['phone'], now()->format('Y-m-d'));
        }
    }

    private function getType(array $accident): AccidentEnum
    {
        return str_contains($accident['description'], AccidentEnum::SERVICE->value) ? AccidentEnum::SERVICE : AccidentEnum::REPORT_ACCIDENT;
    }

    private function getDate(?string $dateToCheck): ?array
    {
        if (empty($dateToCheck)) {
            return null;
        }

        $carbonDate = Carbon::parse($dateToCheck);
        $week = $carbonDate->week;
        $date = $carbonDate->format('Y-m-d');

        if (empty($week) || empty($date)) {
            return null;
        }

        return ['date' => $date, 'week' => $week];
    }

    private function getPriority(array $accident): PriorityEnum
    {
        if (str_contains($accident['description'], 'bardzo pilne')) {
            return PriorityEnum::CRITICAL;
        } elseif (str_contains($accident['description'], 'pilne')) {
            return PriorityEnum::HIGH;
        } else {
            return PriorityEnum::NORMAL;
        }
    }

    private function saveFile()
    {
        $servicesJson = json_encode($this->services);
        $reportAccidentsJson = json_encode($this->reportAccidents);
        $unprocessedAccidentsJson = json_encode($this->unprocessedAccidents);

        Storage::disk('public')->put('services.json', $servicesJson);
        Storage::disk('public')->put('reportAccidents.json', $reportAccidentsJson);
        Storage::disk('public')->put('unprocessedAccidents.json', $unprocessedAccidentsJson);
    }
}
