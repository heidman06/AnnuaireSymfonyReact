import React, { useState } from 'react';

function GroupCreator() {
    const [message, setMessage] = useState('');

    async function handleClick() {
        const groupName = document.getElementById("grp-name").value;

        if (!groupName) {
            setMessage('Le nom du groupe est requis.');
            return;
        }

        try {
            const response = await fetch('/api/create_group', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ group_name: groupName }),
            });

            const data = await response.json();
            if (response.ok) {
                setMessage(`Le groupe ${groupName} a été créé avec succès !`);
            } else {
                setMessage(data.message || 'Une erreur est survenue lors de la création du groupe.');
            }
        } catch (error) {
            setMessage('Une erreur est survenue lors de la création du groupe.');
            console.error(error);
        }
    }

    return (
        <div id="groupCreator" className="container">
            <h3>Création d'un groupe</h3>
            <div className='group-box'>
                <input type="text" id="grp-name" className="search-box-input" placeholder="Nom du groupe" />
                <div className="search-choicebox">
                </div>
                <button onClick={handleClick} className="search-box-btn">
                    Créer le groupe
                </button>
            </div>
            {message && <div className="affichage" dangerouslySetInnerHTML={{ __html: message }}></div>}
        </div>
    );
}

export default GroupCreator;
