<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 10/10/16
 * Time: 16:55
 */

namespace Assemble\Controllers;

use Exception;
use MyCLabs\Enum\Enum;

class ImageException extends Exception { }

class ErrorCodes extends Enum {
	// Server errors
	const SERVER_UNKNOWN_ERROR = [-1, 'An unknown error occurred.', 500];

	// Client errors
    const CLIENT_VAGUE_BAD_LOGIN = [1, 'Incorrect login details.', 401];
    const CLIENT_BAD_REQUEST = [10, 'The request was not suitable.', 400];
    const CLIENT_VAGUE_BAD_REGISTRATION = [2, 'The submitted registration details did not meet requirements.', 422];
    const CLIENT_EXISTING_USERNAME = [3, 'That username is not available.', 422];
	const CLIENT_NONEXISTENT_ENTITY = [4, 'A resource with that ID could not be found.', 404];

	const CLIENT_VAGUE_BAD_GROUP_CREATION = [5, 'The submitted group details did not meet requirements.', 422];
    const CLIENT_EXISTING_GROUP_NAME = [6, 'A group already exists with this name.', 422];
    const CLIENT_VAGUE_BAD_POST_CREATION = [7, 'The submitted post details did not meet requirements.', 422];
	const CLIENT_BAD_IMAGE = [8, 'The image submitted cannot be processed.', 422];

    public function __construct() {
        // Set default
        parent::__construct(self::SERVER_UNKNOWN_ERROR);
    }
}

class Error {
    public $code;
    public $message;
    public $httpCode;

    public function __construct(array $code = ErrorCodes::SERVER_UNKNOWN_ERROR, $message = null, $httpCode = null) {
        $this->code = $code[0];
        $this->message = $message ?? $code[1];
        $this->httpCode = $httpCode ?? $code[2];
    }
}