import React, {Component} from 'react'
import PropTypes from 'prop-types'

class DetailsCircuitValidation extends Component {
    constructor(props) {
        super(props)
        this.state = {
            circuit: null,
            classeurTypes: [],
            circuitReceived: false
        }
        this.PropTypes = {
            id: PropTypes.number.isRequired
        }
        this.handleClasseurTypeCheckboxChange = this.handleClasseurTypeCheckboxChange.bind(this)
    }

    componentWillReceiveProps(nextProps) {
        this.getCircuitValidation(nextProps.id)
    }

    componentDidMount() {
        this.getCircuitValidation(this.props.id)
        this.getClasseurTypes()
    }

    getCircuitValidation(id) {
        fetch(Routing.generate('sesile_user_circuitvalidationapi_getbyid', {id: id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({circuit: json, circuitReceived: true}))
    }

    getClasseurTypes() {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall'), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({classeurTypes: json}))
    }

    handleClasseurTypeCheckboxChange(event) {
        const target = event.target
        const circuit = this.state.circuit
        const types = this.state.classeurTypes
        if(target.checked) circuit.types.push(types.find(type => type.id == target.id))
        else circuit.types.splice(circuit.types.findIndex(type => type.id == target.id), 1)
        this.setState({circuit})
    }

    render() {
        const circuit = this.state.circuit
        const listClasseurTypes = this.state.classeurTypes.map(classeurType =>
            <ClasseurTypeCheckbox key={classeurType.id}
                                   classeurType={classeurType}
                                   circuit={this.state.circuit}
                                   onChange={this.handleClasseurTypeCheckboxChange}/>)
        return (
            this.state.circuitReceived &&
            <div className="details-circuit-validation">
                <div className="grid-x name-details-circuit-validation">
                    <div className="medium-12 cell">
                        {circuit.nom}
                    </div>
                </div>
                <div className="content-details-circuit-validation">
                    <div className="grid-x">
                        <div className="medium-2 cell">
                            <span>Type de classeur</span>
                        </div>
                        <div className="medium-8 cell">
                            <span>Circuit de validation</span>
                        </div>
                    </div>
                    <div className="grid-x">
                        <div className="medium-2 cell">
                            {listClasseurTypes}
                        </div>
                        <div className="medium-8 cell">
                            <StepsCircuitValidation circuit={circuit} />
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

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
    classeurTypes: PropTypes.array.isRequired,
    circuit: PropTypes.object.isRequired,
    onChange: PropTypes.func.isRequired
}

const StepsCircuitValidation = ({circuit}) => {
    const steps = circuit.etape_groupes.map(groupStep => <CircuitValidation key={groupStep.id} groupStep={groupStep}/>)
    return (
        <div className="grid-x grid-margin-x">
            {steps}
        </div>
    )
}

StepsCircuitValidation.Proptypes = {
    circuit: PropTypes.object.isRequired
}

const CircuitValidation = ({groupStep}) => {
    const users = groupStep.users.map(user => <li key={user.id}>{user._prenom + " " + user._nom}<a>x</a></li>)
    const usersGroups = groupStep.user_packs.map(group => group.users.map(user => <li key={user.id}>{user._prenom + " " + user._nom}<a>x</a></li>))
    return (
        <div className="medium-3 cell">
            <div className="grid-x step-circuit-validation">
                <div className="medium-12 cell name-step-circuit-validation">
                    Etape {groupStep.ordre + 1 + " - ".concat(groupStep.ordre == 0 ? 'Déposante':'Validante')}
                </div>
                <div className="medium-12 cell content-step-circuit-validation">
                    <ul className="no-bullet">
                        {users}
                        {usersGroups}
                    </ul>
                </div>
            </div>
        </div>
    )
}

CircuitValidation.Proptypes = {
    groupStep: PropTypes.object.isRequired
}

export default DetailsCircuitValidation