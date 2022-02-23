<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(MessageRepository $messageRepository, UserRepository $userRepository): Response
    {

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        // $userID = $user->getId();
        $messages = $messageRepository->findBy(['receiverID' => $user->getId()]);
        $users = $userRepository->findAll();
        // var_dump($messages);

        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'messages' => $messages,
            'users' => $users
        ]);
    }
}