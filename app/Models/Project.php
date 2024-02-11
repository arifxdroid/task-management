<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['project_code', 'name', 'user_id'];

    public static function generateUniqueProjectCode(): string
    {
        // Generate a unique project code (you can customize this logic as needed)
        $code = 'PRJ' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // Check if the generated code already exists
        while (self::where('project_code', $code)->exists()) {
            // Regenerate the code if it already exists
            $code = 'PRJ' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        }

        return $code;
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }
}
