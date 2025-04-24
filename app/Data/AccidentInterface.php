<?php

namespace App\Data;

use App\Enum\AccidentEnum;
use App\Enum\PriorityEnum;
use App\Enum\StatusEnum;

interface AccidentInterface
{
    /**
     * Get the accident type
     */
    public function getType(): AccidentEnum;

    /**
     * Get the accident description
     */
    public function getDescription(): string;

    /**
     * Set the accident description
     */
    public function setDescription(string $description): self;

    /**
     * Get the accident priority
     */
    public function getPriority(): PriorityEnum;

    /**
     * Set the accident priority
     */
    public function setPriority(PriorityEnum $priority): self;

    /**
     * Get the accident date
     */
    public function getDate(): ?string;

    /**
     * Set the accident date
     */
    public function setDate(string $date): self;

    /**
     * Get the accident status
     */
    public function getStatus(): StatusEnum;

    /**
     * Set the accident status
     */
    public function setStatus(StatusEnum $status): self;
}
