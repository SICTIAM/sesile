import React, { Component }from 'react'
import { object, array, func, any } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'

import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import ButtonConfirmDelete from '../_components/ButtonConfirmDelete'
import { Input } from '../_components/Form'
import { basicNotification } from '../_components/Notifications'
import { Cell, GridX } from '../_components/UI'
import SelectCollectivite from '../_components/SelectCollectivite'

import { escapedValue } from '../_utils/Search'
import { handleErrors, DisplayLongText } from "../_utils/Utils"
import History from "../_utils/History";
import ButtonDropdown from "../_components/ButtonDropdown"

class Groups extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        collectiviteId: null,
        isSuperAdmin: false,
        groups: [],
        filteredGroups: [],
        groupName: '',
        userName: ''
    }
    componentDidMount() {
        const user = this.props.user
        this.setState({collectiviteId: user.current_org_id})
        this.getListGroupe(user.current_org_id)
        if(user.roles.includes("ROLE_SUPER_ADMIN")) this.setState({isSuperAdmin: true})
    }
    handleClickDelete = (id) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate("sesile_user_userpackapi_remove", {id, collectiviteId: this.state.collectiviteId}), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(groups => {
                _addNotification(
                    basicNotification(
                        'success',
                        t('admin.group.success_delete')))
                this.setState({groups, filteredGroups: groups})})
            .catch(() =>
                _addNotification(
                    basicNotification(
                        'error',
                        t('admin.group.error_delete'))))
    }
    getListGroupe(collectiviteId) {
        fetch(
            Routing.generate(
                'sesile_user_userpackapi_getbycollectivite',
                {collectiviteId}),
            {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({groups: json, filteredGroups: json}))
    }
    handleChangeCollectivite = (collectiviteId) => {
        this.setState({collectiviteId, userName: '', groupName: ''})
        this.getListGroupe(collectiviteId)
    }
    handleSearchByUserName = (key, userName) => {
        this.setState({userName})
        const regex = escapedValue(userName, this.state.filteredGroups, this.state.groups)
        const filteredGroups =
            this.state.groups.filter(group => regex.test(group.users.map(user => user._nom)))
        this.setState({filteredGroups})
    }
    handleSearchByGroupName = (key, groupName) => {
        this.setState({groupName})
        const regex = escapedValue(groupName, this.state.filteredGroups, this.state.groups)
        const filteredGroups = this.state.groups.filter(group => regex.test(group.nom))
        this.setState({filteredGroups})
    }
    render() {
        const { t } = this.context
        const listFilteredGroups = this.state.filteredGroups.map((group, key) =>
            <RowGroup
                key={key}
                group={group}
                collectiviteId={this.state.collectiviteId}
                handleClickDelete={this.handleClickDelete} />)
        return (
            <AdminPage>
                <div className="cell medium-12 text-center">
                    <h2>{t('admin.group.name')}</h2>
                </div>
                <AdminContainer>
                    <div className="grid-x grid-padding-x panel align-center-middle" style={{width:"74em", marginTop:"1em"}}>
                        <div className="cell medium-12 grid-x panel align-center-middle"
                             style={{display:"flex", marginBottom:"0em", marginTop:"10px", width:"62%"}}>
                            <div style={{marginTop:"10px", width:"14em"}}>
                                <Input
                                    value={this.state.groupName}
                                    onChange={this.handleSearchByGroupName}
                                    placeholder={t('common.search_by_groupname')}
                                    type="text"/>
                            </div>
                            <div style={{marginTop:"10px", width:"14em", marginRight:"1%", marginLeft: '1%'}}>
                                <Input
                                    value={this.state.userName}
                                    onChange={this.handleSearchByUserName}
                                    placeholder={t('common.search_by_name')}
                                    type="text"/>
                            </div>
                            {this.state.isSuperAdmin &&
                                <div style={{marginTop:"10px", width:"14em"}}>
                                    <SelectCollectivite
                                        currentCollectiviteId={this.state.collectiviteId}
                                        handleChange={this.handleChangeCollectivite} />
                                </div>}
                        </div>
                        <div className="cell medium-12 text-right"  style={{marginTop:"10px"}}>
                            <button
                                className="button hollow"
                                onClick={() => History.push(`/admin/${this.state.collectiviteId}/groupe`)}
                                style={{
                                    border:"1px solid rgb(204, 0, 102)",
                                    color:"rgb(204, 0, 102)"}}>
                                {t('admin.group.add_group')}
                            </button>
                        </div>
                        <table style={{borderRadius:"6px", margin:"10px"}}>
                            <thead>
                            <tr style={{backgroundColor:"#CC0066", color:"white"}}>
                                <td width="240px" className="text-bold">{ t('common.label.name') }</td>
                                <td width="240px" className="text-bold">{ t('admin.associated_users') }</td>
                                <td width="50px" className="text-bold">{  t('common.label.actions') }</td>
                            </tr>
                            </thead>
                            <tbody>
                            {listFilteredGroups.length > 0 ?
                                listFilteredGroups :
                                <tr>
                                    <td>
                                        <span style={{textAlign:"center"}}>{t('common.no_results', {name: t('admin.group.name')})}</span>
                                    </td>
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

Groups.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Groups)

const RowGroup = ({group, collectiviteId, handleClickDelete}, {t}) =>
    <tr>
        <td onClick={() => History.push(`/admin/${collectiviteId}/groupe/${group.id}`)} style={{cursor:"Pointer"}}>
            {group.nom}
        </td>
        <td>
            <ButtonDropdown
                id={group.id}
                dataToggle={`user-list-${group.id}`}
                content={group.users}/>
        </td>
        <td>
            <ButtonConfirmDelete
                id={group.id}
                dataToggle={`delete-confirmation-update-${group.id}`}
                onConfirm={handleClickDelete}
                content={t('common.confirm_deletion_item')}/>
        </td>
    </tr>

RowGroup.propTypes = {
    group: object.isRequired,
    collectiviteId: any.isRequired
}

RowGroup.contextTypes = {
    t: func
}