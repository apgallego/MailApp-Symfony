<?php
// src/Controller/GroupController.php
/**
 * This controller manages the Groups section. In it we can find a form to create groups and a container with the groups
 * that the user is participating in. This controller manages de creation of the groups too.
 */
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

        //we get the current user and the current date
        /** 
         * @var \App\Entity\User $user
         */
        $user = $this->getUser();
        $userID = $user->getId();
        $date = new \DateTime('@' . strtotime('now'));

        //we create a group and get all the users
        $group = new Group();
        $users = $userRepository->findAll();
        // $groups = $groupRepository->findAll();

        //we also get all the existing groups to print them in the template
        $groups = $groupRepository->createQueryBuilder('g')
            ->orderBy('g.timestamp', 'DESC')
            ->getQuery()
            ->getResult();

        //if there is a post request, we create the group
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['participants'])){

            //the participants are the ones sent by the POST method and the user id itself
            $participants = array_merge($_POST['participants'], [$userID]);

            //now we set the group
            $group->setUserIDs($participants);
            $group->setAlias($_POST['alias']);
            $group->setTimestamp($date);

            //and we update the database
            $entityManager->persist($group);
            $entityManager->flush();

            //then we are redirected to the same page so that it is refreshed
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
