<?php

/*#############################
* Developer: Mohammad Sharaf Ali
* Description: PHP script to send cv
* Date: 23-04-2016
*/#############################

##################### SCRIPT SETTINGS #####################
ini_set('max_execution_time', 0);
ini_set('memory_limit', '3G');

date_default_timezone_set('Asia/Dubai');


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
const SMTP_HOST = 'smtp.mailgun.org'; // can be mailgun, mandrill etc
const SMTP_USER = 'your_smtp_user';
const SMTP_PASS = 'your_smtp_pass';
const SMTP_FROM = 'someone@hotmail.com';
const SMTP_FROM_NAME = 'Someone';
const SMTP_DEFAULT_SUBJECT = 'Apply for Javascript Developer Position';

//cover letter / cv constants
const CL_PIXEL_HOLDER = '__PIXEL__';
const CL_POSITION_HOLDER = '__POSITION__';
const CL_DEFAULT_POSITION = 'Javascript Developer';
const CL_PIXEL_TRACKING_PREFIX = 'http://www.yourdomain.com/mailer/logger.php?v='; // change dir path accordingly
const CL_PATH = 'templates/cover_letter.html';
const CV_NAME = 'Someone_CV.pdf';
const CV_PATH = 'Someone_CV.pdf';

// email detail constants
const EMAIL_WEBSITE_PREFIX = 'http://www.';

// array index constants
const SUBJECT_APPLIED_FOR_IDX = 'subject_applied_for';
const POSITION_APPLIED_FOR_IDX = 'position_applied_for';
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

function getDetailsFromEmail($email) {
	$email = strtolower($email);
	$website = explode('@', $email)[1];
	$company = ucfirst(explode('.', $website)[0]);

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
	$dbTable = DB_TABLE;

	$company = $db->$dbTable()
				  ->where('Email = ?', $email);

	return ($company->fetch() ? $company->fetch : false);
}

function insertRecord($data) { // returns newly inserted record
	global $db;
	$dbTable = DB_TABLE;

	$company =  $db->$dbTable()
			  	   ->insert(array(
			  			'Name' => $data[COMPANY_IDX],
			  			'Website' => $data[WEBSITE_IDX],
			  			'Email' => $data[EMAIL_IDX],
			  			'TrackingURL' => $data[PIXEL_TRACKING_URL_IDX],
			  			'SubAppliedFor' => $data[SUBJECT_APPLIED_FOR_IDX],
			  			'PosAppliedFor' => $data[POSITION_APPLIED_FOR_IDX],
			  			'CreatedDT' => date('Y-m-d H:i:s'),
			  			'ModifiedDT' => date('Y-m-d H:i:s')));

	return (!empty($company) ? $company : false);
}

function printResult($str) {
	echo '<pre>';
	echo $str;
	echo '</pre>';
}


##################### MAIN #####################
if (isset($_POST['submit'])) {
	$startTime = strtotime(date('Y-m-d H:i:s')); // start timer

	$emailSubject = isset($_POST['email_sub']) ? $_POST['email_sub'] : SMTP_DEFAULT_SUBJECT;
	$emailPosition = isset($_POST['email_pos']) ? $_POST['email_pos'] : CL_DEFAULT_POSITION;
	$emailAddresses = isset($_POST['email_ids']) ? $_POST['email_ids'] : array();

	if (!empty($emailAddresses)) {
		$emailAddresses = explode(',', $_POST['email_ids']);
		$emailAddresses = array_map('trim', $emailAddresses);
		$emailAddresses = array_filter($emailAddresses, 'notEmptyString');
	}

	$mail = new PHPMailer();    

	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->CharSet = 'UTF-8';
	$mail->Host     = SMTP_HOST;
	$mail->Username = SMTP_USER;
	$mail->Password = SMTP_PASS;

	$mail->From     = SMTP_FROM;
	$mail->FromName = SMTP_FROM_NAME;
	$mail->AddReplyTo(SMTP_FROM, SMTP_FROM_NAME);
	$mail->Subject  = $emailSubject;

	//$mail->SMTPDebug = 2; // can be 0, 1, 2
	//$mail->SMTPSecure = 'tls'; // can be ssl, tls
	//$mail->Port = 587; // can be 465, 587, etc 

	$template = file_get_contents(CL_PATH); // can also use $mail->getFile(filename)

	$mail->AddAttachment(realpath(CV_PATH), $name = CV_NAME,  $encoding = 'base64', $type = 'application/pdf');

	dbConnect();

	foreach ($emailAddresses as $emailAddress) {
		$emailData = getDetailsFromEmail($emailAddress);

		if (!checkRecord($emailData[EMAIL_IDX])) { // new record
			$clData = array(
				POSITION_APPLIED_FOR_IDX => $emailPosition,
				PIXEL_TRACKING_URL_IDX => getPixelURL($emailData[EMAIL_IDX]));

			$html = outboundHTML($template, $clData);
			$mail->MsgHTML($html);
			$mail->AddAddress($emailData[EMAIL_IDX]);

			if ($mail->Send()) {
				$insertRecordArray = array_merge(
											$emailData, 
											$clData,
											array(SUBJECT_APPLIED_FOR_IDX => $emailSubject));
				
				insertRecord($insertRecordArray);

				$toScreen = 'E-Mail sent successful to: '. $emailData[EMAIL_IDX];
				$toScreen .= '<br />';
				printResult($toScreen);
	 		
			} else {
				$toScreen = 'E-Mail sent unsuccessful to: '. $emailData[EMAIL_IDX]. ' because of: '. $mail->ErrorInfo;
	    		$toScreen .= '<br />';
	    		printResult($toScreen);
			}

			$mail->ClearAddresses();
		} else { // existing record

		}

		ob_flush();
		flush();

		//sleep (0.5);
	}

	dbClose();

	$stopTime = strtotime(date('Y-m-d H:i:s')); // stop timer

	$toScreen = 'Script Execution took <strong>'. ($stopTime - $startTime). ' </strong>second(s)';
	$toScreen .= '<br /><br />';
	printResult($toScreen);
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

	<textarea id="email_sub" name="email_sub" required tabindex="2" rows="2" cols="50" placeholder="Enter email subject"><?php echo SMTP_DEFAULT_SUBJECT ?></textarea>
	<br /><br />

	<textarea id="email_ids" name="email_ids" required autofocus tabindex="3" rows="5" cols="50" placeholder="Enter comma separated email id's"></textarea>
	<br /><br />
	
	<input type="submit" id="submit" name="submit" value="SEND" tabindex="4" />
</form>

</body>
</html>
