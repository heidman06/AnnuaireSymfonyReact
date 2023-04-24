<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\LdapUserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ReactController extends AbstractController
{
    #[Route('/react', name: 'app_react')]
    public function index(): Response
    {
        //$ldap = new LdapUserService();
        //$groups = $ldap->getAllGroups();
        //print_r($groups);
        //$userInfo = $ldap->getUsersByName('Amlah');
        //$ldap->displayUserInfo($userInfo);

        return $this->render('react/index.html.twig', [
            'controller_name' => 'ReactController',
        ]);
    }

    #[Route('/api/search', name: 'app_api_search')]
    public function search(Request $request, LdapUserService $ldapUserService): JsonResponse
    {
        $name = $request->query->get('name');
        $structureOrPerson = $request->query->get('ou');
        if ($structureOrPerson == "personnes") {
            $users = $ldapUserService->getUsersByName($name);
            if (!empty($users)) {
                $message = '';
                foreach ($users as $user) {
                    $message .= 'Nom d\'utilisateur : ' . $user['username'] . '<br>';
                    $message .= 'Nom complet : ' . $user['name'] . '<br>';
                    $message .= 'Email : ' . $user['email'] . '<br>';
                    $message .= 'Poste : ' . $user['metier'] . '<br>';
                    $message .= '<br>';
                }
            } else {
                $message = 'Aucun résultat';
            }
        } else if ($structureOrPerson == "structure") {
            $users = $ldapUserService->getStruct($name);
            if (!empty($users)) {
                $message = '';
                foreach ($users as $user) {
                    $message .= 'Structure : ' . $user['structureName'] . '<br>';
                }
            } else {
                $message = 'Aucun résultat';
            }
        }

        return new JsonResponse(['message' => $message]);
    }

    #[Route('/connexion', name: 'app_connexion', methods: ['POST', 'GET'])]
    public function connexion(Request $request, AuthenticationUtils $authenticationUtils, LdapUserService $ldapUserService): Response
    {
        // Récupère les erreurs d'authentification, s'il y en a
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Si le formulaire a été soumis, vérifie le nom d'utilisateur et le mot de passe
        if ($request->isMethod('POST')) {
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');

            // Récupérer les informations de l'utilisateur en utilisant le nom d'utilisateur (sAMAccountName)
            $user = $ldapUserService->getUser($username);

            // Vérifie si l'utilisateur existe
            if (!$user) {
                throw new BadCredentialsException('User null');
            }

            // Récupérer le DN de l'utilisateur
            $dn = $user['dn'];

            // Vérifie si le mot de passe est correct
            $ldap = $ldapUserService->getLdap();
            try {
                $ldap->bind($dn, $password);
            } catch (ConnectionException $e) {
                throw new BadCredentialsException('Nom d\'utilisateur ou mot de passe incorrect.', 0, $e);
            }

            // Vérifie si l'utilisateur appartient au groupe "Grp_AdmAD"
            if (!in_array('Grp_AdmAD', $user['roles'])) {
                throw new BadCredentialsException('Il n\'appartient pas au groupe autorisé');
            }

            // Si l'authentification est réussie, redirige vers la page d'accueil sécurisée
            return $this->render('react/acceuilAdmins.html.twig');
        }

        // Affiche le formulaire de connexion avec les erreurs éventuelles
        return $this->render('react/connection.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/react', name: 'legal_info')]
    public function info(): Response
    {
        return $this->render('react/infosLegales.html.twig');
    }


    #[Route('/api/search2', name: 'advancedSearchBar')]
    public function advancedSearch(Request $request, LdapUserService $ldapUserService): JsonResponse
    {
        $name = $request->query->get('name');
        $structureOrPerson = $request->query->get('ou');
        if ($structureOrPerson == "personnes") {
            $users = $ldapUserService->getUsersByName2($name);
            if (!empty($users)) {
                $message = '';
                foreach ($users as $user) {
                    $message .= 'Nom d\'utilisateur : ' . $user['username'] . '<br>';
                    $message .= 'Nom complet : ' . $user['name'] . '<br>';
                    $message .= 'Email : ' . $user['email'] . '<br>';
                    $message .= 'Poste : ' . $user['metier'] . '<br>';
                    $message .= 'Naissance : ' . $user['naissance'] . '<br>';
                    $message .= 'Genre : ' . $user['genre'] . '<br>';
                    $message .= 'Service : ' . $user['service'] . '<br>';
                    $message .= '<br>';
                }
            } else {
                $message = 'Aucun résultat';
            }
        } else if ($structureOrPerson == "structure") {
            $users = $ldapUserService->getStruct($name);
            if (!empty($users)) {
                $message = '';
                foreach ($users as $user) {
                    $message .= 'Structure : ' . $user['structureName'] . '<br>';
                }
            } else {
                $message = 'Aucun résultat';
            }
        }

        return new JsonResponse(['message' => $message]);
    }


    #[Route('/api/create_group', name: 'app_create_group', methods: ['POST'])]
    public function createGroup(Request $request, LdapUserService $ldapUserService): JsonResponse
    {
        $groupName = $request->request->get('group_name');

        if (!$groupName) {
            return new JsonResponse(['message' => 'Le nom du groupe est requis.'], Response::HTTP_BAD_REQUEST);
        }

        // Crée le groupe
        $isGroupCreated = $ldapUserService->createGroup($groupName);

        if (!$isGroupCreated) {
            return new JsonResponse(['message' => 'Impossible de créer le groupe il existe déjà.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return new JsonResponse(['message' => 'Groupe créé avec succès.']);
    }


    #[Route('/api/get_groups', name: 'get_groups', methods: ['GET'])]
    public function getGroups(LdapUserService $ldapUserService): JsonResponse
    {
        $groups = $ldapUserService->getAllGroups();
        return new JsonResponse($groups);
    }

    #[Route('/api/delete_group', name: 'delete_group', methods: ['POST'])]
    public function deleteGroup(Request $request, LdapUserService $ldapUserService): JsonResponse
    {
        $groupName = $request->request->get('group_name');
        $success = $ldapUserService->deleteGroup($groupName);
        $message = $success ? 'Le groupe a été supprimé avec succès.' : 'Une erreur s\'est produite lors de la suppression du groupe.';
        return new JsonResponse(['success' => $success, 'message' => $message]);
    }

    #[Route('/api/get_users', name: 'get_users', methods: ['GET'])]
    public function getUsers(LdapUserService $ldapUserService): JsonResponse
    {
        $users = $ldapUserService->getAllUsers();
        return new JsonResponse($users);
    }

    #[Route('/api/add_member', name: 'add_member', methods: ['POST'])]
    public function addMember(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || !isset($data['groupName'])) {
            return new JsonResponse(['message' => 'Missing parameters'], 400);
        }

        $username = $data['username'];
        $groupName = $data['groupName'];

        $ldapUserService = new LdapUserService();
        $result = $ldapUserService->addUserToGroup($username, $groupName);

        if ($result) {
            return new JsonResponse(['message' => 'Le membre a été ajouté au groupe avec succès !'], 200);
        } else {
            return new JsonResponse(['message' => 'Une erreur est survenue.'], 500);
        }
    }

    #[Route('/api/remove_member', name: 'remove_member', methods: ['POST'])]
    public function removeMember(Request $request, LdapUserService $ldapUserService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || !isset($data['groupName'])) {
            return new JsonResponse(['message' => 'Missing parameters'], 400);
        }

        $username = $data['username'];
        $groupName = $data['groupName'];

        $result = $ldapUserService->removeUserFromGroup($username, $groupName);

        if ($result) {
            return new JsonResponse(['message' => 'Le membre a été supprimé du groupe.'], 200);
        } else {
            return new JsonResponse(['message' => 'Une erreur est survenue.'], 500);
        }
    }


    #[Route('/api/get_group_members', name: 'get_group_members', methods: ['POST'])]
    public function getGroupMembers(Request $request, LdapUserService $ldapUserService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['groupName'])) {
            return new JsonResponse(['message' => 'Missing parameters'], 400);
        }

        $groupName = $data['groupName'];

        $members = $ldapUserService->getGroupMembers($groupName);

        return new JsonResponse(['members' => $members], 200);
    }
    #[Route('/api/gender', name: 'genderFilter')]
    public function filterByGender(Request $request)
    {
        $gender = $request->query->get('genre');
        $text = $request->query->get('text');

        $users = explode("<br><br>", $text);
        $filteredUsers = "";

        foreach ($users as $user) {
            if (strpos($user, "Genre : " . $gender) !== false) {
                $filteredUsers .= $user . "<br><br>";
            }
        }

        return new Response($filteredUsers);
    }

    #[Route('/api/service', name: 'serviceFilter')]
    public function filterByService(Request $request)
    {
        $service = $request->query->get('service');
        $text = $request->query->get('text');

        $users = explode("<br><br>", $text);
        $filteredUsers = "";

        foreach ($users as $user) {
            if (strpos($user, "Service : " . $service) !== false) {
                $filteredUsers .= $user . "<br><br>";
            }
        }

        return new Response($filteredUsers);
    }

    #[Route('/api/job', name: 'jobFilter')]
    public function filterByJob(Request $request)
    {
        $job = $request->query->get('job');
        $text = $request->query->get('text');

        $users = explode("<br><br>", $text);
        $filteredUsers = "";

        foreach ($users as $user) {
            if (strpos($user, "Poste : " . $job) !== false) {
                $filteredUsers .= $user . "<br><br>";
            }
        }

        return new Response($filteredUsers);
    }

    #[Route('/api/getJobs', name: 'getJobs')]
    public function getJobs(Request $request)
    {
        $text = $request->query->get('text');
        $jobs = [];

        $users = explode("<br><br>", $text);

        foreach ($users as $user) {
            $job = $this->extractValue($user, "Poste : ");

            if (!array_key_exists($job, $jobs)) {
                $jobs[$job] = 1;
            } else {
                $jobs[$job]++;
            }
        }
        $options = '<option value="">Sélectionner le poste</option>';
        foreach ($jobs as $job => $count) {
            if ($job !== '') {
                $options .= "<option value='{$job}'>{$job}</option>";
            }
        }
        return new Response($options);
    }

    #[Route('/api/getServices', name: 'getServices')]
    public function getServices(Request $request)
    {
        $text = $request->query->get('text');
        $services = [];

        $users = explode("<br><br>", $text);

        foreach ($users as $user) {
            $service = $this->extractValue($user, "Service : ");

            if (!array_key_exists($service, $services)) {
                $services[$service] = 1;
            } else {
                $services[$service]++;
            }
        }
        $options = '<option value="">Sélectionner le service</option>';
        foreach ($services as $service => $count) {
            if ($service !== '') {
                $options .= "<option value='{$service}'>{$service}</option>";
            }
        }
        return new Response($options);
    }

    private function extractValue($text, $prefix)
    {
        $start = strpos($text, $prefix);
        if ($start === false) {
            return null;
        }

        $start += strlen($prefix);
        $end = strpos($text, "<br>", $start);
        if ($end === false) {
            $end = strlen($text);
        }

        return trim(substr($text, $start, $end - $start));
    }

    #[Route('/api/births', name: 'birthFilter')]
    public function filterByBirth(Request $request)
    {
        $minBirth = $request->query->get('minBirth');
        $maxBirth = $request->query->get('maxBirth');
        $text = $request->query->get('text');

        $users = explode("<br><br>", $text);
        $filteredUsers = "";

        foreach ($users as $user) {
            $birth = $this->extractValue($user, "Naissance : ");
            if ($this->isBetweenBirthDates($birth, $minBirth, $maxBirth)) {
                $filteredUsers .= $user . "<br><br>";
            }
        }

        return new Response($filteredUsers);
    }

    private function isBetweenBirthDates($birth, $minBirth, $maxBirth)
    {
        $birthTimestamp = strtotime(str_replace('/', '-', $birth));
        $minBirthTimestamp = strtotime(str_replace('/', '-', $minBirth));
        $maxBirthTimestamp = strtotime(str_replace('/', '-', $maxBirth));

        return $birthTimestamp >= $minBirthTimestamp && $birthTimestamp <= $maxBirthTimestamp;
    }
}
