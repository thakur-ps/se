<?php
	/*
	Response Class
	Written by Pushpendra Singh Thakur <thakurpsr@gmail.com>
	(C) Business Computing Research Laboratory
	*/
	
	namespace bucorel\se;
	
	class Response{
		
		const OK_OK = 'OK';
		const OK_CREATED = 'CREATED';
		const OK_ACCEPTED = 'ACCEPTED';
		const OK_NO_CONTENT = 'NO_CONTENT';
		
		const ERROR_BAD_REQUEST = 'BAD_REQUEST';
		const ERROR_NOT_FOUND = 'NOT_FOUND';
		const ERROR_CONFLICT = 'CONFLICT';
		const ERROR_ERROR = 'INTERNAL_ERROR';
		
		protected $started = false;
		protected $ended = false;
		protected $dataStarted = false;
		
		protected $corsDomain = "*";
		
		function setCorsDomain( $fqdn ){
			$this->corsDomain = $fqdn;
		}
		
		function start( $status, array $params ){
			
			/* if already started it wont work */
			if( $this->started ){
				return;
			}
			
			switch( $status ){
				case self::OK_OK:
					header( 'HTTP/1.1 200 OK' );
					break;
				case self::OK_CREATED:
					header( 'HTTP/1.1 201 Created' );
					break;
				case self::OK_ACCEPTED:
					header( 'HTTP/1.1 202 Accepted' );
					break;
				case self::OK_NO_CONTENT:
					header( 'HTTP/1.1 204 No Content' );
					break;
					
				case self::ERROR_BAD_REQUEST:
					header( 'HTTP/1.1 400 Bad Request' );
					break;
				case self::ERROR_NOT_FOUND:
					header( 'HTTP/1.1 404 Not Found' );
					break;
				case self::ERROR_CONFLICT:
					header( 'HTTP/1.1 409 Conflict' );
					break;
				case self::ERROR_ERROR:
					header( 'HTTP/1.1 500 Internal Server Error' );
					break;
				default:
					throw new \Exception( 'UNKNOWN_RESPONSE_HEADER' );
			}
			/* Enable Cross Origin Resource Sharing */
			header( 'Access-Control-Allow-Origin: '.$this->corsDomain );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Access-Control-Allow-Methods: POST,GET' );
			
			/* Send Content Type */
			header( 'Content-Type: application/json' );
			
			/* Send Response Header */
			echo '{';
			echo '"_sta":'.json_encode( $status ).',';
			
			/* optional parameters */
			foreach( $params as $k=>$v ){
				echo json_encode($k).':'.json_encode($v).',';
			}
			
			echo '"_dat":[';
			
			$this->started = true;
		}
		
		function end(){
			/* Go forward only if response header has been sent */
			if( $this->started ){
				echo ']}';
				$this->ended = true;
				exit;
			}
		}
		
		function __destruct(){
			/* if process has been terminated without calling end() function then
			trigger the end() function to complete JSON response, otherwise 
			client browser will throw invalid json exception */
			if( !$this->ended ){
				$this->end();
			}
		}
		
		function send( $data ){
			if( !$this->started ){
				$this->start( self::OK_OK, array() );
			}
			
			if( $this->dataStarted ){
				echo ',';
			}else{
				$this->dataStarted = true;
			}
			
			echo json_encode( $data );
		}
		
		function sendOk( $status, $message="" ){
			switch( $status ){
				case self::OK_OK:
				case self::OK_CREATED:
				case self::OK_ACCEPTED:
				case self::OK_NO_CONTENT:
					break;
				default:
					throw new \Exception( 'BAD_OK_STATUS' );
			}
			$this->start( $status, array( '_mes'=>$message ) );
			$this->end();
		}
		
		function sendError( $status, $message, $field="" ){
			switch( $status ){
				case self::ERROR_BAD_REQUEST:
				case self::ERROR_NOT_FOUND:
				case self::ERROR_CONFLICT:
				case self::ERROR_ERROR:
					break;
				default:
					throw new \Exception( 'BAD_ERROR_STATUS' );
			}
			$this->start( $status, array( 
								'_mes'=>$message,
								'_fie'=>$field 
								) );
			$this->end();
		}
	}
?>
