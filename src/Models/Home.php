<?php
namespace GetCodeDev\TestLevenshtein\Models;

use GetCodeDev\TestLevenshtein\Database\Factories\HomeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Home extends Model
{
    use HasFactory;

    public $table = 'homes';

    protected $guarded = [];

    protected static function newFactory()
    {
        return HomeFactory::new();
    }
}
