<?php
namespace GetCodeDev\TestLevenshtein\Models;

use GetCodeDev\TestLevenshtein\Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    public $table = 'test';

    protected $guarded = [];
}
