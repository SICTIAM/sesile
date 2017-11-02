import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import History from '../_utils/History'
import { SortableContainer, SortableElement, SortableHandle, arrayMove } from 'react-sortable-hoc'
import { AdminDetailsInput, SimpleContent, StepItem } from '../_components/AdminUI'
import { Button } from '../_components/Form'
import SearchUserAndGroup from '../_components/SearchUserAndGroup'

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
        circuitReceived: false,
        collectiviteId: '',
        edited: false
    }

    componentDidMount() {
        const { collectiviteId, circuitId } = this.props.match.params
        this.setState({collectiviteId})
        if(!!circuitId) this.fetchCircuitValidation(circuitId)
        this.fetchClasseurTypes(collectiviteId)
    }
    
    fetchCircuitValidation(id) {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_circuitvalidationapi_getbyid', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({circuit: json, circuitReceived: true}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name:t('admin.circuit.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    fetchClasseurTypes(id) {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({classeurTypes: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name:t('admin.type.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    sendCircuitValidation = () => {
        const { circuit } = this.state
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_circuitvalidationapi_update', {id: circuit.id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nom: circuit.nom
            }),
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then((json) => {
                this.setState({circuit: json, edited:false})
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
        const circuit = this.state.circuit
        const types = this.state.classeurTypes
        if(target.checked) {
            circuit.types.push(types.find(type => type.id == target.id))
            this.addType(circuit.id, target.id)
        }
        else {
            circuit.types.splice(circuit.types.findIndex(type => type.id == target.id), 1)
            this.removeType(circuit.id, target.id)
        }
        this.setState({circuit})
    }

    addType = (circuit_id, type_id) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_circuitvalidationapi_addtypes', {id_type: type_id, id_groupe: circuit_id}), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then((json) => this.setState({circuit: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_addable', {name:t('admin.type.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    removeType = (circuit_id, type_id) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_circuitvalidationapi_removetypes', {id_type: type_id, id_groupe: circuit_id}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then((json) => this.setState({circuit: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name:t('admin.type.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    addEtape = (circuit_id, ordre) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_etapegroupeapi_addetape', {id_groupe: circuit_id, ordre: ordre}), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(() => this.fetchCircuitValidation(circuit_id))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_addable', {name:t('admin.etape.complet_name_plural'), errorCode: error.status}),
                error.statusText)))
    }

    updateEtape = (etape_id, ordre) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_etapegroupeapi_updateetape', {id: etape_id, ordre: ordre}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_updatable', {name:t('admin.etape.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    removeEtape = (circuit_id, etape_id) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_etapegroupeapi_removeetape', {id_groupe: circuit_id, id_etape: etape_id}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then((json) => this.setState({circuit: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name:t('admin.etape.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    handleChangeCircuit = (key, value) => {
        const { circuit } = this.state
        circuit[key] = value
        this.setState({circuit, edited:true})
    }

    handleClickAddStep = () => {
        const { circuit } = this.state
        this.addEtape(circuit.id,circuit.etape_groupes.length)
    }

    handleClickDeleteStep = (stepKey) => {
        const { circuit } = this.state
        this.removeEtape(circuit.id, stepKey)
    }

    handleClickDeleteGroup = (stepId, userPackId) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_etapegroupeapi_removeuserpacketape', {id_etapeGroupe: stepId, id_userPack: userPackId}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then((json) => this.setState({circuit: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name:t('admin.user_pack.name'), errorCode: error.status}),
                error.statusText)))
    }

    addGroup = (stepId, userPack) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_etapegroupeapi_adduserpacketape', {id_etapeGroupe: stepId, id_userPack: userPack.id}), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then((json) => this.setState({circuit: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name:t('admin.user_pack.name'), errorCode: error.status}),
                error.statusText)))
    }

    addUser = (stepId, user) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_etapegroupeapi_adduseretape', {id_etapeGroupe: stepId, id_user: user.id}), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then((json) => this.setState({circuit: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_addable', {name:t('admin.user.name'), errorCode: error.status}),
                error.statusText)))
    }

    handleClickDeleteUser = (stepId, userId) => {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_user_etapegroupeapi_removeuseretape', {id_etapeGroupe: stepId, id_user: userId}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then((json) => this.setState({circuit: json}))
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name:t('admin.user.name'), errorCode: error.status}),
                error.statusText)))
    }

    onSortEnd = ({oldIndex, newIndex}) => {
        let { circuit } = this.state
        
        circuit.etape_groupes = arrayMove(circuit.etape_groupes, oldIndex, newIndex)
        this.setState(prevState => {
            {
                circuit: prevState.circuit.etape_groupes.forEach((etape_groupe, key) => {
                    etape_groupe.ordre = key
                    this.updateEtape(etape_groupe.id, key)
                })
            }
        })
        this.setState({circuit})
    }

    render() {
        const { t } = this.context
        const { circuit, collectiviteId, edited } = this.state
        const listClasseurTypes = this.state.classeurTypes.map(classeurType =>  <ClasseurTypeCheckbox   key={classeurType.id}
                                                                                                        classeurType={classeurType}
                                                                                                        circuit={this.state.circuit}
                                                                                                        onChange={this.handleChangeClasseurType}/>)

        return (
            <AdminDetailsInput  className="circuit-validation" 
                                title={t('admin.details.title', {name: t('admin.circuit.complet_name')})} 
                                subtitle={t('admin.details.subtitle')} 
                                nom={circuit.nom} 
                                inputName="nom"
                                handleChangeName={this.handleChangeCircuit}
                                placeholder={t('admin.placeholder.name', {name: t('admin.circuit.name')})} >
                <SimpleContent>
                    <div className="grid-x">
                        <div className="large-2 medium-12 cell">
                            <div className="grid-x">
                                <div className="medium-12 cell">
                                    <span>{t('admin.type.name', {count: 2})}</span>
                                </div>
                                <div className="medium-12 cell">
                                    {listClasseurTypes}
                                </div>
                            </div>

                        </div>
                        <div className="medium-10 cell">
                            <div className="grid-x">
                                <div className="medium-12">
                                    <span>{t('admin.circuit.complet_name')}</span>
                                </div>
                                <div className="medium-12 cell">
                                    <CircuitValidationStepList  axis="x"
                                                                pressDelay={200}
                                                                pressThreshold={15}
                                                                steps={this.state.circuit.etape_groupes}
                                                                collectiviteId={collectiviteId}
                                                                onSortEnd={this.onSortEnd}
                                                                handleClickDeleteUser={this.handleClickDeleteUser}
                                                                handleClickDeleteGroup={this.handleClickDeleteGroup}
                                                                handleClickDeleteStep={this.handleClickDeleteStep}
                                                                handleClickAddStep={this.handleClickAddStep}
                                                                addUser={this.addUser}
                                                                addGroup={this.addGroup}
                                                                labelButtonAddStep={t('admin.circuit.add_step')}/>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div className="grid-x">


                        <Button id="submit-infos"
                                className="cell medium-8 text-right"
                                classNameButton="alert"
                                onClick={ this.removeCircuitValidation }
                                labelText={t('common.button.delete')}/>

                        <Button id="submit-infos"
                                className="cell medium-4 text-right"
                                classNameButton=""
                                onClick={this.sendCircuitValidation}
                                disabled={!edited}
                                labelText={t('common.button.edit_save')}/>
                    </div>
                </SimpleContent>
            </AdminDetailsInput>
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

const CircuitValidationStepList = SortableContainer(({steps, collectiviteId, handleClickDeleteUser, handleClickDeleteGroup, handleClickDeleteStep, handleClickAddStep, addGroup, addUser, labelButtonAddStep}) => {
    const listStep = steps.map((step, key) => <SortableCircuitValidationStep    key={`item-${key}`}
                                                                                index={key} 
                                                                                stepKey={key}
                                                                                step={step} 
                                                                                collectiviteId={collectiviteId}  
                                                                                handleClickDeleteUser={handleClickDeleteUser}
                                                                                handleClickDeleteGroup={handleClickDeleteGroup}
                                                                                handleClickDeleteStep={handleClickDeleteStep}
                                                                                addUser={addUser}
                                                                                addGroup={addGroup} />)
    return (
        <div className="grid-x grid-margin-x grid-margin-y">
            {listStep}
            <div className="cell medium-3">
                <div className="grid-x step-item">
                    <button className="btn-add" type={"button"} onClick={() => handleClickAddStep()}>{labelButtonAddStep}</button> 
                </div> 
            </div>
        </div>
    )
})

const SortableCircuitValidationStep = SortableElement(({stepKey, step, collectiviteId, handleClickDeleteUser, handleClickDeleteGroup, handleClickDeleteStep, addGroup, addUser}) => {
    return (
        <CircuitValidationStep  key={stepKey} 
                                stepKey={stepKey}
                                collectiviteId={collectiviteId}
                                step={step}
                                handleClickDeleteUser={handleClickDeleteUser}
                                handleClickDeleteGroup={handleClickDeleteGroup}
                                handleClickDeleteStep={handleClickDeleteStep}
                                addUser={addUser}
                                addGroup={addGroup}/>
    )
})

//const DragHandle = SortableHandle(() => <span>::</span>)

class CircuitValidationStep extends Component {
    
    static contextTypes = {
        t: func
    }

    static defaultProps = {
        step : {
            user_packs: [],
            users: []
        }
    }

    state = {
        inputDisplayed: false
    }

    render() {
        const { t } = this.context
        const { stepKey, step, collectiviteId, handleClickDeleteUser, handleClickDeleteGroup, handleClickDeleteStep, addGroup, addUser } = this.props
        const listUsers = step.users && step.users.map((user, key) => <li key={key}>{user._prenom + " " + user._nom}<a onClick={e => handleClickDeleteUser(step.id, user.id)}>x</a></li>)
        const listGroups = step.user_packs && step.user_packs.map((group, key) => <li key={key}>{group.nom}<a onClick={e => handleClickDeleteGroup(step.id, group.id)}>x</a></li>)
        return (
            <StepItem   stepKey={stepKey}
                        stepId={step.id}
                        className="cell medium-3"
                        handleClickDeleteStep={handleClickDeleteStep} 
                        title={step.ordre == 0 ? t('admin.circuit.applicant_step', {ordre:step.ordre + 1}) : t('admin.circuit.validat_step', {ordre:step.ordre + 1})}>
                <ul className="no-bullet">
                    {listUsers && listUsers.length > 0 && <li><strong className="text-uppercase">{t('admin.user.name', {count: listUsers.length})}</strong></li>}
                    {listUsers}
                    {listGroups && listGroups.length > 0 && <li><strong className="text-uppercase">{t('admin.group.name', {count: listGroups.length})}</strong></li>}
                    {listGroups}
                    {this.state.inputDisplayed ?
                        <SearchUserAndGroup placeholder={t('admin.placeholder.type_userName_or_groupName')} addGroup={addGroup} addUser={addUser} stepKey={stepKey} step={step} collectiviteId={collectiviteId} /> :
                        <li><button className="btn-add" type={"button"} onClick={() => this.setState({inputDisplayed: true})}>{t('common.button.add_user')}</button></li>
                    }
                </ul>
            </StepItem>
        )
    }
}