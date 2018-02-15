<?php

namespace LicenseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use LicenseBundle\Entity\Uzytkownik;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use LicenseBundle\Command\EmailSender;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use \LicenseBundle\Model\ChangePassword;

class UzytkownicyController extends Controller
{

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/uzytkownicy", name = "uzytkownicy")
     *
     */
    public function uzytkownicyAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('LicenseBundle:Uzytkownik')->findAll();

        $data['Administrator'] = 'ROLE_ADMIN';
        $data['Użytkownik'] = 'ROLE_USER';


        $form = $this->createFormBuilder()
            ->add('imie', TextType::class, array('label' => 'Imię:', 'attr' => array('class' => 'form-control col-md-7 col-xs-12')))
            ->add('nazwisko', TextType::class, array('label' => 'Nazwisko:', 'attr' => array('class' => 'form-control col-md-7 col-xs-12')))
            ->add('role', ChoiceType::class, array('label' => 'Rola:', 'choices' => $data, 'expanded' => true))
            ->add('save', SubmitType::class, array('label' => 'Zapisz', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new Uzytkownik();
            $data = $form->getData();
            $user->setImie($data['imie']);
            $user->setNazwisko($data['nazwisko']);
            $user->setLogin($em->getRepository('LicenseBundle:Uzytkownik')->createLogin($user->getImie(), $user->getNazwisko()));
            //$user->setMail($user->getLogin() . '@ec-e.pl');
            //$wiadomosc = 'Utworzono konto w systmie. Nowe dane do logowania:<br><br>';
            $haslo = \LicenseBundle\Model\ChangePassword::generujHaslo();
            //$wiadomosc .= 'Login: <b>' . $user->getLogin() . '</b><br>Hasło: <b>' . $haslo . '</b><br><br>Link do systemu: <br>';
            $url = $this->generateUrl('homepage', array(/* 'uuid' => $user->getUuid() */), UrlGeneratorInterface::ABSOLUTE_URL);
            //$wiadomosc .= $url . '<br><br>';
            //$mail = new EmailSender();
           // $mail->CreateEmail($user->getMail(), 'Utworzono konto', $wiadomosc);
            //$mail->SendEmail();
            $user->setHaslo(\LicenseBundle\Model\ChangePassword::szyfrujHaslo($haslo));
            $user->setRole(array($data['role']));
            $user->setAktywny(true);
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('uzytkownicy');
        }

        return $this->render("LicenseBundle:Users:users.html.twig", array('entities' => $entities, 'form' => $form->createView()));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/edit/{idUzytkownik}", name = "edit")
     * @ParamDecryptor(params={"idUzytkownik"})
     */
    public function UsersEditAction(Request $request, $idUzytkownik)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('LicenseBundle:Uzytkownik')->find($idUzytkownik);

        $data['Administrator'] = 'ROLE_ADMIN';
        $data['Użytkownik'] = 'ROLE_USER';
        $data['Deaktywuj'] = 'Deaktywuj';


        $form = $this->createFormBuilder()
            ->add('imie', TextType::class, array('label' => 'Imię:', 'data' => $user->getImie(), 'attr' => array('class' => 'form-control col-md-7 col-xs-12')))
            ->add('nazwisko', TextType::class, array('label' => 'Nazwisko:', 'data' => $user->getNazwisko(), 'attr' => array('class' => 'form-control col-md-7 col-xs-12')))
            ->add('mail', TextType::class, array('label' => 'E-mail:', 'data' => $user->getMail(), 'attr' => array('class' => 'form-control col-md-7 col-xs-12')))
            ->add('role', ChoiceType::class, array('label' => 'Rola:', 'data' => $user->getRole()?$user->getRole()[0]:'', 'choices' => $data, 'expanded' => true))
            ->add('save', SubmitType::class, array('label' => 'Zapisz', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user->setImie($data['imie']);
            $user->setNazwisko($data['nazwisko']);
            $user->setMail($data['mail']);
            $user->setRole(array($data['role']));
            if($user->getRole())
                $user->setAktywny(true);
            $em->persist($user);
            $em->flush();
            if($data['role']=='Deaktywuj')
                return $this->redirectToRoute('deaktywuj', array('idUzytkownik'=>$this->get('nzo_url_encryptor')->encrypt($user->getId())));
            return $this->redirectToRoute('uzytkownicy');
        }
        return $this->render("LicenseBundle:Users:UsersEdit.html.twig", array('form' => $form->createView()));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/resetHasla/{idUzytkownik}", name = "resetHasla")
     * @ParamDecryptor(params={"idUzytkownik"})
     */
    public function ResetHaslaAction($idUzytkownik)
    {
        $wiadomosc = 'Zresetowano hasło w systemie . Nowe dane do logowania:<br><br>';
        $em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository('LicenseBundle:Uzytkownik')->find($idUzytkownik);
        $login = $user->getLogin();
        $haslo = \LicenseBundle\Model\ChangePassword::generujHaslo();
        $wiadomosc .= 'Login: <b>' . $login . '</b><br>Hasło: <b>' . $haslo . '</b><br><br>Link do systemu: <br>';
        $url = $this->generateUrl('homepage', array(/* 'uuid' => $user->getUuid() */), UrlGeneratorInterface::ABSOLUTE_URL);
        $wiadomosc .= $url . '<br><br>';
        $mail = new EmailSender();
        $mail->CreateEmail($user->getMail(), 'Zresetowano hasło', $wiadomosc);
        $mail->SendEmail();
        $user->setHaslo(\LicenseBundle\Model\ChangePassword::szyfrujHaslo($haslo));
        $em->flush();
        return $this->redirectToRoute('uzytkownicy');
    }

    /**
     * @Route("/reset", name = "reset")
     *
     */
    public function resetAction(Request $request)
    {

        $form = $this->createFormBuilder()
            ->add('mail', TextType::class, array('label' => 'Mail:', 'attr' => array('class' => 'form-control', 'placeholder' => 'Mail')))
            ->add('save', SubmitType::class, array('label' => 'Resetuj', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();
        $success = false;
        $failed = false;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $user = $this->getDoctrine()->getRepository('LicenseBundle:Uzytkownik')->findOneBy(array('mail' => $data['mail']));
            if ($user) {
                $wiadomosc = 'Witaj, ' . $user->__toString() . '<br><br>Zresetowano hasło w systmie. Nowe dane do logowania:<br><br>';
                $login = $user->getLogin();
                $haslo = \LicenseBundle\Model\ChangePassword::generujHaslo();
                $wiadomosc .= 'Login: <b>' . $login . '</b><br>Hasło: <b>' . $haslo . '</b><br><br>Link do systemu: <br>';
                $url = $this->generateUrl('homepage', array(/* 'uuid' => $user->getUuid() */), UrlGeneratorInterface::ABSOLUTE_URL);
                $wiadomosc .= $url . '<br><br>';
                $mail = new EmailSender();
                $mail->CreateEmail($user->getMail(), 'Zresetowano hasło', $wiadomosc);
                $mail->SendEmail();
                $user->setHaslo(\LicenseBundle\Model\ChangePassword::szyfrujHaslo($haslo));
                $em->flush();
                $success = true;
            } else {
                $failed = true;
            }
        }
        return $this->render("LicenseBundle:Users:reset.html.twig", array('form' => $form->createView(), 'success' => $success, 'failed' => $failed));
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/ogolne", name = "ogolne")
     */
    public function ogolneAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data['haslo'] = $this->getUser()->getHaslo();
        $zmianaHasla = new ChangePassword();
        $form = $this->createFormBuilder($zmianaHasla)
            ->add('stareHaslo', PasswordType::class, array("label" => "Podaj aktualne hasło:", 'attr' => array('class' => 'form-control col-md-7 col-xs-12')))
            ->add('noweHaslo', PasswordType::class, array('label' => 'Podaj nowe hasło:', 'attr' => array('class' => 'form-control col-md-7 col-xs-12')))
            ->add('powtorzHaslo', PasswordType::class, array('label' => 'Powtórz nowe hasło:', 'attr' => array('class' => 'form-control col-md-7 col-xs-12')))
            ->add('save', SubmitType::class, array('label' => 'Zmień', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $this->getDoctrine()->getRepository('LicenseBundle:Uzytkownik')->find($this->getUser()->getId());
            $user->setHaslo($zmianaHasla->getHaslo());
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('logout');
        }

        return $this->render("LicenseBundle:Users:ogolne.html.twig", array('form' => $form->createView(), 'show' => FALSE));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/deaktywuj/{idUzytkownik}", name = "deaktywuj")
     * @ParamDecryptor(params={"idUzytkownik"})
     */
    public function deaktywujAction($idUzytkownik)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository('LicenseBundle:Uzytkownik')->find($idUzytkownik);
        $user->setAktywny(false);
        $user->setRole(array());
        $em->flush();
        return $this->redirectToRoute('uzytkownicy');
    }

    /**
     * @Route("/register", name = "register")
     *
     */
    public function registerAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('imie', TextType::class, array('label' => 'Imię:', 'attr' => array('class' => 'form-control col-md-7 col-xs-12', 'placeholder' => 'Imię')))
            ->add('nazwisko', TextType::class, array('label' => 'Nazwisko:', 'attr' => array('class' => 'form-control col-md-7 col-xs-12', 'placeholder' => 'Nazwisko')))
            ->add('mail', TextType::class, array('label' => 'Mail:', 'attr' => array('class' => 'form-control col-md-7 col-xs-12', 'placeholder' => 'Mail')))
            ->add('save', SubmitType::class, array('label' => 'Utwórz konto', 'attr' => array('class' => 'btn btn-warning')))
            ->getForm();

        $form->handleRequest($request);
        $error = false;
        $success = false;
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $mailAD = $em->getRepository('LicenseBundle:AktywneMaile')->findBy(array('nazwa' => $data['mail']));
            $isRegistered = $em->getRepository('LicenseBundle:Uzytkownik')->findBy(array('mail' => $data['mail']));
            if ($mailAD && !$isRegistered) {
                $user = new Uzytkownik();
                $user->setImie($data['imie']);
                $user->setNazwisko($data['nazwisko']);

                $user->setLogin($em->getRepository('LicenseBundle:Uzytkownik')->createLogin($user->getImie(), $user->getNazwisko()));
                $user->setMail($data['mail']);

                $wiadomosc = 'Witaj, ' . $user->__toString() . '<br><br>' . 'Utworzono konto w systemie. Kliknij w link, aby dokończyć rejestrację:<br><br>';
                $haslo = \LicenseBundle\Model\ChangePassword::generujHaslo();
                $user->setHaslo(\LicenseBundle\Model\ChangePassword::szyfrujHaslo($haslo));
                $user->setUuid(\LicenseBundle\Model\ChangePassword::generujUUID($haslo));
                $url = $this->generateUrl('endRegister', array('uuid' => $user->getUuid()), UrlGeneratorInterface::ABSOLUTE_URL);
                $wiadomosc .= $url . '<br><br>';
                $mail = new EmailSender();
                $mail->CreateEmail($user->getMail(), 'Utworzono konto', $wiadomosc);
                $mail->SendEmail();
                $success = true;
                $error = false;
                $user->setAktywny(false);
                $em->persist($user);
                $em->flush();
            } else {
                $error = true;
            }
        }

        return $this->render("LicenseBundle:Users:register.html.twig", array('form' => $form->createView(), 'error' => $error, 'success' => $success));
    }

    /**
     * @Route("/endRegister/{uuid}", name = "endRegister")
     *
     */
    public function endRegisterAction($uuid)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('LicenseBundle:Uzytkownik')->findOneBy(array('uuid' => $uuid));
        $wiadomosc = 'Witaj, ' . $user->__toString() . '<br><br>' . 'Przesyłamy dane do logowania w systemie. <br><br>';
        $user->setRole(array('ROLE_USER'));
        $user->setAktywny(1);
        $haslo = \LicenseBundle\Model\ChangePassword::generujHaslo();
        $wiadomosc .= 'Login: <b>' . $user->getLogin() . '</b><br>Hasło: <b>' . $haslo . '</b><br><br>Link do systemu: <br>';
        $url = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $wiadomosc .= $url . '<br><br>';
        $mail = new EmailSender();
        $mail->CreateEmail($user->getMail(), 'Rejestracja przebiegła pomyślnie', $wiadomosc);
        $mail->SendEmail();
        $user->setHaslo(\LicenseBundle\Model\ChangePassword::szyfrujHaslo($haslo));
        $em->persist($user);
        $em->flush();

        return $this->render("LicenseBundle:Users:endRegister.html.twig", array('mailAD' => true));
    }

}
