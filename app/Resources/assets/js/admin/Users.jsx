import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'

import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import ButtonConfirmDelete from '../_components/ButtonConfirmDelete'
import { Input } from '../_components/Form'
import { basicNotification } from '../_components/Notifications'
import { Cell, GridX } from '../_components/UI'
import SelectCollectivite from '../_components/SelectCollectivite'

import { escapedValue } from '../_utils/Search'
import { handleErrors } from '../_utils/Utils'
import History from "../_utils/History";
import ClasseurProgress from "../classeur/ClasseurProgress";

class Users extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        users: [],
        filteredUsers: [],
        collectiviteId: this.props.user.current_org_id,
        fieldSearch: '',
        infos: '',
        isSuperAdmin: false
    }
    componentDidMount() {
        if(this.props.user.roles.includes("ROLE_SUPER_ADMIN")) this.setState({isSuperAdmin: true})
        this.fetchUsers(this.props.user.current_org_id)
    }
    fetchUsers (id) {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_userapi_userscollectivite', {id}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({users: json, filteredUsers: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name: t('admin.user.name', {count: 2}), errorCode: error.status}),
                error.statusText)))
    }
    handleDeleteUser = (id) => {
        const { _addNotification } = this.context
        const id_collectivite = this.state.collectiviteId
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
            this.fetchUsers(id_collectivite)
        })
    }
    handleChangeSearchUser = (key, fieldSearch) => {
        this.setState({fieldSearch})
        const regex = escapedValue(fieldSearch, this.state.filteredUsers, this.state.users)
        const filteredUsers = this.state.users.filter(user => regex.test(user._prenom && user._prenom.concat(user._nom)))
        this.setState({filteredUsers})
    }
    onSearchByCollectiviteFieldChange = (collectiviteId) => {
        this.setState({collectiviteId, fieldSearch: ""})
        this.fetchUsers(collectiviteId)
    }
    render() {
        const { t } = this.context
        const listUser = this.state.filteredUsers.map(filteredUser =>
            <RowUser key={filteredUser.id} User={filteredUser} handleDeleteUser={this.handleDeleteUser} collectiviteId={this.state.collectiviteId}/>)

        return (
            <AdminPage
                title={t('admin.user.name_plural')}>
                <AdminContainer>
                    <div className="grid-x grid-padding-x panel align-center-middle" style={{width:"74em", marginTop:"1em"}}>
                        <div className="grid-x grid-padding-x medium-6 panel align-center-middle" style={{display:"flex", marginBottom:"0em", marginTop:"10px"}}>
                        <div className="" style={{marginTop:"10px",marginLeft:"1%",width:"17em",marginRight:"10px"}}>
                        <Input
                            className=""
                            labelText=""
                            value={this.state.fieldSearch}
                            onChange={this.handleChangeSearchUser}
                            placeholder={t('admin.user.search_by_first_name_and_name')}
                            type="text"/>
                        </div>
                        {this.state.isSuperAdmin &&
                        <div className="" style={{marginTop:"10px",marginLeft:"10px",width:"17em",marginRight:"1%"}}>
                            <SelectCollectivite currentCollectiviteId={this.state.collectiviteId}
                                                handleChange={this.onSearchByCollectiviteFieldChange} />
                        </div>
                        }
                        </div>
                            <table style={{margin:"10px", borderRadius:"6px"}}>
                                <thead>
                                <tr style={{backgroundColor:"#CC0066", color:"white"}}>
                                    <td width="160px" className="text-bold">{ t('admin.user.label_name') }</td>
                                    <td width="160px" className="text-bold">{ t('admin.user.label_firstname') }</td>
                                    <td width="210px" className="text-bold">{  t('admin.user.label_email') }</td>
                                    <td width="30px" className="text-bold">{ t('common.label.actions') }</td>
                                </tr>
                                </thead>
                                <tbody>
                                {listUser.length > 0 ?
                                    listUser :
                                    <tr>
                                        <td>
                                            <span style={{textAlign:"center"}}>{this.props.message}</span>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>}
                                </tbody>
                            </table>
                    </div>
                </AdminContainer>
            </AdminPage>
        )
    }

}

Users.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Users)

const RowUser = ({User, handleDeleteUser, collectiviteId}, {t}) =>
    <tr id="classrow">
        <td onClick={() => History.push(`/admin/${collectiviteId}/utilisateur/${User.id}`)} style={{cursor:"Pointer"}}>
            {User._nom}
        </td>
        <td onClick={() => History.push(`/admin/${collectiviteId}/utilisateur/${User.id}`)} style={{cursor:"Pointer"}}>
            {User._prenom}
        </td>
        <td onClick={() => History.push(`/admin/${collectiviteId}/utilisateur/${User.id}`)} style={{cursor:"Pointer"}}>
            {User.email}
        </td>
        <td>
            <Link
                to={`/admin/${collectiviteId}/classeurs/${User.id}`}
                className="fa fa-copy icon-action text-center"
                title={t('common.classeur', {count: 2})}/>

        </td>
    </tr>

RowUser.PropTypes = {
    User: object.isRequired,
    handleDeleteUser: func.isRequired
}

RowUser.contextTypes = {
    t: func
}