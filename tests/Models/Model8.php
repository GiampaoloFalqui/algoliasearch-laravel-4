<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model8 extends Model
{
    use AlgoliaEloquentTrait;

    public $indices = ['indice_first', 'indice_second'];

    public function getAlgoliaRecord()
    {
        return [
            'indice_first' => [
                'indice_attribute_first_test' => 'value_1',
                'objectID'                    => 1337
            ],
            'indice_second' => [
                'indice_attribute_second_test' => 'value_2'
            ]
        ];
    }
}
