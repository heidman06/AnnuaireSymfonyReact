import React, { useState } from 'react';
import axios from 'axios';

function SearchBar() {
    const [message, setMessage] = useState('');

    const handleClick = () => {
        const searchValue = document.getElementsByClassName("search-box-input")[0].value;
        const structureOrPerson = document.getElementById("filtre-select").value;
        axios.get('/api/search', { params: { name: searchValue, ou: structureOrPerson } })
            .then(response => {
                console.log(response);
                setMessage(response.data.message);
            })
            .catch(error => {
                console.error(error);
            });
    };

    return (
        <div className="container">
            <div className="search-box">
                <div>
                    <div className="search-choicebox">
                        <select name="filtres" id="filtre-select">
                            <option value="personnes">Personnes</option>
                            <option value="structure">Structure</option>
                        </select>
                    </div>
                    <input type="text" className="search-box-input" placeholder="Vous cherchez quelqu'un ou quelque chose" />
                    <button onClick={handleClick} className="search-box-btn">
                        Recherche
                    </button>
                </div>
            </div>
            <div className="affichage" dangerouslySetInnerHTML={{ __html: message }}></div> {/* dangerouslySetInnerHTML est utilisé pour afficher le contenu HTML renvoyé par l'API tout en la sécurisant des injections*/}
        </div>
    );
}

export default SearchBar;
