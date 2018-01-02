<?php

/*#############################
* Developer: Mohammad Sharaf Ali
* Description: Script to send cv with cover letter
* Date: 23-04-2016
*/#############################

##################### SCRIPT SETTINGS #####################
ini_set('max_execution_time', 0);
ini_set('memory_limit', '3G');

date_default_timezone_set('Asia/Karachi');


##################### LIBRARIES #####################
require_once 'phpmailer/class.phpmailer.php';
require_once 'db/NotORM.php';


##################### CONSTANTS #####################
//db constants
const DB_HOST = 'your_db_host';
const DB_USER = 'your_db_user';
const DB_PASS = 'your_db_pass';
const DB_NAME = 'your_db_name';
const DB_TABLE = 'companies';
const DB_DSN = 'mysql:dbname='. DB_NAME. ';host='. DB_HOST;

//mailer constants
const SMTP_HOST = 'your_smtp_host'; // can be smtp.sendgrid.net, smtp.mailgun.org etc
const SMTP_PORT = 'your_smtp_port'; // can be 465, 587 etc
const SMTP_USER = 'your_smtp_user';
const SMTP_PASS = 'your_smtp_pass';
const SMTP_FROM = 'someone@hotmail.com';
const SMTP_FROM_NAME = 'Someone';
const SMTP_DEFAULT_SUBJECT = 'Apply for Some Developer Position';

//cover letter / cv constants
const CL_PIXEL_HOLDER = '__PIXEL__';
const CL_POSITION_HOLDER = '__POSITION__';
const CL_DEFAULT_POSITION = 'Some Developer';
const CL_DEFAULT_POSITION_STATUS = '0';
const CL_PIXEL_TRACKING_PREFIX = 'http://www.yourdomain.com/mailer/logger.php?v='; // change dir path accordingly
const CL_PATH_UNKNOWN = 'templates/cover_letter_unknown.html';
const CL_PATH_KNOWN = 'templates/cover_letter_known.html';
const CV_NAME = 'Someone_CV.pdf';
const CV_PATH = 'Someone_CV.pdf';

// email detail constants
const EMAIL_WEBSITE_PREFIX = 'http://www.';

// array index constants
const SUBJECT_APPLIED_FOR_IDX = 'subject_applied_for';
const POSITION_APPLIED_FOR_IDX = 'position_applied_for';
const POSITION_APPLIED_STATUS_IDX = 'position_applied_status';
const PIXEL_TRACKING_URL_IDX = 'pixel_tracking_url';
const COMPANY_IDX = 'company_name';
const WEBSITE_IDX = 'website_url';
const EMAIL_IDX = 'email_id';


##################### DB STRUCTURE #####################
$structure = new NotORM_Structure_Convention(
    $primary = 'ID',
    $foreign = '%s_ID',
    $table = '%s',
    $prefix = '');

$pdo = null;
$db = null;


##################### HELPER METHODS #####################
function dbConnect() {
	global $pdo;
	global $db;
	global $structure;

	$pdo = new PDO(DB_DSN, DB_USER, DB_PASS); // array(PDO::ATTR_PERSISTENT => true) // make persistent connection
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
	$db = new NotORM($pdo, $structure); // new NotORM_Cache_Session()); // enable NotORM caching
}

function dbClose() {
	global $pdo;
	global $db;

	$pdo = null;
	$db = null;
}

function notEmptyString($str) {
	return $str !== '';
}

function getPixelURL($email) {
	return CL_PIXEL_TRACKING_PREFIX. base64_encode($email. '::'. strtotime(date('Y-m-d H:i:s')));
}

function getName($url) {
 	$pieces = parse_url($url);
  	$domain = isset($pieces['host']) ? $pieces['host'] : '';
  
  	if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
    	return explode('.', $regs['domain'])[0];
  	}

  	return false;
}

function getDetailsFromEmail($email) {
	$email = strtolower($email);
	$website = explode('@', $email)[1];
	$company = ucfirst(getName(EMAIL_WEBSITE_PREFIX. $website));

	return array(
		COMPANY_IDX => $company,
		WEBSITE_IDX => EMAIL_WEBSITE_PREFIX. $website,
		EMAIL_IDX => $email);
}

function outboundHTML($template, $data) {
    $template = preg_replace('/'. CL_POSITION_HOLDER. '/', $data[POSITION_APPLIED_FOR_IDX], $template);
    $template = preg_replace('/'. CL_PIXEL_HOLDER. '/', $data[PIXEL_TRACKING_URL_IDX], $template);

    return $template;
}

function checkRecord($email) { // returns true/false
	global $db;

	$company = $db->{DB_TABLE}()
				  ->where('Email = ?', $email);

	return ($company->fetch() ? true : false); // can return record
}

function insertRecord($data) { // returns newly inserted record
	global $db;

	$company =  $db->{DB_TABLE}()
			  	   ->insert(array(
			  			'Name' => $data[COMPANY_IDX],
			  			'Website' => $data[WEBSITE_IDX],
			  			'Email' => $data[EMAIL_IDX],
			  			'TrackingURL' => $data[PIXEL_TRACKING_URL_IDX],
			  			'SubAppliedFor' => $data[SUBJECT_APPLIED_FOR_IDX],
			  			'PosAppliedFor' => $data[POSITION_APPLIED_FOR_IDX],
			  			'IsPosKnown' => $data[POSITION_APPLIED_STATUS_IDX],
			  			'CreatedDT' => date('Y-m-d H:i:s'),
			  			'ModifiedDT' => date('Y-m-d H:i:s')));

	return (!empty($company) ? true : false); // can return record
}

