<?php

namespace App\Types;

class SupplierWorkingHour
{
    private string $date;
    private string $from;
    private string $to;
    private ?string $timezone;

    public function __construct(string $date, string $from, string $to, ?string $timezone)
    {
        $this->date = $date;
        $this->from = $from;
        $this->to = $to;
        $this->timezone = $timezone;
    }

    public function date(): string
    {
        return $this->date;
    }

    public function from(): string
    {
        return $this->from;
    }

    public function to(): string
    {
        return $this->to;
    }

    public function timezone(): string
    {
        return $this->timezone ?? 'UTC';
    }
}
