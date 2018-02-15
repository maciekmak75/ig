<?php

namespace LicenseBundle\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class Model {

    public function wrongDate($tab, $data) { //sprawdzanie czy podane daty nie sa zarezerwowane
        foreach ($tab as $it) {//sprawdzanie czy daty nie nakladaja sie
            if ($it->getDataOd() <= $data->getDataOd() && $it->getDataDo() >= $data->getDataOd() ||
                    $it->getDataOd() <= $data->getDataDo() && $it->getDataDo() >= $data->getDataDo()
            )
                return true;
        }
        return false;
    }

    static function SendWarning() {
        $em = $this->get('doctrine.orm.entity_manager');
        $lic = $em->getRepository('LicenseBundle:Licencje')->findBy(array('typ' => $typ, 'p2' => 1));
        $licznik = 0;
        for ($i = 0; $i < count($lic); $i++) {
            if ($em->getRepository('LicenseBundle:RezerwacjaI')->findAktualne($lic[$i]->getId())) {
                $licznik++;
            }
        }
        var_dump($licznik);
        if (count($lic) - $licznik < 3) {
            $mail = new EmailSender();
            $wiadomosc = 'Zostało bardzo mało licencji na pierwszą zmianę!<br><br>Proszę o zweryfikowanie grafiku pracowników';
            $mail->CreateEmail($em->getRepository('LicenseBundle:Uzytkownik')->mailingList(), 'Ostrzeżenie!!!', $wiadomosc);
            $mail->Send();
        }
    }

}
