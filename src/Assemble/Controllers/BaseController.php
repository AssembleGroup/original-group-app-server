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
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class BaseController extends Controller
{
    // GET '/'
    public function getBase(RequestInterface $req, Response $res, array $args): ResponseInterface {
        return $this->successRender($res);
    }

    public function getTestAuth(RequestInterface $req, Response $res, array $args) : ResponseInterface {
        $userdata = $this->ci->user->toArray();
        unset($userdata['Password']);
        return $this->successRender($res, $userdata);
    }

    public function getLogs(Request $req, Response $res, array $args): ResponseInterface {
        $listing = array_diff(@scandir($this->ci['settings']['logDir']), array('..', '.', '.gitignore'));
	    $additional = "";
	    $size = sizeof($listing);
	    if($size == 0)
	    	$additional .= "<h1>No log files. Try <a href='https://assemblegroup.github.io/original-group-app-server/'>performing an action</a> first.</h1>";
	    else
	    	$additional .= "<h1>$size stored currently:</h1>";
	    $additional.= "<p>Note: logs are only written on changes or errors (i.e. not GET /user, but logs with POST /register)</p>";
        $body = $res->getBody();
        $body->write("<!DOCTYPE html><html>$additional<ul>");
        foreach ($listing as $logfile) {
            $body->write("<li><a href='" . $req->getUri()->getPath() . '/' . $logfile . "'>$logfile</a></li>");
        }
        $body->write('</ul></html>');

        return $res->withBody($body);
    }

    public function getSpecificLog(Request $req, Response $res, array $args): ResponseInterface {
        $log = file_get_contents($this->ci['settings']['logDir'] . $args['logName']);
        if($log === false)
            return $this->ci['notFoundHandler']();

        return $res->write('<h1>Note, the timezone of this server is ' . date_timezone_get(new \DateTime())->getName() . ' </h1> <pre><code>' . $log . '</code></pre>');
    }
}