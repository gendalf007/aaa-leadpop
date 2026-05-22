<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\CrmMapping;

class CrmMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Найдем первый сайт
        $site = Site::first();
        
        if (!$site) {
            $this->command->info('Сайты не найдены. Сначала создайте сайт.');
            return;
        }
        
        // Создаем пример маппинга
        $mappings = [
            [
                'crm_field' => 'phone',
                'mapping_value' => '{phone}',
                'value_type' => 'field',
                'order' => 0
            ],
            [
                'crm_field' => 'name_first',
                'mapping_value' => '{first_name}',
                'value_type' => 'field',
                'order' => 1
            ],
            [
                'crm_field' => 'name_last',
                'mapping_value' => '{last_name}',
                'value_type' => 'field',
                'order' => 2
            ],
            [
                'crm_field' => 'name_middle',
                'mapping_value' => '{middle_name}',
                'value_type' => 'field',
                'order' => 3
            ],
            [
                'crm_field' => 'source_id',
                'mapping_value' => '{source_id}',
                'value_type' => 'field',
                'order' => 4
            ],
            [
                'crm_field' => 'entry_point',
                'mapping_value' => '{site_entry_point}',
                'value_type' => 'field',
                'order' => 5
            ],
            [
                'crm_field' => 'comment',
                'mapping_value' => '{comment}',
                'value_type' => 'field',
                'order' => 6
            ],
            [
                'crm_field' => 'user_info',
                'mapping_value' => 'Заявка от пользователя {user_name} ({user_email})',
                'value_type' => 'template',
                'order' => 7
            ],
            [
                'crm_field' => 'operator_id',
                'mapping_value' => '{user_id}',
                'value_type' => 'field',
                'order' => 8
            ],
            [
                'crm_field' => 'operator_name',
                'mapping_value' => '{user_name}',
                'value_type' => 'field',
                'order' => 9
            ],
            [
                'crm_field' => 'operator_login',
                'mapping_value' => '{user_username}',
                'value_type' => 'field',
                'order' => 10
            ],
            [
                'crm_field' => 'is_admin_lead',
                'mapping_value' => '{user_is_admin}',
                'value_type' => 'field',
                'order' => 11
            ],
            [
                'crm_field' => 'lead_source',
                'mapping_value' => 'Веб-форма с сайта {site_name}',
                'value_type' => 'template',
                'order' => 12
            ],
            [
                'crm_field' => 'additional_info',
                'mapping_value' => 'Сайт: {site_domain}, Оператор: {user_display_name}, ID заявки: {source_id}',
                'value_type' => 'template',
                'order' => 13
            ]
        ];
        
        foreach ($mappings as $mapping) {
            CrmMapping::create([
                'site_id' => $site->id,
                'crm_field' => $mapping['crm_field'],
                'mapping_value' => $mapping['mapping_value'],
                'value_type' => $mapping['value_type'],
                'is_active' => true,
                'order' => $mapping['order']
            ]);
        }
        
        $this->command->info("Создан маппинг CRM для сайта: {$site->name}");
    }
}
