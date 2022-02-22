<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Message;
// use App\Entity\User;
// use App\Controller\DateTime;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    #[Route('/message', name: 'message')]
    public function index(ManagerRegistry $doctrine, UserRepository $userRepository): Response
    {
        $entityManager = $doctrine->getManager();

        //Current date for the message
        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $userID = $user->getId();
        $date = new \DateTime('@' . strtotime('now'));
        // var_dump($date);

        $message = new Message();
        $users = $userRepository->findAll();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            if ($user->getId() == $_POST['receiver']) {
                return $this->redirectToRoute('messages_error?errorMessage=Cant send this message');
            }

            // $receiverEmail = $userRepository->findBy(['email' => $_POST['receiver']]);
            // receiverEmail->getId();
            // var_dump($receiverEmail);

            //DATA FROM FORM
            $message->setReceiverID($_POST['receiver']);
            $message->setHeader($_POST['messageHead']);
            $message->setBody($_POST['messageBody']);
            // $message->setAttachFile($_POST['fileToUpload']);

            //Data that is default
            $message->setSenderID($user->getId());
            $message->setTimestamp($date);
            // $message->setIsRead(false);

            //Save new message in database
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }
        return $this->render('message/message.html.twig', [
            'controller_name' => 'MessageController',
            'userID' => $userID,
            'users' => $users
        ]);
    }
}
