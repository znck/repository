<?php namespace Znck\Tests\Repositories;

use GrahamCampbell\TestBench\AbstractTestCase;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Znck\Repositories\Contracts\CriteriaInterface;
use Znck\Repositories\Contracts\RepositoryQueryInterface;
use Znck\Repositories\Exceptions\RepositoryException;
use Znck\Repositories\Repository;

class RepositoryTest extends AbstractTestCase
{
    /**
     * @var Container
     */
    protected $app;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $builder;

    public function makeBuilder()
    {
        if ($this->builder) {
            return $this->builder;
        }
        $this->builder = $mocked = $this
            ->getMockBuilder(DummyModelForMocking::class)
            ->setMethods(['get', 'count', 'find', 'where', 'paginate', 'first'])
            ->getMockForAbstractClass();
        $mocked->method('get')->willReturn(new Collection());
        $mocked->method('count')->willReturn(0);
        $mocked->method('find')->willReturn(null);
        $mocked->method('where')->willReturn($mocked);
        $mocked->method('paginate')->willReturn(null);
        $mocked->method('first')->willReturn(null);
        return $mocked;
    }

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    public function makeModel()
    {
        if ($this->model) {
            return $this->model;
        }

        $this->model = $mocked = $this
            ->getMockBuilder(DummyModelForMocking::class)
            ->setMethods(['newQuery'])
            ->getMockForAbstractClass();
        $mocked->method('newQuery')->willReturn($this->makeBuilder());

        return $mocked;
    }

    public function makeRepository($methods = [])
    {
        if (! $this->app) {
            $this->app = new Container();
            $this->app->singleton(DummyModelForMocking::class, function () {return $this->makeModel();});
        }
        $mocked = $this
          ->getMockBuilder(DummyRepositoryForMocking::class)
          ->setMethods(array_merge([], $methods))
          ->setConstructorArgs([$this->app, new Collection()])
          ->getMockForAbstractClass();

        return $mocked;
    }

    public function makeCriteria($methods = [])
    {
        $mocked = $this
            ->getMockBuilder(DummyCriteriaForMocking::class)
            ->setMethods($methods)
            ->getMock();

        return $mocked;
    }

    public function test_it_can_get_all()
    {
        $mock = $this->makeRepository();
        $this->builder->expects($this->once())->method('get');
        $mock->all();
    }

    public function test_it_can_get_count()
    {
        $mock = $this->makeRepository();
        $this->builder->expects($this->exactly(1))->method('count');
        $mock->count();
    }

    public function test_it_can_get_find()
    {
        $mock = $this->makeRepository();
        $this->builder->expects($this->exactly(2))->method('find');
        $mock->disableHttpMode()->find(1);
        $this->expectException(NotFoundHttpException::class);
        $mock->enableHttpMode()->find(1);
    }

    public function test_it_can_get_findBy()
    {
        $mock = $this->makeRepository();
        $this->builder->expects($this->exactly(2))->method('first');
        $this->builder->expects($this->exactly(2))->method('where');
        $mock->disableHttpMode()->findBy('id', 1);
        $this->expectException(NotFoundHttpException::class);
        $mock->enableHttpMode()->findBy('id', 1);
    }

    public function test_it_can_get_paginate()
    {
        $mock = $this->makeRepository();
        $this->builder->expects($this->exactly(1))->method('paginate');
        $mock->paginate();
    }

    public function test_it_can_get_where()
    {
        $mock = $this->makeRepository();
        $this->builder->expects($this->exactly(3))->method('where')->with('id', '=', 1, 'and');
        $mock->where(['id', 1]);
        $mock->where(['id', '=', 1]);
        $mock->where(['id', '=', 1, 'and']);
        $this->expectException(\InvalidArgumentException::class);
        $mock->where([]);
    }

    public function test_it_can_push_criteria()
    {
        $mock = $this->makeRepository();
        $mock->pushCriteria(new DummyCriteriaForMocking());
        $this->assertCount(1, $mock->getCriteria());
    }

    public function test_it_can_skip_criteria()
    {
        $mock = $this->makeRepository();
        $criteria = $this->makeCriteria(['apply']);
        $criteria->expects($this->once())->method('apply')->willReturn($this->builder);
        $mock->pushCriteria($criteria);
        $mock->all();

        $mock->skipCriteria();
        $mock->all();
    }

    public function test_it_can_get_by_criteria()
    {
        $mock = $this->makeRepository();
        $criteria1 = $this->makeCriteria(['apply']);
        $criteria1->expects($this->once())->method('apply')->willReturn($this->builder);
        $mock->pushCriteria($criteria1);
        $criteria2 = $this->makeCriteria(['apply']);
        $criteria2->expects($this->once())->method('apply')->willReturn($this->builder);

        $mock->getByCriteria($criteria2)->all();

        $this->assertCount(1, $mock->getCriteria());
    }

    public function test_it_can_work_with_model_function()
    {
        $this->makeRepository();
        $mock = new DummyRepositoryWithModelFunction($this->app, new Collection());
        $this->builder->expects($this->once())->method('get');
        $mock->all();
    }

    public function test_it_cannot_work_without_model()
    {
        $this->expectException(RepositoryException::class);
        $this->makeRepository();
        $mock = new DummyRepositoryWithoutModel($this->app, new Collection());
        $this->builder->expects($this->once())->method('get');
        $mock->all();
    }

    public function test_it_cannot_work_without_eloquent()
    {
        $this->expectException(RepositoryException::class);
        $this->makeRepository();
        new DummyRepositoryWithWrongModel($this->app, new Collection());
    }
}

class DummyCriteriaForMocking implements CriteriaInterface
{
    public function apply($query, RepositoryQueryInterface $repository)
    {
        return $query;
    }
}
class DummyInvalidModel
{
}
class DummyModelForMocking extends Model
{
}
class DummyRepositoryWithWrongModel extends Repository
{
    protected $model = DummyInvalidModel::class;
    public function create(array $attributes)
    {
    }
    public function delete($id)
    {
    }
    public function update(array $attributes, $id)
    {
    }
}
class DummyRepositoryWithoutModel extends Repository
{
    public function create(array $attributes)
    {
    }
    public function delete($id)
    {
    }
    public function update(array $attributes, $id)
    {
    }
}
class DummyRepositoryWithModelFunction extends Repository
{
    public function model()
    {
        return DummyModelForMocking::class;
    }
    public function create(array $attributes)
    {
    }
    public function delete($id)
    {
    }
    public function update(array $attributes, $id)
    {
    }
}
class DummyRepositoryForMocking extends Repository
{
    protected $model = DummyModelForMocking::class;
    public function create(array $attributes)
    {
    }
    public function delete($id)
    {
    }
    public function update(array $attributes, $id)
    {
    }
}
