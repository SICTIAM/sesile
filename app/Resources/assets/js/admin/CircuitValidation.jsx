import React, { Component } from 'react'
import ListCircuitValidation from './ListCircuitValidation'
import DetailsCircuitValidation from './DetailsCircuitValidation'

class CircuitValidation extends Component {

    constructor(props) {
        super(props)
        this.state = {
            circuitDetailsId: null
        }
        this.onListCircuitValidationClick = this.onListCircuitValidationClick.bind(this)
    }

    onListCircuitValidationClick(id) {
        this.setState({circuitDetailsId: id})
    }

    render() {
        const { circuitDetailsId } = this.state
        return (
            <div className="circuit-validation">
                <h4 className="text-center text-bold">Rechercher votre circuit de validation</h4>
                <p className="text-center">Puis accéder aux paramétres</p>
                <ListCircuitValidation onClick={this.onListCircuitValidationClick}/>
                {(circuitDetailsId) && <DetailsCircuitValidation id={circuitDetailsId} />}
            </div>
        )
    }
}

export default CircuitValidation