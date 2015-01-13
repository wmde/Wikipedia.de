<?php

if (!class_exists("Mail")) {
  require_once("Mail.php");
}

class Mailer {

	public function __construct($impl = NULL) {
		$this->from = defined('MAIL_FROM') ? MAIL_FROM : NULL;
		$this->returnPath = defined('MAIL_RETURN_PATH') ? MAIL_RETURN_PATH : NULL;
		$this->sender = defined('MAIL_SENDER') ? MAIL_SENDER : NULL;
		$this->replyto = NULL;
		$this->type = "text/plain";
		$this->charset = mb_internal_encoding();
		$this->transfer_encoding = "8bit";
		$this->idDomain = NULL; //TODO: sensible init from $_SERVER
		$this->sendmail_mode = false;

		$s = ini_get('sendmail_path');
		if ($s) {
			$a = '';
			$i = strpos($s, ' ');
		
			if ($i>0) {
				$a = substr($s, $i+1);
				$s = substr($s, 0, $i);
			}

			if ( $this->sender ) $a .= " -f'{$this->sender}' ";
			else if ( $this->returnPath ) $a .= " -f'{$this->returnPath}' ";

			$this->sendmail_args = $a; 
			$this->sendmail_path = $s;
		} else {
			$this->sendmail_args = false; 
			$this->sendmail_path = false;
		}

		if (is_string($impl) && preg_match("/^pear::(.*)/", $impl, $m)) {
			$options = array();
			$impl = $m[1];

			if ($impl == "sendmail") {
				$this->sendmail_mode = true;

				$options['sendmail_args'] = $this->sendmail_args; 
				$options['sendmail_path'] = $this->sendmail_path;
			}

			$r = Mail::factory($impl, $options);
			if (Pear::isError($r)) throw new Exception("unknown pear mail engine: " . $r->getMessage());

			$impl = $r;
		} else if ($impl == "sendmail") {
			$this->sendmail_mode = true;
		}

		$this->impl = $impl;
	}

