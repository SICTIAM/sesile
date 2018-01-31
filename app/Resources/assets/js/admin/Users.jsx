import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'
import { escapedValue } from '../_utils/Search'
import { basicNotification } from '../_components/Notifications'
import SelectCollectivite from '../_components/SelectCollectivite'
import {handleErrors} from '../_utils/Utils'
import ButtonConfirmDelete from '../_components/ButtonConfirmDelete'

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
            collectiviteId: this.props.user.collectivite.id,
            fieldSearch: '',
            infos: '',
            isSuperAdmin: false
        }
    }

    componentDidMount() {
        if(this.props.user.roles.includes("ROLE_SUPER_ADMIN")) this.setState({isSuperAdmin: true})
        this.fetchUsers(this.props.user.collectivite.id)
    }

    fetchUsers (id) {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_userscollectivite', {id}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({users: json, filteredUsers: json}))
            .then(() => {
                if (this.state.fieldSearch) this.handleChangeSearchUser(this.state.fieldSearch)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name', {count: 2}), errorCode: error.status}),
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
        const filteredUsers = this.state.users.filter(user => regex.test(user._prenom && user._prenom.concat(user._nom)))
        this.setState({filteredUsers})
    }

    onSearchByCollectiviteFieldChange = (collectiviteId) => {
        this.setState({collectiviteId})
        this.fetchUsers(collectiviteId)
    }

    render() {
        const { t } = this.context
        const filteredUsers = this.state.filteredUsers
        const Row = filteredUsers && filteredUsers.map(filteredUser =>
            <UserRow key={filteredUser.id} User={filteredUser} handleDeleteUser={this.handleDeleteUser} />
        )

        return (
            <div>
                <h4 className="text-center text-bold">{t('admin.title', {name: t('admin.user.name')})}</h4>
                <p className="text-center">{t('admin.subtitle')}</p>
                <p className="text-center">{ this.state.infos }</p>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x align-center-middle">
                            <div className="medium-auto cell">
                                <label htmlFor="circuit_name_search">{t('admin.label.which')}</label>
                                <input id="type_name_search"
                                       value={this.state.fieldSearch}
                                       onChange={(event) => this.handleChangeSearchUser(event.target.value)}
                                       placeholder="Entrez le nom de l'utilisateur..."
                                       type="text" />
                            </div>
                            {this.state.isSuperAdmin &&
                                    <div className="medium-auto cell">
                                        <SelectCollectivite currentCollectiviteId={this.state.collectiviteId} 
                                                            handleChange={this.onSearchByCollectiviteFieldChange} />
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
            <div className="cell medium-3 grid-x align-middle">
                <Link to={`/admin/${User.collectivite.id}/utilisateur/${User.id}`} className="fa fa-pencil icon-action cell medium-4 text-center" title={t('common.button.edit')} ></Link>
                <Link to={`/admin/${User.collectivite.id}/classeurs/${User.id}`} className="fa fa-th-list icon-action cell medium-4 text-center" title={t('common.classeur', {count: 2})}></Link>
                <ButtonConfirmDelete
                    id={User.id}
                    dataToggle={`delete-confirmation-update-${User.id}`}
                    onConfirm={handleDeleteUser}
                    content={t('common.confirm_deletion_item')}
                    className="cell medium-4 text-center"
                />
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