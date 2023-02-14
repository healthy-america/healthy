<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

class Password extends \Magento\Framework\App\Helper\AbstractHelper
{

    const NUMBERS = '1234567890';
    const ALPHABET = 'abcdefghijklmnopqrstuvwxyz';
    const SPECIAL_CHARACTERS = '!"#$%&()*+,-./:;<=>?@[\]^_{|}~';

    public function randomPassword(): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /*
     * return $pass //Example AAAAaaaa1!
     * */
    public function randomPassWordByWord($_word = ''): string
    {
        $pass = '';

        $word = str_replace(' ', '', $_word);
        $word = $word == '' ? self::ALPHABET : $word;
        $lowerCase = strtolower($word);
        $upperCase = strtoupper($word) ;

        $pass = $this->generateRandomPassword($upperCase ,4);
        $pass .= $this->generateRandomPassword($lowerCase ,4);
        $pass .= $this->generateRandomPassword(self::NUMBERS ,1);
        $pass .= $this->generateRandomPassword(self::SPECIAL_CHARACTERS ,1);

        return $pass;
    }

    public function generateUrl($name, $length): string
    {
        $url = preg_replace('#[^0-9a-z]+#i', '-', $name);
        $url = strtolower($url);
        return $url.$this->generateRandomPassword($url, $length);
    }

    public function generateUrlCategory($categoryName, $length): string
    {
        $categoryUrl = str_replace(' ', '-', $categoryName);
        $categoryUrl .= $this->generateRandomPassword($this->remove_accents($categoryUrl), $length);
        return $categoryUrl;
    }

    public function generateRandomPassword($word, $length): string
    {
        $pass = array();
        $wordLength = strlen($word) - 1;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $wordLength);
            $pass[] = $word[$n];
        }
        return implode($pass);
    }

    public function remove_accents($string){

        //Reemplazamos la A y a
        $string = str_replace(
            array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
            array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
            $string
        );

        //Reemplazamos la E y e
        $string = str_replace(
            array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
            array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
            $string );

        //Reemplazamos la I y i
        $string = str_replace(
            array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
            array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
            $string );

        //Reemplazamos la O y o
        $string = str_replace(
            array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
            array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
            $string );

        //Reemplazamos la U y u
        $string = str_replace(
            array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
            array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
            $string );

        //Reemplazamos la N, n, C y c
        $string = str_replace(
            array('Ñ', 'ñ', 'Ç', 'ç'),
            array('N', 'n', 'C', 'c'),
            $string
        );

        return $string;
    }
}
