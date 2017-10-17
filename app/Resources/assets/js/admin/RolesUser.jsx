import React, { Component } from 'react'
import { number, func } from 'prop-types'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'

class RolesUser extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            userrole: {
                id: '',
                user_roles: '',
                user: ''
            }
        }
    }

    handleErrors(response) {
        if (response.ok) {
            return response
        }
        throw response
    }

    componentDidMount() {
        this.setState({user: this.props.user})
        this.fetchUserRoles(this.props.user)
    }

    fetchUserRoles (id) {
        fetch(Routing.generate('sesile_user_userroleapi_getbyuser', {id}), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(userrole => this.setState({userrole}))
    }

    handleChangeRole = (key, value) => {
        const { userrole, user } = this.state
        userrole[key].user_roles = value
        userrole[key].user = user
        this.setState({userrole})
    }

    putUserRole = (key, id) => {
        const { t, _addNotification } = this.context
        const userrole = this.state.userrole[key]
        fetch(Routing.generate("sesile_user_userroleapi_updateuserrole", {id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userrole),
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
            .then(response => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.update', {name: t('admin.roles_user.name'), errorCode: response.status}),
                    response.statusText))
            })
            .catch(error => _addNotification(basicNotification('error',
                t('admin.error.not_updatable',
                    {name: t('admin.roles_user.name'), errorCode: error.status}),
                error.statusText)))
    }

    createUserRole = (key) => {
        const { t, _addNotification } = this.context
        let userrole = this.state.userrole[key]
        fetch(Routing.generate("sesile_user_userroleapi_postuserrole"), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(userrole),
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
            .then(response => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.add', {name: t('admin.roles_user.name'), errorCode: response.status}),
                    response.statusText))
                this.fetchUserRoles(this.props.user)
            })
            .catch(error => {
                _addNotification(basicNotification('error',
                    t('admin.error.not_addable',
                        {name: t('admin.roles_user.name'), errorCode: error.status}),
                    error.statusText))
            })
    }

    handleClickDeleteRole = (key, id) => {
        const { t, _addNotification } = this.context
        const newUserRole = this.state.userrole
        newUserRole.splice(key,1)
        this.setState({userrole: newUserRole})
        if (id) {
            fetch(Routing.generate("sesile_user_userroleapi_remove", {id}), {
                method: 'DELETE',
                credentials: 'same-origin'
            })
                .then(this.handleErrors)
                .then(response => {
                    _addNotification(basicNotification(
                        'success',
                        t('admin.success.delete', {name: t('admin.roles_user.name'), errorCode: response.status}),
                        response.statusText))
                    this.fetchUserRoles(this.props.user)
                })
                .catch(error => _addNotification(basicNotification('error',
                    t('admin.error.not_removable',
                        {name: t('admin.roles_user.name'), errorCode: error.status}),
                    error.statusText)))
        }

    }

    handleClickAddRole() {
        const newRole = {id: null, user_roles: '', user: this.props.user}
        const newUserRole = this.state.userrole
        newUserRole.push(newRole)

        this.setState({userrole: newUserRole})
    }


    render () {
        const { t } = this.context
        const { userrole } = this.state

        return (
            <div className="medium-12 cell">
                <label>{t('admin.user.label_role_user')}
                    { userrole && userrole.length > 0 && userrole.map((role, key) =>
                        (
                            <div key={key} className="grid-x">
                                <div className="medium-6">
                                    <input name={`userrole[${key}].user_roles`} value={role.user_roles} onChange={(e) => this.handleChangeRole(key, e.target.value)} placeholder={t('admin.user.placeholder_role_user')} />
                                </div>
                                <div className="medium-3">
                                    {
                                        role.id
                                            ? <button className="secondary button float-right text-uppercase" onClick={() => this.putUserRole(key, role.id)}>{t('common.button.update')}</button>
                                            : <button className="secondary button float-right text-uppercase" onClick={() => this.createUserRole(key)}>{t('common.button.save')}</button>
                                    }
                                </div>
                                <div className="medium-3">
                                    <button className="alert button float-right text-uppercase" onClick={() => this.handleClickDeleteRole(key, role.id)}>{t('common.button.delete')}</button>
                                </div>
                            </div>
                        )
                    )}
                </label>
                <div className="grid-x">
                    <div className="medium-9">
                    </div>
                    <div className="medium-3">
                        <button className="primary button float-right text-uppercase" onClick={() => this.handleClickAddRole()}>{t('common.button.add')}</button>
                    </div>
                </div>
            </div>
        )
    }
}

RolesUser.PropTypes = {
    user: number.isRequired
}

export default translate(['sesile'])(RolesUser)