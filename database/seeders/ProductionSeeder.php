<?php

namespace Database\Seeders;

use App\Enums\CrmProvider;
use App\Enums\PlexLeadType;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Начальные данные: админ и сайт «Лид М» → Драйв Порт.
 * Домен сайта — m.<хост APP_URL>: на проде m.leadpop.ru, локально m.aaa-lead-form.test.
 * Идемпотентен: существующие записи не перезаписывает (правки из админки сохраняются).
 *
 *   php artisan db:seed --class=ProductionSeeder --force
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedLeadMSite();
    }

    private function seedAdmin(): void
    {
        if (User::where('email', 'cs@danali.ru')->orWhere('username', 'cs')->exists()) {
            $this->command->info('Пользователь cs / cs@danali.ru уже существует — пропускаю.');
            return;
        }

        $password = Str::password(16, symbols: false);

        User::create([
            'name'     => 'Администратор',
            'username' => 'cs',
            'email'    => 'cs@danali.ru',
            'password' => $password,
            'is_admin' => true,
        ]);

        $this->command->warn("Админ создан: логин cs / cs@danali.ru, пароль: {$password}");
        $this->command->warn('Сохраните пароль — повторно он не выводится.');
    }

    private function seedLeadMSite(): void
    {
        $leadTypes = array_values(array_filter(
            PlexLeadType::values(),
            fn (string $v) => $v !== PlexLeadType::Unknown->value
        ));

        $domain = 'm.' . parse_url(config('app.url'), PHP_URL_HOST);

        $site = Site::firstOrCreate(['domain' => $domain], [
            'name'               => 'Лид М',
            'is_active'          => true,
            'send_to_crm'        => true,
            'crm_provider'       => CrmProvider::DrivePort->value,
            // Несколько типов → в форме выпадающий список «Тип заявки».
            'allowed_lead_types' => $leadTypes,
            'default_lead_type'  => PlexLeadType::Callback->value,
            'settings'           => [
                'design' => [
                    'primary_color'   => '#667eea',
                    'secondary_color' => '#764ba2',
                    'button_style'    => 'gradient',
                ],
            ],
        ]);

        $fields = [
            ['name' => 'name',    'plex_key' => 'clientName',  'label' => 'ФИО',         'type' => 'text',     'required' => true,  'order' => 1],
            ['name' => 'phone',   'plex_key' => 'clientPhone', 'label' => 'Телефон',     'type' => 'phone',    'required' => true,  'order' => 2],
            ['name' => 'comment', 'plex_key' => 'comment',     'label' => 'Комментарий', 'type' => 'textarea', 'required' => false, 'order' => 3],
        ];

        foreach ($fields as $field) {
            $site->fields()->firstOrCreate(['name' => $field['name']], $field + ['is_active' => true]);
        }

        $this->command->info("Сайт {$site->domain} ({$site->crmProvider()->label()}) готов.");
    }
}
