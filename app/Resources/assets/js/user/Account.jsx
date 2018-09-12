import React, { Component } from 'react'
import {func} from 'prop-types'
import { translate } from 'react-i18next'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import AvatarForm from "./AvatarForm"
import SignatureForm from "./SignatureForm"
import {Link} from 'react-router-dom'
import { CertificateValidity } from '../_components/CertificateExpiry'

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
                qualite: ' '
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
            // collectivite: user.collectivite.id
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
                    this.props.updateUserInfos()
                }
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name', {count: 2}), errorCode: error.status}),
                error.statusText)))
    }
    handleChangeUser = (user) => {
        this.setState({user})
        this.props.updateUserInfos()
    }
    render () {
        const { t } = this.context
        const { user } = this.state
        const userId = user.id

        if (this.state.certificate && this.state.certificate.HTTP_X_SSL_CLIENT_NOT_AFTER) {
            var now = Moment();
            var end = Moment(this.state.certificate.HTTP_X_SSL_CLIENT_NOT_AFTER);
            var certificateRemainingDays = end.diff(now, 'days');
        }
        return (
            <div className="cell medium-12">
                <div className="grid-x grid-padding-y">
                    <div className="cell medium-12 text-center text-uppercase text-bold">
                        <h2>{t('common.user.title')}</h2>
                    </div>
                </div>
                <div
                    className="grid-x grid-padding-y panel"
                    style={{borderTop: '2px solid #663399'}}>
                    <div className="cell medium-12" style={{padding: '10px'}}>
                        <div className="grid-x grid-padding-y">
                            <label className="cell medium-2 text-bold" htmlFor="nom">
                                {t('admin.user.label_name')}
                            </label>
                            <div className="cell medium-10" id="nom">
                                {user._nom}
                            </div>
                        </div>
                        <div className="grid-x grid-padding-y">
                            <label className="cell medium-2 text-bold" htmlFor="prenom">
                                {t('admin.user.label_firstname')}
                            </label>
                            <div className="cell medium-10" id="prenom">
                                {user._prenom}
                            </div>
                        </div>
                        <div className="grid-x grid-padding-y">
                            <label className="cell medium-2 text-bold" htmlFor="email">
                                {t('admin.user.label_email')}
                            </label>
                            <div className="cell medium-10" id="email">
                                {user.email}
                            </div>
                        </div>
                        <div className="grid-x grid-padding-y">
                            <div className="cell medium-12 text-right text-bold">
                                <a href={ "https://" + user.ozwillo_url + "/my/profile"} target="_blank" className="button hollow ozwillo">
                                    <img src="https://www.ozwillo.com/static/img/favicons/favicon-96x96.png" alt="Ozwillo" className="image-button" />
                                    {t('common.user.upadate_account')}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="grid-x grid-padding-y panel">
                    <div className="cell medium-12">
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3 className="text-capitalize">{t('common.infos')}</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-y align-center-middle">
                                    <SignatureForm
                                        handleChangeUser={this.handleChangeUser}
                                        user={user}
                                        styleClass="small-12 medium-12 large-12 cell"/>
                                    <AvatarForm
                                        handleChangeUser={this.handleChangeUser}
                                        user={user}
                                        styleClass={"cell small-12 medium-12 large-12"}/>
                                </div>
                                <div className="grid-x grid-padding-y">
                                    <div className="cell medium-2">
                                        <label className="text-bold">{t('admin.user.label_quality')}</label>
                                    </div>
                                    <div className="cell medium-10">
                                    <textarea
                                        name="qualite"
                                        value={this.state.user.qualite || " "}
                                        onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} />
                                    </div>
                                </div>
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <div className="cell small-12 medium-6 large-6">
                                    <span className="help-text" style={{fontSize: '0.65em'}}>
                                        {t('common.file_acceptation_rules',
                                            {types: '(png, jpeg, gif)', sizeMax: '5 Mo'})}
                                    </span>
                                    </div>
                                    <div className="cell small-12 medium-6 large-6">
                                        <button
                                            className="button float-right text-uppercase hollow"
                                            onClick={() => this.handleClickSave()}>
                                            {(!userId) ? t('common.button.add_user') : t('common.button.edit_save')}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="grid-x grid-padding-y panel">
                    <div className="cell medium-12">
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>{t('admin.user.subtitle_certificate')}</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x grid-padding-y">
                            <div className="medium-6 cell">
                                <CertificateValidity
                                    certificate={this.state.certificate}
                                    certificateRemainingDays={certificateRemainingDays}
                                    CertifRemain={t('common.user.certificate_validity', {count: certificateRemainingDays | 1})}
                                    NoCertif={t('common.no_certificat')}>
                                </CertificateValidity>
                                <Link
                                    className="button float-left text-uppercase hollow"
                                    to="https://www.sictiam.fr/certificat-electronique/"
                                    target="_blank">
                                    {t('common.button.certificate_order')}
                                </Link>
                            </div>
                            <div className="medium-6 cell text-right">
                                {this.state.certificate &&
                                <Link
                                    className="button text-uppercase hollow"
                                    to="/utilisateur/certificat-electronique">
                                    {t('common.button.certificate_user')}
                                </Link>}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )}
}

export default translate(['sesile'])(Account)