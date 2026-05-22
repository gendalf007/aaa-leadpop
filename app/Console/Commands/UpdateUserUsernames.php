<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserUsernames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-usernames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update usernames for existing users based on their email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('username')->get();
        
        if ($users->isEmpty()) {
            $this->info('Все пользователи уже имеют username.');
            return;
        }
        
        $this->info("Найдено {$users->count()} пользователей без username.");
        
        foreach ($users as $user) {
            // Создаем username из email (часть до @)
            $username = explode('@', $user->email)[0];
            
            // Проверяем, не занят ли уже такой username
            $counter = 1;
            $originalUsername = $username;
            while (User::where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            $user->update(['username' => $username]);
            $this->line("Пользователь {$user->name} ({$user->email}) получил username: {$username}");
        }
        
        $this->info('Обновление завершено!');
    }
}
