import React, { Component } from 'react'
import { func, object, number, string } from 'prop-types'
import { translate } from 'react-i18next'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import History from '../_utils/History'
import { escapedValue } from '../_utils/Search'
import SelectCollectivite from '../_components/SelectCollectivite'
import { Button, Input } from '../_components/Form'

class CircuitsValidation extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            circuits: [],
            filteredCircuits: [],
            collectivites: [],
            filteredCollectivites: [],
            currentCollectiviteId: '',
            userName: '',
            circuitName: '',
            isSuperAdmin: false,
            newValidationcircuit: ''
        }
    }

    componentDidMount() {
        const user = this.props.user
        this.setState({currentCollectiviteId: user.collectivite.id})

        this.fetchCircuitsValidations(user.collectivite.id)
        if(user.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined) {
            this.fetchCollectivites()
            this.setState({isSuperAdmin: true})
        }
    }

    fetchCollectivites() {
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({collectivites: json}))
    }

    fetchCircuitsValidations(collectiviteId) {
        fetch(Routing.generate('sesile_user_circuitvalidationapi_listbycollectivite', {collectiviteId}) , { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({circuits: json, filteredCircuits: json}))
    }

    handleChangeUserName(userName) {
        this.setState({userName})
        const regex = escapedValue(userName, this.state.filteredCircuits, this.state.circuits)
        const filteredCircuits = this.state.circuits.filter(circuit =>
                                 regex.test(circuit.etape_groupes.map(groupe => groupe.users.map(user => user._nom))))
        this.setState({filteredCircuits})
    }

    handleChangeCircuitName(circuitName) {
        this.setState({circuitName})
        const regex = escapedValue(circuitName, this.state.filteredCircuits, this.state.circuits)
        const filteredCircuits = this.state.circuits.filter(circuit => regex.test(circuit.nom))
        this.setState({filteredCircuits})
    }

    handleChangeCollectivite = (currentCollectiviteId) => {
        this.setState({currentCollectiviteId, userName: '', circuitName: ''}) 
        this.fetchCircuitsValidations(currentCollectiviteId)
    }

    handleAddValidationCircuit = () => {
        const { t, _addNotification} = this.context
        const { currentCollectiviteId, newValidationcircuit } = this.state
        fetch(Routing.generate('sesile_user_circuitvalidationapi_post'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(({
                collectivite: currentCollectiviteId,
                nom: newValidationcircuit
            })) ,
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(groupe => {
                History.push(`/admin/${currentCollectiviteId}/circuit-de-validation/${groupe.id}`)
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_addable', {name:t('admin.circuit.complet_name'), errorCode: error.status}),
                error.statusText)))
    }

    handleChangeValidationCircuit = (name, value) => {
        this.setState({newValidationcircuit: value})
    }

    render () {
        const { t } = this.context
        const { newValidationcircuit, isSuperAdmin, currentCollectiviteId } = this.state
        const listCircuits = this.state.filteredCircuits.map((circuit) =>
            <ValidationCircuitRow  key={circuit.id} circuit={circuit} onClick={this.props.onClick} collectiviteId={currentCollectiviteId} />
        )
        return (
            <div className="circuit-validation">
                <h4 className="text-center text-bold">{t('admin.title', {name: t('admin.circuit.complet_name')})}</h4>
                <p className="text-center">{t('admin.subtitle')}</p>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x">
                            <div className="auto cell">
                                <label htmlFor="name-search-admin">{t('admin.label.which')}</label>
                                <input id="name-search-admin"
                                       value={this.state.circuitName}
                                       onChange={(event) => this.handleChangeCircuitName(event.target.value)}
                                       placeholder={t('admin.placeholder.type_name', {name: 'admin.circuit.name'})}
                                       type="text" />
                            </div>
                            <div className="auto cell">
                                <label htmlFor="user-search-admin">{t('admin.label.who')}</label>
                                <input id="user-search-admin"
                                       value={this.state.userName}
                                       onChange={(event) => this.handleChangeUserName(event.target.value)}
                                       placeholder={t('admin.placeholder.type_user_name')}
                                       type="text" />
                            </div>
                            {isSuperAdmin &&
                                <div className="auto cell">
                                    <SelectCollectivite currentCollectiviteId={currentCollectiviteId} handleChange={this.handleChangeCollectivite} />
                                </div>
                            }
                        </div>
                    </div>
                    <div className="cell medium-10 list-admin">
                        <div className="grid-x grid-padding-x panel">
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-4">{t('admin.circuit.name')}</div>
                                <div className="cell medium-8">{t('admin.associated_users')}</div>
                            </div>
                            <AddValidationCircuitRow newValidationcircuit={newValidationcircuit} addValidationCircuit={ this.handleAddValidationCircuit } changeValidationCircuit={ this.handleChangeValidationCircuit } />
                            {
                                (listCircuits.length > 0) ? listCircuits :
                                <div className="cell medium-12 panel-body">
                                    <div className="text-center">
                                        {t('common.no_results', {name: t('admin.circuit.name')})}
                                    </div>
                                </div>
                            }
                        </div>
                    </div>
                </div>
            </div>
    )
    }
}

CircuitsValidation.PropTypes = {
    onClick: func.isRequired
}

export default translate(['sesile'])(CircuitsValidation)

const ValidationCircuitRow = ({circuit, collectiviteId}) => {
    const arrayNoms = []
    circuit.etape_groupes.map((groupe, index) => groupe.users.map(user => arrayNoms.unshift(user._nom)))
    return (
        <div className="cell medium-12 panel-body grid-x row-admin" onClick={() => History.push(`${collectiviteId}/circuit-de-validation/${circuit.id}`)}>
            <div className="cell medium-4">
                {circuit.nom}
            </div>
            <div className="cell medium-8">
                {arrayNoms.join(' | ')}
            </div>
        </div>
    )
}

ValidationCircuitRow.propTypes = {
    circuit: object.isRequired,
    collectiviteId: number.isRequired
}

const AddValidationCircuitRow = ({ newValidationcircuit, addValidationCircuit, changeValidationCircuit }, {t}) => {

    return (
        <div className="cell medium-12 panel-body grid-x row-admin">
            <Input id="circuit"
                   className="cell medium-4"
                   placeholder={ t('admin.placeholder.add_circuit') }
                   value={ newValidationcircuit }
                   onChange={ changeValidationCircuit }
            />
            <Button id="add-circuit"
                    className="cell medium-8 text-right"
                    classNameButton=""
                    onClick={ addValidationCircuit }
                    disabled={ !newValidationcircuit.length }
                    labelText={t('common.button.add')}
            />
        </div>
    )
}

AddValidationCircuitRow.propTypes = {
    newValidationcircuit: string,
    addValidationCircuit: func,
    changeValidationCircuit: func
}

AddValidationCircuitRow.contextTypes = {
    t: func
}