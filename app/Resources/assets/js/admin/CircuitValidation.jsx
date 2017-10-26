import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import { SortableContainer, SortableElement, SortableHandle, arrayMove } from 'react-sortable-hoc'
import { AdminDetailsInput, SimpleContent, StepItem } from '../_components/AdminUI'
import { Button } from '../_components/Form'
import SearchUserAndGroup from '../_components/SearchUserAndGroup'

class CircuitValidation extends Component {

    static contextTypes = {
        t: func
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
        fetch(Routing.generate('sesile_user_circuitvalidationapi_getbyid', {id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({circuit: json, etape_groupes: json.etape_groupes, circuitReceived: true}))
    }

    fetchClasseurTypes(id) {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({classeurTypes: json}))
    }

    sendCircuitValidation = () => {
        //TODO: put modification or post new circuit
    }

    putCircuitValidation = () => {
        //TODO: put circuit de validation
    }

    handleChangeClasseurType = (event) => {
        const target = event.target
        const circuit = this.state.circuit
        const types = this.state.classeurTypes
        if(target.checked) circuit.types.push(types.find(type => type.id == target.id))
        else circuit.types.splice(circuit.types.findIndex(type => type.id == target.id), 1)
        this.setState({circuit})
    }

    handleChangeCircuit = (key, value) => {
        const { circuit } = this.state
        circuit[key] = value
        this.setState({circuit})
    }

    handleClickAddStep = () => this.setState(prevState => {circuit: prevState.circuit.etape_groupes.push({id: null, ordre: this.state.circuit.etape_groupes.length, user_packs:[], users:[]})})

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

    onSortEnd = ({oldIndex, newIndex, collection}, e) => {
        let { circuit } = this.state
        
        circuit.etape_groupes = arrayMove(circuit.etape_groupes, oldIndex, newIndex)
        this.setState(prevState => {
            {circuit: prevState.circuit.etape_groupes.forEach((etape_groupe, key) => { etape_groupe.ordre = key })}
        })
        this.setState({circuit})
    }

    render() {
        const { t } = this.context
        const { circuit, circuitReceived, collectiviteId, edited } = this.state
        const listClasseurTypes = this.state.classeurTypes.map(classeurType =>  <ClasseurTypeCheckbox   key={classeurType.id}
                                                                                                        classeurType={classeurType}
                                                                                                        circuit={this.state.circuit}
                                                                                                        onChange={this.handleChangeClasseurType}/>)
        const stepsCircuitValidation = this.state.circuit.etape_groupes.map((step, key) => <CircuitValidationStep   key={key} 
                                                                                                                    stepKey={key}
                                                                                                                    collectiviteId={collectiviteId}
                                                                                                                    step={step}
                                                                                                                    handleClickDeleteUser={this.handleClickDeleteUser}
                                                                                                                    handleClickDeleteGroup={this.handleClickDeleteGroup}
                                                                                                                    addUser={this.addUser}
                                                                                                                    addGroup={this.addGroup}/>)
        
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
                        <div className="medium-2 cell">
                            <span>{t('admin.type.name', {count: 2})}</span>
                        </div>
                        <div className="medium-8 cell">
                            <span>{t('admin.circuit.complet_name')}</span>
                        </div>
                    </div>
                    <div className="grid-x">
                        <div className="medium-2 cell">
                            {listClasseurTypes}
                        </div>
                        <div className="medium-8 cell">
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
                        <Button id="submit-infos"
                                className="cell medium-12"
                                classNameButton="float-right"
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

const DragHandle = SortableHandle(() => <span>::</span>)

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
        const listUsers = step.users.map((user, key) => <li key={key}>{user._prenom + " " + user._nom}<a onClick={e => handleClickDeleteUser(stepKey, key)}>x</a></li>)
        const listGroups = step.user_packs.map((group, key) => <li key={key}>{group.nom}<a onClick={e => handleClickDeleteGroup(stepKey, key)}>x</a></li>)
        return (
            <StepItem   stepKey={stepKey}
                        className="cell medium-3"
                        handleClickDeleteStep={handleClickDeleteStep} 
                        title={step.ordre == 0 ? t('admin.circuit.applicant_step', {ordre:step.ordre + 1}) : t('admin.circuit.validat_step', {ordre:step.ordre + 1})}>
                <ul className="no-bullet">
                    {listUsers.length > 0 && <li><strong className="text-uppercase">{t('admin.user.name', {count: listUsers.length})}</strong></li>}
                    {listUsers}
                    {listGroups.length > 0 && <li><strong className="text-uppercase">{t('admin.group.name', {count: listGroups.length})}</strong></li>}
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