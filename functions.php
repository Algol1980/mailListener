<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 23.01.2017
 * Time: 23:51
 */

function recursive_search($structure)
{

    $encoding = "";

    if ($structure->subtype == "HTML" ||
        $structure->type == 0
    ) {

        if ($structure->parameters[0]->attribute == "charset") {

            $charset = $structure->parameters[0]->value;
        }

        return array(
            "encoding" => $structure->encoding,
            "charset" => strtolower($charset),
            "subtype" => $structure->subtype
        );
    } else {

        if (isset($structure->parts[0])) {

            return recursive_search($structure->parts[0]);
        } else {

            if ($structure->parameters[0]->attribute == "charset") {

                $charset = $structure->parameters[0]->value;
            }

            return array(
                "encoding" => $structure->encoding,
                "charset" => strtolower($charset),
                "subtype" => $structure->subtype
            );
        }
    }
}

function isInCorrectContent ($text) {
    $ignore = '/Вам выделено место для закачки/iu';
    return preg_match($ignore, $text);
}

function getStatus($text)
{
    $statArray = [ 'question' => '/У меня к Вам вопрос:/iu',
                   'order' => '/поступил зaкaз нa книгу:/iu'];

    foreach ($statArray as $item => $value) {
        if (preg_match($value, $text)) {
            return $item;
        }
    }
    return '';
}

function parseBody($mail, $text)
{
    $addressGrep = '/(?:Индекс:(?<index>\d+)).*?(?:Адрес:(?<address>.+)).*?(?:ФИО:(?<customer>.+)).*?(?:\s\(Е-mail:\s?(?<email>.*))\)/iu';
    $telGrep = '/(?:Тел:\s*(?<tel>[\d+|\-]+))/iu';
    $commentGrep = '/(?:Комментарий к заказу:\s*<b>(?<comment>.*)<\/b>)/iu';
    $bookGrep = '/<p><b>(?<bookname>.*?)<\/b>(?<bookinfo>.*?)<br>\(.*Цена:\s*(?<price>\d+)\s*(?<currency>.\D*?)\./iu';

    preg_match($addressGrep, $text, $matchesAdr);
    preg_match($telGrep, $text, $matchesTel);
    preg_match($commentGrep, $text, $matchesComment);
    preg_match($bookGrep, $text, $matchesBook);

    $mail['index'] = trim($matchesAdr['index']);
    $mail['address'] = trim($matchesAdr['address']);
    $mail['customer'] = trim($matchesAdr['customer']);
    $mail['email'] = trim($matchesAdr['email']);
    $mail['tel'] = trim($matchesTel['tel']);
    $mail['comment'] = trim($matchesComment['comment']);
    $mail['bookname'] = trim($matchesBook['bookname']);
    $mail['bookinfo'] = trim($matchesBook['bookinfo']);
    $mail['price'] = trim($matchesBook['price']);
    $mail['currency'] = trim($matchesBook['currency']);
//    $mail['description'] = trim($matchesBook['description']);

    return $mail;

}