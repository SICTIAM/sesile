import React, { Component } from 'react'
import PropTypes from 'prop-types'
import History from '../_utils/History'
import { escapedValue } from '../_utils/Search'

const { func, object, array, number } = PropTypes

class CircuitsValidation extends Component {
    constructor(props) {
        super(props)
        this.state = {
            circuits: [],
            filteredCircuits: [],
            collectivites: [],
            currentCollectiviteName: '',
            currentCollectiviteId: '',
            userName: '',
            circuitName: '',
            isSuperAdmin: false
        }
    }

    componentDidMount() {
        const user = this.props.user
        this.setState({currentCollectiviteName: user.collectivite.domain, currentCollectiviteId: user.collectivite.id})

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

    render () {
        const { currentCollectiviteName, collectivites, isSuperAdmin, currentCollectiviteId } = this.state
        const listCircuits = this.state.filteredCircuits.map((circuit) =>
            <ValidationCircuitRow  key={circuit.id} circuit={circuit} onClick={this.props.onClick} collectiviteId={currentCollectiviteId} />
        )
        return (
            <div className="circuit-validation">
                <h4 className="text-center text-bold">Rechercher votre circuit de validation</h4>
                <p className="text-center">Puis accéder aux paramétres</p>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x">
                            <div className="auto cell">
                                <label htmlFor="name-search-admin">Lequel ?</label>
                                <input id="name-search-admin"
                                       value={this.state.circuitName}
                                       onChange={(event) => this.handleChangeCircuitName(event.target.value)}
                                       placeholder="Entrez le nom du circuit..."
                                       type="text" />
                            </div>
                            <div className="auto cell">
                                <label htmlFor="user-search-admin">Avec qui ?</label>
                                <input id="user-search-admin"
                                       value={this.state.userName}
                                       onChange={(event) => this.handleChangeUserName(event.target.value)}
                                       placeholder="Entrez le nom d'un utilisateur..."
                                       type="text" />
                            </div>
                            {isSuperAdmin &&
                                <div className="auto cell">
                                    <label htmlFor="collectivites_select">Quelle collectivité ?</label>
                                    <SelectCollectivite currentCollectivite={currentCollectiviteName} collectivites={collectivites} handleChange={this.handleChangeCollectivite} />
                                </div>
                            }
                        </div>
                    </div>
                    <div className="cell medium-10 list-admin">
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
            </div>
    )
    }
}

CircuitsValidation.PropTypes = {
    onClick: func.isRequired
}

export default CircuitsValidation

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

const SelectCollectivite = ({currentCollectivite, collectivites, handleChange}) => {
    const options = collectivites.map((collectivite, key) => {
        if(collectivite.active) { return <option key={key} value={collectivite.id}>{collectivite.domain}</option> }
    })
    return(
        <select id="collectivites_select" value={currentCollectivite.domain} onChange={(e) => handleChange(e.target.value)} >
            {options}
        </select>
    )
}

SelectCollectivite.PropTypes = {
    collectivites: array.isRequired,
    handleChange: func.isRequired,
    currentCollectivite:object.isRequired
}