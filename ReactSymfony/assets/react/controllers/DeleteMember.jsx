import React, { useState, useEffect } from 'react';
import Select from 'react-select';

function DeleteMember() {
    const [groups, setGroups] = useState([]);
    const [selectedGroup, setSelectedGroup] = useState(null);
    const [members, setMembers] = useState([]);
    const [selectedMember, setSelectedMember] = useState(null);
    const [responseMessage, setResponseMessage] = useState('');

    useEffect(() => {
        fetch('/api/get_groups')
            .then(response => response.json())
            .then(data => {
                const options = data.map(group => ({ value: group.groupName, label: group.groupName }));
                setGroups(options);
            })
            .catch(error => console.error(error));
    }, []);

    useEffect(() => {
        if (selectedGroup) {
            fetch('/api/get_group_members', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ groupName: selectedGroup.value }),
            })
                .then(response => response.json())
                .then(data => {
                    const options = data.members.map(member => ({ value: member.value, label: member.label }));
                    setMembers(options);
                })
                .catch(error => console.error(error));
        } else {
            setMembers([]);
        }
    }, [selectedGroup]);


    function handleSubmit(event) {
        event.preventDefault();

        if (!selectedMember || !selectedGroup) {
            alert('Veuillez sélectionner un membre et un groupe.');
            return;
        }

        fetch('/api/remove_member', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username: selectedMember.value, groupName: selectedGroup.value }),
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
        <div id="deleteMemberContainer" className="container">
            <div id="deleteMemberContent" className="contentSection">
                <h3>Suppression d'un membre d'un groupe</h3>
                <form onSubmit={handleSubmit}>
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
                    <div className="formGroup">
                        <label htmlFor="memberSelect">Membre :</label>
                        <Select
                            id="memberSelect"
                            name="member"
                            options={members}
                            onChange={setSelectedMember}
                            value={selectedMember}
                        />
                    </div>
                    <button type="submit">Supprimer</button>
                </form>
                {responseMessage && (
                    <div className="responseMessage">{responseMessage}</div>
                )}
            </div>
        </div>
    );
}

export default DeleteMember;
