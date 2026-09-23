<?php

namespace App\Enums;

enum CrmProvider: string
{
    case Plex      = 'plex';
    case DrivePort = 'drive_port';

    public function label(): string
    {
        return match ($this) {
            self::Plex      => 'Plex CRM',
            self::DrivePort => 'Драйв Порт',
        };
    }

    /**
     * @return array<string, string> [key => label]
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }
        return $out;
    }
}
