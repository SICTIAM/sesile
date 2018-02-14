import React, { Component } from 'react'
import { func, object, number } from 'prop-types'
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
        this.handleChangeCollectivite(user.collectivite.id)
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
        this.setState({userName})
        const regex = escapedValue(userName, this.state.filteredCircuits, this.state.circuits)
        let filteredCircuits = this.state.circuits.filter(circuit =>
                                 regex.test(circuit.etape_groupes.map(groupe => groupe.users.map(user => user._nom))))
        this.setState({filteredCircuits})
    }
    handleChangeCircuitName = (key, circuitName) => {
        this.setState({circuitName})
        const regex = escapedValue(circuitName, this.state.filteredCircuits, this.state.circuits)
        const filteredCircuits = this.state.circuits.filter(circuit => regex.test(circuit.nom))
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
            <AdminPage
                title={t('admin.title', {name: t('admin.circuit.name')})}
                subtitle={t('admin.subtitle')}>
                <AdminContainer>
                    <Cell className="medium-6">
                        <GridX className="grid-padding-x align-center-middle">
                            <Input
                                className="cell medium-auto"
                                labelText={t('admin.circuit.which_circuit')}
                                value={this.state.circuitName}
                                onChange={this.handleChangeCircuitName}
                                placeholder={t('common.search_by_name')}
                                type="text"/>
                            <Input
                                className="cell medium-auto"
                                labelText={t('admin.user.which_user')}
                                value={this.state.userName}
                                onChange={this.handleChangeUserName}
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
                        title={t('admin.circuit.circuit_list')}
                        listLength={listCircuits.length}
                        labelButton={t('admin.circuit.add_circuit')}
                        addLink={`/admin/${this.state.collectiviteId}/circuit-de-validation`}
                        headTitles={[t('common.label.name'), t('admin.associated_users'), t('admin.associated_group'), t('common.label.actions')]}
                        headGrid={['medium-2', 'medium-auto', 'medium-auto', 'medium-2']}
                        emptyListMessage={t('common.no_results', {name: t('admin.circuit.name')})}>
                        {listCircuits}
                    </AdminList>
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
        <AdminListRow>
            <Cell className="medium-2">
                <DisplayLongText text={circuit.nom} maxSize={20}/>
            </Cell>
            <Cell className="medium-auto">
                <DisplayLongText
                    text=
                        {etapeGroupes.map(etapeGroupe =>
                            etapeGroupe.users.map(user => user._nom).join(' | ')).join(' | ')}
                    title=
                        {etapeGroupes.map(etapeGroupe =>
                            etapeGroupe.users.map(user =>
                                `${user._prenom} ${user._nom}`).join('\n')).join('\n______\n')}
                    maxSize={30}/>
            </Cell>
            <Cell className="medium-auto">
                <DisplayLongText
                    text={usersPackName.join(' | ')}
                    title={usersPackName.join('\n')}
                    maxSize={30}/>
            </Cell>
            <Cell className="medium-2">
                <GridX>
                    <Cell className="medium-auto">
                        <Link
                            to={`/admin/${collectiviteId}/circuit-de-validation/${circuit.id}`}
                            className="fa fa-pencil icon-action"
                            title={t('common.button.edit')}/>
                    </Cell>
                    <Cell className="medium-auto">
                        <ButtonConfirmDelete
                            id={circuit.id}
                            dataToggle={`delete-confirmation-update-${circuit.id}`}
                            onConfirm={handleClickDelete}
                            content={t('common.confirm_deletion_item')}/>
                    </Cell>
                </GridX>
            </Cell>
        </AdminListRow>
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