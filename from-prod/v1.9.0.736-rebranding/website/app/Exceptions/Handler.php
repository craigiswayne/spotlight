<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Exception;
use Illuminate\Database\QueryException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'hashedApiToken',
        'apiToken',
        'api_token'
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function(Exception $exception, $request) {

            if($exception instanceof \GuzzleHttp\Exception\ConnectException) 
            {
                return response()->json($exception->getMessage(), 500);
            }
            else if($exception instanceof \GuzzleHttp\Exception\ServerException)
            {
                return response()->json($exception->getMessage(), 500);
            }
            if($exception instanceof \GuzzleHttp\Exception\ClientException)
            {
                $response = $exception->getResponse();
                if(!$response) {
                    return;
                }

                $reason = $response->getReasonPhrase();
                if(!$reason) {
                    return;
                }
                return response()->json("API - {$reason}", 500);		
            }
            else if($exception instanceof QueryException)
            {
               return $this->handleSqlUserException($exception);
            }   
        });
    }

    function handleSqlUserException($queryException)
	{        
        if(!$queryException || !$queryException->errorInfo) {
			return;
		}
        
        $error = $queryException->errorInfo[2];
        $error = preg_replace('/\[Microsoft\]\[ODBC Driver \d\d for SQL Server\](\[SQL Server\])?/', '', $error);
             
        $messageStartPos = strpos(strtolower($error), "message:");
        if(!$messageStartPos) {
            $messageStartPos = -1;
            $message = $error;
        }

		if($messageStartPos != -1) {
			$message = substr($error, $messageStartPos + 9);	
			$remainingDetails = ltrim(rtrim(substr($error, 0, $messageStartPos)));
			if(substr($remainingDetails, strlen($remainingDetails)-1) === ',') {
				$remainingDetails = ltrim(rtrim(substr($remainingDetails, 0, strlen($remainingDetails)-1)));
			}

			$parts = explode (",", $remainingDetails ,10);		
		} else {
			$parts = explode (",", $error ,10);	
		}		

        if(count($parts) == 1) {
            $message = $parts[0];
        }  
        
        if(count($parts) > 1) {

            $level = null;
            $state = null;        
            foreach($parts as $part)
            {
                $part = ltrim(rtrim($part));
            
                $sections = explode (" ", $part, 5);

                switch(strtolower(ltrim(rtrim($sections[0]))))
                {
                    case "level":
                    if(ctype_digit($sections[1])) {
                        $level = intval($sections[1]);
                    }
                    break;
                    case "state":
                    if(ctype_digit($sections[1])) {
                        $state = intval($sections[1]);
                    }
                    break;
                    case "message:":
                    $message = join(' ', array_slice($sections, 1)); ;
                    break;
                } 
            }
        }

        if(env('APP_DEBUG', false) || ($level == 12 && $state == 1)) {
            return response()->json($message, 500);
		} else {
            return response()->json("An database exception occured", 500);		
        }    
	}
}
