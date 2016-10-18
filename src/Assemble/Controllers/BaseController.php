<?php
/**
 * User: Peter Clotworthy
 * Date: 10/10/16
 * Time: 01:22
 */

namespace Assemble\Controllers;


use Assemble\Models\Person;
use Assemble\Models\PersonQuery;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BaseController extends Controller {
	// GET '/'
	public function getBase(RequestInterface $req, ResponseInterface $res, array $args): ResponseInterface {
		return $this->render($res);
	}

	public function getTestAuth(RequestInterface $req, ResponseInterface $res, array $args) : ResponseInterface{
	    $userdata = $this->ci->user->toArray();
        unset($userdata['Password']);
        return $this->render($res, $userdata);
    }

	public static function hasPermission(ContainerInterface $ci) : bool {
		// TODO: Implement checkPermission() method.
		return true;
	}
}