import React, { Component } from 'react'
import {func, object} from 'prop-types'
import { translate } from 'react-i18next'
import Moment from 'moment'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import SignatureForm from "./SignatureForm"
import { CertificateValidity } from '../_components/CertificateExpiry'
import RolesUser from "../admin/RolesUser"
import UserAvatar from "react-user-avatar"

class Account extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func,
        user: object,
        updateUser: func,
    }

    state = {
        certificate: null,
        user: {
            _nom: '',
            _prenom: '',
            qualite: ' ',
            userrole: []
        }
    }

    componentDidMount() {
        this.setState({user: this.context.user})
        this.state.certificate === null && this.fetchCertificate()
    }

    fetchCertificate() {
        fetch(Routing.generate("sesile_user_userapi_getcertificate"), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(certificate => this.setState({certificate}))
    }

    handleChangeField = (field, value) => {
        const {user} = this.state
        user[field] = value
        this.setState({user})
    }

    handleClickSave = () => {
        const {user} = this.state
        const id = user.id
        user.userrole.map((role, key) => this.setState(prevState => prevState.user.userrole[key].user = id))

        const field = {
            qualite: user.qualite,
            cp: user.cp,
            ville: user.ville,
            departement: user.departement,
            pays: user.pays,
            enabled: user.enabled,
            apiactivated: user.apiactivated,
            roles: user.roles,
            userrole: user.userrole
            // collectivite: user.collectivite.id
        }
        this.putUser(field, this.state.user.id)
    }

    putUser = (user, id) => {
        const {t, _addNotification} = this.context
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
                if (response.ok === true) {
                    _addNotification(basicNotification(
                        'success',
                        t('admin.success.update', {name: t('admin.user.name')}),
                        t('admin.success.update', {name: t('admin.user.name')})
                    ))
                    this.context.updateUser()
                }
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {
                    name: t('admin.user.name', {count: 2}),
                    errorCode: error.status
                }),
                error.statusText)))
    }
    putFile = (image) => {
        const {t, _addNotification} = this.context
        let formData = new FormData()
        formData.append('path', image)

        fetch(Routing.generate("sesile_user_userapi_uploadavatar", {id: this.state.user.id}), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(user => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.update', {name: t('admin.user.image_avatar')})
                ))
                this.handleChangeUser(user)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.add', {name: t('admin.user.image_avatar'), errorCode: error.status}),
                error.statusText)))
    }
    handleChangeUser = (user) => {
        this.setState({user})
        this.context.updateUser()
    }
    handleChangeUserRole = (key, role) => this.setState(prevState => prevState.user.userrole[key].user_roles = role)
    handleRemoveUserRole = (key) => this.setState(prevState => prevState.user.userrole.splice(key, 1))
    handleAddUserRole = (role) => this.setState(prevState => prevState.user.userrole.push({
        user_roles: role,
        user: this.state.user.id
    }))
    userNomAndPrenomIsNotEmpty = () => this.state.user._nom.length > 0 && this.state.user._prenom.length > 0
    userNomAndPrenomAndImagePathIsNotEmpty = () => this.userNomAndPrenomIsNotEmpty() && this.state.user.path

    render() {
        const {t} = this.context
        const {user} = this.state
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
                                <a href={user.ozwillo_url + "/my/profile"} target="_blank"
                                   className="button hollow ozwillo">
                                    <img src="https://www.ozwillo.com/static/img/favicons/favicon-96x96.png"
                                         alt="Ozwillo" className="image-button"/>
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
                                        styleClass="small-6 medium-6 large-6 cell"/>
                                    <div className="small-6 medium-6 large-6 cell">
                                        <div className="grid-x grid-padding-x align-middle">
                                            <label className="cell medium-2 text-bold text-capitalize-first-letter"
                                                   htmlFor="profil_img">
                                                {t('admin.user.image_avatar')}
                                            </label>
                                            <div className="cell medium-4"
                                                 style={{display: "block", overflow: "hidden", height: "5em"}}>
                                                {this.userNomAndPrenomAndImagePathIsNotEmpty() ?
                                                    <UserAvatar
                                                        id="profil_img"
                                                        size="70"
                                                        name={`${this.state.user._prenom.charAt(0)}${this.state.user._nom.charAt(0)} `}
                                                        src={"/uploads/avatars/" + this.state.user.path}
                                                        className=" float-center"/> :
                                                    this.userNomAndPrenomIsNotEmpty() &&
                                                    <UserAvatar
                                                        id="profil_img"
                                                        size="70"
                                                        name={`${this.state.user._prenom.charAt(0)}${this.state.user._nom.charAt(0)} `}
                                                        className="txt-avatar"/>}
                                                <input type="file" accept="image/png,image/jpeg"
                                                       onChange={e => this.putFile(e.target.files[0])}
                                                       id="upload_input" name="upload" style={{
                                                    fontSize: "60px",
                                                    width: "70px",
                                                    opacity: "0",
                                                    filter: "alpha(opacity=0)",
                                                    position: "relative",
                                                    top: "-73px"
                                                }}/>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="cell small-12 medium-12 large-12">
                                    <span className="help-text align-left" style={{fontSize: '0.65em'}}>
                                        {t('common.file_acceptation_rules',
                                            {types: '(png, jpeg, gif)', sizeMax: '5 Mo'})}
                                    </span>
                                    </div>
                                </div>
                                <div className="grid-x grid-padding-y">
                                    <div className="cell medium-6">
                                        <div className="cell medium-2">
                                            <label className="text-bold">{t('admin.user.label_quality')}</label>
                                        </div>
                                        <div className="cell medium-10" style={{paddingRight: "1em"}}>
                                            <textarea
                                                name="qualite"
                                                value={this.state.user.qualite || " "}
                                                onChange={(e) => this.handleChangeField(e.target.name, e.target.value)}
                                            />
                                        </div>
                                    </div>
                                    <div className="medium-6 cell">
                                        <RolesUser roles={Object.assign([], user.userrole)}
                                                   changeUserRole={this.handleChangeUserRole}
                                                   removeUserRole={this.handleRemoveUserRole}
                                                   addUserRole={this.handleAddUserRole}
                                                   userId={user.id}
                                        />
                                    </div>
                                </div>
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <div className="cell small-12 medium-12 large-12">
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
                                    NoCertif={t('common.no_certificat')}/>
                            </div>
                            <div className="cell medium-6 small-6 text-right text-bold">
                                <a href={"https://www.sictiam.fr/certificat-electronique/"}
                                   style={{textDecoration: "underline"}}>
                                    {t('common.button.certificate_order')}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(Account)