import React, { Component } from 'react'
import { object, func, number, array } from 'prop-types'
import { translate } from 'react-i18next'

import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import { basicNotification } from '../_components/Notifications'
import { Cell, GridX } from '../_components/UI'

import { handleErrors } from '../_utils/Utils'

class UsersOzwillo extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        usersOzwillo: [],
        collectiviteId: parseInt(this.props.match.params.collectiviteId),
        fieldSearch: '',
        infos: '',
        message: null
    }
    componentDidMount() {
        this.fetchUsersOzwillo(this.state.collectiviteId)
    }
    fetchUsersOzwillo (id) {
        const { t, _addNotification } = this.context
        this.setState({message: t('common.loading')})
        fetch(Routing.generate('sesile_user_userapi_ozwillo', {id}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                this.setState({usersOzwillo: json, message: null})
            })
            .catch(error => {
                let message =  t('common.error_loading_list')
                if(error.status === 401) window.location.reload();
                if(error.status === 400) message = t('common.collectivite_not_provisioned_on_ozwillo')
                if(error.status === 403) message = t('admin.error.ozwillo_instance_users')
                this.setState({message})
                _addNotification(basicNotification('error', message, error.statusText))})
    }

    handleAddUser = (user) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_postuser', {id: this.state.collectiviteId}), {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: user.user_email_address,
                username: user.user_name
            }),
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(() => this.fetchUsersOzwillo(this.state.collectiviteId))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.add_user', {errorCode: error.status}),
                error.statusText)))
    }

    handleAddUsers = () => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_postusers', {id: this.state.collectiviteId}), {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(() => this.fetchUsersOzwillo(this.state.collectiviteId))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.add_user_plural', {errorCode: error.status}),
                error.statusText)))
    }

    render() {
        const { t } = this.context
        const { usersOzwillo } = this.state
        const listUser = usersOzwillo.map(userOzwillo =>
            <RowUser key={userOzwillo.user_id} UserOzwillo={userOzwillo} handleAddUser={this.handleAddUser} />)

        return (
            <AdminPage title={t('admin.user.ozwillo_title_user')}>
                <AdminContainer>
                    <AdminList
                        title={t('admin.users_list_ozwillo')}
                        listLength={listUser.length}
                        headTitles={[t('admin.user.name'), t('admin.user.label_email'), t('common.label.actions')]}
                        emptyListMessage={this.state.message}>
                        {
                            usersOzwillo.length >> 0 &&
                            <AdminListRow>
                                <Cell className="medium-auto"></Cell>
                                <Cell className="medium-auto"></Cell>
                                <Cell className="medium-auto">
                                    <button className="button hollow ozwillo" onClick={() => this.handleAddUsers()}>
                                        <img src="https://www.ozwillo.com/static/img/favicons/favicon-96x96.png" alt="Ozwillo" className="image-button" />
                                        {t('admin.user.ozwillo_provisionning_users')}
                                    </button>
                                </Cell>
                            </AdminListRow>
                        }
                            {listUser}
                    </AdminList>
                </AdminContainer>
            </AdminPage>
        )
    }
}

UsersOzwillo.PropTypes = {
    user: object.isRequired,
    match: number.isRequired
}

export default translate(['sesile'])(UsersOzwillo)

const RowUser = ({UserOzwillo, handleAddUser}, {t}) =>
    <AdminListRow>
        <Cell className="medium-auto">
            {UserOzwillo.user_name}
        </Cell>
        <Cell className="medium-auto">
            {UserOzwillo.user_email_address}
        </Cell>
        <Cell className="medium-auto">
            <GridX>
                <Cell className="medium-auto">
                    <button className="button hollow ozwillo" onClick={() => handleAddUser(UserOzwillo)}>
                        <img src="https://www.ozwillo.com/static/img/favicons/favicon-96x96.png" alt="Ozwillo" className="image-button" />
                        {t('admin.user.ozwillo_provisionning_user')}
                    </button>
                </Cell>
            </GridX>
        </Cell>
    </AdminListRow>

RowUser.PropTypes = {
    UserOzwillo: object.isRequired,
    handleAddUser: func.isRequired
}

RowUser.contextTypes = {
    t: func
}