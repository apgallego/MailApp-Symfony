<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Message;
use App\Repository\MessageRepository;
// use App\Entity\User;
// use App\Controller\DateTime;
use App\Repository\UserRepository;
// use DateTime;
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
            
            //no possibility of sending messages to your own
            if ($user->getId() == $_POST['receiver']) {
                return $this->redirectToRoute('messages_error?errorMessage=Cant send this message');
            }

            //DATA FROM FORM
            if(isset($_POST['receiver']) && $_POST['receiver'] !== '')
                $receiver = $userRepository->findBy(['email' => $_POST['receiver']]);
            $message->setReceiverID($receiver[0]->getId());
            $message->setHeader($_POST['messageHead']);
            $message->setBody($_POST['messageBody']);
            // $message->setAttachFile($_POST['fileToUpload']);

            //Data that is default
            $message->setSenderID($user->getId());
            $message->setTimestamp($date);
            $message->setIsRead(0);
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

    #[Route('/view-message', name: 'view_message')]
    public function viewMessage(UserRepository $userRepository, MessageRepository $messageRepository): Response
    {
        if(isset($_GET['messageID'])){
            //TODO: fix this and logic here for seen messages
            $users = $userRepository->findAll();
            $fullMessage = $messageRepository->findBy(['id' => $_GET['messageID']]);

            return $this->render('message/viewMessage.html.twig', [
                'controller_name' => 'MessageController',
                'users' => $users,
                'message' => $fullMessage
            ]);
        }
    }
}