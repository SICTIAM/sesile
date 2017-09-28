import React, { Component } from 'react'
import PropTypes, { number, func } from 'prop-types'
import { translate } from 'react-i18next'

class RolesUser extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
        this.state = {
            user: "",
            userrole: {
                id: '',
                user_roles: '',
                user: ''
            }
        }
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
            .then(response => {if(response.ok === true) console.log("save !") })
    }

    createUserRole = (key) => {
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
            .then(response => {if(response.ok === true) console.log("nouveau role !")})
            .then(() => {
                this.fetchUserRoles(this.state.user)
            })
    }

    handleClickDeleteRole = (key, id) => {
        const newUserRole = this.state.userrole
        newUserRole.splice(key,1)
        this.setState({userrole: newUserRole})
        if (id !== undefined) {
            fetch(Routing.generate("sesile_user_userroleapi_remove", {id}), {
                method: 'DELETE',
                credentials: 'same-origin'
            })
                .then(response => {if(response.ok === true) console.log("Role supprimé")})
        }

    }

    handleClickAddRole() {
        const newRole = {id: null, user_roles: '', user: this.state.user}
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