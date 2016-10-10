<?php
/**
 * User: Peter Clotworthy
 * Date: 10/10/16
 * Time: 01:22
 */

namespace Assemble\Controllers;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Base extends Controller {
	// GET '/'
	public function getBase(RequestInterface $req, ResponseInterface $res, array $args){
		return $this->render($res);
	}

	public function postRegister(RequestInterface $req, ResponseInterface $res, array $args){
		$data = $req->getParsedBody();
		// TODO: do something with the parsed data (native PHP array), then return status codes and what not.
		$this->render($res, $data);
	}
}