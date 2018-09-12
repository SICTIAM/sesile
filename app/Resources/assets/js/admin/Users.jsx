import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'

import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import ButtonConfirmDelete from '../_components/ButtonConfirmDelete'
import { Input } from '../_components/Form'
import { basicNotification } from '../_components/Notifications'
import { Cell, GridX } from '../_components/UI'
import SelectCollectivite from '../_components/SelectCollectivite'

import { escapedValue } from '../_utils/Search'
import { handleErrors } from '../_utils/Utils'

class Users extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        users: [],
        filteredUsers: [],
        collectiviteId: this.props.user.current_org_id,
        fieldSearch: '',
        infos: '',
        isSuperAdmin: false
    }
    componentDidMount() {
        if(this.props.user.roles.includes("ROLE_SUPER_ADMIN")) this.setState({isSuperAdmin: true})
        this.fetchUsers(this.props.user.current_org_id)
    }
    fetchUsers (id) {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_userscollectivite', {id}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({users: json, filteredUsers: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name', {count: 2}), errorCode: error.status}),
                error.statusText)))
    }
    handleDeleteUser = (id) => {
        const { _addNotification } = this.context
        const id_collectivite = this.state.collectiviteId
        fetch(Routing.generate("sesile_user_userapi_remove", {id}), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(response => {
            _addNotification(
                basicNotification(
                    response.status,
                    response.message))
            this.fetchUsers(id_collectivite)
        })
    }
    handleChangeSearchUser = (key, fieldSearch) => {
        this.setState({fieldSearch})
        const regex = escapedValue(fieldSearch, this.state.filteredUsers, this.state.users)
        const filteredUsers = this.state.users.filter(user => regex.test(user._prenom && user._prenom.concat(user._nom)))
        this.setState({filteredUsers})
    }
    onSearchByCollectiviteFieldChange = (collectiviteId) => {
        this.setState({collectiviteId, fieldSearch: ""})
        this.fetchUsers(collectiviteId)
    }
    render() {
        const { t } = this.context
        const listUser = this.state.filteredUsers.map(filteredUser =>
            <RowUser key={filteredUser.id} User={filteredUser} handleDeleteUser={this.handleDeleteUser} collectiviteId={this.state.collectiviteId}/>)

        return (
            <AdminPage
                title={t('admin.title', {name: t('admin.user.name')})}
                subtitle={t('admin.subtitle')}>
                <AdminContainer>
                    <Cell className="medium-6">
                        <GridX className="grid-padding-x align-center-middle">
                            <Input
                                className="cell medium-auto"
                                labelText={t('admin.label.which')}
                                value={this.state.fieldSearch}
                                onChange={this.handleChangeSearchUser}
                                placeholder={t('admin.user.search_by_first_name_and_name')}
                                type="text"/>
                            {this.state.isSuperAdmin &&
                                <Cell className="medium-auto">
                                    <SelectCollectivite currentCollectiviteId={this.state.collectiviteId} 
                                                        handleChange={this.onSearchByCollectiviteFieldChange} />
                                </Cell>
                            }
                        </GridX>
                    </Cell>
                    <AdminList
                        title={t('admin.users_list')}
                        listLength={listUser.length}
                        labelButton={t('common.button.add_user')}
                        headTitles={[t('admin.user.first_name_and_name'), t('admin.user.label_email'), t('common.label.actions')]}
                        headGrid={['medium-auto', 'medium-auto', 'medium-2']}
                        emptyListMessage={t('common.no_results', {name: t('admin.user.name')})}>
                            {listUser}
                    </AdminList>
                </AdminContainer>
            </AdminPage>
        )
    }

}

Users.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Users)

const RowUser = ({User, handleDeleteUser, collectiviteId}, {t}) =>
    <AdminListRow>
        <Cell className="medium-auto">
            {User._prenom} {User._nom}
        </Cell>
        <Cell className="medium-auto">
            {User.email}
        </Cell>
        <Cell className="medium-2">
            <GridX>
                <Cell className="medium-auto">
                    <Link 
                        to={`/admin/${collectiviteId}/utilisateur/${User.id}`}
                        className="fa fa-pencil icon-action" 
                        title={t('common.button.edit')}/>
                </Cell>
                <Cell className="medium-auto">
                    <Link 
                        to={`/admin/${collectiviteId}/classeurs/${User.id}`}
                        className="fa fa-th-list icon-action" 
                        title={t('common.classeur', {count: 2})}/>
                </Cell>
                {/*<Cell className="medium-auto">*/}
                    {/*<ButtonConfirmDelete*/}
                        {/*id={User.id}*/}
                        {/*dataToggle={`delete-confirmation-update-${User.id}`}*/}
                        {/*onConfirm={handleDeleteUser}*/}
                        {/*content={t('common.confirm_deletion_item')}/>*/}
                {/*</Cell>*/}
            </GridX>
        </Cell>
    </AdminListRow>

RowUser.PropTypes = {
    User: object.isRequired,
    handleDeleteUser: func.isRequired
}

RowUser.contextTypes = {
    t: func
}