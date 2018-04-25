import React, { Component } from 'react'
import { number, array, func, object } from 'prop-types'
import Debounce from 'debounce'
import { translate } from 'react-i18next'
import History from '../_utils/History'
import { handleErrors } from '../_utils/Utils'
import { ButtonConfirm } from '../_components/Form'
import { GridX, Cell } from '../_components/UI'
import { basicNotification } from '../_components/Notifications'
import { AdminDetailsWithInputField, SimpleContent } from '../_components/AdminUI'

class Group extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
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
        $(document).foundation()
        const { collectiviteId, groupId } = this.props.match.params
        this.setState({collectiviteId})
        if(!!groupId) this.getGroup(groupId)
    }

    handleChangeGroupName = (key, value) => {
        const { group } = this.state
        group[key] = value
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
        const { t, _addNotification } = this.context
        fetch(Routing.generate("sesile_user_userpackapi_remove", {id: this.state.group.id}), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => { _addNotification(basicNotification(
            'success',
            t('admin.group.success_delete')))
            History.push(`/admin/groupes`)})
        .catch(error => _addNotification(basicNotification(
            'error',
            t('admin.error.not_removable', {name:t('admin.group.complet_name'), errorCode: error.status}),
            error.statusText)))
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
    }, 800)

    render() {
        const { t } = this.context
        const { group, inputDisplayed, inputSearchUser, users, value, suggestions } = this.state
        const ListUser = group.users.map((user, key) => 
                        <li key={key}>{user._prenom + ' ' + user._nom} 
                            <a onClick={() => this.handleClickRemoveUser(key)}>X</a>
                        </li>)
        return (
            <AdminDetailsWithInputField className="parameters-user-group" 
                                        title={t('admin.details.title', {name: t('admin.group.complet_name')})} 
                                        subtitle={t('admin.details.subtitle')} 
                                        nom={group.nom} 
                                        inputName="nom"
                                        handleChangeName={this.handleChangeGroupName}
                                        placeholder={t('admin.placeholder.name', {name: t('admin.group.name')})} >
                <SimpleContent>
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
                        <Cell>
                            <GridX className="grid-margin-x">
                                <ButtonConfirm  id="confirm-delete"
                                                className="cell medium-9 text-right"
                                                handleClickConfirm={this.handleClickDelete}
                                                labelButton={t('common.button.delete')}
                                                confirmationText={"Voulez-vous le supprimer ?"}
                                                labelConfirmButton={t('common.button.confirm')}/>
                                <Cell className="medium-3">
                                    <button className="button float-right text-uppercase" 
                                            onClick={() => this.handleClickSave()}>
                                            {(!group.id) ? t('admin.button.save', {name: t('admin.group.name')}) : t('common.button.edit_save')}
                                    </button>        
                                </Cell>
                            </GridX>
                        </Cell>
                    </div>
                </SimpleContent>
            </AdminDetailsWithInputField>
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