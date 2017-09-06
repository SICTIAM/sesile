import React, { Component } from 'react'
import PropTypes from 'prop-types'

class ListCircuitValidation extends Component {
    constructor(props) {
        super(props)
        this.state = {
            circuits: [],
            filteredCircuits: [],
            userName: '',
            circuitName: '',
        }
        this.PropTypes = {
            onClick: PropTypes.func.isRequired
        }
    }

    componentDidMount() {
        this.getCircuitsValidations()
    }

    getCircuitsValidations() {
        fetch(Routing.generate('sesile_user_circuitvalidationapi_listbycollectivite') , { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({circuits: json, filteredCircuits: json}))
    }

    escapedValue(value) {
        const escapedValue = value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

        if (escapedValue === '') {
            this.setState({filteredCircuits: this.state.circuits})
        }

        return RegExp(escapedValue, 'i')
    }

    onSearchUserNameFieldChange(value) {
        this.setState({userName:value})
        const regex = this.escapedValue(value)
        const filteredCircuits = this.state.circuits.filter(circuit =>
                                 regex.test(circuit.etape_groupes.map(groupe => groupe.users.map(user => user._nom))))
        this.setState({filteredCircuits})
    }

    onSearchCircuitNameFieldChange(value) {
        this.setState({circuitName:value})
        const regex = this.escapedValue(value)
        const filteredCircuits = this.state.circuits.filter(circuit => regex.test(circuit.nom))
        this.setState({filteredCircuits})
    }

    render () {
        const listCircuits = this.state.filteredCircuits.map((circuit) =>
            <ValidationCircuitRow  key={circuit.id} circuit={circuit} onClick={this.props.onClick} />
        )
        return (
            <div className="grid-x align-center-middle">
                <div className="cell medium-6">
                    <div className="grid-x grid-padding-x">
                        <div className="medium-6 cell">
                            <label htmlFor="circuit_name_search">Lequel ?</label>
                            <input id="circuit_name_search"
                                   value={this.state.circuitName}
                                   onChange={(event) => this.onSearchCircuitNameFieldChange(event.target.value)}
                                   placeholder="Entrez le nom du circuit..."
                                   type="text" />
                        </div>
                        <div className="medium-6 cell">
                            <label htmlFor="user_name_search">Avec qui ?</label>
                            <input id="user_name_search"
                                   value={this.state.userName}
                                   onChange={(event) => this.onSearchUserNameFieldChange(event.target.value)}
                                   placeholder="Entrez le nom d'un utilisateur..."
                                   type="text" />
                        </div>
                    </div>
                </div>
                <div className="cell medium-10 list-circuit-validation">
                    <div className="grid-x grid-padding-x panel">
                        <div className="cell medium-12 panel-heading grid-x">
                            <div className="cell medium-4">Circuit de validation</div>
                            <div className="cell medium-8">Utilisateurs associés</div>
                        </div>
                        {
                            (listCircuits.length > 0) ? listCircuits :
                            <div className="cell medium-12 panel-body">
                                <div className="text-center">
                                    Aucun circuit ne correspond à votre recherche...
                                </div>
                            </div>
                        }
                    </div>
                </div>
            </div>
    )
    }
}

const ValidationCircuitRow = ({circuit, onClick}) => {
    const arrayNoms = []
    circuit.etape_groupes.map((groupe, index) => groupe.users.map(user => arrayNoms.unshift(user._nom)))
    return (
        <div className="cell medium-12 panel-body grid-x row-circuit" onClick={() => onClick(circuit.id)}>
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
    circuit: PropTypes.object.isRequired,
    onClick: PropTypes.func.isRequired
}

export default ListCircuitValidation