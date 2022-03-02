<?php
// src/Controller/HomeController.php
/**
 * This controller manages the Home page (inbox)
 */
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
        //we get the current user

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        // $userID = $user->getId();
        // $messages = $messageRepository->findBy(['receiverID' => $user->getId()]);

        //we get all the existing messages
        $messages = $messageRepository->createQueryBuilder('m')
            ->andWhere('m.receiverID = :id')
            ->setParameter('id', $user->getId())
            ->orderBy('m.timestamp', 'DESC')
            ->getQuery()
            ->getResult();

        //we also get all the existing users
        $users = $userRepository->findAll();

        //the rest of the logic is in the corresponding twig template
        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'messages' => $messages,
            'users' => $users
        ]);
    }
}
