<?php

namespace App\Model;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Exception\ConnectionException;
use function PHPUnit\Framework\throwException;


class LdapUserService
{
    private Ldap $ldap;
    private string $dn = 'CN=Admin BD,CN=Users,DC=saeG4A,DC=local';
    private string $password = 'heidman123*';

    public function __construct()
    {
        $this->ldap = LDAP::create('ext_ldap', [
            'host' => '10.22.32.8',
            'encryption' => 'none',
            'port' => 389,
        ]);
    }

    public function getLdap(): Ldap
    {
        return $this->ldap;
    }

    public function testConnection(): bool
    {
        try {
            $this->ldap->bind($this->dn, $this->password);
            echo "Connection reussi!";
            return true;
        } catch (ConnectionException) {
            echo "Connection non reussi!";
            return false;
        }
    }
    public function getUsersByName(string $name): array
    {
        $this->ldap->bind($this->dn, $this->password);

        // Recherche des utilisateurs ayant le même nom
        $name = explode(" ", $name);
        if (count($name) == 1) {
            $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=user)(sn=' . $name[0] . '*))');
        } else {
            if ($name[0] == "*") {
                $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=user)(givenName=' . $name[1] . '*))');
            } else {
                $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=user)(sn=' . $name[0] . ')(givenName=' . $name[1] . '*))');
            }
        }
        $result = $query->execute();

        $users = [];

        foreach ($result as $entry) {
            $groups = $entry->getAttribute('memberOf');
            $res = null;

            if ($groups !== null) {
                $parts = explode(',', $groups[count($groups) - 1]);

                // Récupère la première valeur du tableau et la divise en utilisant le délimiteur '='
                $first_part = explode('=', $parts[0]);

                // Récupère la deuxième valeur
                $res = $first_part[1];
            }

            $users[] = [
                'username' => $entry->getAttribute('sAMAccountName')[0],
                'email' => $entry->getAttribute('mail')[0],
                'name' => $entry->getAttribute('displayName')[0],
                'surname' => $entry->getAttribute('sn')[0],
                'metier' => $res
            ];
        }

        return $users;
    }

    public function getUsersByName2(string $name)
    {
        $this->ldap->bind($this->dn, $this->password);

        // Recherche des utilisateurs ayant le même nom
        $name = explode(" ", $name);
        if (count($name) == 1) {
            $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=user)(sn=' . $name[0] . '*))');
        } else {
            if ($name[0] == "*") {
                $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=user)(givenName=' . $name[1] . '*))');
            } else {
                $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=user)(sn=' . $name[0] . ')(givenName=' . $name[1] . '*))');
            }
        }
        $result = $query->execute();
        $users = [];
        foreach ($result as $entry) {
            $parts = explode(',', $entry->getAttribute('memberOf')[count($entry->getAttribute('memberOf')) - 1]);

            // Récupère la première valeur du tableau et la divise en utilisant le délimiteur '='
            $first_part = explode('=', $parts[0]);

            // Récupère la deuxième valeur
            $res = $first_part[1];
            $dn = $entry->getAttribute('distinguishedName')[0];
            $uo = explode("=", explode(",", $dn)[1])[1];
            if ($entry->getAttribute('birthdate') != null) {
                $users[] = [
                    'username' => $entry->getAttribute('sAMAccountName')[0],
                    'email' => $entry->getAttribute('mail')[0],
                    'name' => $entry->getAttribute('displayName')[0],
                    'surname' => $entry->getAttribute('sn')[0],
                    'metier' => $res,
                    'naissance' => $entry->getAttribute('birthdate')[0],
                    'genre' => $entry->getAttribute('sex')[0],
                    'service' => $uo
                ];
            }
        }
        return $users;
    }

    public function getStruct(string $name): array
    {
        $this->ldap->bind($this->dn, $this->password);

        // Recherche des utilisateurs ayant le même nom
        $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=organizationalUnit)(ou=*' . $name . '*))');
        $result = $query->execute();

        // Récupère les résultats de la requête
        $entries = $result->toArray();

        $users = [];

        foreach ($entries as $entry) {
            if ($entry->getAttribute('ou')[0] != "Domain Controllers")
                $users[] = [
                    'structureName' => $entry->getAttribute('ou')[0]
                ];
        }
        return $users;
    }

    public function getUser(string $username): ?array
    {
        $this->ldap->bind($this->dn, $this->password);

        // Recherche de l'utilisateur par son nom d'utilisateur
        $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=user)(sAMAccountName=' . $username . '))');
        $result = $query->execute();

        // Vérifie si l'utilisateur existe
        if ($result->count() == 0) {
            return null;
        }

        $entry = $result[0];

        // Vérifie si l'utilisateur appartient au groupe "Grp_AdmAD"
        $groups = $entry->getAttribute('memberOf');

        $roles = [];

        foreach ($groups as $group) {
            if (str_contains($group, 'Grp_AdmAD')) {
                $roles[] = 'Grp_AdmAD';
                break;
            }
        }

        // Retourne les informations de l'utilisateur, ses rôles et son DN
        return [
            'username' => $entry->getAttribute('sAMAccountName')[0],
            'email' => $entry->getAttribute('mail')[0],
            'name' => $entry->getAttribute('displayName')[0],
            'roles' => $roles,
            'dn' => $entry->getDn(),
        ];
    }

    public function getAllUsers(): array
    {
        $this->ldap->bind($this->dn, $this->password);

        // Recherche de tous les utilisateurs
        $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=user))');
        $result = $query->execute();

        $users = [];

        foreach ($result as $entry) {
            $username = $entry->getAttribute('sAMAccountName')[0];
            $users[] = $username;
        }

        return $users;
    }
    public function createGroup(string $groupName): bool
    {
        // Crée un nouveau groupe
        $this->ldap->bind($this->dn, $this->password);

        $groupEntry = new Entry('CN=' . $groupName . ',CN=Users,DC=saeG4A,DC=local', [
            'objectClass' => ['top', 'group'],
            'sAMAccountName' => [$groupName],
        ]);
        try {
            $this->ldap->getEntryManager()->add($groupEntry);
        } catch (LdapException $e) {
            // En cas d'échec de la création du groupe, on renvoie false
            return false;
        }

        return true;
    }



    public function getAllGroups(): array
    {
        $this->ldap->bind($this->dn, $this->password);

        // Recherche de tous les groupes
        $query = $this->ldap->query('DC=saeG4A,DC=local', '(&(objectClass=group))');
        $result = $query->execute();

        $groups = [];

        foreach ($result as $entry) {
            if ($entry->getAttribute('sAMAccountName')[0] != "Grp_AdmAD") {
                $groupName = $entry->getAttribute('sAMAccountName')[0];
                $members = $entry->getAttribute('member');
                $memberCount = is_array($members) ? count($members) : 0;

                $groups[] = [
                    'groupName' => $groupName,
                    'memberCount' => $memberCount,
                ];
            }
        }

        return $groups;
    }

    public function deleteGroup(string $groupName): bool
    {
        $this->ldap->bind($this->dn, $this->password);

        // Vérifie si le groupe existe déjà
        $existingGroup = $this->getGroup($groupName);
        if ($existingGroup === null) {
            return false;
        }

        try {
            $groupDn = $existingGroup['dn'];
            $this->ldap->getEntryManager()->remove(new Entry($groupDn, []));
        } catch (LdapException $e) {
            // En cas d'échec de la suppression du groupe, on renvoie false
            return false;
        }

        return true;
    }




    public function getGroup(string $groupName): ?array
    {
        $this->ldap->bind($this->dn, $this->password);

        $query = $this->ldap->query('CN=Users,DC=saeG4A,DC=local', '(&(objectClass=group)(sAMAccountName=' . $groupName . '))');
        $result = $query->execute();

        if ($result->count() == 0) {
            return null;
        }

        $entry = $result[0];
        return [
            'groupName' => $entry->getAttribute('sAMAccountName')[0],
            'dn' => $entry->getDn(),
        ];
    }


    public function addUserToGroup(string $username, string $groupName): bool
    {
        $this->ldap->bind($this->dn, $this->password);

        // Récupère l'utilisateur et le groupe
        $user = $this->getUser($username);
        $group = $this->getGroup($groupName);

        // Vérifie si l'utilisateur et le groupe existent
        if (empty($user) || empty($group)) {
            return false;
        }

        // Ajoute l'utilisateur au groupe
        try {
            $userDn = $user['dn'];
            $groupDn = $group['dn'];
            $entryManager = $this->ldap->getEntryManager();
            $entryManager->addAttributeValues(new Entry($groupDn, []), 'member', [$userDn]);
        } catch (LdapException $e) {
            // En cas d'échec de l'ajout de l'utilisateur au groupe, on renvoie false
            return false;
        }

        return true;
    }


    public function removeUserFromGroup(string $username, string $groupName): bool
    {
        $this->ldap->bind($this->dn, $this->password);

        // Récupère l'utilisateur et le groupe
        $user = $this->getUser($username);
        $group = $this->getGroup($groupName);

        // Vérifie si l'utilisateur et le groupe existent
        if (empty($user) || empty($group)) {
            return false;
        }

        // Supprime l'utilisateur du groupe
        try {
            $userDn = $user['dn'];
            $groupDn = $group['dn'];
            $entryManager = $this->ldap->getEntryManager();
            $entryManager->removeAttributeValues(new Entry($groupDn, []), 'member', [$userDn]);
        } catch (LdapException $e) {
            // En cas d'échec de la suppression de l'utilisateur du groupe, on renvoie false
            return false;
        }

        return true;
    }


    public function getGroupMembers(string $groupName): array
    {
        $this->ldap->bind($this->dn, $this->password);

        $group = $this->getGroup($groupName);

        if (empty($group)) {
            return array();
        }

        $groupDn = $group['dn'];
        $query = $this->ldap->query($groupDn, '(&(objectClass=group)(sAMAccountName=' . $groupName . '))');
        $result = $query->execute();

        if ($result->count() == 0) {
            return array();
        }

        $entry = $result[0];
        $memberDns = $entry->getAttribute('member');
        $members = array();

        foreach ($memberDns as $memberDn) {
            $query = $this->ldap->query($memberDn, '(objectClass=person)');
            $result = $query->execute();

            if ($result->count() > 0) {
                $entry = $result[0];
                $members[] = array(
                    'value' => $entry->getAttribute('sAMAccountName')[0],
                    'label' => $entry->getAttribute('name')[0],
                );
            }
        }

        return $members;
    }
}
