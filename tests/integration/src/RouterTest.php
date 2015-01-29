<?php

namespace Tests\Integration;

use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Http\Request as HttpRequest;
use Nette\Http\UrlScript;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class RouterTest extends Test
{

	/** @var IRouter */
	private $router;

	public function _before()
	{
		parent::_before();
		$this->router = $this->guy->grabService(IRouter::class);
	}

	public function testValidRequest()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('nullable'));
		$request = $this->router->match($httpRequest);
		$this->assertInstanceOf(Request::class, $request);
		$parameters = $request->getParameters();
		$this->assertArrayNotHasKey('entity', $parameters);
	}

	/**
	 * @expectedException \Nette\Application\BadRequestException
	 * @expectedExceptionCode 404
	 * @expectedExceptionMessage Request has invalid parameter.
	 */
	public function testInvalidParameter()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('notnullable'));
		$this->router->match($httpRequest);
	}

	/**
	 * @expectedException \Nette\Application\BadRequestException
	 * @expectedExceptionCode 404
	 * @expectedExceptionMessage Request has invalid presenter.
	 */
	public function testInvalidPresenter()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('wrong'));
		$this->router->match($httpRequest);
	}

	/**
	 * @expectedException \Nette\Application\BadRequestException
	 * @expectedExceptionCode 404
	 * @expectedExceptionMessage Request has invalid presenter.
	 */
	public function testNoAction()
	{
		$httpRequest = new HttpRequest($this->createUrlScript('noaction'));
		$this->router->match($httpRequest);
	}

	private function createUrlScript($url, array $params = array())
	{
		$urlScript = new UrlScript('http://example.com/' . $url);
		$urlScript->appendQuery($params);
		return $urlScript;
	}

}
