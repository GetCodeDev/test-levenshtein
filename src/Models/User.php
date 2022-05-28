<?php
namespace GetCodeDev\TestLevenshtein\Models;

use GetCodeDev\TestLevenshtein\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Kirschbaum\PowerJoins\PowerJoins;

class User extends Model
{
    use HasFactory, PowerJoins;

    public $table = 'users';

    protected $guarded = [];

    protected static function newFactory()
    {
        return UserFactory::new();
    }


    /**
     * @return BelongsTo
     */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
