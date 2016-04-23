/*
* Developer: Mohammad Sharaf Ali
* Description: SQL script to query different sets of data
* Date: 23-04-2016
*/

-- Opened Emails
SELECT ID, Name, Website, Email, DAYNAME(CreatedDT) AS SENT_DAY, DATE_FORMAT(CreatedDT,'%d-%m-%Y') AS SENT_DATE, DATE_FORMAT(CreatedDT, '%h:%i:%s %p') AS SENT_TIME, DAYNAME(IsOpenedDT) AS OPENED_DAY, DATE_FORMAT(IsOpenedDT,'%d-%m-%Y') AS OPENED_DATE, DATE_FORMAT(IsOpenedDT, '%h:%i:%s %p') AS OPENED_TIME
FROM companies
WHERE IsActive = '1'
AND IsOpened = '1';

-- Not Opened Emails
SELECT ID, Name, Website, Email, DAYNAME(CreatedDT) AS SENT_DAY, DATE_FORMAT(CreatedDT,'%d-%m-%Y') AS SENT_DATE, DATE_FORMAT(CreatedDT, '%h:%i:%s %p') AS SENT_TIME
FROM companies
WHERE IsActive = '1'
AND IsOpened = '0';

-- Companies List
SELECT ID, Name, Website, Email, CreatedDT
FROM companies
WHERE IsActive = '1';