<?php
	namespace bucorel\se;
	
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
		
		public $db = null;
		public $cache = null;
		
		function setDefaultCredentials( $userName, $password ){
			$this->defaultUser = $userName;
			$this->defaultPassword = $password;
		}
		
		function setHandler( $url, $method, $className ){
			$url = $this->removeBothEndSlashes( $url );
			$this->urlMap[ $url ][ $method ] = $className ;
		}
		
		function start(){
			if( !isset( $_REQUEST[ '_url' ] ) || $_REQUEST[ '_url' ] == "" ){
				self::sendHttpError( self::STATUS_FORBIDDEN );
			}
			
			$url = $this->removeBothEndSlashes( $_REQUEST['_url'] );
			
			/* check whether url exists */
			if( !isset( $this->urlMap[ $url ] ) ){
				self::sendHttpError( self::STATUS_NOT_FOUND, 'not found ('.$url.')' );
			}
			
			/* check whether method is supported */
			if( !isset( $this->urlMap[ $url ][ $_SERVER['REQUEST_METHOD'] ] ) ){
				self::sendHttpError( self::STATUS_METHOD_NOT_ALLOWED, 'method not allowed' );
			}
			
			$className = $this->urlMap[ $url ][ $_SERVER['REQUEST_METHOD'] ];
			
			$this->authenticationCheck();
			
			/*include $file;*/
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
		
		function removeBothEndSlashes( $url ){
			$l = strlen( $url );
			if( substr( $url, 0 ,1 ) == '/' ){
				$url = substr( $url, 1, $l-1 );
			}
			
			if( substr( $url, -1 ) == '/' ){
				$url = substr( $url,0,$l-1 );
			}
			
			return $url;
		}
	}
?>
