import React, { Component } from 'react'
import PropTypes, { array, number, bool, func } from 'prop-types'
import { translate } from 'react-i18next'
import UserAvatar from 'react-user-avatar'
import RolesUser from './RolesUser'
import History from '../_utils/History'
import { basicNotification } from '../_components/Notifications'

class User extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            userId: null,
            collectivites: [],
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
                collectivite: this.props.match.params.collectiviteId,
                userrole: [],
                apiactivated: false,
                apitoken: '',
                apisecret: '',
                path:'',
                path_signature: ''
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
        this.fetchCollectivites()
        if(this.props.user.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined) {
            this.setState({isSuperAdmin: true})
        }
        if (this.props.match.params.userId) {
            this.setState({userId: this.props.match.params.userId})
            this.fetchUser(this.props.match.params.userId)
        }
        this.fetchRoles()
    }

    fetchUser(id) {
        const { t, _addNotification } = this.context
        fetch(Routing.generate("sesile_user_userapi_get", {id}), {credentials: 'same-origin'})
            .then(this.handleErrors)
            .then(response => response.json())
            .then(json => {
                json.collectivite = json.collectivite.id
                this.setState({user: json})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extrayable_list', {name: t('admin.user.name'), errorCode: error.status}),
                error.statusText)))
    }

    fetchCollectivites () {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), { credentials: 'same-origin'})
            .then(this.handleErrors)
            .then(response => response.json())
            .then(collectivites => this.setState({collectivites}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extrayable_list', {name: t('admin.collectivite.name'), errorCode: error.status}),
                error.statusText)))
    }

    fetchRoles () {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_getroles'), { credentials: 'same-origin'})
            .then(this.handleErrors)
            .then(response => response.json())
            .then(roles => this.setState({roles}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extrayable_list', {name: t('admin.role_application.name', {count: 2}), errorCode: error.status}),
                error.statusText)))
    }

    handleChangeField = (field, value) => {
        const { user } = this.state
        user[field] = value
        this.setState({user})
    }

    handleChangeRoles = (options) => {
        const { user } = this.state
        const newRoles = [...options].filter(o => o.selected).map(o => o.value)

        user['roles'] = newRoles
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
            collectivite: user.collectivite
        }

        if (this.state.userId) {
            this.putUser(field, this.state.userId)
        } else {
            field['email'] = user.email
            this.createUser(field)
        }
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
                t('admin.error.not_extrayable_list', {name: t('admin.user.name', {count: 2}), errorCode: error.status}),
                error.statusText)))
    }

    createUser = (user) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate("sesile_user_userapi_postuser"), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(user),
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
            .then(response => response.json())
            .then(response => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.error.not_addable', {name: t('admin.user.name'), errorCode: error.status}),
                    error.statusText))
                History.push(`/admin/${response.collectivite.id}/utilisateur/${response.id}`)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_addable', {name: t('admin.user.name'), errorCode: error.status}),
                error.statusText)))

    }

    putFile = (image, userId) => {
        const { t, _addNotification } = this.context
        let formData  = new FormData()
        formData.append('path', image)

        fetch(Routing.generate("sesile_user_userapi_uploadavatar", {id: userId}), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.succes.update', {name: t('admin.user.image_avatar')}),
                    t('admin.succes.update', {name: t('admin.user.image_avatar')})
                ))
                this.fetchUser(this.state.user.id)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_addable', {name: t('admin.user.image_avatar'), errorCode: error.status}),
                error.statusText)))
    }

    deleteFile = (userId) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_deleteavatar', {id: userId}), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.succes.delete', {name: t('admin.user.image_avatar')}),
                    t('admin.succes.delete', {name: t('admin.user.image_avatar')})
                ))
                this.fetchUser(this.state.user.id)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name: t('admin.user.image_avatar'), errorCode: error.status}),
                error.statusText)))

    }

    putFileSignature = (image, userId) => {
        const { t, _addNotification } = this.context
        let formData  = new FormData()
        formData.append('signatures', image)

        fetch(Routing.generate("sesile_user_userapi_uploadsignature", {id: userId}), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.succes.add', {name: t('admin.user.image_signature')}),
                    t('admin.succes.add', {name: t('admin.user.image_signature')})
                ))
                this.fetchUser(this.state.user.id)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_addable', {name: t('admin.user.image_signature'), errorCode: error.status}),
                error.statusText)))
    }

    deleteFileSignature = (userId) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_deletesignature', {id: userId}), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(this.handleErrors)
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.succes.delete', {name: t('admin.user.image_signature')}),
                    t('admin.succes.delete', {name: t('admin.user.image_signature')})
                ))
                this.fetchUser(this.state.user.id)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name: t('admin.user.image_signature'), errorCode: error.status}),
                error.statusText)))

    }

    handleClickDelete = (id) => {
        fetch(Routing.generate("sesile_user_userapi_remove", {id}), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
            .then(response => {
                if(response.ok === true) {
                    History.push(`/admin/utilisateurs`)
                }
            })
    }



    render() {
        const { t } = this.context
        const {user,collectivites}  = this.state
        const roles = this.state.roles
        const userId = this.props.match.params.userId

        const rolesSelect = roles && roles.map((role,key) =>
            {
                return <option value={role} key={key}>{role}</option>
            }
        )

        return (
            <div className="grid-x">
                <div className="admin-details medium-12 cell">
                    <div className="grid-x admin-head-details">
                        {user._prenom}&nbsp;{user._nom} - {user.email}
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
                                    {
                                        (userId) &&
                                        <div className="medium-2 cell">
                                            {
                                                user.path ?
                                                    <UserAvatar size="100" name="user" src={"/uploads/avatars/" + user.path} />
                                                    : <UserAvatar size="100" name="user" className="txt-avatar" />

                                            }
                                            <input type="file" name="file" onChange={(e) => this.putFile(e.target.files[0], user.id)} />
                                            { user.path && <button className="button alert text-uppercase" onClick={() => this.deleteFile(user.id)}>{t('common.button.delete')}</button>}
                                        </div>
                                    }

                                    <div className="medium-10 cell">
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            {
                                                (!userId) &&
                                                <div className="medium-12 cell">
                                                    <label>{t('admin.user.label_email')}
                                                        <input name="email" value={user.email}
                                                               onChange={(e) => this.handleChangeField(e.target.name, e.target.value)}
                                                               placeholder={t('admin.user.placeholder_email')} />
                                                    </label>
                                                </div>
                                            }
                                        </div>
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
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            <CollectivitesMap collectivites={collectivites} collectiviteId={user.collectivite} isSuperAdmin={this.state.isSuperAdmin} handleChangeField={this.handleChangeField} />
                                            <div className="medium-6 cell">
                                                <label>
                                                    {t('admin.user.placeholder_enable')}
                                                    <input type="checkbox" name="enabled" checked={user.enabled || false} onChange={(e) => this.handleChangeField(e.target.name, e.target.checked)} />
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
                                        (userId) &&
                                        <div className="medium-4 cell">
                                            {
                                                user.path_signature &&
                                                <div className="grid-x grid-padding-x align-center-middle">
                                                    <img className="medium-4 cell" src={"/uploads/signatures/" + user.path_signature} />
                                                </div>
                                            }
                                            <input type="file" onChange={(e) => this.putFileSignature(e.target.files[0], user.id)} />
                                            { user.path_signature && <button className="button alert text-uppercase" onClick={() => this.deleteFileSignature(user.id)}>{t('common.button.delete')}</button>}
                                        </div>
                                    }

                                    <div className="medium-8 cell">
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            <div className="medium-8 cell">
                                                <label>{t('admin.user.label_quality')}
                                                    <textarea name="qualite" value={user.qualite || ''} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} />
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
                                <h3>{t('admin.user.subtitle_geo')}</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <div className="medium-6 cell">
                                        <label>{t('admin.user.label_zip')}
                                            <input name="cp" value={user.cp || ''} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={t('admin.user.placeholder_zip')} />
                                        </label>
                                    </div>
                                    <div className="medium-6 cell">
                                        <label>{t('admin.user.label_city')}
                                            <input name="ville" value={user.ville || ''} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={t('admin.user.placeholder_zip')} />
                                        </label>
                                    </div>
                                </div>
                                <div className="grid-x grid-padding-x grid-padding-y">
                                    <div className="medium-6 cell">
                                        <label>{t('admin.user.label_department')}
                                            <input name="departement" value={user.departement || ''} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={t('admin.user.placeholder_department')} />
                                        </label>
                                    </div>
                                    <div className="medium-6 cell">
                                        <label>{t('admin.user.label_country')}
                                            <input name="pays" value={user.pays || ''} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={t('admin.user.placeholder_country')} />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr/>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>{t('admin.user.subtitle_application')}</h3>
                            </div>
                        </div>

                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <div className="medium-6 cell">
                                        <label>{t('admin.user.label_role_app')}
                                            <select name="roles" value={this.state.user.roles || []} onChange={(e) => this.handleChangeRoles(e.target.options)} multiple>
                                                {rolesSelect}
                                            </select>
                                        </label>
                                    </div>
                                    <div className="medium-6 cell">
                                        <label>{t('admin.user.label_api_enable')}
                                            <input type="checkbox" name="apiactivated" checked={user.apiactivated || false} onChange={(e) => this.handleChangeField(e.target.name, e.target.checked)} />
                                        </label>
                                    </div>
                                </div>
                                {
                                    (userId) &&
                                    <div className="grid-x grid-padding-x grid-padding-y">
                                        <div className="medium-6 cell">
                                            <label>{t('admin.user.label_api_key')}
                                                <input name="apitoken" value={user.apitoken} />
                                            </label>
                                        </div>
                                        <div className="medium-6 cell">
                                            <label>{t('admin.user.label_api_secret')}
                                                <input name="apisecret" value={user.apisecret} />
                                            </label>
                                        </div>
                                    </div>
                                }

                            </div>
                            <div className="medium-12 cell">
                                <button className="button float-right text-uppercase" onClick={() => this.handleClickSave()}>{(!userId) ? t('common.button.add_user') : t('common.button.edit_save')}</button>
                                {(userId) && <button className="alert button float-right text-uppercase" onClick={() => this.handleClickDelete(user.id)}>{t('common.button.delete')}</button>}
                            </div>
                        </div>


                        {(userId) &&
                            <div>
                                <hr/>
                                <div className="grid-x grid-margin-x grid-padding-x">
                                    <div className="medium-12 cell">
                                        <h3>{t('admin.user.subtitle_role')}</h3>
                                    </div>
                                </div>

                                <div className="grid-x grid-margin-x grid-padding-x">
                                    <RolesUser user={userId} />
                                </div>
                            </div>
                        }

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


const CollectivitesMap = ({collectivites, collectiviteId, isSuperAdmin, handleChangeField}, {t}) => {

    const collectiviteOptions = collectivites && collectivites.map(collectivite =>
        {
            if (collectivite.active) {
                return <option value={collectivite.id} key={collectivite.id}>{collectivite.nom}</option>
            }
        }
    )

    return (
        isSuperAdmin &&
        <div className="medium-6 cell">
            {
                <div>
                    <label>{t('admin.collectivite.name')}
                        <select name="collectivite"
                                value={collectiviteId}
                                onChange={(e) => handleChangeField(e.target.name, e.target.value)}>
                            {collectiviteOptions}
                        </select>
                    </label>
                </div>
            }
        </div>
    )
}

CollectivitesMap.PropTypes = {
    collectivites: array.isRequired,
    collectiviteId: number,
    isSuperAdmin: bool.isRequired,
    handleChangeField: func.isRequired
}

CollectivitesMap.contextTypes = {
    t: func
}