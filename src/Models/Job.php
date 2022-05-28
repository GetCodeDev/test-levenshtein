<?php
namespace GetCodeDev\TestLevenshtein\Models;

use GetCodeDev\TestLevenshtein\Database\Factories\JobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    use HasFactory;

    public $table = 'jobs';

    protected $guarded = [];

    protected static function newFactory()
    {
        return JobFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
