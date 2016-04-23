## Synopsis

The hacker script to automate the process of sending cv's to employers when you are on the job hunt mission. The script comes up with the html conver letter and the pdf cv that is sent over with the email and is easily customizable. Furthermore, all the emails that you send using this script will be saved in the database for your future reference. In addition, pixel tracking is also present in the script which gives answer to your question *Is the mail read by HR which I sent yesterday?*.

## Motivation

This script was developed while I was job hunting. The problem I faced was that it was really a pain to compose mail each time you want to mail the CV. What if you want to send tons of CV? What if you want know that mail you sent was a total waste or the HR had the glimpse on it? What if you want the list of all the software houses you applied and show them to your parents or share with friends?

The answer to all the above questions is the script that will automate all your tasks and help you track all the steps you have take during your job hunt.

## Installation

Running the script is just the matter of minutes and not ~~seconds~~. Here are the steps listed:
* Create the database on your live server and execute the script *models/create_companies_table_script.sql*.
* Place the script *logger.php* on your live server.
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
```

* Replace *Someone_CV.pdf* with your original CV, *templates/images/my_thumb.jpg* with your image, and modify the cover letter *templates/cover_letter.html* if you want to.
* Now you are good and ready to test your script on your localhost.

## Note
* For testing the script with yahoo, gmail, or hotmail you will need to customize the script and configure it with the certificate file..
* The script *verify.php* has the method that can be used to test whether the email id is valid or not. Incorporate the method in the core script if you want to.
* The script *models/companies_queries.sql* contains set of queries that you can use to analyze your data such as Opened Emails, Not Opened Emails etc.


This script was build with love and a little bit frustration. For more info about [ME] (https://pk.linkedin.com/in/mohammad-sharaf-ali-96a4038a) visit my profile.
