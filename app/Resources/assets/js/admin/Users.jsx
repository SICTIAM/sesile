import React, { Component } from 'react'
import PropTypes, { object, func } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'
import { escapedValue } from '../_utils/Search'
import { basicNotification } from '../_components/Notifications'

class Users extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

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

    handleErrors(response) {
        if (response.ok) {
            return response
        }
        throw response
    }

    componentDidMount() {
        if(this.props.user.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined) {
            this.setState({isSuperAdmin: true})
        }
        this.fetchUsers(this.props.user.collectivite.id)
        this.fetchCollectivites()
    }

    fetchUsers (id) {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_userscollectivite', {id}), { credentials: 'same-origin'})
            .then(this.handleErrors)
            .then(response => response.json())
            .then(json => this.setState({users: json, filteredUsers: json}))
            .then(() => {
                if (this.state.fieldSearch) this.handleChangeSearchUser(this.state.fieldSearch)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extrayable_list', {name: t('admin.user.name', {count: 2}), errorCode: error.status}),
                error.statusText)))
    }

    fetchCollectivites () {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), { credentials: 'same-origin'})
            .then(this.handleErrors)
            .then(response => response.json())
            .then(json => this.setState({collectivites: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extrayable_list', {name: t('admin.collectivite.name', {count: 2}), errorCode: error.status}),
                error.statusText)))
    }

    handleDeleteUser = (id) => {
        const { t, _addNotification } = this.context
        const id_collectivite = this.state.collectiviteId
        fetch(Routing.generate("sesile_user_userapi_remove", {id}), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
            .then(response => {
                if(response.ok === true) {
                    _addNotification(basicNotification(
                        'success',
                        t('admin.success.delete', {name: t('admin.user.name')}),
                        t('admin.success.delete', {name: t('admin.user.name')})
                    ))
                    this.fetchUsers(id_collectivite)
                }
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name: t('admin.user.name'), errorCode: error.status}),
                error.statusText)
            ))
    }

    handleChangeSearchUser = (fieldSearch) => {
        this.setState({fieldSearch})
        const regex = escapedValue(fieldSearch, this.state.filteredUsers, this.state.users)
        const filteredUsers = this.state.users.filter(user => regex.test(user._prenom.concat(user._nom)))
        this.setState({filteredUsers})
    }

    onSearchByCollectiviteFieldChange(collectiviteId) {
        this.setState({collectiviteId})
        this.fetchUsers(collectiviteId)
    }

    render() {
        const { t } = this.context
        const filteredUsers = this.state.filteredUsers
        const Row = filteredUsers && filteredUsers.map(filteredUser =>
            <UserRow key={filteredUser.id} User={filteredUser} handleDeleteUser={this.handleDeleteUser} />
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
                <h4 className="text-center text-bold">{t('admin.title', {name: t('admin.user.name')})}</h4>
                <p className="text-center">{t('admin.subtitle')}</p>
                <p className="text-center">{ this.state.infos }</p>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x align-center-middle">
                            <div className="medium-6 cell">
                                <label htmlFor="circuit_name_search">{t('admin.label.which')}</label>
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
                                                <label htmlFor="collectivite_name_search">{t('admin.label.which_collectivite')}</label>
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
                                <div className="cell medium-12">{t('admin.users_list')}</div>
                            </div>
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-3">
                                    <Link to={`/admin/${this.state.collectiviteId}/utilisateur`} className="button primary" >{t('common.button.add_user')}</Link>
                                </div>
                            </div>
                            {
                                (Row.length > 0) ? Row :
                                    <div className="cell medium-12 panel-body">
                                        <div className="text-center">
                                            {t('common.no_results', {name: t('admin.user.name')})}
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
    user: object.isRequired
}

export default translate(['sesile'])(Users)

const UserRow = ({ User, handleDeleteUser}, {t}) => {
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
                <Link to={`/admin/${User.collectivite.id}/utilisateur/${User.id}`} className="button primary" >{t('common.button.edit')}</Link>
                <Link to={`/admin/${User.collectivite.id}/classeurs/${User.id}`} className="button secondary" >{t('common.classeur', {count: 2})}</Link>
                <button className="button alert" onClick={() => handleDeleteUser(User.id)}>{t('common.button.delete')}</button>
            </div>
        </div>
    )
}

UserRow.PropTypes = {
    User: object.isRequired,
    handleDeleteUser: func.isRequired
}

UserRow.contextTypes = {
    t: func
}