<?php

namespace AlgoliaSearch\Tests\Models;

use AlgoliaSearch\Laravel\AlgoliaEloquentTrait;
use Illuminate\Database\Eloquent\Model;

class Model8 extends Model
{
    use AlgoliaEloquentTrait;

    public $indices = ['index_first', 'index_second'];

    public function getAlgoliaRecord($indexName)
    {
        if ($indexName === 'index_first') {
            return [
                'indice_attribute_first_test' => 'value_1',
                'objectID'                    => 1337
            ];
        }

        if ($indexName === 'index_second') {
            return [
                'indice_attribute_second_test' => 'value_2'
            ];
        }
    }
}
