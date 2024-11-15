<?php
declare(strict_types=1);
namespace Database\Factories;

use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

class BaseFactory
{
    protected string $modelClass = Model::class;

    /**
     * Создать модель и добавить запись в БД
     */
    public function create(array $attributes = []): Model
    {
        $model = $this->make($attributes);

        $model->save();

        return $model;
    }

    /**
     * Создать модель без добавления записи в БД
     */
    public function make(array $attributes = []): Model
    {
        $model = new $this->modelClass();

        $attributes = array_merge($this->definition(), $attributes);

        foreach ($attributes as $key => $value) {
            $model->$key = $value;
        }

        return $model;
    }

    /**
     * Определить модель
     */
    public function definition(): array
    {
        return [];
    }
}