function toScreen($data, $exit = false) {
    $isCli = !empty($_REQUEST['curl']) || substr(PHP_SAPI, 0, 3) === 'cli' ? true : false;

    ob_implicit_flush(true);

    if (is_object($data) || is_array($data)) {
        echo($isCli ? print_r($data, true) : '<pre>' . print_r($data, true) . '</pre>');
    } else {
        echo($isCli ? preg_replace('#<br\s*?/?>#i', PHP_EOL, $data) . PHP_EOL : nl2br($data, true) . '<br />');
    }

    ob_get_level() > 0 ? ob_flush() : flush();
    ob_implicit_flush(false);

    if ($exit) {
        exit;
    }
}


##################### MAIN #####################
if (isset($_POST['submit'])) {
	$startTime = strtotime(date('Y-m-d H:i:s')); // start timer

	$emailSubject = isset($_POST['email_sub']) ? $_POST['email_sub'] : SMTP_DEFAULT_SUBJECT;
	$emailPosition = isset($_POST['email_pos']) ? $_POST['email_pos'] : CL_DEFAULT_POSITION_;
	$emailPositionStatus = isset($_POST['pos_status']) ? $_POST['pos_status'] : CL_DEFAULT_POSITION_STATUS;
	$emailAddresses = isset($_POST['email_ids']) ? $_POST['email_ids'] : array();

	if (!empty($emailAddresses)) {
		$emailAddresses = explode(',', $emailAddresses);
		$emailAddresses = array_map('trim', $emailAddresses);
		$emailAddresses = array_filter($emailAddresses, 'notEmptyString');
	}

	$mail = new PHPMailer();    

	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->CharSet = 'UTF-8';
	$mail->Host = SMTP_HOST;
	$mail->Port = SMTP_PORT;
	$mail->Username = SMTP_USER;
	$mail->Password = SMTP_PASS;

	$mail->From = SMTP_FROM;
	$mail->FromName = SMTP_FROM_NAME;
	$mail->AddReplyTo(SMTP_FROM, SMTP_FROM_NAME);
	$mail->Subject = $emailSubject;

	//$mail->SMTPDebug = 2; // can be 0, 1, 2
	//$mail->SMTPSecure = 'tls'; // can be ssl, tls

	$template = ($emailPositionStatus == '1' ? $mail->getFile(CL_PATH_KNOWN) : $mail->getFile(CL_PATH_UNKNOWN));

	$mail->AddAttachment(realpath(CV_PATH), $name = CV_NAME,  $encoding = 'base64', $type = 'application/pdf');

	dbConnect();

	foreach ($emailAddresses as $emailAddress) {
		$emailData = getDetailsFromEmail($emailAddress);

		if (!checkRecord($emailData[EMAIL_IDX])) { // new record
			$clData = array(
				POSITION_APPLIED_FOR_IDX => $emailPosition,
				POSITION_APPLIED_STATUS_IDX => $emailPositionStatus,
				PIXEL_TRACKING_URL_IDX => getPixelURL($emailData[EMAIL_IDX]));

			$html = outboundHTML($template, $clData);
			$mail->MsgHTML($html);
			$mail->AddAddress($emailData[EMAIL_IDX]);

			if ($mail->Send()) {
				$insertRecordArray = array_merge(
										$emailData, 
										$clData,
										array(SUBJECT_APPLIED_FOR_IDX => $emailSubject));
				
				insertRecord($insertRecordArray); // check based on successful insertion of record

				$toPrint = 'E-Mail sent successful to: '. $emailData[EMAIL_IDX];
				$toPrint .= '<br />';
				toScreen($toPrint);
	 		
			} else {
				$toPrint = 'E-Mail sent unsuccessful to: '. $emailData[EMAIL_IDX]. ' because of: '. $mail->ErrorInfo;
	    		$toPrint .= '<br />';
	    		toScreen($toPrint);
			}

			$mail->ClearAddresses();
		} else { // existing record
			$toPrint = 'The E-mail <strong>'. $emailData[EMAIL_IDX]. '</strong> already exists!';
			$toPrint .= '<br />';
			toScreen($toPrint);
		}

		//sleep (0.5);
	}

	dbClose();

	$endTime = strtotime(date('Y-m-d H:i:s')); // stop timer

	$toPrint = 'Script Execution took <strong>'. ($endTime - $startTime). ' </strong>second(s)';
	$toPrint .= '<br /><br />';
	toScreen($toPrint);
}

?>

<html>
<head>
	<title>Script for sending CV</title>
</head>

<body>

<h4>Script for sending CV</h4>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
	<input type="text" id="email_pos" name="email_pos" required tabindex="1" placeholder="Enter position applying for cover letter" value="<?php echo CL_DEFAULT_POSITION ?>" />
	<br /><br />

	<input type="checkbox" id="pos_status" name="pos_status" tabindex="2" value="1" />Position Known?
	<br /><br />

	<textarea id="email_sub" name="email_sub" required tabindex="2" rows="3" cols="50" placeholder="Enter email subject"><?php echo SMTP_DEFAULT_SUBJECT ?></textarea>
	<br /><br />

	<textarea id="email_ids" name="email_ids" required autofocus tabindex="4" rows="5" cols="50" placeholder="Enter comma separated email id's"></textarea>
	<br /><br />
	
	<input type="submit" id="submit" name="submit" value="SEND" tabindex="5" />
</form>

</body>
</html>
