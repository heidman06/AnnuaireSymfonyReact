import React, { useState, useEffect } from 'react';

function ListGroups() {
    const [groups, setGroups] = useState([]);

    // Récupère la liste des groupes lors du chargement du composant
    useEffect(() => {
        fetch('/api/get_groups')
            .then(response => response.json())
            .then(data => setGroups(data))
            .catch(error => console.error(error));
    }, []);

    // Supprime un groupe
    function deleteGroup(groupName) {
        fetch('/api/delete_group', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ group_name: groupName }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Met à jour la liste des groupes en supprimant le groupe supprimé
                    setGroups(groups.filter(group => group.groupName !== groupName));
                } else {
                    console.error(data.message);
                }
            })
            .catch(error => console.error(error));
    }

    return (
        <div id="listGroupsContainer" className="container">
            <div id="listGroups" className="contentSection">
                <h3>Suppression d'un groupe</h3>
                <p>Liste des groupes :</p>
                <ul>
                    {groups.map(group => (
                        <li key={group.groupName}>
                            {group.groupName} - {group.memberCount} membres
                            <button onClick={() => deleteGroup(group.groupName)}>
                                <img src={require('../../../public/Image/poubelle.png')} alt="Supprimer" />
                            </button>
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    );
}

export default ListGroups;
