<?php

declare(strict_types=1);

namespace Controller;

use OCA\IamDiskbg\AppInfo\Application;
use OCA\IamDiskbg\Controller\ApiController;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase {
	public function testIndex(): void {
		$request = $this->createMock(IRequest::class);
		$controller = new ApiController(Application::APP_ID, $request);

		$this->assertEquals($controller->index()->getData()['message'], 'Hello world!');
	}
}
