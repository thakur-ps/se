<?php
	/*
	MvcResponse Class
	Written by Pushpendra Singh Thakur <thakurpsr@gmail.com>
	(C) Business Computing Research Laboratory
	*/
	
	namespace se;
	
	class Response{
		
		const STATUS_OK = 1;
		const STATUS_ERROR = 0;
		
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
		
		function send( array $data ){
			if( $this->dataStrated ){
				echo ',';
			}else{
				$this->dataStarted = true;
			}
			
			echo json_encode( $data );
		}
		
		function sendOk( $message="" ){
			$this->start( 1, array( '_mes'=>$message ) );
			$this->end();
		}
		
		function sendError( $message, $field="" ){
			$this->start( 0, array( 
								'_mes'=>$message,
								'_fie'=>$field 
								) );
			$this->end();
		}
	}
?>
