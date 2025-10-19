<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSetting extends Model
{
    use HasFactory, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
        'is_active',
        'locale',
        'last_modified_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_modified_at' => 'datetime',
    ];

    /**
     * Get the admin who created this setting.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get the admin who last updated this setting.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    /**
     * Scope for active settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for settings by group.
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope for settings by locale.
     */
    public function scopeByLocale($query, string $locale = 'en')
    {
        return $query->where('locale', $locale);
    }

    /**
     * Get setting value by key.
     */
    public static function getValue(string $key, $default = null, string $locale = 'en')
    {
        $setting = static::where('key', $key)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key.
     */
    public static function setValue(string $key, $value, string $group = 'general', string $locale = 'en', ?int $adminId = null)
    {
        return static::updateOrCreate(
            [
                'key' => $key,
                'locale' => $locale,
            ],
            [
                'value' => $value,
                'group' => $group,
                'is_active' => true,
                'last_modified_at' => now(),
                'updated_by' => $adminId,
                'created_by' => $adminId,
            ]
        );
    }

    /**
     * Get all settings by group as key-value array.
     */
    public static function getByGroup(string $group, string $locale = 'en'): array
    {
        return static::byGroup($group)
            ->byLocale($locale)
            ->active()
            ->pluck('value', 'key')
            ->toArray();
    }
}