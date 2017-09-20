import React, { Component } from 'react'
import Debounce from 'debounce'
import {Link} from "react-router-dom";

class Users extends Component {

    constructor(props) {
        super(props)
        this.state = {
            users: [],
            filteredUsers: [],
            collectivites: [],
            collectiviteId: '',
            name: '',
            nom: '',
            infos: '',
            isSuperAdmin: false
        }
    }

    componentDidMount() {
        this.getCurrentCollectivite()
        this.getCollectivites()
    }

    getUsers (id) {
        if (id === undefined) id = this.state.collectiviteId
        fetch(Routing.generate('sesile_user_userapi_userscollectivite', {id}), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({users: json, filteredUsers: json, collectiviteId: id}))
            .then(() => {
                if (this.state.name) this.handleChangeSearchUser(this.state.name)
            })
    }

    getCollectivites () {
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({collectivites: json}))
    }

    getCurrentCollectivite () {
        fetch(Routing.generate('sesile_user_userapi_getcurrent'), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({collectiviteId: json.collectivite.id})
                if(json.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined) {
                    this.setState({isSuperAdmin: true})
                }

                this.getUsers(this.state.collectiviteId)
            })
    }

    deleteType (typeId) {
        fetch(Routing.generate('sesile_user_userapi_remove', {id: typeId}), { method: 'delete', credentials: 'same-origin'})
            .then(response => response.json())
            .then(() => {
                this.getUsers(this.state.collectiviteId)
                this.setState({infos: 'Enregistrement supprimé !'})
            })
    }

    findUser = Debounce((value, collectiviteId) => {
        fetch(Routing.generate("sesile_user_userapi_findbynomorprenom", {value,collectiviteId}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({filteredUsers: json})
            })
    }, 800, true)

    handleChangeSearchUser = (value) => {
        this.setState({name: value})
        if(value.trim().length > 2) this.findUser(value, this.state.collectiviteId)
        else this.setState({users: []})
    }

    onSearchByCollectiviteFieldChange(value) {
        this.setState({collectiviteId:value})
        this.getUsers(value)
    }

    render() {
        const filteredUsers = this.state.filteredUsers

        const Row = filteredUsers && filteredUsers.map(filteredUsers =>
            <div className="cell medium-12 panel-body grid-x" key={filteredUsers.id}>
                <div className="cell medium-3">
                    {filteredUsers._prenom} {filteredUsers._nom}
                </div>
                <div className="cell medium-3">
                    {filteredUsers.collectivite.nom}
                </div>
                <div className="cell medium-3">
                    {filteredUsers.email}
                </div>
                <div className="cell medium-3">
                    <Link to={`/admin/${filteredUsers.collectivite.id}/user/${filteredUsers.id}`} className="button primary" >éditer</Link>
                    <button className="button alert" onClick={() => this.deleteType(filteredUsers.id)}>supprimer</button>
                </div>
            </div>
        )

        const collectivites = this.state.collectivites
        const collectivitesSelect = collectivites && collectivites.map(collectivite =>
            {
                if (collectivite.active) {
                    return <option value={collectivite.id} key={collectivite.id}>{collectivite.nom}</option>
                }
            }
        )

        return (
            <div>
                <h4 className="text-center text-bold">Recherche d'un utilisateur</h4>
                <p className="text-center">Puis accéder aux paramétres</p>
                <p className="text-center">{ this.state.infos }</p>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x align-center-middle">
                            <div className="medium-6 cell">
                                <label htmlFor="circuit_name_search">Lequel ?</label>
                                <input id="type_name_search"
                                       value={this.state.name}
                                       onChange={(event) => this.handleChangeSearchUser(event.target.value)}
                                       placeholder="Entrez le nom de l'utilisateur..."
                                       type="text" />
                            </div>
                            {
                                (collectivitesSelect.length > 0 && this.state.isSuperAdmin) ?
                                    <div className="medium-6 cell">
                                        {
                                            <div>
                                                <label htmlFor="collectivite_name_search">Collectivité ?</label>
                                                <select id="collectivite_name_search" value={this.state.collectiviteId} onChange={(event) => this.onSearchByCollectiviteFieldChange(event.target.value)}>
                                                    {collectivitesSelect}
                                                </select>
                                            </div>
                                        }
                                    </div>
                                    : ""
                            }

                        </div>
                    </div>
                    <div className="cell medium-8">
                        <div className="grid-x grid-padding-x panel">
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-12">Liste des utilisateurs</div>
                            </div>
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-3">
                                    <button className="button primary" onClick={() => this.postTypes()}>Ajouter un utilisateur</button>
                                </div>
                            </div>
                            {
                                (Row.length > 0) ? Row :
                                    <div className="cell medium-12 panel-body">
                                        <div className="text-center">
                                            Aucun utilisateur ne correspond à votre recherche...
                                        </div>
                                    </div>
                            }
                        </div>
                    </div>
                </div>
            </div>
        )
    }

}


export default Users