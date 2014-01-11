<?php

namespace Tests\Unit;

use Arachne\ClassResolver\ClassResolver;
use Arachne\EntityLoader\ParameterFinder;
use Codeception\TestCase\Test;
use Mockery;
use Nette\Application\Request;
use Nette\Caching\Storages\DevNullStorage;

/**
 * @author Jáchym Toušek
 */
class ParameterFinderTest extends Test
{

	/** @var ParameterFinder */
	private $finder;

	protected function _before()
	{
		$presenterFactory = Mockery::mock('Nette\Application\IPresenterFactory');
		$presenterFactory->shouldReceive('getPresenterClass')
			->once()
			->andReturn('Tests\Unit\TestPresenter');
		$storage = Mockery::mock('Nette\Caching\IStorage');
		$storage->shouldReceive('read')
			->once();
		$storage->shouldReceive('lock')
			->once();
		$storage->shouldReceive('write')
			->once();
		$classResolver = new ClassResolver(new DevNullStorage());
		$this->finder = new ParameterFinder($presenterFactory, $classResolver, $storage);
	}

	public function testAction()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'persistent' => 0,
		]);
		$this->assertEquals([
			'persistent1' => 'Tests\Unit\Class1',
			'actionEntity' => 'Tests\Unit\Class2',
		], $this->finder->getEntityParameters($request));
	}

	public function testRenderAndHandle()
	{
		$request = new Request('', 'GET', [
			'action' => 'testRender',
			'do' => 'testHandle',
		]);
		$this->assertEquals([
			'persistent1' => 'Tests\Unit\Class1',
			'renderEntity' => 'Tests\Unit\Class3',
			'handleEntity' => 'Tests\Unit\Class4',
		], $this->finder->getEntityParameters($request));
	}

	public function testComponent()
	{
		$request = new Request('', 'GET', [
			'action' => 'testAction',
			'do' => 'component-testHandle',
			'component-persistent' => 1,
		]);
		$this->assertEquals([
			'persistent1' => 'Tests\Unit\Class1',
			'actionEntity' => 'Tests\Unit\Class2',
			'component-persistent' => 'Tests\Unit\Class5',
			'component-handleEntity' => 'Tests\Unit\Class6',
		], $this->finder->getEntityParameters($request));
	}

}
