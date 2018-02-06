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
        this.setState({collectiviteId: user.collectivite.id})
        this.getListGroupe(user.collectivite.id)
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
            <AdminPage
                title={t('admin.title', {name: t('admin.group.name')})}
                subtitle={t('admin.subtitle')}>
                <AdminContainer>
                    <Cell className="medium-6">
                        <GridX className="grid-padding-x align-center-middle">
                            <Input
                                className="cell medium-auto"
                                labelText={t('admin.group.which_group')}
                                value={this.state.groupName}
                                onChange={this.handleSearchByGroupName}
                                placeholder={t('common.search_by_name')}
                                type="text"/>
                            <Input
                                className="cell medium-auto"
                                labelText={t('admin.user.which_user')}
                                value={this.state.userName}
                                onChange={this.handleSearchByUserName}
                                placeholder={t('common.search_by_name')}
                                type="text"/>
                            {this.state.isSuperAdmin &&
                                <Cell className="medium-auto">
                                    <SelectCollectivite
                                        currentCollectiviteId={this.state.collectiviteId}
                                        handleChange={this.handleChangeCollectivite} />
                                </Cell>}
                        </GridX>
                    </Cell>
                    <AdminList
                        title={t('admin.group.groups_list')}
                        listLength={listFilteredGroups.length}
                        labelButton={t('admin.group.add_group')}
                        addLink={`/admin/${this.state.collectiviteId}/groupe`}
                        headTitles={[t('common.label.name'), t('admin.associated_users'), t('common.label.actions')]}
                        headGrid={['medium-3', 'medium-auto', 'medium-2']}
                        emptyListMessage={t('common.no_results', {name: t('admin.group.name')})}>
                        {listFilteredGroups}
                    </AdminList>
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
    <AdminListRow>
        <Cell className="medium-3">
            <DisplayLongText text={group.nom} maxSize={30}/>
        </Cell>
        <Cell className="medium-auto">
            <DisplayLongText
                text={group.users.map(user => user._nom).join(' | ')}
                title={group.users.map(user => `${user._prenom} ${user._nom}`).join('\n')}
                maxSize={100}/>
        </Cell>
        <Cell className="medium-2">
            <GridX>
                <Cell className="medium-auto">
                    <Link
                        to={`/admin/${collectiviteId}/groupe/${group.id}`}
                        className="fa fa-pencil icon-action"
                        title={t('common.button.edit')}/>
                </Cell>
                <Cell className="medium-auto">
                    <ButtonConfirmDelete
                        id={group.id}
                        dataToggle={`delete-confirmation-update-${group.id}`}
                        onConfirm={handleClickDelete}
                        content={t('common.confirm_deletion_item')}/>
                </Cell>
            </GridX>
        </Cell>
    </AdminListRow>

RowGroup.propTypes = {
    group: object.isRequired,
    collectiviteId: any.isRequired
}

RowGroup.contextTypes = {
    t: func
}