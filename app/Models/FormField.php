<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = [
        'site_id',
        'name',
        'plex_key',
        'label',
        'type',
        'required',
        'placeholder',
        'options',
        'validation_rules',
        'order',
        'is_active'
    ];
    
    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'required' => 'boolean',
        'is_active' => 'boolean'
    ];
    
    /**
     * Отношение к сайту
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
    
    /**
     * Получить правила валидации для поля
     */
    public function getValidationRules()
    {
        $rules = $this->validation_rules ?? [];
        
        // Добавляем базовые правила в зависимости от типа
        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'phone':
                $rules[] = 'regex:/^\+?[0-9\s\-\(\)]+$/';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'file':
                $rules[] = 'file';
                break;
        }
        
        if ($this->required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        
        return $rules;
    }
    
    /**
     * Получить HTML атрибуты для поля
     */
    public function getHtmlAttributes()
    {
        $attributes = [
            'id' => $this->name,
            'name' => $this->name,
            'class' => 'form-control'
        ];
        
        if ($this->required) {
            $attributes['required'] = 'required';
        }
        
        if ($this->placeholder) {
            $attributes['placeholder'] = $this->placeholder;
        }
        
        // Специфичные атрибуты для разных типов
        switch ($this->type) {
            case 'email':
                $attributes['type'] = 'email';
                break;
            case 'phone':
                $attributes['type'] = 'tel';
                break;
            case 'number':
                $attributes['type'] = 'number';
                break;
            case 'date':
                $attributes['type'] = 'date';
                break;
            case 'url':
                $attributes['type'] = 'url';
                break;
            case 'file':
                $attributes['type'] = 'file';
                break;
            default:
                $attributes['type'] = 'text';
        }
        
        return $attributes;
    }
    
    /**
     * Проверить, является ли поле обязательным
     */
    public function isRequired()
    {
        return $this->required;
    }
    
    /**
     * Проверить, активено ли поле
     */
    public function isActive()
    {
        return $this->is_active;
    }
    
    /**
     * Получить опции для select/radio/checkbox
     */
    public function getOptions()
    {
        return $this->options ?? [];
    }
    
    /**
     * Проверить, имеет ли поле опции
     */
    public function hasOptions()
    {
        return in_array($this->type, ['select', 'radio', 'checkbox']) && !empty($this->options);
    }
}
