<?php
// src/Controller/MessageController.php
/**
 * This controller manages everything related to messages (simple ones, to multiple recipients, to groups, answers and the their separated view)
 */
namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\GroupRepository;
// use App\Entity\User;
// use App\Controller\DateTime;
use App\Repository\UserRepository;
// use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    /* Function for simple messages */
    #[Route('/message', name: 'message')]
    public function index(ManagerRegistry $doctrine, UserRepository $userRepository): Response
    {
        $entityManager = $doctrine->getManager();

        //we get the current user and the current date
        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $userID = $user->getId();
        $date = new \DateTime('@' . strtotime('now'));

        //we create a new messaage and get all the existing users
        $message = new Message();
        $users = $userRepository->findAll();

        //if there is a post request we create the message
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            //no possibility of sending messages to your own
            if ($user->getId() == $_POST['receiver']) {
                return $this->redirectToRoute('messages_error?errorMessage=Cant send this message');
            }

            //we get the data from the form and get the receiver user
            if(isset($_POST['receiver']) && $_POST['receiver'] !== '')
                $receiver = $userRepository->findBy(['email' => $_POST['receiver']]);
            
            //we set all the parameters to create a new message
            $message->setReceiverID($receiver[0]->getId());
            $message->setHeader($_POST['messageHead']);
            $message->setBody($_POST['messageBody']);
            $message->setSenderID($user->getId());
            $message->setTimestamp($date);
            $message->setIsRead(0);
            // $message->setAttachFile($_POST['fileToUpload']);

            //now we update the database
            $entityManager->persist($message);
            $entityManager->flush();

            //and after sending the message we are redirected to the homepage
            return $this->redirectToRoute('home');
        }
        return $this->render('message/message.html.twig', [
            'controller_name' => 'MessageController',
            'userID' => $userID,
            'users' => $users
        ]);
    }

    /* Function for message replies */
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

        // The previous logic is very similar to the simple messages one
        //if there is a get response with the message id and the receiver id too, we create the reply
        if(isset($_GET['msgID']) && isset($_GET['receiverID'])){
            //we look for the receiver and for the previous message by id
            $receiver = $userRepository->findBy(['id' => $_GET['receiverID']])[0];
            $prevMessage = $messageRepository->findBy(['id' => $_GET['msgID']])[0];

            //we set all the required parameters
            $message->setSenderID($userID);
            $message->setReceiverID($receiver->getId());
            $replyHeader = 'Re: ' . $prevMessage->getHeader();
            $message->setHeader($replyHeader);

            //after setting all the "default" data we create the message now
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                //we set all the parameters required by the user
                $message->setBody($_POST['messageBody']);
                // $message->setAttachFile($_POST['fileToUpload']);
    
                //Data that is default
                $message->setSenderID($user->getId());
                $message->setTimestamp($date);
                $message->setIsRead(0);
                // $message->setIsRead(false);
    
                //we save the new message in database
                $entityManager->persist($message);
                $entityManager->flush();
    
                //and we are redirected to the home page
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('message/messageReply.html.twig', [
            'controller_name' => 'MessageController',
            'userEmail' => $receiver->getEmail(),
            'replyHeader' => $replyHeader
        ]);
    }

    /* Function to send messages to several users */
    #[Route('/various-message', name: 'various_message')]
    public function variousMessage(ManagerRegistry $doctrine, UserRepository $userRepository): Response
    {
        $entityManager = $doctrine->getManager();

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $date = new \DateTime('@' . strtotime('now'));
        // var_dump($date);

        $users = $userRepository->findAll();
        
        //The logic is similar to the previous functions, but it differs from them when doing the foreach
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['receivers'])) {
            //we get all the ids of the receivers and create a loop to iterate throught them and send as many messages as users were selected
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

            return $this->redirectToRoute('home');
        }
        return $this->render('message/variousMessage.html.twig', [
            'controller_name' => 'MessageController',
            // 'userID' => $userID,
            'users' => $users
        ]);
    }

    /* Another function to send messages to multiple users, this time using groups */
    #[Route('/group-messages', name: 'group_messages')]
    public function groupMessages(ManagerRegistry $doctrine, UserRepository $userRepository, GroupRepository $groupRepository, Request $request): Response
    {

        /* The logi is very similar to the previous one, but this time we get the ids from the groups with the logic from the template */
        $entityManager = $doctrine->getManager();

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $userID = $user->getId();
        $senderUsername = $user->getUsername();
        $date = new \DateTime('@' . strtotime('now'));

        //we create the new message
        $message = new Message();

        //we get the id of the current group and all the users
        $groupID = $request->query->get('id');
        $users = $userRepository->findAll();

        //if there is a response with the group id we get that group from the database
        if(isset($groupID)){
            $group = $groupRepository->findBy(['id' => $groupID])[0];

            //after getting the group, if there is a post request (coming from the form to send the message) we create the message
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                //we iterate through all the ids of the users which will receive the message and, as the previous cases, we set all the required fields and update the database
                foreach($group->getUserIDs() as $member){
                    if($member !== $userID){
                        //DATA FROM FORM
                        $message->setHeader($_POST["messageHead"]);
                        $message->setBody($_POST['messageBody']);
                        // $message->setAttachFile($_POST['fileToUpload']);

                        //Data that is default
                        $message->setSenderID($userID);
                        $message->setReceiverID($member);
                        $message->setTimestamp($date);
                        $message->setIsRead(0);
                        // $message->setIsRead(false);

                        //Save new message in database
                        $entityManager->persist($message);
                        $entityManager->flush();
                    }
                }
                return $this->redirectToRoute('home');
            }
        }
        return $this->render('message/groupMessage.html.twig', [
            'controller_name' => 'MessageController',
            'userID' => $userID,
            'sender' => $senderUsername,
            'users' => $users,
            // 'receivers' => $stringEmails,
            'group' => $group,
            'groupUserIDs' => $group->getUserIDs()
        ]);
    }

    /* Function to manage the view of the different messages */
    #[Route('/view-message', name: 'view_message')]
    public function viewMessage(ManagerRegistry $doctrine, UserRepository $userRepository, MessageRepository $messageRepository): Response
    {
        //if the message id has been sent we display it on a new template
        if(isset($_GET['messageID'])){

            //We get all the users and the chosen message by id (the rest of the logic is in the corresponding twig template)
            $users = $userRepository->findAll();
            $message = $messageRepository->findBy(['id' => $_GET['messageID']]);
            
            $entityManager = $doctrine->getManager();
            
            //now we set the state of the message to "read" and we update the database
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