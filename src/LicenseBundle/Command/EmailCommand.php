<?php

namespace LicenseBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

//use OcenyBundle\Command;

class EmailCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('app:send-email')
                ->setDescription('Send emails with notifications.')
                ->setHelp("This command sends notification via email.")
        ;
    }

    /* protected function initialize(InputInterface $input, OutputInterface $output) {
      } */

    /* protected function interact(InputInterface $input, OutputInterface $output) {
      } */

    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $emails = $em->getRepository('OcenyBundle:Email')->findEmail2Send();
        $mail = new EmailSender();
        for ($i = 0; $i < count($emails); $i++) {
            $mail->CreateEmail($emails[$i]->getAdres(), $emails[$i]->getTemat(), $emails[$i]->getWiadomosc());
            if ($mail->SendEmail()) {
                $emails[$i]->setCzyWyslany(TRUE);
                $emails[$i]->setDataWyslania(new \DateTime("now"));
            }
        }
        $em->flush();
    }

}
