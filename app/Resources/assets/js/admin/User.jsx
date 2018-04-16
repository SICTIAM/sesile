import React, { Component } from 'react'
import PropTypes, { array, number, bool, func } from 'prop-types'
import { translate } from 'react-i18next'
import { handleErrors } from '../_utils/Utils'
import RolesUser from './RolesUser'
import History from '../_utils/History'
import { basicNotification } from '../_components/Notifications'
import AvatarForm from '../user/AvatarForm'
import SignatureForm from '../user/SignatureForm'
import {Input, Switch, Select, Textarea} from '../_components/Form'

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
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                json.collectivite = json.collectivite.id
                this.setState({user: json})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name'), errorCode: error.status}),
                error.statusText)))
    }

    fetchCollectivites () {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(collectivites => this.setState({collectivites}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.collectivite.name'), errorCode: error.status}),
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
                t('admin.error.not_extractable_list', {name: t('admin.role_application.name', {count: 2}), errorCode: error.status}),
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

    handleChangeUserRole = (key, role) => this.setState(prevState => prevState.user.userrole[key].user_roles = role)
    handleRemoveUserRole = (key) => this.setState(prevState => prevState.user.userrole.splice(key, 1))
    handleAddUserRole = () => this.setState(prevState => prevState.user.userrole.push({user_roles: '', user: this.state.userId}))

    handleClickSave = () => {
        const { user } = this.state
        const { t, _addNotification } = this.context
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
            collectivite: user.collectivite,
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
                if(response.ok === true) {
                    this.fetchUser(id)
                    _addNotification(basicNotification(
                        'success',
                        t('admin.success.update', {name: t('admin.user.name')}),
                        t('admin.user.succes_update', {name: user._prenom + ' ' + user._nom})
                    ))
                }
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name', {count: 2}), errorCode: error.status}),
                error.statusText)))
    }

    handleClickDelete = (id) => {
        const { _addNotification } = this.context
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

    render() {
        const { t } = this.context
        const {user,collectivites}  = this.state
        const roles = this.state.roles
        const userId = this.props.match.params.userId
        const rolesSelect = roles && roles.map((role,key) => <option value={role} key={key}>{role}</option>)

        return (
            <div className="grid-x">
                <div className="admin-details medium-12 cell">
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
                                        user.id &&
                                        <AvatarForm
                                            user={user}
                                            styleClass="medium-6 cell text-center"
                                            tyleClass={"medium-4 cell"}
                                            helpText={t('common.file_acceptation_rules', { types: '(png, jpeg, gif)', sizeMax: '5 Mo'})}/>
                                    }

                                    <div className="medium-6 cell">
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            {
                                                (user.id) &&
                                                <div className="medium-12 cell">
                                                    {user.email}
                                                </div>
                                            }
                                        </div>
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            {
                                                (user.id) &&
                                                <div className="medium-12 cell">
                                                    {user._prenom} {user._nom}
                                                </div>
                                            }
                                        </div>
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            <CollectivitesMap collectivites={collectivites} collectiviteId={user.collectivite} isSuperAdmin={this.state.isSuperAdmin} handleChangeField={this.handleChangeField} />
                                            <Switch id="enabled"
                                                    className="cell medium-4"
                                                    labelText={t('admin.user.placeholder_enable')}
                                                    checked={user.enabled}
                                                    onChange={this.handleChangeField}
                                                    activeText={t('common.label.yes')}
                                                    inactiveText={t('common.label.no')}/>
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
                                        user.id &&
                                        <SignatureForm
                                            user={user}
                                            styleClass="medium-6 cell text-center"
                                            tyleClass={"medium-4 cell"}
                                            helpText={t('common.file_acceptation_rules', { types: '(png, jpeg, gif)', sizeMax: '5 Mo'})}/>
                                    }

                                    <Textarea id="qualite"
                                              name="qualite"
                                              className="cell medium-6"
                                              labelText={t('admin.user.label_quality')}
                                              value={user.qualite || ''}
                                              onChange={this.handleChangeField}/>
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
                                    <Input  id="cp"
                                            className="cell medium-6"
                                            labelText={t('admin.user.label_zip')}
                                            onChange={this.handleChangeField}
                                            value={user.cp || ''}
                                            type="text"
                                    />
                                    <Input  id="ville"
                                            className="cell medium-6"
                                            labelText={t('admin.user.label_city')}
                                            onChange={this.handleChangeField}
                                            value={user.ville || ''}
                                            type="text"
                                    />
                                </div>
                                <div className="grid-x grid-padding-x grid-padding-y">
                                    <Input  id="departement"
                                            className="cell medium-6"
                                            labelText={t('admin.user.label_department')}
                                            onChange={this.handleChangeField}
                                            value={user.departement || ''}
                                            type="text"
                                    />
                                    <Input  id="pays"
                                            className="cell medium-6"
                                            labelText={t('admin.user.label_country')}
                                            onChange={this.handleChangeField}
                                            value={user.pays || ''}
                                            type="text"
                                    />
                                </div>
                            </div>
                        </div>

                        { (userId) &&
                            <div>
                                <hr/>
                                <div className="grid-x grid-margin-x grid-padding-x">
                                    <div className="medium-12 cell">
                                        <h3>{t('admin.user.subtitle_role')}</h3>
                                    </div>
                                </div>

                                <div className="grid-x grid-margin-x grid-padding-x">
                                    <RolesUser roles={ Object.assign([], user.userrole) }
                                               changeUserRole={ this.handleChangeUserRole }
                                               removeUserRole={ this.handleRemoveUserRole }
                                               addUserRole={ this.handleAddUserRole }
                                    />
                                </div>
                            </div>
                        }


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
                                    <Switch id="apiactivated"
                                            className="cell medium-6"
                                            labelText={t('admin.user.label_api_enable')}
                                            checked={user.apiactivated}
                                            onChange={this.handleChangeField}
                                            activeText={t('common.label.yes')}
                                            inactiveText={t('common.label.no')}/>
                                </div>
                                {
                                    (userId) &&
                                    <div className="grid-x grid-padding-x grid-padding-y">
                                        <div className="admin_search_input medium-6 cell">
                                            <label>{t('admin.user.label_api_key')}
                                                <input name="apitoken" value={user.apitoken} />
                                            </label>
                                        </div>
                                        <div className="admin_search_input medium-6 cell">
                                            <label>{t('admin.user.label_api_secret')}
                                                <input name="apisecret" value={user.apisecret} />
                                            </label>
                                        </div>
                                    </div>
                                }

                            </div>
                            <div className="medium-12 cell">
                                <button className="button float-right hollow text-uppercase" onClick={() => this.handleClickSave()}>{(!userId) ? t('common.button.add_user') : t('common.button.edit_save')}</button>
                                {(userId) && <button className="alert button float-right hollow text-uppercase" onClick={() => this.handleClickDelete(user.id)}>{t('common.button.delete')}</button>}
                            </div>
                        </div>

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

    const collectiviteOptions = collectivites && collectivites.filter(collectivite => collectivite.active)
        .map(collectivite => <option value={collectivite.id} key={collectivite.id}>{collectivite.nom}</option>)

    return (
        isSuperAdmin &&
        <Select id="collectivite"
                value={collectiviteId}
                className={"medium-8 cell"}
                label={t('admin.collectivite.name')}
                onChange={handleChangeField}>
            {collectiviteOptions}
        </Select>
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