<?php

namespace App\Data;

use App\Enum\AccidentEnum;
use App\Enum\PriorityEnum;
use App\Enum\StatusEnum;

class ReportAccident implements AccidentInterface
{
    public AccidentEnum $type;

    public function __construct(
        public string $description = '',
        public PriorityEnum $priority = PriorityEnum::NORMAL,
        public ?string $date = null,
        public StatusEnum $status = StatusEnum::NEW,
        public ?string $recommendation = null,
        public ?string $phone = null,
        public string $createdDate = ''
    ) {
        $this->type = AccidentEnum::REPORT_ACCIDENT;
    }

    public function getType(): AccidentEnum
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPriority(): PriorityEnum
    {
        return $this->priority;
    }

    public function setPriority(PriorityEnum $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): StatusEnum
    {
        return $this->status;
    }

    public function setStatus(StatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRecommendation(): ?string
    {
        return $this->recommendation;
    }

    public function setRecommendation(string $recommendation): self
    {
        $this->recommendation = $recommendation;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCreatedDate(): string
    {
        return $this->createdDate;
    }

    public function setCreatedDate(string $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }
}
