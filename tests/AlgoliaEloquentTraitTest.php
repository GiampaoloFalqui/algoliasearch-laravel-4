<?php

namespace AlgoliaSearch\Tests;

use AlgoliaSearch\Tests\Models\Model2;
use AlgoliaSearch\Tests\Models\Model4;
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
        $model4 = new Model4();
        $this->assertEquals(array('id2' => 1, 'objectID' => 1), $model2->getAlgoliaRecordDefault());
        $this->assertEquals(array('id2' => 1, 'objectID' => 1, 'id3' => 1, 'name' => 'test'), $model4->getAlgoliaRecordDefault());
    }

    public function testPushToindex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        $index = Mockery::mock('\AlgoliaSearch\Index');

        $modelHelper->shouldReceive('getIndices')->andReturn(array($index, $index));
        $modelHelper->shouldReceive('getObjectId')->andReturn($realModelHelper->getObjectId(new Model4()));
        $modelHelper->shouldReceive('indexOnly')->andReturn(true);

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $index->shouldReceive('addObject')->times(2)->with((new Model4())->getAlgoliaRecordDefault());

        $this->assertEquals(null, (new Model4())->pushToIndex());
    }

    public function testRemoveFromIndex()
    {
        /** @var \AlgoliaSearch\Laravel\ModelHelper $realModelHelper */
        $realModelHelper = App::make('\AlgoliaSearch\Laravel\ModelHelper');

        $modelHelper = Mockery::mock('\AlgoliaSearch\Laravel\ModelHelper');

        $index = Mockery::mock('\AlgoliaSearch\Index');

        $modelHelper->shouldReceive('getIndices')->andReturn(array($index, $index));
        $modelHelper->shouldReceive('getObjectId')->andReturn($realModelHelper->getObjectId(new Model4()));

        App::instance('\AlgoliaSearch\Laravel\ModelHelper', $modelHelper);

        $index->shouldReceive('deleteObject')->times(2)->with(1);

        $this->assertEquals(null, (new Model4())->removeFromIndex());
    }

    public function tearDown()
    {
        Mockery::close();
    }
}
