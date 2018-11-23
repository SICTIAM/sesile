import React, { Component } from 'react'
import {func, object, any, number} from 'prop-types'
import { translate } from 'react-i18next'

import { AdminPage, SimpleContent } from '../_components/AdminUI'
import { basicNotification } from '../_components/Notifications'
import { handleErrors } from '../_utils/Utils'
import History from "../_utils/History"
import {Avatar, Input} from '../_components/Form'
import { escapedValue } from '../_utils/Search'



class AdminDashboard extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func,
        user: object
    }
    state = {
        collectivite: {
            ozwillo: {
                organization_id: ''
            },
            siren: '',
            nom: ' ',
            image: ' '
        },
        users: [],
        filteredUsers: [],
        groups: [],
        filteredGroups: [],
        circuits: [],
        filteredCircuits: [],
        circuitName: '',
        UserName:'',
        groupName:''

    }

    componentDidMount() {
        this.fetchCollectivite(this.context.user.current_org_id)
        this.fetchUsers(this.context.user.current_org_id)
        this.fetchGroups(this.context.user.current_org_id)
        this.fetchCircuitsValidations(this.context.user.current_org_id)
    }

    fetchCollectivite(id) {
        fetch(Routing.generate('sesile_main_collectiviteapi_getbyid', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({collectivite: json}))
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.collectivite.error.fetch', {errorCode: error.status}),
                error.statusText)))
    }

    fetchUsers(id) {
        const {t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_userapi_userscollectivite', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                let users = json.filter(user => user.enabled)
                this.setState({users, filteredUsers: users})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {
                    name: t('admin.user.name', {count: 2}),
                    errorCode: error.status
                }),
                error.statusText)))
    }

    fetchGroups(collectiviteId) {
        const {t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_userpackapi_getbycollectivite', {collectiviteId}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({groups: json, filteredGroups: json}))
    }

    fetchCircuitsValidations(collectiviteId) {
        fetch(
            Routing.generate(
                'sesile_user_circuitvalidationapi_listbycollectivite',
                {collectiviteId}),
            {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({circuits: json, filteredCircuits: json}))
            .catch(() =>
                this.context._addNotification(basicNotification(
                    'error',
                    t('admin.circuit.error_fetch_list'))))
    }

    handleChangeCircuitName = (key, circuitName) => {
        this.setState({circuitName})
        const regex = escapedValue(circuitName, this.state.filteredCircuits, this.state.circuits)
        const filteredCircuits = this.state.circuits.filter(circuit => regex.test(circuit.nom))
        this.setState({filteredCircuits})
    }
    handleChangeGroupName = (key, groupName) => {
        this.setState({groupName})
        const regex = escapedValue(groupName, this.state.filteredGroups, this.state.groups)
        const filteredGroups = this.state.groups.filter(group => regex.test(group.nom))
        this.setState({filteredGroups})
    }
    handleChangeName = (key, UserName) => {
        this.setState({UserName})
        const regex = escapedValue(UserName, this.state.filteredUsers, this.state.users)
        const filteredUsers = this.state.users.filter(user => regex.test(user._nom))
        this.setState({filteredUsers})
    }

    render() {
        const {t} = this.context
        const listUser = this.state.filteredUsers.map(filteredUser =>
            <RowUser
                key={filteredUser.id}
                onClick={this.onClickButtonClasseurList}
                user={filteredUser}
                handleDeleteUser={this.handleDeleteUser}
                collectiviteId={this.state.collectiviteId}/>)
        const listFilteredGroups = this.state.filteredGroups.map((group, key) =>
            <RowGroup
                key={key}
                group={group}
                collectiviteId={this.context.user.current_org_id}/>)
        const listCircuits = this.state.filteredCircuits.map((circuit) =>
            <RowCircuit
                key={circuit.id}
                circuit={circuit}
                collectiviteId={this.context.user.current_org_id}/>)

        return (
            <AdminPage>
                <div className="cell medium-12 text-center" style={{marginBottom: "1.3em"}}>
                    <h2>{this.state.collectivite.nom}</h2>
                </div>
                <div className="admin-content-details panel" style={{height: "15em"}}>
                    <div className="cell">
                        <h3>{t('admin.collectivite.infos')}</h3>
                    </div>
                    <div className="grid-x grid-padding-y" style={{paddingLeft: "1em"}}>
                        <label className="cell medium-2 text-bold text-capitalize-first-letter" htmlFor="nom">
                            {t('common.siren')}
                        </label>
                        <div className="cell medium-6" id="nom">
                            {this.state.collectivite.siren}
                        </div>
                    </div>
                    <div className="grid-x grid-padding-y" style={{paddingLeft: "1em"}}>
                        <label className="cell medium-2 text-bold text-capitalize-first-letter" htmlFor="nom">
                            Ozwillo Id
                        </label>
                        <div className="cell medium-10" id="nom">
                            {this.state.collectivite.ozwillo.organization_id}
                        </div>
                    </div>
                    <div style={{marginTop: "-7em", float:"right"}}>
                        <Avatar className="cell medium-3" size={150} nom={this.state.collectivite.nom}
                                fileName={this.state.collectivite.image ? "/uploads/logo_coll/" + this.state.collectivite.image : null}/>
                    </div>
                </div>
                <SimpleContent className="panel">
                    <div className="cell">
                        <h3>{t('admin.user.name_plural')}</h3>
                    </div>
                    <div style={{width:"12em", float:"right"}}>
                            <Input
                                value={this.state.UserName}
                                onChange={this.handleChangeName}
                                placeholder={t('common.search_by_name')}
                                type="text"/>
                        </div>
                    <div>
                        <table style={{margin: "10px", borderRadius: "6px", width: "98%"}}>
                            <thead>
                            <tr style={{backgroundColor: "#CC0066", color: "white"}}>
                                <td width="160px" className="text-bold">{t('admin.user.label_name')}</td>
                                <td width="160px" className="text-bold">{t('admin.user.label_firstname')}</td>
                                <td width="210px" className="text-bold">{t('admin.user.label_email')}</td>
                                <td width="30px" className="text-bold">{t('common.label.actions')}</td>
                            </tr>
                            </thead>
                            <tbody>
                            {listUser.length > 0 ?
                                listUser :
                                <tr>
                                    <td>
                                        <span
                                            style={{textAlign: "center"}}>{t('common.no_results', {name: t('admin.user.name')})}</span>
                                    </td>
                                    <td/>
                                    <td/>
                                    <td/>
                                </tr>}
                            </tbody>
                        </table>
                    </div>
                </SimpleContent>
                <div style={{display:"flex"}}>
                    <div className="admin-content-details panel" style={{width: '49%', marginRight:'2em'}}>
                        <div className="cell">
                            <h3>{t('admin.group.name_plural')}</h3>
                        </div>
                            <div style={{width:"12em", float:"right"}}>
                                <Input
                                    value={this.state.groupName}
                                    onChange={this.handleChangeGroupName}
                                    placeholder={t('common.search_by_groupname')}
                                    type="text"/>
                            </div>
                        <div>
                            <table style={{margin: "10px", borderRadius: "6px", width: "98%"}}>
                                <thead>
                                <tr style={{backgroundColor: "#CC0066", color: "white"}}>
                                    <td width="240px" className="text-bold">{t('common.label.name')}</td>
                                </tr>
                                </thead>
                                <tbody>
                                {listFilteredGroups.length > 0 ?
                                    listFilteredGroups :
                                    <tr>
                                        <td>
                                            <span
                                                style={{textAlign: "center"}}>{t('common.no_results', {name: t('admin.group.name')})}</span>
                                        </td>
                                    </tr>}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div className="admin-content-details panel" style={{width: '49%'}}>
                        <div className="cell">
                            <h3>{t('admin.circuit.name_plural')}</h3>
                        </div>
                            <div style={{width:"12em", float:"right"}}>
                                <Input
                                    value={this.state.circuitName}
                                    onChange={this.handleChangeCircuitName}
                                    placeholder={t('common.search_by_circuit')}
                                    type="text"/>
                            </div>
                        <div>
                            <table style={{margin: "10px", borderRadius: "6px", width: "98%"}}>
                                <thead>
                                <tr style={{backgroundColor: "#CC0066", color: "white"}}>
                                    <td width="240px" className="text-bold">{t('common.label.name')}</td>
                                </tr>
                                </thead>
                                <tbody>
                                {listCircuits.length > 0 ?
                                    listCircuits :
                                    <tr>
                                        <td>
                                            <span
                                                style={{textAlign: "center"}}>{t('common.no_results', {name: t('admin.circuit.name')})}</span>
                                        </td>
                                    </tr>}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </AdminPage>
        )
    }
}
export default translate(['sesile'])(AdminDashboard)

const RowUser = ({ user, onClick, handleDeleteUser, collectiviteId }, { t }) =>
    <tr onClick={() => History.push(`/admin/${collectiviteId}/utilisateur/${user.id}`)} style={{ cursor: "Pointer" }}>
        <td>
            {user._nom}
        </td>
        <td>
            {user._prenom}
        </td>
        <td>
            {user.email}
        </td>
        <td>
            <span
                onClick={(e) => onClick(e, user)}
                className="fa fa-copy icon-action text-center"
                title={t('common.classeur', { count: 2 })} />

        </td>
    </tr>

RowUser.PropTypes = {
    User: object.isRequired,
    handleDeleteUser: func.isRequired
}

RowUser.contextTypes = {
    t: func
}

const RowGroup = ({ group, collectiviteId, handleClickDelete }, { t }) =>
    <tr>
        <td onClick={() => History.push(`/admin/${collectiviteId}/groupe/${group.id}`)} style={{ cursor: "Pointer" }}>
            {group.nom}
        </td>
    </tr>

RowGroup.propTypes = {
    group: object.isRequired,
    collectiviteId: any.isRequired
}

RowGroup.contextTypes = {
    t: func
}

const RowCircuit = ({circuit, collectiviteId, handleClickDelete}, {t}) => {
    const etapeGroupes = circuit.etape_groupes
    const usersPackName = []
    etapeGroupes.map(etapeGroupe => etapeGroupe.user_packs.map(userPack => usersPackName.unshift(userPack.nom)))
    return (
        <tr id="classrow-dashboard">
            <td onClick={() => History.push(`/admin/${collectiviteId}/circuit-de-validation/${circuit.id}`)} style={{cursor:"Pointer"}}>
                {circuit.nom}
            </td>
        </tr>

    )
}

RowCircuit.propTypes = {
    circuit: object.isRequired,
    collectiviteId: number.isRequired
}

RowCircuit.contextTypes = {
    t: func
}