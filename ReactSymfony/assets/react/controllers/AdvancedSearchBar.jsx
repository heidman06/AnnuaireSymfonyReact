import React, { useState } from 'react';
import axios from 'axios';

function AdvancedSearchBar() {
    const [resultat, setResultat] = useState('');
    const [message, setMessage] = useState('');
    const [selectedFilters, setSelectedFilters] = useState([]);
    const [showFilters, setShowFilters] = useState(false);
    const [jobs, setJobs] = useState('');
    const [services, setServices] = useState('');
    const [minBirth, setMinBirth] = useState('');
    const [maxBirth, setMaxBirth] = useState('');


    const handleFilterChange = (filterName, isChecked) => {
        let newFilters = [];
        if (isChecked) {
            newFilters.push(filterName);
        }
        setSelectedFilters(newFilters);
        const searchValue = document.getElementsByClassName("search-box-input")[0].value;
        const structureOrPerson = document.getElementById("filtre-select").value;
        let params = { name: searchValue, ou: structureOrPerson };
        if (filterName === "genre-value") {
            if (isChecked) {
                params["filter"] = filterName;
                const gender = document.getElementById("genre-select").value;
                params["genre"] = gender;
                axios.get('/api/gender', { params: { ...params, text: resultat } })
                    .then(response => {
                        console.log(response);
                        setMessage(response.data);
                        if (message == "") {
                            setMessage("Aucun résultat");
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        }
        if (filterName === "poste-value") {
            if (isChecked) {
                params["filter"] = filterName;
                const poste = document.getElementById("poste-select").value;
                params["job"] = poste;
                axios.get('/api/job', { params: { ...params, text: resultat } })
                    .then(response => {
                        console.log(response);
                        setMessage(response.data);
                        if (message == "") {
                            setMessage("Aucun résultat");
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        }
        if (filterName === "poste") {
            axios.get('/api/getJobs', { params: { ...params, text: resultat } })
                .then(response => {
                    console.log(response);
                    setJobs(response.data);
                })
                .catch(error => {
                    console.error(error);
                });
        }
        if (filterName === "service-value") {
            if (isChecked) {
                params["filter"] = filterName;
                const service = document.getElementById("service-select").value;
                params["service"] = service;
                axios.get('/api/service', { params: { ...params, text: resultat } })
                    .then(response => {
                        console.log(response);
                        setMessage(response.data);
                        if (message == "") {
                            setMessage("Aucun résultat");
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        }
        if (filterName === "service") {
            axios.get('/api/getServices', { params: { ...params, text: resultat } })
                .then(response => {
                    console.log(response);
                    setServices(response.data);
                })
                .catch(error => {
                    console.error(error);
                });
        }

    };


    const handleClickBirths = () => {
        const searchValue = document.getElementsByClassName("search-box-input")[0].value;
        const structureOrPerson = document.getElementById("filtre-select").value;
        let params = { name: searchValue, ou: structureOrPerson };
        setMinBirth(document.getElementById('birth-min').value);
        setMaxBirth(document.getElementById('birth-max').value);
        params["minBirth"] = minBirth;
        params["maxBirth"] = maxBirth;
        axios.get('/api/births', { params: { ...params, text: resultat } })
            .then(response => {
                console.log(response);
                setMessage(response.data);
                if (message == "") {
                    setMessage("Aucun résultat");
                }
            })
            .catch(error => {
                console.error(error);
            });
    }

    const handleClick = () => {
        let filters = [];
        setSelectedFilters(filters);
        setShowFilters(false);
        const searchValue = document.getElementsByClassName("search-box-input")[0].value;
        const structureOrPerson = document.getElementById("filtre-select").value;
        let params = { name: searchValue, ou: structureOrPerson };
        if (selectedFilters.length > 0) {
            params["filter"] = selectedFilters[0];
            if (selectedFilters.includes("genre-value")) {
                params["genre"] = selectedFilters.find(filter => filter === "genre-value") + 1;
            }
        }
        axios.get('/api/search2', { params: params })
            .then(response => {
                console.log(response);
                setMessage(response.data.message);
                if (message == "") {
                    setMessage("Aucun résultat");
                }
                setResultat(response.data.message);
                const boxWithId = document.getElementById("decalage-box");
                if (boxWithId != null) {
                    boxWithId.removeAttribute("id");
                }
                setShowFilters(structureOrPerson === "personnes");
                axios.get('/api/getJobs', { params: { ...params, text: resultat } })
                    .then(response => {
                        console.log(response);
                        setJobs(response.data);
                    })
                    .catch(error => {
                        console.error(error);
                    });
                axios.get('/api/getServices', { params: { ...params, text: resultat } })
                    .then(response => {
                        console.log(response);
                        setServices(response.data);
                    })
                    .catch(error => {
                        console.error(error);
                    });
            })
            .catch(error => {
                console.error(error);
            });

    };

    return (
        <div className="container" id="decalage-box">
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
                {showFilters && (
                    <>
                        <br></br>
                        <ul>
                            <li>
                                <input type="radio" id="service" name="filter" onChange={(e) => handleFilterChange("service", e.target.checked)} />
                                <label htmlFor="service" className='texteBlanc'>Filtrer par service</label>
                                {selectedFilters.includes("service") && (
                                    <select name="service-select" id="service-select" onChange={(e) => handleFilterChange("service-value", e.target.value)} dangerouslySetInnerHTML={{ __html: services }}></select>
                                )}
                            </li>
                            <li>
                                <input type="radio" id="date-de-naissance" name="filter" onChange={(e) => handleFilterChange("date-de-naissance", e.target.checked)} />
                                <label htmlFor="date-de-naissance" className='texteBlanc'>Filtrer par date de naissance</label>
                                {selectedFilters.includes("date-de-naissance") && (
                                    <div>
                                        Saisir un intervalle entre
                                        <input type="date" id="birth-min" name="birth-min" defaultValue="1900-01-01" />
                                        et
                                        <input type="date" id="birth-max" name="birth-max" defaultValue="2010-01-01" />
                                        <button onClick={handleClickBirths}>Filtrer</button>
                                    </div>

                                )}
                            </li>
                            <li>
                                <input type="radio" id="genre" name="filter" onChange={(e) => handleFilterChange("genre", e.target.checked)} />
                                <label htmlFor="genre" className='texteBlanc'>Filtrer par genre</label>
                                {selectedFilters.includes("genre") && (
                                    <select name="genre-select" id="genre-select" onChange={(e) => handleFilterChange("genre-value", e.target.value)}>
                                        <option value="">Sélectionner le genre</option>
                                        <option value="man">Homme</option>
                                        <option value="woman">Femme</option>
                                    </select>
                                )}
                            </li>
                            <li>
                                <input type="radio" id="poste" name="filter" onChange={(e) => handleFilterChange("poste", e.target.checked)} />
                                <label htmlFor="poste" className='texteBlanc'>Filtrer par poste</label>
                                {selectedFilters.includes("poste") && (
                                    <select name="poste-select" id="poste-select" onChange={(e) => handleFilterChange("poste-value", e.target.value)} dangerouslySetInnerHTML={{ __html: jobs }}></select>
                                )}
                            </li>
                        </ul>
                    </>
                )}
            </div>
            {message ? (
                message == "" ? (
                    <div>
                        {setMessage("Aucun résultat")}
                        <div className="affichage">Aucun résultat</div>
                    </div>
                ) : (
                    <div className="affichage" dangerouslySetInnerHTML={{ __html: message }}></div>
                )
            ) : (
                <div>
                    {setMessage("Aucun résultat")}
                    <div className="affichage">Aucun résultat</div>
                </div>
            )}



        </div >
    )
};

export default AdvancedSearchBar;