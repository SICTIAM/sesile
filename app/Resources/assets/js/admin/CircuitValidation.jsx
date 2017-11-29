import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import { arrayMove } from 'react-sortable-hoc'
import { handleErrors } from '../_utils/Utils'
import History from '../_utils/History'
import { basicNotification } from '../_components/Notifications'
import { AdminDetailsWithInputField, SimpleContent } from '../_components/AdminUI'
import { Button, ButtonConfirm } from '../_components/Form'
import { GridX, Cell } from '../_components/UI'
import CircuitValidationSteps from '../circuit/CircuitValidationSteps'

class CircuitValidation extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    state = {
        circuit: {
            id: 0,
            nom: '',
            etape_groupes: [],
            types: []
        },
        classeurTypes: [],
        collectiviteId: ''
    }

    componentDidMount() {
        const { collectiviteId, circuitId } = this.props.match.params
        this.setState({collectiviteId})
        if(circuitId) this.fetchCircuitValidation(circuitId)
        this.fetchClasseurTypes(collectiviteId)
        $("#admin-details-input").foundation()
    }
    
    fetchCircuitValidation(id) {
        fetch(Routing.generate('sesile_user_circuitvalidationapi_getbyid', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({circuit: json}))
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.error.not_extractable_list', {name: this.context.t('admin.circuit.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    fetchClasseurTypes(id) {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({classeurTypes: json}))
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.error.not_extractable_list', {name: this.context.t('admin.type.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    sendCircuitValidation = () => {
        const { circuit } = this.state
        let valid = false 
        
        if(circuit.nom.length > 2 && circuit.types.length > 0 && circuit.etape_groupes.length > 0) {
            valid = circuit.etape_groupes.every(etape_groupe => etape_groupe.users.length > 0 || etape_groupe.user_packs.length > 0)
        } 
        if (valid) {
            const etape_groupes = circuit.etape_groupes
            Object.assign(etape_groupes, circuit.etape_groupes.map(etape_groupe => { return {
                ordre: etape_groupe.ordre,
                users: etape_groupe.users.map(user => user.id),
                user_packs: etape_groupe.user_packs.map(user_pack => user_pack.id),
            }}))
            const fields = {
                nom: circuit.nom,
                collectivite: this.state.collectiviteId,
                types: circuit.types.map(type => type.id),
                etapeGroupes: etape_groupes
            }
            if(circuit.id) this.putCircuitValidation(circuit.id, fields)
            else this.postCircuitValidation(fields)
        } else {
            this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.circuit.not_valid'),
                this.context.t('admin.circuit.validation_conditions'),15))
        }
    }

    postCircuitValidation = (fields) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_circuitvalidationapi_post'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields),
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(json => {
            this.setState({circuit: json})
            _addNotification(basicNotification(
                'success',
                t('admin.success.add', {name:t('admin.circuit.complet_name')}))
            )
            History.push(`/admin/${this.state.collectiviteId}/circuit-de-validation/${json.id}`)
        })
        .catch(error => _addNotification(basicNotification(
            'error',
            t('admin.error.add', {name:t('admin.circuit.complet_name'), errorCode: error.status}),
            error.statusText)))
    }

    putCircuitValidation = (id, fields) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_circuitvalidationapi_update', {id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields),
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then((json) => {
                this.setState({circuit: json})
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.update', {name:t('admin.circuit.complet_name')}))
                )
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_updatable', {name:t('admin.circuit.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    removeCircuitValidation = () => {
        const { id } = this.state.circuit
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_circuitvalidationapi_remove', {id_groupe: id}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.delete', {name:t('admin.circuit.complet_name')}))
                )
                History.push(`/admin/circuits-de-validation`)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name:t('admin.circuit.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    handleChangeClasseurType = (event) => {
        const target = event.target
        const { circuit, classeurTypes } = this.state
        if(target.checked) circuit.types.push(classeurTypes.find(type => type.id == target.id))
        else circuit.types.splice(circuit.types.findIndex(type => type.id == target.id), 1)
        this.setState({circuit})
    }

    handleChangeCircuit = (key, value) => this.setState(prevState => {circuit: prevState.circuit[key] = value})

    handleClickAddStep = () => this.setState(prevState => prevState.circuit.etape_groupes.push({ordre: this.state.circuit.etape_groupes.length, user_packs:[], users:[]}))

    handleClickDeleteStep = (stepKey) => {
        this.setState(prevState => {
            prevState.circuit.etape_groupes.forEach((etape_groupe, key) => {if(key > stepKey) etape_groupe.ordre-- })
            {circuit: prevState.circuit.etape_groupes.splice(stepKey,1)}
        })
    }

    handleClickDeleteUser = (stepKey, userKey) => this.setState(prevState => {circuit: prevState.circuit.etape_groupes[stepKey].users.splice(userKey, 1)})

    handleClickDeleteGroup = (stepKey, groupId) => this.setState(prevState => {circuit: prevState.circuit.etape_groupes[stepKey].user_packs.splice(groupId, 1)})

    addGroup = (stepKey, group) => this.setState(prevState => {circuit: prevState.circuit.etape_groupes[stepKey].user_packs.push(group)})
    
    addUser = (stepKey, user) => this.setState(prevState => {circuit: prevState.circuit.etape_groupes[stepKey].users.push(user)})

    onSortEnd = ({oldIndex, newIndex}) => {
        let { circuit } = this.state
        circuit.etape_groupes = arrayMove(circuit.etape_groupes, oldIndex, newIndex)
        this.setState(prevState => {circuit: prevState.circuit.etape_groupes.forEach((etape_groupe, key) => { etape_groupe.ordre = key })})
        this.setState({circuit})
    }

    render() {
        const { t } = this.context
        const { circuit, collectiviteId } = this.state
        const listClasseurTypes = this.state.classeurTypes.map(classeurType =>  <ClasseurTypeCheckbox   key={classeurType.id}
                                                                                                        classeurType={classeurType}
                                                                                                        circuit={this.state.circuit}
                                                                                                        onChange={this.handleChangeClasseurType}/>)

        return (
            <AdminDetailsWithInputField className="circuit-validation" 
                                        title={t('admin.details.title', {name: t('admin.circuit.complet_name')})} 
                                        subtitle={t('admin.details.subtitle')} 
                                        nom={circuit.nom} 
                                        inputName="nom"
                                        handleChangeName={this.handleChangeCircuit}
                                        placeholder={t('admin.placeholder.name', {name: t('admin.circuit.name')})} >
                <SimpleContent>
                    <GridX>
                        <Cell className="medium-2">
                            <GridX>
                                <Cell className="medium-12">
                                    <span>{t('admin.type.name', {count: 2})}</span>
                                </Cell>
                                <Cell className="medium-12">
                                    {listClasseurTypes}
                                </Cell>
                            </GridX>
                        </Cell>
                        <Cell className="medium-10">
                            <GridX>
                                <Cell className="medium-12">
                                    <span>{t('admin.circuit.complet_name')}</span>
                                </Cell>
                                <Cell className="medium-12">
                                    <CircuitValidationSteps steps={Object.assign([], circuit.etape_groupes)}
                                                            collectiviteId={collectiviteId}
                                                            onSortEnd={this.onSortEnd}
                                                            handleClickDeleteUser={this.handleClickDeleteUser}
                                                            handleClickDeleteGroup={this.handleClickDeleteGroup}
                                                            handleClickDeleteStep={this.handleClickDeleteStep}
                                                            handleClickAddStep={this.handleClickAddStep}
                                                            addUser={this.addUser}
                                                            addGroup={this.addGroup}
                                                            labelButtonAddStep={t('admin.circuit.add_step')}/>
                                </Cell>
                            </GridX>
                        </Cell>
                    </GridX>
                    <GridX className="grid-padding-y">
                        <ButtonConfirm  id="confirm_delete"
                                        className="cell medium-10 text-right"
                                        handleClickConfirm={this.removeCircuitValidation}
                                        labelButton={t('common.button.delete')}
                                        confirmationText={"Voulez-vous le supprimer ?"}
                                        labelConfirmButton={t('common.button.confirm')}/>
                        <Button id="submit-infos"
                                className="cell medium-2 text-right"
                                onClick={this.sendCircuitValidation}
                                labelText={t('common.button.edit_save')}/>
                    </GridX>
                </SimpleContent>
            </AdminDetailsWithInputField>
        )
    }
}

export default translate(['sesile'])(CircuitValidation)

const ClasseurTypeCheckbox = ({classeurType, circuit, onChange}) => {
    const checked = (circuit.types.find(type => classeurType.nom === type.nom) !== undefined) ? true : false
    return (
        <div>
            <input id={classeurType.id} type="checkbox" checked={checked} onChange={event => onChange(event)}/>
            <label htmlFor={classeurType.id}>{classeurType.nom}</label>
        </div>
    )
}

ClasseurTypeCheckbox.Proptypes = {
    classeurType: object.isRequired,
    circuit: object.isRequired,
    onChange: func.isRequired
}
