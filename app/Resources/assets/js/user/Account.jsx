import React, { Component } from 'react'
import {func} from 'prop-types'
import { translate } from 'react-i18next'
import { basicNotification } from '../_components/Notifications'
import AvatarForm from "./AvatarForm"
import SignatureForm from "./SignatureForm"

class Account extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props);
        this.state = {
            user: {
                _nom: '',
                _prenom: '',
                qualite: ''
            }
        }
    }

    componentDidMount() {
        this.fetchUser()
    }

    fetchUser() {
        fetch(Routing.generate("sesile_user_userapi_getcurrent"), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({user: json})
            })
    }

    handleChangeField = (field, value) => {
        const { user } = this.state
        user[field] = value
        this.setState({user})
    }

    handleClickSave = () => {
        const { user } = this.state
        const field = {
            _nom: user._nom,
            _prenom: user._prenom,
            qualite: user.qualite,
            cp: user.cp,
            ville: user.ville,
            departement: user.departement,
            pays: user.pays,
            enabled: user.enabled,
            apiactivated: user.apiactivated,
            roles: user.roles,
            collectivite: user.collectivite.id
        }

        this.putUser(field, this.state.user.id)
    }

    putUser = (user, id) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate("sesile_user_userapi_updateuser", {id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(user),
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
            .then(response => {
                if(response.ok === true) {
                    _addNotification(basicNotification(
                        'success',
                        t('admin.success.update', {name: t('admin.user.name')}),
                        t('admin.success.update', {name: t('admin.user.name')})
                    ))
                }
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name', {count: 2}), errorCode: error.status}),
                error.statusText)))
    }

    render () {
        const { t } = this.context
        const { user } = this.state
        const userId = user.id

        return (
        <div className="grid-x">
            <div className="admin-details medium-12 cell">
                <div className="grid-x admin-head-details">
                    {user._prenom + " " + user._nom + " - " + user.email}
                    <i className="fi-pencil small medium-6 cell"></i>
                </div>
                <div className="admin-content-details">

                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <h3>{t('admin.user.subtitle_user')}</h3>
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <div className="grid-x grid-padding-x align-center-middle">

                                { userId &&
                                    <AvatarForm user={user} styleClass={"medium-4 cell"} />
                                }

                                <div className="medium-8 cell">
                                    <div className="grid-x grid-padding-x grid-padding-y">
                                        <div className="medium-6 cell">
                                            <label>{t('admin.user.label_firstname')}
                                                <input name="_prenom" value={user._prenom} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={t('admin.user.placeholder_firstname')} />
                                            </label>
                                        </div>
                                        <div className="medium-6 cell">
                                            <label>{t('admin.user.label_name')}
                                                <input name="_nom" value={user._nom} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={t('admin.user.placeholder_name')} />
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <hr/>
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <h3>{t('admin.user.subtitle_signature')}</h3>
                        </div>
                    </div>
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <div className="grid-x grid-padding-x align-center-middle">

                                {
                                    userId &&
                                    <SignatureForm user={user} styleClass={"medium-4 cell"} />
                                }

                                <div className="medium-8 cell">
                                    <div className="grid-x grid-padding-x grid-padding-y">
                                        <div className="medium-8 cell">
                                            <label>{t('admin.user.label_quality')}
                                                <textarea name="qualite" value={user.qualite} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} />
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <button className="button float-right text-uppercase" onClick={() => this.handleClickSave()}>{(!userId) ? t('common.button.add_user') : t('common.button.edit_save')}</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    )
    }
}

export default translate(['sesile'])(Account)