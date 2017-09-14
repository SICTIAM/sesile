import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Debounce from 'debounce'

const { number, array, func } = PropTypes

class Group extends Component {
    constructor(props) {
        super(props)

        this.state = {
            group: {
                id: null,
                nom: '',
                users: []
            },
            collectivite: null,
            users: [],
            inputDisplayed: false,
            inputSearchUser: ''
        }
    }

    componentDidMount() {
        const { collectiviteId, groupId } = this.props.match.params
        this.setState({collectivite: collectiviteId})
        if(!!groupId) this.getGroup(groupId)
    }

    onChangeGroupName = (value) => {
        const { group } = this.state
        group.nom = value
        this.setState({group})
    }

    handleChangeSearchUser = (value) => {
        this.setState({inputSearchUser: value})
        if(value.trim().length > 2) this.findUser(value, this.state.collectivite)
        else this.setState({users: []})
    }

    onClickUser = (user) => {
        const { group } = this.state
        group.users.push(user)
        this.setState({inputSearchUser: '', users: [], group})
    }

    onClickDelete = (key) => {
        const group = this.state.group
        group.users.splice(key, 1)
        this.setState({group})
    }

    onClickSave = () => {
        const { group, collectivite } = this.state
        const fields = {
            nom: group.nom,
            collectivite: collectivite,
            users: group.users.map(user => user.id)
        }
        if(group.id) this.putGroup(group.id, fields) 
        else this.postGroup(fields)
    }

    postGroup = (fields) => {
        fetch(Routing.generate("sesile_user_userpackapi_postuserpack"), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields)
        })
        .then(response => response.json())
        .then(json => this.setState({group: json}))
    }

    putGroup = (id, fields) => {
        fetch(Routing.generate("sesile_user_userpackapi_updateuserpack", {id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields)
        })
        .then(response => response.json())
        .then(json => this.setState({group: json}))
    }

    addUser = () => {
        this.setState({inputDisplayed: true})
    }

    getGroup(id) {
        fetch(Routing.generate("sesile_user_userpackapi_getbyid", {id}), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => { this.setState({group: json}) })
    }

    findUser = Debounce((value, collectiviteId) => {
        fetch(Routing.generate("sesile_user_userapi_findbynomorprenom", {value,collectiviteId}), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => {
            let users = json
            this.state.group.users.map(userGroup => users = users.filter(user => user.id !== userGroup.id))
            this.setState({users})
        })
    }, 800, true)

    render() {
        const { group, inputDisplayed, inputSearchUser, users, value, suggestions } = this.state
        const ListUser = group.users.map((user, key) => 
                        <li key={key}>{user._prenom + ' ' + user._nom} 
                            <a onClick={() => this.onClickDelete(key)}>X</a>
                        </li>)
        return (
            <div className="parameters-user-group">
                <h4 className="text-center text-bold">Paramètrer votre groupe d'utilisateurs</h4>
                <p className="text-center">Puis sauvegarder</p>
                <div className="details-user-group">
                    <div className="grid-x name-details-user-group">
                        <div className="medium-12 cell">
                            <input value={group.nom} onChange={(e) => this.onChangeGroupName(e.target.value)} placeholder={"Nom du groupe"} />
                            <i className={"fi-pencil small"}></i>
                        </div>
                    </div>
                    <div className="content-details-user-group">
                        <div className="grid-x grid-margin-x">
                            <div className="medium-3 cell">
                                <div className="grid-x list-user-group">
                                    <div className="medium-12 cell name-list-user-group">
                                        Liste des utilisateurs
                                    </div>
                                    <div className="medium-12 cell content-list-user-group">
                                        <ul className="no-bullet">
                                            {ListUser}
                                            {inputDisplayed && 
                                                <div className="autocomplete">
                                                    <input value={inputSearchUser} type={"text"} onChange={(e) => this.handleChangeSearchUser(e.target.value)} className="input-autocomplete"></input>
                                                    {users.length > 0 &&
                                                        <ListSearchUser users={users} onClick={this.onClickUser} />
                                                    }
                                                </div>
                                            }
                                            <li><button className={"btn-add"} type={"button"} onClick={this.addUser}>Ajouter un utilisateur</button></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div className="medium-12 cell">
                                <button className="button float-right text-uppercase" onClick={() => this.onClickSave()}>{(!group.id) ? "Ajouter le groupe" : "Valider les modificatrions"}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

Group.PropTypes = {
    groupId: number.isRequired
}

export default Group

const ListSearchUser = ({ users, onClick }) => {
    const list = users.map((user, key) => <li className="list-group-item" onClick={() => onClick(user)} key={key}>{user._prenom + ' ' + user._nom}</li>)
    return (
        <div className="list-autocomplete">
            <ul className="list-group">
                {list}
            </ul>
        </div>
    )
}

ListSearchUser.PropTypes = {
    users: array.isRequired,
    onClick: func.isRequired
}