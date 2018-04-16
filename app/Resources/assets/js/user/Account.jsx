import React, { Component } from 'react'
import {func} from 'prop-types'
import { translate } from 'react-i18next'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import AvatarForm from "./AvatarForm"
import SignatureForm from "./SignatureForm"
import {Link} from 'react-router-dom'

class Account extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props);
        this.state = {
            certificate: {},
            user: {
                _nom: '',
                _prenom: '',
                qualite: ''
            }
        }
    }

    componentDidMount() {
        this.fetchUser()
        this.fetchCertificate()
    }

    fetchUser() {
        const { t, _addNotification } = this.context
        fetch(Routing.generate("sesile_user_userapi_getcurrent"), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(user => this.setState({user}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name'), errorCode: error.status}),
                error.statusText)))
    }

    fetchCertificate() {
        fetch(Routing.generate("sesile_user_userapi_getcertificate"), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(certificate => this.setState({certificate}))
    }

    handleChangeField = (field, value) => {
        const { user } = this.state
        user[field] = value
        this.setState({user})
    }

    handleClickSave = () => {
        const { user } = this.state
        const field = {
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
        <div className="grid-x align-center">

            <div className="cell medium-12">
                <div className="grid-x grid-margin-x grid-padding-x align-top align-center grid-padding-y">
                    <div className="cell medium-12 text-center">
                        <h1>{t('common.user.title')}</h1>
                    </div>
                </div>
            </div>


            <div className="medium-12 cell">

                <div className="grid-x panel grid-padding-y">
                    <div className="cell medium-12">
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>{t('admin.user.subtitle_user')}</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x align-center-middle">

                                    { userId &&
                                        <AvatarForm
                                            user={user}
                                            styleClass={"medium-6 cell"}
                                            helpText={t('common.file_acceptation_rules', { types: '(png, jpeg, gif)', sizeMax: '5 Mo'})}/>
                                    }

                                    <div className="medium-6 cell">
                                        <div className="grid-x grid-padding-y">
                                            <span className="cell medium-6 text-bold">{t('admin.user.label_name')}</span>
                                            <div className="cell medium-6">{user._nom}</div>
                                        </div>
                                        <div className="grid-x grid-padding-y">
                                            <span className="cell medium-6 text-bold">{t('admin.user.label_firstname')}</span>
                                            <div className="cell medium-6">{user._prenom}</div>
                                        </div>
                                        <div className="grid-x grid-padding-y">
                                            <span className="cell medium-6 text-bold">{t('admin.user.label_email')}</span>
                                            <div className="cell medium-6">{user.email}</div>
                                        </div>
                                        <div className="grid-x grid-padding-y">
                                            <div className="cell medium-6"></div>
                                            <div className="cell medium-6 text-bold">
                                                <a href={ "https://" + user.ozwillo_url + "/my/profile"} target="_blank" className="button hollow ozwillo">
                                                    <img src="https://www.ozwillo.com/static/img/favicons/favicon-96x96.png" alt="Ozwillo" className="image-button" />
                                                    {t('common.user.upadate_account')}
                                                </a>
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
                                        <SignatureForm
                                            user={user}
                                            styleClass="medium-6 cell"
                                            helpText={t('common.file_acceptation_rules', { types: '(png, jpeg, gif)', sizeMax: '5 Mo'})}/>
                                    }

                                    <div className="medium-6 cell">
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            <div className="medium-8 cell">
                                                <label>
                                                    <span className="text-bold">{t('admin.user.label_quality')}</span>
                                                    <textarea name="qualite" value={user.qualite} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <div className="cell medium-12">
                                        <button className="button float-right text-uppercase hollow" onClick={() => this.handleClickSave()}>{(!userId) ? t('common.button.add_user') : t('common.button.edit_save')}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr/>

                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>{t('admin.user.subtitle_certificate')}</h3>
                            </div>
                        </div>

                        <div className="grid-x grid-margin-x grid-padding-x grid-padding-y">
                            <div className="medium-6 cell">
                                <Link className="button float-left text-uppercase hollow" to="https://www.sictiam.fr/certificat-electronique/" target="_blank">{ t('common.button.certificate_order') }</Link>
                            </div>
                            <div className="medium-6 cell text-right">
                                {
                                    this.state.certificate &&
                                    <Link className="button text-uppercase hollow" to="/utilisateur/certificat-electronique">{ t('common.button.certificate_user') }</Link>
                                }
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    )
    }
}

export default translate(['sesile'])(Account)