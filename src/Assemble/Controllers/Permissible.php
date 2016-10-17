<?php
/**
 * User: Peter Clotworthy
 * Date: 12/10/16
 * Time: 03:41
 */

namespace Assemble\Controllers;


use Interop\Container\ContainerInterface;

interface Permissible {
	public static function hasPermission(ContainerInterface $ci) : bool;
}