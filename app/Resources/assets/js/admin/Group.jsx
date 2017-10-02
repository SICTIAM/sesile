import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Debounce from 'debounce'
import { translate } from 'react-i18next'
import History from '../_utils/History'

const { number, array, func, object } = PropTypes

class Group extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)

        this.state = {
            group: {
                id: '',
                nom: '',
                users: []
            },
            collectiviteId: '',
            users: [],
            inputDisplayed: false,
            inputSearchUser: ''
        }
    }

    componentDidMount() {
        const { collectiviteId, groupId } = this.props.match.params
        this.setState({collectiviteId})
        if(!!groupId) this.getGroup(groupId)
    }

    handleChangeGroupName = (value) => {
        const { group } = this.state
        group.nom = value
        this.setState({group})
    }

    handleChangeSearchUser = (value) => {
        this.setState({inputSearchUser: value})
        if(value.trim().length > 2) this.findUser(value, this.state.collectiviteId)
        else this.setState({users: []})
    }

    handleClickUser = (user) => {
        const { group } = this.state
        group.users.push(user)
        this.setState({inputSearchUser: '', users: [], group})
    }

    handleClickRemoveUser = (key) => {
        const group = this.state.group
        group.users.splice(key, 1)
        this.setState({group})
    }

    handleClickSave = () => {
        const { group, collectiviteId } = this.state
        const fields = {
            nom: group.nom,
            collectivite: collectiviteId,
            users: group.users.map(user => user.id)
        }
        if(fields.users.length >= 2) {
            if(group.id) this.putGroup(group.id, fields) 
            else this.postGroup(fields)
        }
    }

    handleClickDelete = () => {
        fetch(Routing.generate("sesile_user_userpackapi_remove", {id: this.state.group.id}), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
        .then(response => {if(response.ok === true) History.push(`/admin/groupes`)})
    }

    postGroup = (fields) => {
        fetch(Routing.generate("sesile_user_userpackapi_postuserpack"), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields),
            credentials: 'same-origin'
        })
        .then(response => {if(response.ok === true) History.push(`/admin/groupes`)})
    }

    putGroup = (id, fields) => {
        fetch(Routing.generate("sesile_user_userpackapi_updateuserpack", {id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields),
            credentials: 'same-origin'
        })
        .then(response => {if(response.ok === true) History.push(`/admin/groupes`)})
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
        const { t } = this.context
        const { group, inputDisplayed, inputSearchUser, users, value, suggestions } = this.state
        const ListUser = group.users.map((user, key) => 
                        <li key={key}>{user._prenom + ' ' + user._nom} 
                            <a onClick={() => this.handleClickRemoveUser(key)}>X</a>
                        </li>)
        return (
            <div className="parameters-user-group">
                <h4 className="text-center text-bold">{t('admin.details.title', {name: t('admin.group.complet_name')})}</h4>
                <p className="text-center">{t('admin.details.subtitle')}</p>
                <div className="details-user-group">
                    <div className="grid-x name-details-user-group">
                        <div className="medium-12 cell">
                            <input value={group.nom} onChange={(e) => this.handleChangeGroupName(e.target.value)} placeholder={t('admin.placeholder.add_', {name: t('admin.group.name')})} />
                            <i className={"fi-pencil small"}></i>
                        </div>
                    </div>
                    <div className="content-details-user-group">
                        <div className="grid-x grid-margin-x">
                            <div className="medium-3 cell">
                                <div className="grid-x list-user-group">
                                    <div className="medium-12 cell name-list-user-group">
                                        {t("admin.users_list")}
                                    </div>
                                    <div className="medium-12 cell content-list-user-group">
                                        <ul className="no-bullet">
                                            {ListUser}
                                            {inputDisplayed && 
                                                <div className="autocomplete">
                                                    <input value={inputSearchUser} type={"text"} onChange={(e) => this.handleChangeSearchUser(e.target.value)} className="input-autocomplete"></input>
                                                    {users.length > 0 &&
                                                        <ListSearchUser users={users} onClick={this.handleClickUser} />
                                                    }
                                                </div>
                                            }
                                            <li><button className={"btn-add"} type={"button"} onClick={this.addUser}>{t('common.button.add_user')}</button></li>
                                        </ul>
                                    </div>
                                </div>  
                            </div>
                            <div className="medium-12 cell">
                                <button className="button float-right text-uppercase" onClick={() => this.handleClickSave()}>{(!group.id) ? t('admin.button.save', {name: t('admin.group.name')}) : t('common.button.edit_save')}</button>
                                {(group.id) && <button className="alert button float-right text-uppercase" onClick={() => this.handleClickDelete()}>{t('common.button.delete')}</button>}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

Group.PropTypes = {
    match: object.isRequired
}

export default translate(['sesile'])(Group)

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