<?php
	namespace se;
	
	abstract class ServiceHandler extends Response{
		
		abstract function handleRequest();
	} 
?>
