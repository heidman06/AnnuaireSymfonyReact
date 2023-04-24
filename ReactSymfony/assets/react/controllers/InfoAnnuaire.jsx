import React, { useState, useEffect } from 'react';
import axios from 'axios';

function InfoAnnuaire() {
    const [users, setUsers] = useState('');
    const [groups, setGroups] = useState('');

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

    return (
        <section class="information-section">
            <div>
                <p>{users.length}</p>
                <p>Employés</p>
            </div>
            <div>
                <p>{groups.length}</p>
                <p>Postes</p>
            </div>
            <div>
                <p>5</p>
                <p>Services</p>
            </div>
        </section>
    );
}

export default InfoAnnuaire;
