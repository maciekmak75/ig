<?php

namespace LicenseBundle\Controller;

use LicenseBundle\Command\EmailSender;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DefaultController extends Controller
{

    /**
     * @Route("/")
     *
     */
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('tresc', TextareaType::class,array( 'required' => false,'attr' => array('class' => 'form-control col-md-7 col-xs-12 ', "rows"=>5)))
            ->add('save', SubmitType::class, array('label' => 'Wyślij email', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();
        $success = false;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $mail = new EmailSender();
            $helpdesk = 'Witaj, ' . $this->getUser()->__toString() . '<br><br>Twoje tłumaczenie rozpoznanej mowy:  <br><br>' . $data['tresc'];


            $mail->CreateEmail('powiadomienia@krakus.net', 'Tłumaczenie', $helpdesk);
            $mail->SendEmail();

            $success = true;
            return $this->redirectToRoute('homepage');
        }

        return $this->render('LicenseBundle:Default:index.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/login", name = "login")
     */
    public function loginAction(Request $request)
    {

        $authenticationUtils = $this->get('security.authentication_utils');
        $success = false;
        $failed = false;

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createFormBuilder()
            ->add('mail', TextType::class, array('label' => 'Mail:', 'attr' => array('class' => 'form-control', 'placeholder' => 'Mail', 'required' => false)))
            ->add('save', SubmitType::class, array('label' => 'Resetuj', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $wiadomosc = 'Zresetowano hasło w systmie rezerwacji lincencji ECE. Nowe dane do logowania:<br><br>';
            $em = $this->getDoctrine()->getManager();
            $user = $this->getDoctrine()->getRepository('LicenseBundle:Uzytkownik')->findOneBy(array('mail' => $data['mail']));
            if ($user) {
                $wiadomosc = 'Zresetowano hasło w systmie rezerwacji lincencji ECE. Nowe dane do logowania:<br><br>';
                $login = $user->getLogin();
                $haslo = \LicenseBundle\Model\ChangePassword::generujHaslo();
                $wiadomosc .= 'Login: <b>' . $login . '</b><br>Hasło: <b>' . $haslo . '</b><br><br>Link do systemu: <br>';
                $url = $this->generateUrl('homepage', array('uuid' => $user->getUuid()), UrlGeneratorInterface::ABSOLUTE_URL);
                $wiadomosc .= $url . '<br><br>';
                $mail = new EmailSender();
                $mail->CreateEmail($login . '@ec-e.pl', 'Zresetowano hasło', $wiadomosc);
                //$mail->SendEmail();
                $user->setHaslo(\LicenseBundle\Model\ChangePassword::szyfrujHaslo($haslo));
                $em->flush();
                $success = true;
            } else {
                $failed = true;
            }
        }

        return $this->render('LicenseBundle::logowanie.html.twig', array(
            // last username entered by the user
            'last_username' => $lastUsername,
            'error' => $error,
            'form' => $form->createView(),
            'success' => $success,
            'failed' => $failed
        ));
    }

    /**
     * @Route("/logout", name = "logout")
     */
    public function logoutAction()
    {
        //return $this->render('OcenyBundle::logout.html.twig');
    }

    /**
     * @Route("/send", name = "send")
     */
    public function sendAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('tresc', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Wyślij email', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();
        $success = false;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $mail = new EmailSender();
            $helpdesk = 'Witaj, ' . $this->getUser()->__toString() . '<br><br>Twoje tłumaczenie rozpoznanej mowy:  <br><br>' . $data['tresc'];


            $mail->CreateEmail('powiadomienia@krakus.net', 'Panel - problem', $helpdesk);
            $mail->SendEmail();

            $success = true;
        }

        return $this->render('PanelBundle:Help:help.html.twig', array('form' => $form->createView(), 'success' => $success));
    }

}
