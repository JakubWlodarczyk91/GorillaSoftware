<?php

namespace App\Data;

use App\Enum\AccidentEnum;
use App\Enum\PriorityEnum;
use App\Enum\StatusEnum;

class Service extends ReportAccident
{
    public function __construct(
        public string $description = '',
        public PriorityEnum $priority = PriorityEnum::NORMAL,
        public ?string $date = null,
        public ?int $week = null,
        public StatusEnum $status = StatusEnum::NEW,
        public ?string $recommendation = '',
        public ?string $phone = '',
        public string $createdDate = ''
    ) {
        parent::__construct($description, $priority, $date, $status, $recommendation, $phone, $createdDate);
        $this->type = AccidentEnum::SERVICE;
    }

    public function getWeek(): ?int
    {
        return $this->week;
    }

    public function setWeek(?int $week): self
    {
        $this->week = $week;

        return $this;
    }
}
