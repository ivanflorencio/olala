<?php
	
	/**********************************************************************
	 * 	Descrição: 	Classe que implementa envio de email 				
	 * 	
	 * 	Autor: 		Ivan Florencio
	 *  Data: 		18/04/2015
	 * 
	 **********************************************************************/
	
	require_olala('library/phpmailer/class.phpmailer.php');
	
	class OlalaEmail extends PHPMailer {
		
		public function __construct($fromName, $fromEmail, $subject, $message) {
		    
		    //Set who the message is to be sent from
		    $this->setFrom($fromEmail, $fromName);
		    
		    // Set PHPMailer to use the sendmail transport
		    $this->isSendmail();
		    
		    //Set the subject line
		    $this->Subject = $subject;
		    
		    //convert HTML into a basic plain-text alternative body
		    $this->msgHTML($message);
		    
		}
		
		public function sendTo($toEmail) {
		    
		    $this->clearAddresses();
		    $this->addAddress(strtolower($toEmail));
		    
			return $this->send();
					
		}
		
	}