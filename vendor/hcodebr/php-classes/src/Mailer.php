<?php 

namespace Hcode;

use Rain\Tpl;

class Mailer
{

	const USERMAIL = "";
	const PASSWORD = "";
	const NAME_FROM = "Udemy Store";

	private $mail;

	/**
	 * [__construct Referente ao disparo de email de redefinição da senha]
	 * @param [type] $toAddress [Endereço de e-mail qual será disparado]
	 * @param [type] $toName    [Nome do destinatário]
	 * @param [type] $subject   [Assunto do e-mail]
	 * @param [type] $tplName   [Template de de disparo do e-mail]
	 * @param array  $data      [Dados]
	 */
	public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
	{
		
		// config template
		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."views/email/",
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => false // set to false to improve the speed
		);

		Tpl::configure( $config );

		// create the Tpl object
		$tpl = new Tpl;

		foreach ($data as $key => $value) {
			
			$tpl->assign($key, $value);

		}

		$html = $tpl->draw($tplName, true);
		
		//Create a new PHPMailer instance
		$this->mail = new \PHPMailer;
		
		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();
		
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->mail->SMTPDebug = 0;

		$this->mail->Debugoutput = 'html';
		
		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		// use
		// $this->mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6
		
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->mail->Port = 587;
		
		//Set the encryption system to use - ssl (deprecated) or tls
		$this->mail->SMTPSecure = 'tls';
		
		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;
		
		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = Mailer::USERMAIL;
		
		//Password to use for SMTP authentication
		$this->mail->Password = Mailer::PASSWORD;
		
		//Set who the message is to be sent from
		$this->mail->setFrom(Mailer::USERMAIL, Mailer::NAME_FROM);
		
		//Set an alternative reply-to address
		// $this->mail->addReplyTo('replyto@example.com', 'First Last');
		
		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName);
		
		//Set the subject line
		$this->mail->Subject = $subject;
		
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML($html);
		
		//Replace the plain text body with one created manually
		$this->mail->AltBody = 'This is a plain-text message body';
		
	}

	public function send()
	{
		return $this->mail->send();
	}

}






 ?>