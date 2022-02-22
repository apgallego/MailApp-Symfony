<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutboxController extends AbstractController
{
    #[Route('/outbox', name: 'outbox')]
    public function index(MessageRepository $messageRepository): Response
    {

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $messages = $messageRepository->findBy(['senderID' => $user->getId()]);
        // $messages = $messageRepository->findBy(['message_sent' => $user->getMessageSent()]);
        
        return $this->render('outbox/outbox.html.twig', [
            'controller_name' => 'OutboxController',
            'messages' => $messages
        ]);
    }
}
