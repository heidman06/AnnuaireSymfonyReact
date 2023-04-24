import React, { useState, useEffect } from 'react';
import Select from 'react-select';

function AddMember() {
    const [users, setUsers] = useState([]);
    const [groups, setGroups] = useState([]);
    const [selectedUser, setSelectedUser] = useState(null);
    const [selectedGroup, setSelectedGroup] = useState(null);
    const [responseMessage, setResponseMessage] = useState(null);

    useEffect(() => {
        fetch('/api/get_users')
            .then(response => response.json())
            .then(data => {
                const options = data.map(user => ({ value: user, label: user }));
                setUsers(options);
            })
            .catch(error => console.error(error));

        fetch('/api/get_groups')
            .then(response => response.json())
            .then(data => {
                const options = data.map(group => ({ value: group.groupName, label: group.groupName }));
                setGroups(options);
            })
            .catch(error => console.error(error));
    }, []);

    function handleSubmit(event) {
        event.preventDefault();

        if (!selectedUser || !selectedGroup) {
            alert('Veuillez sélectionner un utilisateur et un groupe.');
            return;
        }

        fetch('/api/add_member', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username: selectedUser.label, groupName: selectedGroup.label }),
        })
            .then(response => response.json())
            .then(data => {
                setResponseMessage(data.message);
            })
            .catch(error => {
                console.error(error);
                setResponseMessage('Une erreur est survenue.');
            });
    }


    return (
        <div id="addMemberContainer" className="container">
            <div id="addMemberContent" className="contentSection">
                <h3>Ajout d'un membre à un groupe</h3>
                <form onSubmit={handleSubmit}>
                    <div className="formGroup">
                        <label htmlFor="userSelect">Utilisateur :</label>
                        <Select
                            id="userSelect"
                            name="user"
                            options={users}
                            onChange={setSelectedUser}
                            value={selectedUser}
                        />
                    </div>
                    <div className="formGroup">
                        <label htmlFor="groupSelect">Groupe :</label>
                        <Select
                            id="groupSelect"
                            name="group"
                            options={groups}
                            onChange={setSelectedGroup}
                            value={selectedGroup}
                        />
                    </div>
                    <button type="submit">Ajouter</button>
                </form>
                {responseMessage && <p>{responseMessage}</p>}
            </div>
        </div>
    );
}

export default AddMember;
