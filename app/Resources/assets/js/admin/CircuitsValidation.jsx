import React, { Component } from 'react'
import { func, object, number } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'


import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import ButtonConfirmDelete from '../_components/ButtonConfirmDelete'
import ButtonPopup from '../_components/ButtonPopup'

import { Input } from '../_components/Form'
import { basicNotification } from '../_components/Notifications'
import { Cell, GridX } from '../_components/UI'
import SelectCollectivite from '../_components/SelectCollectivite'

import { escapedValue } from '../_utils/Search'
import { handleErrors, DisplayLongText } from "../_utils/Utils"
import History from "../_utils/History";

class CircuitsValidation extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        circuits: [],
        filteredCircuits: [],
        collectiviteId: '',
        userName: '',
        circuitName: '',
        isSuperAdmin: false
    }
    componentDidMount() {
        const user = this.props.user
        this.handleChangeCollectivite(user.current_org_id)
        if(user.roles.includes("ROLE_SUPER_ADMIN")) this.setState({isSuperAdmin: true})
    }

    fetchCircuitsValidations(collectiviteId) {
        fetch(
            Routing.generate(
                'sesile_user_circuitvalidationapi_listbycollectivite',
                {collectiviteId}) ,
            {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({circuits: json, filteredCircuits: json}))
            .catch(() =>
                this.context._addNotification(basicNotification(
                    'error',
                    t('admin.circuit.error_fetch_list'))))
    }
    handleClickDelete = (id) => {
        const { t, _addNotification} = this.context
        fetch(
            Routing.generate(
                "sesile_user_circuitvalidationapi_remove",
                {id, collectiviteId: this.state.collectiviteId}),
            {method: 'DELETE', credentials: 'same-origin'})
        .then(handleErrors)
        .then(response => response.json())
        .then(circuits => {
            _addNotification(basicNotification(
                'success',
                t('admin.circuit.success_delete')))
            this.setState({circuits, filteredCircuits: circuits})})
        .catch(() =>
            _addNotification(basicNotification(
                'error',
                t('admin.circuit.error_delete'))))
    }
    handleChangeUserName = (key, userName) => {
        this.setState({userName, filteredCircuits: this.state.circuits})
        let regexName = escapedValue(this.state.circuitName, this.state.filteredCircuits, this.state.circuits)
        let regexUser = escapedValue(userName, this.state.filteredCircuits, this.state.circuits)
        this.handleDualSearch(regexName, regexUser)
    }
    handleChangeCircuitName = (key, circuitName) => {
        this.setState({circuitName, filteredCircuits: this.state.circuits})
        let regexName = escapedValue(circuitName, this.state.filteredCircuits, this.state.circuits)
        let regexUser = escapedValue(this.state.userName, this.state.filteredCircuits, this.state.circuits)
        this.handleDualSearch(regexName, regexUser)
    }
    handleDualSearch = (regexName, regexUser) => {
        let filteredCircuits = this.state.circuits.filter(circuit => regexName.test(circuit.nom) && regexUser.test(circuit.etape_groupes.map(groupe => groupe.users.map(user => user._nom))))
        this.setState({filteredCircuits})
    }
    handleChangeCollectivite = (collectiviteId) => {
        this.setState({collectiviteId, userName: '', circuitName: ''})
        this.fetchCircuitsValidations(collectiviteId)
    }
    render () {
        const { t } = this.context
        const listCircuits = this.state.filteredCircuits.map((circuit) =>
            <RowCircuit
                key={circuit.id}
                circuit={circuit}
                collectiviteId={this.state.collectiviteId}
                handleClickDelete={this.handleClickDelete} />)
        return (
            <AdminPage>
                <div className="cell medium-12 text-center">
                    <h2>{t('admin.title', { name: t('admin.circuit.name') })}</h2>
                </div>
                <AdminContainer>
                    <div className="grid-x grid-padding-x panel align-center-middle" style={{width:"74em", marginTop:"1em"}}>
                        <div className="cell medium-12 grid-x panel align-center-middle"
                             style={{display:"flex", marginBottom:"0em", marginTop:"10px", width:"62%"}}>
                            <div style={{marginTop:"10px",width:"14em",marginRight:"1%"}}>
                                <Input
                                    value={this.state.circuitName}
                                    onChange={this.handleChangeCircuitName}
                                    placeholder={t('common.search_by_circuit')}
                                    type="text"/>
                            </div>
                            <div style={{marginTop:"10px",marginLeft:"1%",width:"14em",marginRight:"1%"}}>
                                <Input
                                    value={this.state.userName}
                                    onChange={this.handleChangeUserName}
                                    placeholder={t('common.search_by_user')}
                                    type="text"/>
                            </div>
                            {this.state.isSuperAdmin &&
                            <div className="" style={{marginTop:"10px",marginLeft:"1%",width:"14em"}}>
                                <SelectCollectivite
                                    currentCollectiviteId={this.state.collectiviteId}
                                    handleChange={this.handleChangeCollectivite} />
                            </div>}
                        </div>
                        <div className="cell medium-12 text-right" style={{marginTop:"10px"}}>
                            <button
                                className="button hollow"
                                onClick={() => History.push(`/admin/${this.state.collectiviteId}/circuit-de-validation`)}
                                style={{
                                    border:"1px solid rgb(204, 0, 102)",
                                    color:"rgb(204, 0, 102)"}}>
                                {t('admin.circuit.add_circuit')}
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
                            {listCircuits.length > 0 ?
                                listCircuits :
                                <tr>
                                    <td>
                                        <span style={{textAlign:"center"}}>{t('common.no_results', {name: t('admin.circuit.name')})}</span>
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

export default translate(['sesile'])(CircuitsValidation)

const RowCircuit = ({circuit, collectiviteId, handleClickDelete}, {t}) => {
    const etapeGroupes = circuit.etape_groupes
    const usersPackName = []
    etapeGroupes.map(etapeGroupe => etapeGroupe.user_packs.map(userPack => usersPackName.unshift(userPack.nom)))
    return (
            <tr id="circuits-row">
                <td onClick={() => History.push(`/admin/${collectiviteId}/circuit-de-validation/${circuit.id}`)} style={{cursor:"Pointer"}}>
                    {circuit.nom}
                </td>
                <td>
                        <ButtonPopup
                            key={circuit.id}
                            id={circuit.nom}
                            dataToggle={`user-list-${circuit.id}`}
                            content={etapeGroupes}/>
                </td>
                <td>
                        <ButtonConfirmDelete
                            id={circuit.id}
                            dataToggle={`delete-confirmation-update-${circuit.id}`}
                            onConfirm={handleClickDelete}
                            content={t('common.confirm_deletion_item')}/>
                </td>
            </tr>

            )
}

RowCircuit.propTypes = {
    circuit: object.isRequired,
    collectiviteId: number.isRequired,
    handleClickDelete: func.isRequired
}

RowCircuit.contextTypes = {
    t: func
}