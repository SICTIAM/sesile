import React, { Component } from 'react'
import PropTypes from 'prop-types'
import UserAvatar from 'react-user-avatar'

class User extends Component {
    constructor(props) {
        super(props)
        this.state = {
            userId: null,
            collectivites: [],
            roles: [],
            isSuperAdmin: true,
            user: {
                id: null,
                _nom: '',
                _prenom: '',
                qualite: '',
                cp: '',
                ville: '',
                departement: '',
                pays: '',
                enabled: false,
                roles: [],
                collectivite: {},
                userrole: {},
                apiactivated: false,
                apitoken: '',
                apisecret: ''
            }
        }
    }


    componentDidMount() {
        this.fetchCollectivites()
        if(this.props.user.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined) {
            this.setState({isSuperAdmin: true})
        }
        this.setState({userId: this.props.match.params.userId})
        this.fetchUser(this.props.match.params.userId)
        this.fetchRoles()
    }

    fetchUser(id) {
        fetch(Routing.generate("sesile_user_userapi_get", {id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(user => {this.setState({user})})
    }

    fetchCollectivites () {
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(collectivites => this.setState({collectivites}))
    }

    fetchRoles () {
        fetch(Routing.generate('sesile_user_userapi_getroles'), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(roles => this.setState({roles}))
    }



    handleChangeField = (field, value) => {
        const { user } = this.state
        user[field] = value
        this.setState({user})
    }

    handleChangeRole = (key, value) => {
        const { user } = this.state
        user.userrole[key].user_roles = value
        user.userrole[key].user = user.id
        this.setState({user})
    }

    handleChangeCheck = (field, value) => {
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
            roles: user.roles
        }

        if (this.state.userId) {
            this.putUser(field, this.state.userId)
            user.userrole.map(role => {
                role.user = user.id
                if (role.id) {
                    this.putUserRole(role, role.id)
                } else {
                    this.postUserRole(role)
                }
            })
        } else {
            this.postUser(field)
        }
    }

    putUser = (user, id) => {
        fetch(Routing.generate("sesile_user_userapi_updateuser", {id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(user),
            credentials: 'same-origin'
        })
            .then(response => {if(response.ok === true) console.log("save !") })
    }

    putAvatar = (image, id) => {
        let formData  = new FormData();

        formData.append('file', image);

        fetch(Routing.generate("sesile_user_userapi_uploadavatar", {id}), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(response => {if(response.ok === true) console.log("Image save !") })
    }

    putUserRole = (userrole, id) => {
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

    postUser = (user) => {
        console.log("nouvelle utilisateur !")
    }

    postUserRole = (userrole) => {
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
                this.fetchUser(this.state.user.id)
            })
    }

    handleClickDelete() {
        console.log("delete")
    }

    handleClickDeleteRole = (key, id) => {
        const newUser = this.state.user
        newUser.userrole.splice(key,1)
        this.setState({user: newUser})
        if (id !== undefined) {
            fetch(Routing.generate("sesile_user_userroleapi_remove", {id}), {
                method: 'DELETE',
                credentials: 'same-origin'
            })
                .then(response => {if(response.ok === true) console.log("Role supprimé")})
        }

    }

    handleClickAddRole() {
        const newRole = {id: null, user_roles: '', user: this.state.user.id}
        const newUser = this.state.user
        newUser.userrole.push(newRole)

        this.setState({user: newUser})
    }

    render() {
        const {user,collectivites}  = this.state

        const collectivitesSelect = collectivites && collectivites.map(collectivite =>
            {
                if (collectivite.active) {
                    return <option value={collectivite.id} key={collectivite.id}>{collectivite.nom}</option>
                }
            }
        )

        const roles = this.state.roles
        const rolesSelect = roles && roles.map((role,key) =>
            {
                return <option value={role} key={key}>{role}</option>
            }
        )

        return (
            <div className="grid-x">
                <div className="admin-details medium-12 cell">
                    <div className="grid-x admin-head-details">
                        {user._prenom} {user._nom} - {user.email}
                        <i className="fi-pencil small medium-6 cell"></i>
                    </div>
                    <div className="admin-content-details">

                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>Informations utilisateurs</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <div className="medium-2 cell">
                                        {
                                            user.path ?
                                                <UserAvatar size="100" name="user" src={"/uploads/avatars/" + user.path} />
                                                : <UserAvatar size="100" name="user" className="txt-avatar" />

                                        }
                                    </div>
                                    <div className="medium-10 cell">
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            <div className="medium-6 cell">
                                                <label>Prénom
                                                    <input name="_prenom" value={user._prenom} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={"Prénom de l'utilisateur"} />
                                                </label>
                                            </div>
                                            <div className="medium-6 cell">
                                                <label>Nom
                                                    <input name="_nom" value={user._nom} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={"Nom de l'utilisateur"} />
                                                </label>
                                            </div>
                                        </div>
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            {
                                                (collectivitesSelect.length > 0 && this.state.isSuperAdmin) &&
                                                <div className="medium-6 cell">
                                                    {
                                                        <div>
                                                            <label>Collectivité
                                                                <select name="collectivite" value={this.state.collectiviteId} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)}>
                                                                    {collectivitesSelect}
                                                                </select>
                                                            </label>
                                                        </div>
                                                    }
                                                </div>
                                            }
                                            <div className="medium-6 cell">
                                                <label>
                                                    Actif
                                                    <input type="checkbox" name="enabled" checked={user.enabled || false} onChange={(e) => this.handleChangeField(e.target.name, e.target.checked)} placeholder={"Nom de l'utilisateur"} />
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
                                <h3>Informations signatures</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <div className="medium-2 cell">
                                        {
                                            user.fileSignature ?
                                                <UserAvatar size="100" name="signature" src={"/uploads/signatures/" + user.fileSignature} />
                                                : <UserAvatar size="100" name="signature" className="txt-avatar" />
                                        }
                                    </div>
                                    <div className="medium-10 cell">
                                        <div className="grid-x grid-padding-x grid-padding-y">
                                            <div className="medium-6 cell">
                                                <label>Qualité
                                                    <textarea name="qualite" value={user.qualite || ''} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={"Qualité de l'utilisateur"} />
                                                </label>
                                            </div>
                                            <div className="medium-6 cell">
                                                <label>Role
                                                    { user.userrole.length > 0 && user.userrole.map((role, key) =>
                                                        (
                                                            <div key={key} className="grid-x">
                                                                <div className="medium-9">
                                                                    <input name={`userrole[${key}].user_roles`} value={role.user_roles} onChange={(e) => this.handleChangeRole(key, e.target.value)} placeholder={"Role de l'utilisateur"} />
                                                                </div>
                                                                <div className="medium-3">
                                                                    <button className="alert button float-right text-uppercase" onClick={() => this.handleClickDeleteRole(key, role.id)}>Supprimer</button>
                                                                </div>
                                                            </div>
                                                        )
                                                    )}
                                                </label>
                                                <div className="grid-x">
                                                    <div className="medium-9">
                                                    </div>
                                                    <div className="medium-3">
                                                        <button className="primary button float-right text-uppercase" onClick={() => this.handleClickAddRole()}>Ajouter</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr/>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>Informations géographiques</h3>
                            </div>
                        </div>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <div className="medium-6 cell">
                                        <label>Code postal
                                            <input name="cp" value={user.cp} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={"Code postal de l'utilisateur"} />
                                        </label>
                                    </div>
                                    <div className="medium-6 cell">
                                        <label>Ville
                                            <input name="ville" value={user.ville} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={"Ville de l'utilisateur"} />
                                        </label>
                                    </div>
                                </div>
                                <div className="grid-x grid-padding-x grid-padding-y">
                                    <div className="medium-6 cell">
                                        <label>Département
                                            <input name="departement" value={user.departement || ''} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={"Département de l'utilisateur"} />
                                        </label>
                                    </div>
                                    <div className="medium-6 cell">
                                        <label>Pays
                                            <input name="pays" value={user.pays || ''} onChange={(e) => this.handleChangeField(e.target.name, e.target.value)} placeholder={"Pays de l'utilisateur"} />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr/>
                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <h3>Informations applications</h3>
                            </div>
                        </div>

                        <div className="grid-x grid-margin-x grid-padding-x">
                            <div className="medium-12 cell">
                                <div className="grid-x grid-padding-x align-center-middle">
                                    <div className="medium-6 cell">
                                        <label>Role application
                                            <select name="roles" value={this.state.user.roles} onChange={(e) => this.handleChangeRoles(e.target.options)} multiple>
                                                {rolesSelect}
                                            </select>
                                        </label>
                                    </div>
                                    <div className="medium-6 cell">
                                        <label>Accès aux API
                                            <input type="checkbox" name="apiactivated" checked={user.apiactivated || false} onChange={(e) => this.handleChangeField(e.target.name, e.target.checked)} placeholder={"Nom de l'utilisateur"} />
                                        </label>
                                    </div>
                                </div>
                                <div className="grid-x grid-padding-x grid-padding-y">
                                    <div className="medium-6 cell">
                                        <label>Clé API
                                            <input name="apitoken" value={user.apitoken} placeholder={"Token API"} />
                                        </label>
                                    </div>
                                    <div className="medium-6 cell">
                                        <label>Secret API
                                            <input name="apisecret" value={user.apisecret} placeholder="Secret API" />
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div className="medium-12 cell">
                                <button className="button float-right text-uppercase" onClick={() => this.handleClickSave()}>{(!user.id) ? "Ajouter l'utilisateur" : "Valider les modifications"}</button>
                                {(user.id) && <button className="alert button float-right text-uppercase" onClick={() => this.handleClickDelete()}>{"Supprimer l'utilisateur"}</button>}
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

export default User