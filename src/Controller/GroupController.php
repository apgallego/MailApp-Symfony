<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Repository\GroupRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Group;

class GroupController extends AbstractController
{
    #[Route('/groups', name: 'groups')]
    public function index(GroupRepository $groupRepository, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $userID = $user->getId();
        $date = new \DateTime('@' . strtotime('now'));

        $group = new Group();
        $users = $userRepository->findAll();
        // $groups = $groupRepository->findAll();
        $groups = $groupRepository->createQueryBuilder('g')
            ->orderBy('g.timestamp', 'DESC')
            ->getQuery()
            ->getResult();


        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['participants'])){

            $participants = array_merge($_POST['participants'], [$userID]);
            $group->setUserIDs($participants);
            $group->setAlias($_POST['alias']);
            $group->setTimestamp($date);

            $entityManager->persist($group);
            $entityManager->flush();

            return $this->redirectToRoute('groups');

        }
        return $this->render('group/index.html.twig', [
            'controller_name' => 'GroupController',
            'groups' => $groups,
            'users' => $users,
            'creatorUsername' => $user->getUsername()
        ]);
    }
}
