## Synopsis

The hacker script to automate the process of sending cv's to employers when you are on the job hunt mission. The script comes up with the html cover letter and the pdf cv that is sent over with the email and is easily customizable. Furthermore, all the emails that you send using this script will be saved in the database for your future reference. In addition, pixel tracking is also present in the script which gives answer to your question *Is the mail read by HR which I sent yesterday?*.

## Motivation

This script was developed while I was job hunting. It was really a pain to compose mail (PDF CV attached with HTML cover letter) each time when applying for respective job role. What if you want to send tons of CV? What if you want know that mail you sent was a total waste or HR had the glimpse on it? What if you want the list of all the software houses you applied and show them to your parents or share in your friends circle?

The answer to all the above questions is this script that will automate all of your tasks and help you track all the necessary steps you have taken during your job hunt mission.

## Installation

Running the script is just the matter of minutes and not ~~seconds~~. Here are the steps listed (in order):
* Create the database on your LIVE server and execute the script *models/create_companies_table_script.sql*.
* Place the script *logger.php* on your LIVE server and configure the database connection.
* Configure the constants as shown below:
```
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
```

* Replace *Someone_CV.pdf* with your original CV, *templates/images/my_thumb.jpg* with your image, and modify the cover letter(s) *templates/cover_letter_known.html* and *templates/cover_letter_unknown.html* if you want to.
* Now you are good to go and test the script on your localhost.

## Note
* For testing the script with yahoo, gmail, or hotmail you will need to customize the script and configure it with the certificate file.
* The script *verify.php* has the method that can be used to test whether the email id is valid or not. Incorporate the method in the core script if you want to.
* The script *models/companies_queries.sql* contains set of queries that you can use to analyze your data such as Opened Emails, Not Opened Emails etc.
* To work with TLD's from website extracted from email id you can use [this](http://jecas.cz/tld-list/) comprehensive list for your purpose.
* Last but not the least, the PHPMailer version used is 2.3 but if you wish to use the latest vesion than kindly use [Composer](https://getcomposer.org).

This script was built with love and a little bit frustration. For more info about [ME](http://bit.ly/msharaf-linkedin) visit my profile.
