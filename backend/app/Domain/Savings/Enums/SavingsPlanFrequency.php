<?php

declare(strict_types=1);

namespace App\Domain\Savings\Enums;

use DateTimeImmutable;

enum SavingsPlanFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public static function values(): array
    {
        return array_map(fn (self $frequency) => $frequency->value, self::cases());
    }

    public function nextExecutionDate(): DateTimeImmutable
    {
        return match ($this) {
            self::Daily => (new DateTimeImmutable)->modify('+1 day')->setTime(0, 0),
            self::Weekly => (new DateTimeImmutable)->modify('+1 week'),
            self::Monthly => (new DateTimeImmutable)->modify('+1 month'),
        };
    }
}
