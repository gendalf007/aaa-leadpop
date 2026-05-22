<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\FormField;
use App\Models\FormRequest;

class TestSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем тестовый сайт
        $site = Site::create([
            'name' => 'Тестовый сайт',
            'domain' => 'test.local',
            'is_active' => true,
            'settings' => [
                'design' => [
                    'primary_color' => '#667eea',
                    'secondary_color' => '#764ba2'
                ],
                'crm' => [
                    'enabled' => false
                ]
            ]
        ]);
        
        // Создаем поля формы
        $fields = [
            [
                'name' => 'name',
                'label' => 'Имя',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Введите ваше имя',
                'order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'phone',
                'label' => 'Телефон',
                'type' => 'phone',
                'required' => true,
                'placeholder' => '+7 (999) 123-45-67',
                'order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => false,
                'placeholder' => 'example@mail.com',
                'order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'comment',
                'label' => 'Комментарий',
                'type' => 'textarea',
                'required' => false,
                'placeholder' => 'Опишите вашу задачу',
                'order' => 4,
                'is_active' => true
            ]
        ];
        
        foreach ($fields as $fieldData) {
            FormField::create(array_merge($fieldData, ['site_id' => $site->id]));
        }
        
        $this->command->info('Тестовый сайт создан: test.local');
    }
}
