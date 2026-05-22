<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
    
    /**
     * Проверить, является ли пользователь администратором
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }
    
    /**
     * Отношение к сайтам через промежуточную таблицу
     */
    public function sites()
    {
        return $this->belongsToMany(Site::class, 'user_sites');
    }
    
    /**
     * Проверить, имеет ли пользователь доступ к сайту
     */
    public function hasAccessToSite($siteId)
    {
        if ($this->isAdmin()) {
            return true;
        }
        
        return $this->sites()->where('site_id', $siteId)->exists();
    }
    
    /**
     * Получить все сайты, к которым у пользователя есть доступ
     */
    public function getAccessibleSites()
    {
        if ($this->isAdmin()) {
            return Site::all();
        }
        
        return $this->sites;
    }
    
    /**
     * Найти пользователя по username или email
     */
    public static function findByUsernameOrEmail($value)
    {
        return static::where('username', $value)
            ->orWhere('email', $value)
            ->first();
    }
    
    /**
     * Получить отображаемое имя (username или email)
     */
    public function getDisplayName()
    {
        return $this->username ?: $this->email;
    }
    
    /**
     * Отношение к заявкам
     */
    public function formRequests()
    {
        return $this->hasMany(FormRequest::class);
    }
}
