import React, { Component } from 'react'
import PropTypes, { array, number, bool, func } from 'prop-types'
import { translate } from 'react-i18next'
import { handleErrors } from '../_utils/Utils'
import RolesUser from './RolesUser'
import History from '../_utils/History'
import { basicNotification } from '../_components/Notifications'
import AvatarForm from '../user/AvatarForm'
import SignatureForm from '../user/SignatureForm'
import {Input, Switch, Select, Textarea, InputFile} from '../_components/Form'
import UserAvatar from "react-user-avatar"
import SearchCollectivite from "./SearchCollectivite"

class User extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            userId: null,
            roles: [],
            isSuperAdmin: true,
            user: {
                id: null,
                email: '',
                _nom: '',
                _prenom: '',
                qualite: '',
                cp: '',
                ville: '',
                departement: '',
                pays: '',
                enabled: false,
                roles: [],
                userrole: [],
                apiactivated: false,
                apitoken: '',
                apisecret: '',
                path: '',
                path_signature: '',
                collectivities: [],
                test: false
            }
        }
    }

    componentDidMount() {
        if (this.props.user.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined) {
            this.setState({isSuperAdmin: true})
        }
        if (this.props.match.params.userId) {
            this.setState({userId: this.props.match.params.userId})
            this.fetchUser(this.props.match.params.userId)
        }
        this.fetchRoles()
    }

    fetchUser(id) {
        const {t, _addNotification} = this.context
        fetch(Routing.generate("sesile_user_userapi_get", {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                this.setState({user: json})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name'), errorCode: error.status}),
                error.statusText)))
    }

    fetchRoles() {
        const {t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_userapi_getroles'), {credentials: 'same-origin'})
            .then(this.handleErrors)
            .then(response => response.json())
            .then(roles => this.setState({roles}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {
                    name: t('admin.role_application.name', {count: 2}),
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
                this.fetchUser(this.state.userId)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.add', {name: t('admin.user.image_avatar'), errorCode: error.status}),
                error.statusText)))
    }


    handleChangeField = (field, value) => {
        const {user} = this.state
        user[field] = value
        this.setState({user})
    }

    handleChangeRoles = (field, value) => {
        const {user} = this.state
        const newRole = user.roles
        field === "ROLE_USER" ? field = "" : field = field
        if (value === true) {
            newRole.indexOf(field) === -1 && newRole.push(field)
        }
        if (value === false) {
            const indexs = newRole.indexOf(field)
            newRole.splice(indexs, 1)
        }
        user['roles'] = newRole
        this.setState({user})
    }

    handleChangeUserRole = (key, role) => this.setState(prevState => prevState.user.userrole[key].user_roles = role)
    handleRemoveUserRole = (key) => this.setState(prevState => prevState.user.userrole.splice(key, 1))
    handleAddUserRole = (role) => this.setState(prevState => prevState.user.userrole.push({
        user_roles: role,
        user: this.state.userId
    }))
    userNomAndPrenomIsNotEmpty = () => this.state.user._nom.length > 0 && this.state.user._prenom.length > 0

    userNomAndPrenomAndImagePathIsNotEmpty = () => this.userNomAndPrenomIsNotEmpty() && this.state.user.path
    handleClickSave = () => {
        const {user} = this.state
        const {t, _addNotification} = this.context
        const id = this.state.userId
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
        }

        fetch(Routing.generate("sesile_user_userapi_updateuser", {id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(field),
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => {
                if (response.ok === true) {
                    this.fetchUser(id)
                    _addNotification(basicNotification(
                        'success',
                        t('admin.success.update', {name: t('admin.user.name')}),
                        t('admin.user.succes_update', {name: user._prenom + ' ' + user._nom})
                    ))
                    History.push(`/admin/utilisateurs/`)
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

    handleClickDelete = (id) => {
        const {_addNotification} = this.context
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
                History.push(`/admin/utilisateurs`)
            })
    }
    isCheckedRoleSelect = (role) => {
        role === "ROLE_USER" ? role = "" : role = role
        return this.state.user.roles.indexOf(role) !== -1
    }

    handleChangeUser = (user) => {
        this.setState({user})
        this.fetchUser(user.id)
    }

    render() {
        const {t} = this.context
        const {user} = this.state
        const roles = this.state.roles
        const userId = this.props.match.params.userId
        const rolesSelect = roles && roles.map((role, key) => <Switch id={role}
                                                                      key={role + key}
                                                                      className="cell medium-4"
                                                                      labelText={role}
                                                                      checked={this.isCheckedRoleSelect(role)}
                                                                      onChange={this.handleChangeRoles}
                                                                      activeText={t('common.label.yes')}
                                                                      inactiveText={t('common.label.no')}/>)
        const collectiviteList = user.collectivities.map((collectivite, key) => <li
            key={key + collectivite.id.toString()}>{collectivite.nom}</li>)

        return (
            <div className="grid-x">
                <div className="cell align-center-middle">
                    <h4 className="text-center text-bold text-uppercase">UTILISATEUR</h4>
                </div>
                <div className="admin-details medium-12 cell">
                    <div className="panel" style={{padding: "10px", borderTop: "2px solid rgb(102, 51, 153)"}}>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x">
                                    <div className="medium-6 cell">
                                        {(user.id) &&
                                        <div className="grid-x grid-padding-y">
                                            <span
                                                className="cell medium-6 text-bold">{t('admin.user.label_email')}</span>
                                            <div className="cell medium-6">{user.email}</div>
                                        </div>}
                                        <div className="grid-x grid-padding-y">
                                            <span
                                                className="cell medium-6 text-bold">{t('admin.user.label_name')}</span>
                                            <div className="cell medium-6">{user._nom}</div>
                                        </div>
                                        <div className="grid-x grid-padding-y">
                                            <span
                                                className="cell medium-6 text-bold">{t('admin.user.label_firstname')}</span>
                                            <div className="cell medium-6">{user._prenom}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="panel" style={{padding: "10px", height:"17em"}}>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>{t('admin.user.subtitle_user')}</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x" style={{height: "4em"}}>
                            {
                                user.id &&
                                <div className="cell medium-6" style={{display: "flex"}}>
                                    <div className="medium-2" style={{width: "15em"}}>
                                        <label className="medium-2 text-bold text-capitalize-first-letter"
                                               htmlFor="profil_img">photo de profil</label>
                                    </div>
                                    <div className="cell medium-4" style={{width: "15em"}}>
                                        <div style={{display: "block", overflow: "hidden"}}>
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
                                                fontSize: "100px",
                                                width: "80px",
                                                opacity: "0",
                                                filter: "alpha(opacity=0)",
                                                position: "relative",
                                                top: "-100px"
                                            }}/>
                                        </div>
                                    </div>
                                </div>
                            }
                            <div className="cell medium-6">
                                <label className="text-bold text-capitalize-first-letter">collectivités</label>
                                <div style={{height: "11em"}}>
                                    <SearchCollectivite />
                                </div>
                            </div>
                        </div>
                        <div className="grid-x grid-padding-x grid-padding-y" style={{marginLeft: "0.1em"}}>
                            <Switch id="enabled"
                                    className="cell medium-4"
                                    labelText={t('admin.user.placeholder_enable')}
                                    checked={user.enabled}
                                    onChange={this.handleChangeField}
                                    activeText={t('common.label.yes')}
                                    inactiveText={t('common.label.no')}/>
                        </div>
                    </div>
                    <div className="panel" style={{padding: "10px"}}>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>{t('admin.user.subtitle_signature')}</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-6 cell">
                                <div className="grid-x grid-padding-x">
                                    {
                                        user.id &&
                                        <SignatureForm
                                            user={user}
                                            handleChangeUser={this.handleChangeUser}
                                            styleClass="medium-12 cell text-center"
                                            tyleClass={"medium-4 cell"}
                                            helpText={t('common.file_acceptation_rules', {
                                                types: '(png, jpeg, gif)',
                                                sizeMax: '5 Mo'
                                            })}/>
                                    }
                                </div>
                            </div>
                            {(userId) &&
                            <div className="medium-6 cell">
                              <Textarea id="qualite"
                                        name="qualite"
                                        className="cell medium-6"
                                        labelText={t('admin.user.label_quality')}
                                        value={user.qualite || ''}
                                        onChange={this.handleChangeField}/>
                            </div>
                            }
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-7 cell">
                                <RolesUser roles={Object.assign([], user.userrole)}
                                           changeUserRole={this.handleChangeUserRole}
                                           removeUserRole={this.handleRemoveUserRole}
                                           addUserRole={this.handleAddUserRole}
                                           userId={this.state.userId}
                                />
                            </div>
                        </div>
                    </div>
                    <div className="panel" style={{padding: "10px"}}>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>{t('admin.user.subtitle_geo')}</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <Input id="cp"
                                           className="cell medium-6"
                                           labelText={t('admin.user.label_zip')}
                                           onChange={this.handleChangeField}
                                           value={user.cp || ''}
                                           type="text"
                                    />
                                    <Input id="ville"
                                           className="cell medium-6"
                                           labelText={t('admin.user.label_city')}
                                           onChange={this.handleChangeField}
                                           value={user.ville || ''}
                                           type="text"
                                    />
                                </div>
                                <div className="grid-x grid-padding-x grid-padding-y">
                                    <Input id="departement"
                                           className="cell medium-6"
                                           labelText={t('admin.user.label_department')}
                                           onChange={this.handleChangeField}
                                           value={user.departement || ''}
                                           type="text"
                                    />
                                    <Input id="pays"
                                           className="cell medium-6"
                                           labelText={t('admin.user.label_country')}
                                           onChange={this.handleChangeField}
                                           value={user.pays || ''}
                                           type="text"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="panel" style={{padding: "10px"}}>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>{t('admin.user.subtitle_application')}</h3>
                            </div>
                        </div>

                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x align-right">
                                    <div className="medium-6 cell">
                                        <label className="text-bold">{t('admin.user.label_role_app')}
                                            {rolesSelect}
                                        </label>
                                    </div>
                                    <Switch id="apiactivated"
                                            className="cell medium-6"
                                            labelText={t('admin.user.label_api_enable')}
                                            checked={user.apiactivated}
                                            onChange={this.handleChangeField}
                                            activeText={t('common.label.yes')}
                                            inactiveText={t('common.label.no')}/>
                                    {
                                        (user.apiactivated && userId) &&
                                        <div className="medium-6 cell" style={{marginTop: "-10em"}}>
                                            <div className="admin_search_input medium-6 cell">
                                                <label className="text-bold">{t('admin.user.label_api_key')}
                                                    <p name="apitoken"> {user.apitoken}</p>
                                                </label>
                                            </div>
                                            <div className="admin_search_input medium-6 cell">
                                                <label className="text-bold">{t('admin.user.label_api_secret')}
                                                    <p name="apisecret">{user.apisecret}</p>
                                                </label>
                                            </div>
                                        </div>
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="medium-12 cell" style={{marginBottom: "2em"}}>
                        <button className="button float-right hollow text-uppercase"
                                onClick={() => this.handleClickSave()}>{(!userId) ? t('common.button.add_user') : t('common.button.edit_save')}</button>
                    </div>
                </div>
            </div>
        )
    }
}

User.PropTypes = {
    User: PropTypes.object.isRequired,
    match: PropTypes.object.isRequired
}

export default translate(['sesile'])(User)