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
    #[Route('/message/reply', name: 'message_reply')]
    public function reply(ManagerRegistry $doctrine, UserRepository $userRepository, MessageRepository $messageRepository): Response
    {
        $entityManager = $doctrine->getManager();

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $userID = $user->getId();
        $date = new \DateTime('@' . strtotime('now'));
        // var_dump($date);

        $message = new Message();
        $users = $userRepository->findAll();

        if(isset($_GET['msgID']) && isset($_GET['receiverID'])){
            $receiver = $userRepository->findBy(['id' => $_GET['receiverID']])[0];
            $prevMessage = $messageRepository->findBy(['id' => $_GET['msgID']])[0];

            $message->setSenderID($userID);
            $message->setReceiverID($receiver->getId());
            $replyHeader = 'Re: ' . $prevMessage->getHeader();
            $message->setHeader($replyHeader);
            // $entityManager->persist($message);
            // $entityManager->flush();

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                //DATA FROM FORM
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
        }

        return $this->render('message/messageReply.html.twig', [
            'controller_name' => 'MessageController',
            'userEmail' => $receiver->getEmail(),
            'replyHeader' => $replyHeader
        ]);
    }

    #[Route('/various-message', name: 'various_message')]
    public function variousMessage(ManagerRegistry $doctrine, UserRepository $userRepository): Response
    {
        $entityManager = $doctrine->getManager();

        //Current date for the message
        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $date = new \DateTime('@' . strtotime('now'));
        // var_dump($date);

        $users = $userRepository->findAll();
        
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['receivers'])) {
            
            foreach($_POST['receivers'] as $receiverID){
                //check this
                $message = new Message();
                $message->setReceiverID($receiverID);
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
            }

            return $this->redirectToRoute('various_message');
        }
        return $this->render('message/variousMessage.html.twig', [
            'controller_name' => 'MessageController',
            // 'userID' => $userID,
            'users' => $users
        ]);
    }

    #[Route('/view-message', name: 'view_message')]
    public function viewMessage(ManagerRegistry $doctrine, UserRepository $userRepository, MessageRepository $messageRepository): Response
    {
        if(isset($_GET['messageID'])){

            $users = $userRepository->findAll();
            $message = $messageRepository->findBy(['id' => $_GET['messageID']]);
            
            $entityManager = $doctrine->getManager();
            
            $message[0]->setIsRead(1);
            $entityManager->persist($message[0]);
            $entityManager->flush();
            
            return $this->render('message/viewMessage.html.twig', [
                'controller_name' => 'MessageController',
                'users' => $users,
                // 'userPfp' => $userPfp,
                'message' => $message[0]
            ]);
        }
    }
}