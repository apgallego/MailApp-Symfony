<?php
// src/Controller/OutboxController.php
/**
 * This controller manages the outbox of the webapp
 */
namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutboxController extends AbstractController
{
    #[Route('/outbox', name: 'outbox')]
    public function index(MessageRepository $messageRepository, UserRepository $userRepository, GroupRepository $groupRepository): Response
    {
        //we get the current user and all the existing messages which were sent to the current user
        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $userID = $user->getId();
        // $messages = $messageRepository->findBy(['senderID' => $userID]);
        $messages = $messageRepository->createQueryBuilder('m')
            ->andWhere('m.senderID = :id')
            ->setParameter('id', $user->getId())
            ->orderBy('m.timestamp', 'DESC')
            ->getQuery()
            ->getResult();
        //now we get all the users and groups to iterate throught them in the twig template
        $users = $userRepository->findAll();
        $groups = $groupRepository->findAll();

        // $messages = $messageRepository->findBy(['message_sent' => $user->getMessageSent()]);
        
        return $this->render('outbox/outbox.html.twig', [
            'controller_name' => 'OutboxController',
            'messages' => $messages,
            'users' => $users,
            'group' => $groups
        ]);
    }
}
