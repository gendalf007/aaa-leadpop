<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {email} {password} {--name=Администратор}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать администратора';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->option('name');

        // Проверяем, существует ли уже пользователь с таким email
        if (User::where('email', $email)->exists()) {
            $this->error("Пользователь с email {$email} уже существует!");
            return 1;
        }

        // Создаем администратора
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info("Администратор успешно создан!");
        $this->info("Email: {$email}");
        $this->info("Имя: {$name}");
        $this->info("Пароль: {$password}");

        return 0;
    }
}
