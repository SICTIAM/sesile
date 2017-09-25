import React, { Component } from 'react'
import PropTypes from 'prop-types'
import {Link} from 'react-router-dom'
import { escapedValue } from '../_utils/Search'

class Users extends Component {

    constructor(props) {
        super(props)
        this.state = {
            users: [],
            filteredUsers: [],
            collectivites: [],
            collectiviteId: this.props.user.collectivite.id,
            fieldSearch: '',
            infos: '',
            isSuperAdmin: false
        }
    }

    componentDidMount() {
        if(this.props.user.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined) {
            this.setState({isSuperAdmin: true})
        }
        this.getUsers(this.props.user.collectivite.id)
        this.getCollectivites()
    }

    getUsers (id) {
        fetch(Routing.generate('sesile_user_userapi_userscollectivite', {id}), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({users: json, filteredUsers: json}))
            .then(() => {
                if (this.state.fieldSearch) this.handleChangeSearchUser(this.state.fieldSearch)
            })
    }

    getCollectivites () {
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({collectivites: json}))
    }

    deleteType (id) {
        fetch(Routing.generate('sesile_user_userapi_remove', {id}), { method: 'delete', credentials: 'same-origin'})
            .then(response => response.json())
            .then(() => {
                this.getUsers(this.state.collectiviteId)
                this.setState({infos: 'Enregistrement supprimé !'})
            })
    }

    handleChangeSearchUser = (fieldSearch) => {
        this.setState({fieldSearch})
        const regex = escapedValue(fieldSearch, this.state.filteredUsers, this.state.users)
        const filteredUsers = this.state.users.filter(user => regex.test(user._prenom.concat(user._nom)))
        this.setState({filteredUsers})
    }

    onSearchByCollectiviteFieldChange(collectiviteId) {
        this.setState({collectiviteId})
        this.getUsers(collectiviteId)
    }

    render() {
        const filteredUsers = this.state.filteredUsers

        const Row = filteredUsers && filteredUsers.map(filteredUser =>
            <UserRow key={filteredUser.id} User={filteredUser} deleteType={this.deleteType} />
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
                                       value={this.state.fieldSearch}
                                       onChange={(event) => this.handleChangeSearchUser(event.target.value)}
                                       placeholder="Entrez le nom de l'utilisateur..."
                                       type="text" />
                            </div>
                            {
                                (collectivitesSelect.length > 0 && this.state.isSuperAdmin) &&
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

Users.PropTypes = {
    user: PropTypes.object.isRequired
}


export default Users

const UserRow = ({User, deleteType}) => {
    return (
        <div className="cell medium-12 panel-body grid-x">
            <div className="cell medium-3">
                {User._prenom} {User._nom}
            </div>
            <div className="cell medium-3">
                {User.collectivite.nom}
            </div>
            <div className="cell medium-3">
                {User.email}
            </div>
            <div className="cell medium-3">
                <Link to={`/admin/${User.collectivite.id}/utilisateur/${User.id}`} className="button primary" >éditer</Link>
                <Link to={`/admin/${User.collectivite.id}/classeurs/${User.id}`} className="button secondary" >classeurs</Link>
                <button className="button alert" onClick={() => deleteType(User.id)}>supprimer</button>
            </div>
        </div>
    )
}

UserRow.PropTypes = {
    User: PropTypes.object.isRequired,
    deleteType: PropTypes.func.isRequired
}