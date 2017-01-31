<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 23.01.2017
 * Time: 23:48
 */

header('Content-Type: text/html; charset: utf-8');

require_once('settings.php');
require_once('functions.php');

$connection = imap_open($mail_imap, $mail_login, $mail_password);

$dateOne = date("d M Y", strToTime("-1 days"));
$dateTwo = date("d M Y", strToTime("-2 days"));
$dateThree = date("d M Y", strToTime("-3 days"));
$dateFour = date("d M Y", strToTime("-4 days"));

$file = 'maildump.csv';

if (!$connection) {
    echo("Ошибка соединения с почтой - " . $mail_login);
    exit;
} else {
    $handle = fopen($file, 'a');
    $mailsData = [];

    $emails = imap_search($connection, "SINCE \"$dateThree\"", SE_UID);
    foreach ($emails as $uid) {
        $emailNumber = imap_msgno($connection, $uid);
        $msg_header = imap_header($connection, $emailNumber);
        if (in_array($msg_header->fromaddress, $addresses)) {

            $structure = imap_fetchstructure($connection, $emailNumber);
            $messageBody = imap_fetchbody($connection, $emailNumber, 1);

            $recursive_data = recursive_search($structure);

            if ($recursive_data["encoding"] == 0 ||
                $recursive_data["encoding"] == 1
            ) {
                $body = mb_convert_encoding($messageBody, "UTF-8", $recursive_data["charset"]);
                $text = strip_tags($body);
            }
            if (isInCorrectContent($text)) {
                continue;
            }
            str_replace('"', ' ', $text);
            $currentMail['mail_number'] = $emailNumber;
            $currentMail['uid'] = $uid;
            $currentMail['from'] = $msg_header->fromaddress;
            $currentMail['date'] = $msg_header->date;

            $currentMail['status'] = getStatus($body);
            $address = parseBody($currentMail, $body);

            $mailsData[$emailNumber] = $address;
            fputcsv($handle,$address, "\t");
        }
    }
}

var_dump($mailsData);
imap_close($connection);

