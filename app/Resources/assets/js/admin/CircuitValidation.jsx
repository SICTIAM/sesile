import React, { Component } from 'react'
import PropTypes from 'prop-types'

class CircuitValidation extends Component {

    constructor(props) {
        super(props)
        this.state = {
            circuits: [],
            filteredCircuits: [],
            user_name: '',
            circuit_name: ''
        }
    }

    componentDidMount() {
        this.getCircuitValidation()
    }

    getCircuitValidation() {
        fetch(Routing.generate('sesile_user_circuitvalidationapi_listbycollectivite') , { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({circuits: json})
                return json
            })
            .then(circuits => this.setState({filteredCircuits: circuits}))
    }

    escapedValue(value) {
        const escapedValue = value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

        if (escapedValue === '') {
            this.setState({filteredCircuits: this.state.circuits})
        }

        return RegExp(escapedValue, 'i');
    }

    onSearchUserNameFieldChange(value) {
        this.setState({user_name:value})
        const regex = this.escapedValue(value)
        const filteredCircuits = this.state.circuits.filter(circuit => regex.test(circuit.etape_groupes.map(groupe => groupe.users.map(user => user._nom))))
        this.setState({filteredCircuits})
    }

    onSearchCircuitNameFieldChange(value) {
        this.setState({circuit_name:value})
        const regex = this.escapedValue(value)
        const filteredCircuits = this.state.circuits.filter(circuit => regex.test(circuit.nom))
        this.setState({filteredCircuits})
    }

    render () {
        const listCircuits = this.state.filteredCircuits.map((circuit) =>
                <RowCircuit  key={circuit.id} circuit={circuit} />
        )
        return (
            <div>
                <div className="grid-x">
                    <div className="medium-3">
                        <label htmlFor="circuit_name_search">Lequel ?</label>
                        <input id="circuit_name_search"
                               value={this.state.circuit_name}
                               onChange={(event) => this.onSearchCircuitNameFieldChange(event.target.value)}
                               placeholder="Entrez le nom du circuit..."
                               type="text" />
                    </div>
                    <div className="divider">&nbsp;</div>
                    <div className="medium-3">
                        <label htmlFor="user_name_search">Avec qui ?</label>
                        <input id="user_name_search"
                               value={this.state.user_name}
                               onChange={(event) => this.onSearchUserNameFieldChange(event.target.value)}
                               placeholder="Entrez le nom d'un utilisateur..."
                               type="text" />
                    </div>
                </div>
                <div className="hover">
                    <table className="unstriped">
                        <thead>
                            <tr>
                                <th width="200">Circuit de validation</th>
                                <th width="300">Utilisateurs associés</th>
                            </tr>
                        </thead>
                        <tbody>
                            {listCircuits}
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
}

const RowCircuit = ({circuit}) => {
    return (
        <tr>
            <td>{circuit.nom}</td>
            <td>
                {
                    circuit.etape_groupes.map((groupe, index) => {
                            if(groupe.users.length > 0){
                                let noms = groupe.users.map((user, index) => index > 0 ? " | " + user._nom : user._nom)
                                noms = index > 0 ? " | " + noms : noms
                                return noms
                            }
                        }
                    )
                }
            </td>
        </tr>
    )
}

RowCircuit.propTypes = {
    circuit: PropTypes.object.isRequired
}

export default CircuitValidation