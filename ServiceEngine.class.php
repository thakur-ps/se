<?php
	namespace se;
	
	class ServiceEngine{
		
		const STATUS_CREATED = 'HTTP/1.1 201 Created';
		const STATUS_ACCEPTED = 'HTTP/1.1 202 Accepted';
		const STATUS_NOT_MODIFIED = 'HTTP/1.1 304 Not Modified';
		const STATUS_BAD_REQUEST = 'HTTP/1.1 400 Bad Request';
		const STATUS_UNAUTHORIZED = 'HTTP/1.1 401 Unauthorized';
		const STATUS_PAYMENT_REQUIRED = 'HTTP/1.1 402 Payment Required';
		const STATUS_FORBIDDEN = 'HTTP/1.1 403 Forbidden';
		const STATUS_NOT_FOUND = 'HTTP/1.1 404 Not Found';
		const STATUS_METHOD_NOT_ALLOWED = 'HTTP/1.1 405 Method Not Allowed';
		const STATUS_NOT_ACCEPTABLE = 'HTTP/1.1 406 Not Acceptable';
		const STATUS_INTERNAL_ERROR = 'HTTP/1.1 500 Internal Server Error';
		const STATUS_SERVICE_UNAVAILABLE = 'HTTP/1.1 503 Service Unavailable';
		
		const METHOD_GET = 'GET';
		const METHOD_PUT = 'PUT';
		const METHOD_POST = 'POST';
		const METHOD_DELETE = 'DELETE';
		
		public $path = '';
		public $urlMap = array();
		
		protected $defaultUser = '';
		protected $defaultPassword = '';
		
		function setDefaultCredentials( $userName, $password ){
			$this->defaultUser = $userName;
			$this->defaultPassword = $password;
		}
		
		function setHandler( $module, $object, $method, $className ){
			$this->urlMap[ $module ][ $object ][ $method ] = $className ;
		}
		
		function start(){
			if( !isset( $_REQUEST[ '_url' ] ) || $_REQUEST[ '_url' ] == "" ){
				self::sendHttpError( self::STATUS_FORBIDDEN );
			}
			
			$parts = explode( '/', $_REQUEST['_url'] );
			
			if( count( $parts ) < 2 ){
				self::sendHttpError( self::STATUS_FORBIDDEN );
			}
			
			/* check whether module exists */
			if( !isset( $this->urlMap[ $parts[0] ] ) ){
				self::sendHttpError( self::STATUS_NOT_FOUND, 'module not found' );
			}
			
			/* check whether object exists */
			if( !isset( $this->urlMap[ $parts[0] ][ $parts[1] ] ) ){
				self::sendHttpError( self::STATUS_NOT_FOUND, 'object not found' );
			}
			
			/* check whether method is supported */
			if( !isset( $this->urlMap[ $parts[0] ][ $parts[1] ][ $_SERVER['REQUEST_METHOD'] ] ) ){
				self::sendHttpError( self::STATUS_METHOD_NOT_ALLOWED, 'method not allowed' );
			}
			
			$className = $this->urlMap[ $parts[0] ][ $parts[1] ][ $_SERVER['REQUEST_METHOD'] ];
			
			$file = $this->path.$parts[0].'/'.$parts[1].'/'.$className.'.class.php';
			
			if( !file_exists( $file ) ){
				self::sendHttpError( self::STATUS_INTERNAL_ERROR, 'handler is missing' );
			}
			
			$this->authenticationCheck();
			
			include $file;
			$h = new $className;
			$h->handleRequest();
		}
		
		public static function sendHttpError( $status, $message="" ){
			header( $status );
			echo "\r\n";
			echo $message;
			exit;
		}
		
		function authenticationCheck(){
			if( !isset( $_SERVER[ 'PHP_AUTH_USER' ] ) || !isset( $_SERVER[ 'PHP_AUTH_PW' ] ) ){
				self::sendHttpError( self::STATUS_UNAUTHORIZED, 'unauthorized' );
			}
			
			if( $_SERVER['PHP_AUTH_USER'] == $this->defaultUser &&
				$_SERVER['PHP_AUTH_PW'] == $this->defaultPassword ){
				return true;
			}
			
			self::sendHttpError( self::STATUS_UNAUTHORIZED, 'unauthorized' );
		}
	}
?>
