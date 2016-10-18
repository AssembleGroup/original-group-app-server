<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 10/10/16
 * Time: 16:55
 */

namespace Assemble\Controllers;

use MyCLabs\Enum\Enum;

class ErrorCodes extends Enum {
	// Server errors
	const SERVER_UNKNOWN_ERROR = [-1, 'An unknown error occurred.'];

	// Client errors
    const CLIENT_VAGUE_BAD_LOGIN = [1, 'Incorrect login details.'];
    const CLIENT_VAGUE_BAD_REGISTRATION = [2, 'The submitted registration details did not meet requirements.'];
    const CLIENT_EXISTING_USERNAME = [3, 'That username is not available.'];
	const CLIENT_NONEXISTENT_ENTITY = [4, 'A resource with that ID could not be found.'];

	const CLIENT_VAGUE_BAD_GROUP_CREATION = [5, 'The submitted group details did not meet requirements.'];
	const CLIENT_EXISTING_GROUP_NAME = [6, 'A group already exists with this name.'];

    public function __construct() {
        // Set default
        parent::__construct(self::SERVER_UNKNOWN_ERROR);
    }
}

class Error {
    public $code;
    public $message;

    public function __construct(array $code = ErrorCodes::SERVER_UNKNOWN_ERROR, $message = null) {
        $this->code = $code[0];
        $this->message = $message ?? $code[1];
    }
}