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
    public function getBase(RequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        return $this->successRender($res);
    }

    public function getTestAuth(RequestInterface $req, ResponseInterface $res, array $args) : ResponseInterface
    {
        $userdata = $this->ci->user->toArray();
        unset($userdata['Password']);
        return $this->successRender($res, $userdata);
    }

    public function getLogs(Request $req, Response $res, array $args): ResponseInterface
    {
        $listing = array_diff(scandir($this->ci['settings']['logDir']), array('..', '.', '.gitignore'));
        $body = $res->getBody();
        $body->write('<li>');
        foreach ($listing as $logfile) {
            $body->write("<a href='" . $req->getUri()->getPath() . '/' . $logfile . "'>$logfile</a>");
        }
        $body->write('</li>');


        return $res->withBody($body);
    }

    public function getSpecificLog(Request $req, Response $res, array $args): ResponseInterface {
        $log = file_get_contents($this->ci['settings']['logDir'] . $args['logName']);
        if($log === false)
            return $this->ci['notFoundHandler']();

        return $res->write('<h1>Note, the timezone of this server is ' . date_timezone_get(new \DateTime())->getName() . ' </h1> <pre><code>' . $log . '</code></pre>');
    }
}