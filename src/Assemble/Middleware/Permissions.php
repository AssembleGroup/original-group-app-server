<?php
/**
 * Created by PhpStorm.
 * User: sacredskull
 * Date: 08/10/16
 * Time: 00:58
 */

namespace Assemble\Middleware;

use MyCLabs\Enum\Enum;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method static PermissionLevel GUEST()
 * @method static PermissionLevel USER()
 * @method static PermissionLevel ADMIN()
 */
class PermissionLevel extends Enum {
	const GUEST = 0;
	const USER = 1;
	const ADMIN = 2;

	public function __construct() {
		// Set default
		parent::__construct(self::GUEST);
	}
}

class Permissions {
	private $level;
	public function __construct(PermissionLevel $level = PermissionLevel::GUEST) {
		$this->level = $level;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
		// TODO: All the permission things
		return $next($request, $response);
	}
}