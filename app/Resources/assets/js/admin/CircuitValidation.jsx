import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import { arrayMove } from 'react-sortable-hoc'
import {DisplayLongText, handleErrors} from '../_utils/Utils'
import History from '../_utils/History'
import { basicNotification } from '../_components/Notifications'
import { AdminDetailsWithInputField, SimpleContent } from '../_components/AdminUI'
import { Button } from '../_components/Form'
import { GridX, Cell } from '../_components/UI'
import CircuitValidationSteps from '../circuit/CircuitValidationSteps'
import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import UsersCopy from '../classeur/UsersCopy'

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
            types: [],
            users_copy: []
        },
        classeurTypes: [],
        collectiviteId: ''
    }

    componentDidMount() {
        const {collectiviteId, circuitId} = this.props.match.params
        this.setState({collectiviteId})
        if (circuitId) this.fetchCircuitValidation(circuitId)
        this.fetchClasseurTypes(collectiviteId)
        $("#admin-details-input").foundation()
    }

    fetchCircuitValidation(id) {
        fetch(Routing.generate('sesile_user_circuitvalidationapi_getbyid', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                    this.setState({circuit: json})
                    const usersCopy = this.state.circuit.users_copy.map(user => {
                        return {label: user._prenom + " " + user._nom, value: user.id}
                    })
                    this.handleUsersCopyChange(usersCopy)
                }
            )
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.error.not_extractable_list', {
                    name: this.context.t('admin.circuit.complet_name'),
                    errorCode: error.status
                }),
                error.statusText)))
    }

    fetchClasseurTypes(id) {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({classeurTypes: json}))
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.error.not_extractable_list', {
                    name: this.context.t('admin.type.complet_name'),
                    errorCode: error.status
                }),
                error.statusText)))
    }

    sendCircuitValidation = () => {
        const {circuit} = this.state
        let valid = false

        if (circuit.nom.length > 2 && circuit.types.length > 0 && circuit.etape_groupes.length > 0) {
            valid = circuit.etape_groupes.every(etape_groupe => etape_groupe.users.length > 0 || etape_groupe.user_packs.length > 0)
        }
        if (valid) {
            const etape_groupes = circuit.etape_groupes
            Object.assign(etape_groupes, circuit.etape_groupes.map(etape_groupe => {
                return {
                    ordre: etape_groupe.ordre,
                    users: etape_groupe.users.map(user => user.id),
                    user_packs: etape_groupe.user_packs.map(user_pack => user_pack.id),
                }
            }))
            const fields = {
                nom: circuit.nom,
                collectivite: this.state.collectiviteId,
                types: circuit.types.map(type => type.id),
                etapeGroupes: etape_groupes,
                usersCopy: circuit.users_copy.map(user => user.value)
            }
            if (circuit.id) this.putCircuitValidation(circuit.id, fields)
            else this.postCircuitValidation(fields)
        } else {
            this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.circuit.not_valid'),
                this.context.t('admin.circuit.validation_conditions'), 15))
        }
    }

    postCircuitValidation = (fields) => {
        const {t, _addNotification} = this.context
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
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.circuit.success_save')))
                History.push(`/admin/circuits-de-validation`)
            })
            .catch(() =>
                _addNotification(basicNotification(
                    'error',
                    t('admin.circuit.error_save'))))
    }

    putCircuitValidation = (id, fields) => {
        const {t, _addNotification} = this.context
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
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.circuit.success_save')))
                History.push(`/admin/circuits-de-validation`)
            })
            .catch(() =>
                _addNotification(basicNotification(
                    'error',
                    t('admin.circuit.error_save'))))
    }
    handleChangeClasseurType = (event) => {
        const target = event.target
        const {circuit, classeurTypes} = this.state
        if (target.checked) circuit.types.push(classeurTypes.find(type => type.id == target.id))
        else circuit.types.splice(circuit.types.findIndex(type => type.id == target.id), 1)
        this.setState({circuit})
    }

    handleChangeCircuit = (e) => {
        const newCircuit = this.state.circuit
        newCircuit.nom = e.target.value
        this.setState({circuit: newCircuit})
    }

    handleClickAddStep = () => this.setState(prevState => prevState.circuit.etape_groupes.push({
        ordre: this.state.circuit.etape_groupes.length,
        user_packs: [],
        users: [],
        autoFocus: true
    }))

    handleClickDeleteStep = (stepKey) => {
        this.setState(prevState => {
            prevState.circuit.etape_groupes.forEach((etape_groupe, key) => {
                if (key > stepKey) etape_groupe.ordre--
            })
            {
                circuit: prevState.circuit.etape_groupes.splice(stepKey, 1)
            }
        })
    }

    handleClickDeleteUser = (stepKey, userKey) => this.setState(prevState => {
        circuit: prevState.circuit.etape_groupes[stepKey].users.splice(userKey, 1)
    })

    handleClickDeleteGroup = (stepKey, groupId) => this.setState(prevState => {
        circuit: prevState.circuit.etape_groupes[stepKey].user_packs.splice(groupId, 1)
    })

    addGroup = (stepKey, group) => this.setState(prevState => {
        circuit: prevState.circuit.etape_groupes[stepKey].user_packs.push(group)
    })

    addUser = (stepKey, user) => this.setState(prevState => {
        circuit: prevState.circuit.etape_groupes[stepKey].users.push(user)
    })

    onSortEnd = ({oldIndex, newIndex}) => {
        let {circuit} = this.state
        circuit.etape_groupes = arrayMove(circuit.etape_groupes, oldIndex, newIndex)
        this.setState(prevState => {
            circuit: prevState.circuit.etape_groupes.forEach((etape_groupe, key) => {
                etape_groupe.ordre = key
            })
        })
        this.setState({circuit})
    }
    handleUsersCopyChange = (users_copy) => {
        const {circuit} = this.state
        circuit.users_copy = users_copy
        this.setState({circuit})
    }

    render() {
        const {t} = this.context
        const {circuit} = this.state
        const listClasseurTypes = this.state.classeurTypes.map(classeurType => <ClasseurTypeCheckbox
            key={classeurType.id}
            classeurType={classeurType}
            circuit={this.state.circuit}
            onChange={this.handleChangeClasseurType}/>)


        return (
            <AdminPage>
                <div className="cell medium-12 text-center" style={{marginBottom:"1.3em"}}>
                    <h2>{t('admin.circuit.complet_name')}</h2>
                </div>
                <SimpleContent className="panel">
                    <GridX>
                        <div className="cell medium-12">
                            <label htmlFor="type-list"
                                   className="text-bold text-capitalize">Nom *</label>
                        </div>
                            <input
                                className="cell medium-auto"
                                style={{margin:"0"}}
                                value={circuit.nom}
                                onChange={(e) => this.handleChangeCircuit(e)}
                                placeholder={t('admin.placeholder.name', {name: t('admin.circuit.name')})}
                                type="text"/>
                    </GridX>
                    <GridX>
                        <div className="cell medium-12" style={{marginTop:"1.5em"}}>
                            <GridX>
                                <Cell className="medium-12">
                                    <label htmlFor="type-list"
                                           className="text-bold text-capitalize">{`${t('admin.type.name', {count: 2})} *`}</label>
                                </Cell>
                                <Cell id="type-list" className="medium-12">
                                    {listClasseurTypes}
                                </Cell>
                            </GridX>
                        </div>
                    </GridX>
                    <div className="cell medium-10" style={{marginTop:"1.5em"}}>
                        <GridX>
                            <Cell className="medium-12">
                                <label htmlFor="circuit-validation"
                                       className="text-bold">{`${t('admin.circuit.complet_name')} *`}</label>
                            </Cell>
                            <Cell id="circuit-validation" className="medium-12">
                                <CircuitValidationSteps steps={Object.assign([], circuit.etape_groupes)}
                                                        collectiviteId={this.props.match.params.collectiviteId}
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
                    </div>
                    <GridX className="grid-padding-y">
                        {<UsersCopy
                            handleChange={this.handleUsersCopyChange}
                            className="cell medium-12"
                            users_copy={circuit.users_copy}/>}
                    </GridX>
                </SimpleContent>
                <GridX className="grid-padding-y">
                    <Button
                        id="submit-infos"
                        className="cell medium-12 text-right"
                        onClick={this.sendCircuitValidation}
                        labelText=
                            {this.props.match.params.circuitId ?
                                t('common.button.edit_save') :
                                t('common.button.save')}/>
                </GridX>
            </AdminPage>
        )
    }
}

export default translate(['sesile'])(CircuitValidation)

const ClasseurTypeCheckbox = ({classeurType, circuit, onChange}) => {
    const checked = (circuit.types.find(type => classeurType.nom === type.nom) !== undefined) ? true : false
    return (
            <div className="pretty p-default p-curve p-thick" style={{width:"20%"}}>
                <input id={classeurType.id} type="checkbox" checked={checked} onChange={event => onChange(event)}/>
                <div className="state p-primary-o">
                    <label/>
                    <span> {classeurType.nom}</span>
                </div>
            </div>
    )
}

ClasseurTypeCheckbox.Proptypes = {
    classeurType: object.isRequired,
    circuit: object.isRequired,
    onChange: func.isRequired
}