	public function mail ( $to, $subject, $message, $headers = NULL ) {
		#$oldenc = mb_internal_encoding();
		mb_internal_encoding($this->charset); #XXX: ugly friggen hack!

		$subject = preg_replace("/[\r\n\t]/", " ", $subject);
		$subject= mb_encode_mimeheader($subject, $this->charset, "B", "\r\n");

		if (is_null($headers)) $headers = array();
		else if (is_string($headers)) $headers = preg_split('/[\r\n]+/', $headers);

		$hh = array();
		foreach ( $headers as $k => $v ) {
			$v = preg_replace("/[\r\n\t]/", " ", $v);

			if (is_int($k)) {
				$ss = preg_split('/\s*:\s*/', $v, 2);
				if (count($ss)>1) {
					$k = $ss[0];
					$v = $ss[1];
					$k = ucfirst(strtolower($k));

					Mailer::put_header($hh, $k, $v, $this->charset);
				}
			}
			else {
				$k = ucfirst(strtolower($k));
				Mailer::put_header($hh, $k, $v, $this->charset);
			}
		}
		$headers = $hh;

		if (!isset($headers["Return-Path"]) && $this->returnPath) $headers["Return-Path"] = $this->returnPath;
		if (!isset($headers["Sender"]) && $this->sender) $headers["Sender"] = $this->sender;

		// NOTE: Mail_Mime is using uppercase on first letter only
		if (isset($headers["Content-type"])) {
			$headers["Content-Type"] = $headers["Content-type"];
			unset($headers["Content-type"]);
		} 
		
		if (!isset($headers["Content-Type"]) && $this->type) $headers["Content-Type"] = $this->type . '; charset=' . $this->charset;
		if (!isset($headers["Content-Transfer-Encoding"]) && $this->transfer_encoding) $headers["Content-Transfer-Encoding"] = $this->transfer_encoding;

		if (!isset($headers["Message-Id"]) && $this->idDomain) {
			$s = is_array($to) ? join(', ', $to) : $to;
			$s = "$s\r\n$subject";
			//$s = "$s\r\n$message";
			$headers["Message-Id"] = '<' . date('YmdHis') . '.' . md5($s) . '@' . $this->idDomain . '>';
		}

		if ($headers["Content-Transfer-Encoding"] == "8bit");
		else if ($headers["Content-Transfer-Encoding"] == "quoted-printable") {
			if (function_exists('quoted_printable_encode')) $message = quoted_printable_encode($message);
			else if (function_exists('imap_8bit')) $message = imap_8bit($message);
			else throw new Exception("unsupported transfer encoding; function quoted_printable_encode or imap_8bit required for quoted-printable"); 
		}
		else if ($headers["Content-Transfer-Encoding"] == "base64") {
			$message = base64_encode($message);
		}
		else  {
			throw new Exception("unsupported transfer encoding: " . $headers["Content-Transfer-Encoding"]); 
		}

		if (!isset($headers["From"]) && $this->from) $headers["From"] = $this->from;
		if (!isset($headers["Reply-To"]) && $this->replyto) $headers["Reply-To"] = $this->replyto;

		$headers["Date"] = date("r");

		if ($this->sendmail_mode) {
			$bcc = @$headers['Bcc'];
			$cc = @$headers['Cc'];

			$headers['Subject'] = $subject;

			if (!($this->impl instanceof Mail_mail)) {
				$headers['To'] = $to;
				if ($to && !is_array($to)) {
					$to = preg_replace("/[\r\n\t]/", " ", $to);
					$to = preg_split('/s*,\s*/', $to);
				}
				if ($bcc && !is_array($bcc)) {
					$bcc = preg_replace("/[\r\n\t]/", " ", $bcc);
					$bcc = preg_split('/s*,\s*/', $bcc);
				}
				if ($cc && !is_array($cc)) {
					$cc = preg_replace("/[\r\n\t]/", " ", $cc);
					$cc = preg_split('/s*,\s*/', $cc);
				}
		
				unset($headers['Bcc']);

				if ($cc) $to = array_merge($to, $cc);
				if ($bcc) $to = array_merge($to, $bcc);
			}

			foreach ( $headers as $k => $v ) {
				if (is_array($v)) $headers[$k] = join(', ', $v);
			}
		}

		if ($this->impl && is_object($this->impl)) {
			$ok = $this->impl->send($to, $headers, $message);
			if (Pear::isError($ok)) throw new Exception("failed to send mail: " . $ok->getMessage());
		}
		else {
			if (is_array($to)) $to = join(", ", $to);
			$to = preg_replace("/\s+/", "", $to);

			$hh = "";
			foreach ( $headers as $k => $v ) {
				if ($hh != "") $hh .= "\r\n";
				if (is_array($v)) $v = join(', ', $v);

				$v = str_replace(array("\r", "\n", "\t"), " ", $v);
				$v = trim($v);
	
				$hh .= $k . ': ' . $v;
			}

			$headers = $hh;

			if ($this->impl == "sendmail") {
				$mail = @popen($this->sendmail_path . " " . $this->sendmail_args . " -- " . escapeshellarg($to), 'w');

				if (!$mail) throw new Exception("failed to invoke sendmail as " . $this->sendmail_path . " " . $this->sendmail_args);

				fputs($mail, trim($headers));
				fputs($mail, "\r\n");  // newline to end the headers section
				fputs($mail, "\r\n");  // newline to end the headers section
				fputs($mail, $message);

				$result = pclose($mail); //todo: read response message
				if ($result) throw new Exception("sendmail returned error code $result\n");

				$ok = !$result;
			} else {
				$ok = mail($to, $subject, $message, $headers); //TODO: handle return-path
				if (!$ok) throw new Exception("failed to send mail!");
			}
		}

		return $ok;
	}

	static function put_header(&$array, $k, $v, $charset) {
		static $multi_value_headers = array(
			"To",
			"Cc",
			"Bcc",
		);

		// NOTE: encoding the multipart boundaries won't work
		if($k != "Content-type") 
			$v= mb_encode_mimeheader($v, $charset, "Q", "\r\n");

		if (!in_array($k, $multi_value_headers)) {
			$array[$k] = $v;
		}
		else {
			$v = preg_replace("/[\r\n\t]/", " ", $v);
			$v = preg_split('/s*,\s*/', $v);

			if(!isset($array[$k])) $array[$k] = $v;
			else {
				$w = $array[$k];

				if (!is_array($w)) $w = array($w);
				$array[$k] = array_merge($w, $v);
			}
		}
	}

}

?>
