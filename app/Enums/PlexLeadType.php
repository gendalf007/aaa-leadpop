<?php

namespace App\Enums;

enum PlexLeadType: string
{
    case Bank           = 'bank';
    case Buyout         = 'buyout';
    case Callback       = 'callback';
    case Credit         = 'credit';
    case FreeSelection  = 'free-selection';
    case HirePurchase   = 'hire-purchase';
    case Leasing        = 'leasing';
    case Recycling      = 'recycling';
    case Reservation    = 'reservation';
    case TestDrive      = 'test-drive';
    case TradeIn        = 'trade-in';
    case VehicleService = 'vehicle-service';
    case Unknown        = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Bank           => 'Банк',
            self::Buyout         => 'Выкуп',
            self::Callback       => 'Обратный звонок',
            self::Credit         => 'Кредит',
            self::FreeSelection  => 'Свободный подбор',
            self::HirePurchase   => 'Рассрочка',
            self::Leasing        => 'Лизинг',
            self::Recycling      => 'Утилизация',
            self::Reservation    => 'Бронирование',
            self::TestDrive      => 'Тест-драйв',
            self::TradeIn        => 'Trade-in',
            self::VehicleService => 'Сервис',
            self::Unknown        => 'Не определён',
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

    /**
     * @return array<int, string> список ключей
     */
    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }
}
