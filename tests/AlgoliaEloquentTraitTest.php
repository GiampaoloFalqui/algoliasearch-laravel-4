<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Tests\Models\Model2;
use AlgoliaSearch\Tests\Models\Model4;
use AlgoliaSearch\Tests\Models\Model6;
use AlgoliaSearch\Tests\Models\Model7;
use AlgoliaSearch\Tests\Models\Model8;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Mockery;
use Orchestra\Testbench\TestCase;

class AlgoliaEloquentTraitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Config::set('algolia.id', 'your-application-id');
        Config::set('algolia.key', 'your-api-key');
    }

    public function testGetAlgoliaRecordDefault()
    {
        $model2 = new Model2();
        $this->assertEquals(
            ['id2' => 1, 'objectID' => 1],
            $model2->getAlgoliaRecordDefault('test')
        );

        $model4 = new Model4();
        $this->assertEquals(
            ['id2' => 1, 'objectID' => 1, 'id3' => 1, 'name' => 'test'],
            $model4->getAlgoliaRecordDefault('test')
        );

        $model8 = new Model8();
        $this->assertEquals(
            ['indice_attribute_first_test' => 'value_1', 'objectID' => 1337],
            $model8->getAlgoliaRecordDefault('index_first')
        );
        $this->assertEquals(
            ['indice_attribute_second_test' => 'value_2', 'objectID' => null],
            $model8->getAlgoliaRecordDefault('index_second')
        );
    }

    public function testPushToindex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        $index = Mockery::mock('\AlgoliaSearch\Index');

        $model4 = new Model4();
        $modelHelper->shouldReceive('getIndices')->andReturn(array($index, $index));
        $modelHelper->shouldReceive('getObjectId')->andReturn($realModelHelper->getObjectId($model4));
        $modelHelper->shouldReceive('indexOnly')->andReturn(true);

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $index->shouldReceive('addObject')->times(2)->with($model4->getAlgoliaRecordDefault('test'));

        $this->assertEquals(null, $model4->pushToIndex());
    }

    public function testRemoveFromIndex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        $index = Mockery::mock('\AlgoliaSearch\Index');

        $model4 = new Model4();

        $modelHelper->shouldReceive('getIndices')->andReturn(array($index, $index));
        $modelHelper->shouldReceive('getObjectId')->andReturn($realModelHelper->getObjectId($model4));

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $index->shouldReceive('deleteObject')->times(2)->with(1);

        $this->assertEquals(null, $model4->removeFromIndex());
    }

    public function testSetSettings()
    {
        $index = Mockery::mock('\AlgoliaSearch\Index');
        $index->shouldReceive('setSettings')->with(array('slaves' => array('model_6_desc_testing')));
        $index->shouldReceive('setSettings')->with(array('ranking' => array('desc(name)')));

        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');
        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $model6 = new Model6();
        $modelHelper->shouldReceive('getSettings')->andReturn($realModelHelper->getSettings($model6));
        $modelHelper->shouldReceive('getIndices')->andReturn([$index]);
        $modelHelper->shouldReceive('getFinalIndexName')->andReturn($realModelHelper->getFinalIndexName($model6, 'model_6_desc'));
        $modelHelper->shouldReceive('getSlavesSettings')->andReturn($realModelHelper->getSlavesSettings($model6));

        $settings = $realModelHelper->getSettings($model6);
        $this->assertEquals($modelHelper->getFinalIndexName($model6, $settings['slaves'][0]), 'model_6_desc_testing');

        $model6->setSettings();
    }

    public function testSetSynonyms()
    {
        $index = Mockery::mock('\AlgoliaSearch\Index');
        $index->shouldReceive('batchSynonyms')->with(
            [
                [
                    'objectID' => 'red-color',
                    'type'     => 'synonym',
                    'synonyms' => [
                        'red',
                        'really red',
                        'much red'
                    ]
                ]
            ],
            true,
            true
        );

        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');
        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $model7 = new Model7();
        $modelHelper->shouldReceive('getSettings')->andReturn($realModelHelper->getSettings($model7));
        $modelHelper->shouldReceive('getIndices')->andReturn([$index]);
        $modelHelper->shouldReceive('getSlavesSettings')->andReturn($realModelHelper->getSlavesSettings($model7));

        $this->assertEquals(null, $model7->setSettings());
    }

    public function testIndexMultipleIndices()
    {
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');
        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        $model8 = new Model8();

        $index_one = Mockery::mock('\AlgoliaSearch\Index');
        $index_one->indexName = "index_first";
        $index_two = Mockery::mock('\AlgoliaSearch\Index');
        $index_two->indexName = "index_second";

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);
        $modelHelper->shouldReceive('getIndices')->with($model8)->andReturn([$index_one, $index_two]);

        $modelHelper->shouldReceive('getObjectId')->times(2)->andReturn($realModelHelper->getObjectId($model8));

        $modelHelper->shouldReceive('indexOnly')->with($model8, $index_one->indexName)->once()->andReturn(true);
        $index_one->shouldReceive('addObject')->with($model8->getAlgoliaRecordDefault($index_one->indexName));

        $modelHelper->shouldReceive('indexOnly')->with($model8, $index_two->indexName)->once()->andReturn(true);
        $index_two->shouldReceive('addObject')->with($model8->getAlgoliaRecordDefault($index_two->indexName));

        $this->assertEquals(null, $model8->pushToIndex());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
