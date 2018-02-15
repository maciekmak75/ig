<?php

namespace LicenseBundle\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword {

    /**
     * @SecurityAssert\UserPassword(message = "Wpisano niepoprawne hasło")
     */
    public $stareHaslo;
    public $noweHaslo;
    public $powtorzHaslo;

    public function getHaslo() {
        $options = [
            'cost' => 8
        ];
        return password_hash($this->noweHaslo, PASSWORD_BCRYPT, $options);
    }

    public function generujHaslo() {
        $male = str_split('abcdefghijklmnopqrstuvwxyz');
        $duze = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $cyfry = str_split('1234567890');
        $specjalne = str_split('!%^&*()-=+[],.?');
        $haslo = '';
        for ($i = 0; $i < 3; $i++) {
            $haslo .= $male[rand(0, count($male) - 1)];
        }
        for ($i = 0; $i < 3; $i++) {
            $haslo .= $duze[rand(0, count($duze) - 1)];
        }
        for ($i = 0; $i < 2; $i++) {
            $haslo .= $male[rand(0, count($male) - 1)];
        }
        for ($i = 0; $i < 2; $i++) {
            $haslo .= $duze[rand(0, count($duze) - 1)];
        }

        return str_shuffle($haslo);
    }

    public function szyfrujHaslo($haslo) {
        $options = [
            'cost' => 8
        ];
        return password_hash($haslo, PASSWORD_BCRYPT, $options);
    }

    /**
     * @Assert\IsTrue(message = "Wpisane hasła się różnią.")
     */
    public function isPassMatch() {
        return ($this->noweHaslo == $this->powtorzHaslo);
    }

    public function generujUUID() {
        $male = str_split('abcdefghijklmnopqrstuvwxyz');
        $duze = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $cyfry = str_split('1234567890');
        $specjalne = str_split('!%^&*()-=+[],.?');
        $haslo = '';
        for ($i = 0; $i < 24; $i++) {
            $haslo .= $male[rand(0, count($male) - 1)];
        }
        for ($i = 0; $i < 24; $i++) {
            $haslo .= $duze[rand(0, count($duze) - 1)];
        }
        for ($i = 0; $i < 24; $i++) {
            $haslo .= $cyfry[rand(0, count($cyfry) - 1)];
        }

        return str_shuffle($haslo);
    }

}
